<?php
namespace PapayasauceClasses;
/**
 * FedEx OAuth2 Authenticator
 * ----------------------------
 * Handles retrieving (and caching) an access token from FedEx's OAuth2 endpoint.
 *
 * SETUP:
 * 1. Register at https://developer.fedex.com and create a project to get:
 *    - Client ID (API Key)
 *    - Client Secret (Secret Key)
 * 2. Use https://apis-sandbox.fedex.com while testing.
 *    Switch to https://apis.fedex.com for production.
 */

class FedExAuthException extends Exception {}

class FedExOAuth
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;

    private ?string $accessToken = null;
    private ?int $expiresAt = null; // unix timestamp

    public function __construct(string $clientId, string $clientSecret, string $baseUrl = 'https://apis-sandbox.fedex.com')
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl      = rtrim($baseUrl, '/');
    }

    /**
     * Returns a valid access token, reusing the cached one until it's close to expiring.
     */
    public function getAccessToken(): string
    {
        if ($this->accessToken !== null && $this->expiresAt !== null && time() < $this->expiresAt) {
            return $this->accessToken;
        }

        return $this->fetchNewToken();
    }

    /**
     * Forces a fresh token request to FedEx, regardless of any cached token.
     */
    public function fetchNewToken(): string
    {
        $ch = curl_init($this->baseUrl . '/oauth/token');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new FedExAuthException("cURL error requesting FedEx token: $error");
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || empty($data['access_token'])) {
            $message = $data['errors'][0]['message'] ?? $response;
            throw new FedExAuthException("FedEx OAuth failed (HTTP $httpCode): $message");
        }

        $this->accessToken = $data['access_token'];

        // FedEx returns 'expires_in' in seconds (typically 3600). Subtract a small
        // buffer so we refresh slightly before it actually expires.
        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        $this->expiresAt = time() + $expiresIn - 60;

        return $this->accessToken;
    }

    /**
     * Returns the unix timestamp when the current cached token expires, or null if none cached.
     */
    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }
}

// ============================
// USAGE EXAMPLE
// ============================
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    try {
        $auth = new FedExOAuth(
            clientId: 'YOUR_FEDEX_API_KEY',
            clientSecret: 'YOUR_FEDEX_SECRET_KEY',
            baseUrl: 'https://apis-sandbox.fedex.com' // switch to https://apis.fedex.com for production
        );

        $token = $auth->getAccessToken();

        echo "Access token: $token\n";
        echo "Expires at: " . date('Y-m-d H:i:s', $auth->getExpiresAt()) . "\n";

    } catch (FedExAuthException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage() . "\n";
    }
}
