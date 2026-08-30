<?php
include("../serive/samparka.php");
global $firebase;

// Load Gateway config
$config = require 'config.php';
$appId = $config['upay']['app_id'];
$appSecret = $config['upay']['secret_key'];
$UPAY_API_URL = $config['upay']['api_url'] ?? "https://api.upay.ink/v1/api/open/order/apply";
$usdt_rate = $config['usdt_rate'] ?? 93;

$tyid = isset($_GET['tyid']) ? intval($_GET['tyid']) : 0;
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0.0;
$uid = isset($_GET['uid']) ? htmlspecialchars($_GET['uid']) : '';

if (!$uid || !$amount) {
    die(json_encode(["status" => false, "message" => "Invalid input parameters"]));
}

// Check if user exists in Firebase
$user = $firebase->get('users/' . $uid);
if (!$user) {
    die(json_encode(["status" => false, "message" => "User not found"]));
}

function generateSignature($params, $key) {
    ksort($params);
    $stringA = "";
    foreach ($params as $k => $v) {
        if ($v !== null && $v !== "") {
            $stringA .= "$k=$v&";
        }
    }
    $stringA .= "appSecret=$key";
    return strtoupper(md5($stringA));
}

// Generate unique order ID
$orderid = 'USDT_' . time() . rand(1000, 9999);
$createdate = date('Y-m-d H:i:s');

$notifyUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/pay/usdt_success.php';
$redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/#/wallet/RechargeHistory';
$fiatCurrency = "USD";
$amount_inr = $usdt_rate * $amount;

// API Request Data
$data2 = [
    "appId" => $appId,
    "merchantOrderNo" => $orderid,
    "chainType" => "1",
    "fiatAmount" => strval($amount),
    "fiatCurrency" => $fiatCurrency,
    "notifyUrl" => $notifyUrl,
];

$signature = generateSignature($data2, $appSecret);

$data = [
    "appId" => $appId,
    "merchantOrderNo" => $orderid,
    "chainType" => "1",
    "fiatAmount" => strval($amount),
    "fiatCurrency" => $fiatCurrency,
    "productName" => "usdtrecharge",
    "notifyUrl" => $notifyUrl,
    "redirectUrl" => $redirectUrl,
    "attach" => strval($uid),
    "signature" => $signature
];

// Send API Request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $UPAY_API_URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);

// Check API Response
if (!empty($responseData["data"]["payUrl"])) {
    $payment_url = $responseData["data"]["payUrl"];
    
    // Save pending payment record in Firebase deposits collection
    $deposit_data = [
        'id' => $orderid,
        'userId' => $uid,
        'amount' => (float)$amount_inr, // The fiat value (in INR/BDT)
        'usdt_amount' => (float)$amount, // The raw USDT value
        'method' => 'USDT',
        'status' => 'pending',
        'createdAt' => $createdate
    ];
    
    $firebase->set('deposits/' . $orderid, $deposit_data);
    
    // Redirect to UPAY checkout page
    header("Location: $payment_url");
    exit();
} else {
    die(json_encode(["status" => false, "message" => "Failed to retrieve payment URL", "api_response" => $responseData]));
}
?>
