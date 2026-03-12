<?php
/* this is the main file this module it was developed by Simnko Jewelery fully iced */
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly.");
}

function lenco_MetaData()
{
    return [
        'DisplayName' => 'Lenco Dev By Simnko',
        'APIVersion' => '1.0',
    ];
}
/*lENCO Dev by Simnko SALT SAANA */
function lenco_config()
{
    return [
        "FriendlyName" => [
            "Type" => "System",
            "Value" => "Lenco Dev By Simnko",
        ],
        "publicKey" => [
            "FriendlyName" => "Lenco Public Key",
            "Type" => "text",
            "Size" => "50",
            "Default" => "",
            "Description" => "Enter your Lenco public key here",
        ],
        "secretKey" => [
            "FriendlyName" => "Lenco Secret Key",
            "Type" => "password",
            "Size" => "50",
            "Default" => "",
            "Description" => "Enter your Lenco secret key here",
        ],
        "exchangeRate" => [
            "FriendlyName" => "USD to ZMW Exchange Rate",
            "Type" => "text",
            "Size" => "10",
            "Default" => "28.50",
            "Description" => "Enter the current exchange rate for USD to ZMW.",
        ],
    ];
}

function lenco_link($params)
{
    $publicKey = $params['publicKey'];
    $invoiceId = $params['invoiceid'];
    $amountUSD = $params['amount'];
    $currency = $params['currency'];
    $email = $params['clientdetails']['email'];
    $phone = $params['clientdetails']['phonenumber'];
    $callbackUrl = $params['systemurl'] . "/modules/gateways/callback/lenco.php?invoiceid=" . $invoiceId;

    $exchangeRate = floatval($params['exchangeRate']);
    $amountZMW = $amountUSD * $exchangeRate;

    $htmlOutput = <<<HTML
        <script src="https://pay.lenco.co/js/v1/inline.js"></script>
        <button type="button" onclick="getPaidWithLenco()">Pay Now</button>
        <script>
            function getPaidWithLenco() {
                LencoPay.getPaid({
                    key: '$publicKey',
                    reference: 'ref-' + Date.now(),
                    email: '$email',
                    amount: $amountZMW,
                    currency: "ZMW",
                    customer: {
                        firstName: '{$params['clientdetails']['firstname']}',
                        lastName: '{$params['clientdetails']['lastname']}',
                        phone: '$phone',
                    },
                    onSuccess: function(response) {
                        window.location = '$callbackUrl&reference=' + response.reference;
                    },
                    onClose: function() {
                        alert('Payment was not completed.');
                    }
                });
            }
        </script>
    HTML;

    return $htmlOutput;
}
