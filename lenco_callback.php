<?php
/* Lenco dev by SimnkoHost Solutions for webhosting you can visit https://simnkohost.net */
require_once "../../../init.php";
require_once "../../../includes/gatewayfunctions.php";
require_once "../../../includes/invoicefunctions.php";

$gatewayModuleName = "lenco";
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (!$gatewayParams["type"]) {
    die("Module not activated.");
}

$invoiceId = $_GET['invoiceid'] ?? '';
$reference = $_GET['reference'] ?? '';

if (!$invoiceId || !$reference) {
    die("Invalid request.");
}
/*this is the callback file */
$secretKey = $gatewayParams['secretKey'];
$apiUrl = "https://api.lenco.co/access/v2/collections/status/$reference";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $secretKey"
]);
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);
logTransaction($gatewayModuleName, [
    'GET' => $_GET,
    'APIResponse' => $responseData
], "Lenco Callback Raw Data");

if (!empty($responseData['status']) && $responseData['data']['status'] === 'successful') {
    $transactionId = $responseData['data']['lencoReference'];
    $amount = round(floatval($responseData['data']['amount']), 2);

    $existing = checkCbTransID($transactionId);
    if (!$existing) {
        addInvoicePayment($invoiceId, $transactionId, $amount, 0, $gatewayModuleName);
        logTransaction($gatewayModuleName, $responseData, "Successful");
    } else {
        logTransaction($gatewayModuleName, $responseData, "Duplicate Transaction");
    }
} else {
    logTransaction($gatewayModuleName, $responseData, "Unsuccessful");
}

header("Location: " . rtrim($gatewayParams['systemurl'], '/') . "/viewinvoice.php?id=" . $invoiceId);
exit();
