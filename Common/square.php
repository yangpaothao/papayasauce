<?php
namespace Square;
require 'square/vendor/autoload.php';


use Square\SquareClient;
use Square\Environments;
use Square\Payments\Requests\CreatePaymentRequest;
use Square\Types\Money;
use Square\Types\Currency;
//https://github.com/square/square-php-sdk
$client = new SquareClient(
    token: 'EAAAl0Bek-ZGWHri7ZovCdT_MBLdCx8v8RI7x1AbjfVtpTv_qZWuQR5zqsfc4iPX',
    options: ['baseUrl' => Environments::Sandbox->value]
);
$client->payments->create(
    new CreatePaymentRequest([
        'amountMoney' => new Money([
            'amount' => 100,
            'currency' => Currency::Usd->value
        ]),
        'idempotencyKey' => '86c6589c-d150-4986-acff-d313425c067a',
        'sourceId' => 'cnon:card-nonce-ok',
    ]),
);
?>