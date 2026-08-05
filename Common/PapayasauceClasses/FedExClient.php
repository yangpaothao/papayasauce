<?php
namespace PapayasauceClasses;
/**
 * FedEx Shipping Label Generator (OOP)
 * -------------------------------------
 * Uses FedEx's REST API (OAuth2 + Ship API v1).
 *
 * SETUP:
 * 1. Register at https://developer.fedex.com and create a project.
 * 2. Get your API Key (Client ID), Secret Key (Client Secret), and Account Number.
 * 3. Test against sandbox first: https://apis-sandbox.fedex.com
 *    Switch to https://apis.fedex.com once approved for production.
 * 4. Don't hardcode credentials in a publicly-served directory — use env vars.
 */

class FedExException extends Exception {}

/**
 * Handles OAuth2 authentication and raw API communication with FedEx.
 */
class FedExClient
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private ?string $accessToken = null;

    public function __construct(string $clientId, string $clientSecret, string $baseUrl)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl      = rtrim($baseUrl, '/');
    }

    /**
     * Fetches (and caches) an OAuth2 access token.
     */
    public function getAccessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $response = $this->request(
            'POST',
            '/oauth/token',
            http_build_query([
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]),
            ['Content-Type: application/x-www-form-urlencoded'],
            authenticated: false
        );

        if (empty($response['access_token'])) {
            throw new FedExException('FedEx auth failed: ' . json_encode($response));
        }

        return $this->accessToken = $response['access_token'];
    }

    /**
     * Sends a JSON POST request to a FedEx API endpoint, attaching the bearer token.
     */
    public function postJson(string $endpoint, array $payload): array
    {
        $token = $this->getAccessToken();

        return $this->request(
            'POST',
            $endpoint,
            json_encode($payload),
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'X-locale: en_US',
            ]
        );
    }

    /**
     * Low-level cURL wrapper shared by auth and API calls.
     */
    private function request(string $method, string $endpoint, string $body, array $headers, bool $authenticated = true): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new FedExException("cURL error calling $endpoint: $error");
        }

        $data = json_decode($response, true) ?? [];

        if ($httpCode < 200 || $httpCode >= 300) {
            $message = $data['errors'][0]['message'] ?? $response;
            throw new FedExException("FedEx API error at $endpoint (HTTP $httpCode): $message");
        }

        return $data;
    }
}

/**
 * Simple value object for an address (shipper or recipient).
 */
class FedExAddress
{
    public function __construct(
        public string $personName,
        public string $phoneNumber,
        public string $streetLine,
        public string $city,
        public string $stateOrProvinceCode,
        public string $postalCode,
        public string $countryCode = 'US',
        public ?string $companyName = null,
    ) {}

    public function toArray(): array
    {
        $contact = [
            'personName'  => $this->personName,
            'phoneNumber' => $this->phoneNumber,
        ];

        if ($this->companyName) {
            $contact['companyName'] = $this->companyName;
        }

        return [
            'contact' => $contact,
            'address' => [
                'streetLines'         => [$this->streetLine],
                'city'                => $this->city,
                'stateOrProvinceCode' => $this->stateOrProvinceCode,
                'postalCode'          => $this->postalCode,
                'countryCode'         => $this->countryCode,
            ],
        ];
    }
}

/**
 * Represents a single package to ship.
 */
class FedExPackage
{
    public function __construct(
        public float $weightLbs,
    ) {}

    public function toArray(): array
    {
        return [
            'weight' => [
                'units' => 'LB',
                'value' => $this->weightLbs,
            ],
        ];
    }
}

/**
 * Builds and creates a shipment (label) against the FedEx Ship API.
 */
class FedExShipment
{
    private FedExClient $client;
    private string $accountNumber;

    public function __construct(FedExClient $client, string $accountNumber)
    {
        $this->client        = $client;
        $this->accountNumber = $accountNumber;
    }

    /**
     * Creates the shipment and returns the raw FedEx API response.
     *
     * @param FedExPackage[] $packages
     */
    public function create(
        FedExAddress $shipper,
        FedExAddress $recipient,
        array $packages,
        string $serviceType = 'FEDEX_GROUND',
        string $packagingType = 'YOUR_PACKAGING',
        string $labelImageType = 'PDF'
    ): array {
        $payload = [
            'labelResponseOptions' => 'URL_ONLY',
            'accountNumber' => [
                'value' => $this->accountNumber,
            ],
            'requestedShipment' => [
                'shipper'    => $shipper->toArray(),
                'recipients' => [$recipient->toArray()],
                'shipDatestamp' => date('Y-m-d'),
                'serviceType'   => $serviceType,
                'packagingType' => $packagingType,
                'pickupType'    => 'DROPOFF_AT_FEDEX_LOCATION',
                'blockInsightVisibility' => false,
                'shippingChargesPayment' => [
                    'paymentType' => 'SENDER',
                    'payor' => [
                        'responsibleParty' => [
                            'accountNumber' => ['value' => $this->accountNumber],
                        ],
                    ],
                ],
                'labelSpecification' => [
                    'imageType'      => $labelImageType,
                    'labelStockType' => 'PAPER_85X11_TOP_HALF_LABEL',
                ],
                'requestedPackageLineItems' => array_map(
                    fn(FedExPackage $pkg) => $pkg->toArray(),
                    $packages
                ),
            ],
        ];

        return $this->client->postJson('/ship/v1/shipments', $payload);
    }

    /**
     * Extracts the tracking number from a create() response.
     */
    public function getTrackingNumber(array $response): ?string
    {
        return $response['output']['transactionShipments'][0]['pieceResponses'][0]['trackingNumber'] ?? null;
    }

    /**
     * Extracts and decodes the base64 label, saving it to disk.
     */
    public function saveLabel(array $response, string $outputPath): void
    {
        $encodedLabel = $response['output']['transactionShipments'][0]['pieceResponses'][0]['packageDocuments'][0]['encodedLabel'] ?? null;

        if (!$encodedLabel) {
            throw new FedExException('No label found in FedEx response: ' . json_encode($response));
        }

        file_put_contents($outputPath, base64_decode($encodedLabel));
    }
}

// ============================
// USAGE EXAMPLE
// ============================
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    try {
        $client = new FedExClient(
            clientId: 'YOUR_FEDEX_API_KEY',
            clientSecret: 'YOUR_FEDEX_SECRET_KEY',
            baseUrl: 'https://apis-sandbox.fedex.com' // switch to https://apis.fedex.com for production
        );

        $shipment = new FedExShipment($client, accountNumber: 'YOUR_FEDEX_ACCOUNT_NUMBER');

        $shipper = new FedExAddress(
            personName: 'Jane Doe',
            phoneNumber: '5555551234',
            streetLine: '123 Main St',
            city: 'Memphis',
            stateOrProvinceCode: 'TN',
            postalCode: '38116',
            companyName: 'My Company LLC'
        );

        $recipient = new FedExAddress(
            personName: 'John Smith',
            phoneNumber: '5555556789',
            streetLine: '456 Oak Ave',
            city: 'Dallas',
            stateOrProvinceCode: 'TX',
            postalCode: '75201'
        );

        $packages = [
            new FedExPackage(weightLbs: 5),
        ];

        $response = $shipment->create($shipper, $recipient, $packages);

        $outputPath = __DIR__ . '/label_' . time() . '.pdf';
        $shipment->saveLabel($response, $outputPath);

        echo "Label created successfully!\n";
        echo "Tracking number: " . $shipment->getTrackingNumber($response) . "\n";
        echo "Saved to: $outputPath\n";

    } catch (FedExException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage() . "\n";
    }
}