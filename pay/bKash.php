<?php
include("../serive/samparka.php");
global $firebase;

// Load Gateway config
$config = require 'config.php';
$system_settings = $firebase->get('system_settings');
$app_id = isset($system_settings['gateway_app_id']) && !empty($system_settings['gateway_app_id']) ? $system_settings['gateway_app_id'] : $config['xsbdwin']['app_id'];
$secretKey = isset($system_settings['gateway_secret_key']) && !empty($system_settings['gateway_secret_key']) ? $system_settings['gateway_secret_key'] : $config['xsbdwin']['secret_key'];
$base_url = isset($system_settings['gateway_base_url']) && !empty($system_settings['gateway_base_url']) ? rtrim($system_settings['gateway_base_url'], '/') : $config['xsbdwin']['base_url'];
$apiUrl = $base_url . '/pay.php';

$ramt = isset($_GET['amount']) ? htmlspecialchars($_GET['amount']) : '0';
$payTypeID = isset($_GET['tyid']) ? htmlspecialchars($_GET['tyid']) : '0';
$uid = isset($_GET['uid']) ? htmlspecialchars($_GET['uid']) : '';

if (empty($uid)) {
    die(json_encode(["code" => -1, "message" => "User ID/Mobile is required"]));
}

// Convert amount formatting to float with 2 decimal points
$dot_pos = strpos($ramt, '.');
if ($dot_pos === false) {
    $ramt = $ramt . '.00';
} else {
    $after_dot = substr($ramt, $dot_pos + 1);
    $after_dot_length = strlen($after_dot);
    if ($after_dot_length > 2) {
        $after_dot = substr($after_dot, 0, 2);
        $ramt = substr($ramt, 0, $dot_pos + 1) . $after_dot;
    } elseif ($after_dot_length < 2) {
        $zeros_to_add = 2 - $after_dot_length;
        $ramt = $ramt . str_repeat('0', $zeros_to_add);
    }
}

// Check if user exists in Firebase
$user = $firebase->get('users/' . $uid);
if (!$user) {
    die(json_encode(["code" => -1, "message" => "User not found"]));
}

$time = time();
$serial = 'ORD_' . $time . rand(1000, 9999);
$createdate = date("Y-m-d H:i:s");

// Channel Mapping (2201 = Nagad, 2202 = bKash)
if ($payTypeID == 2201) {
    $pay_type = '2201';
    $goods_name = 'NAGAD';
} else {
    $pay_type = '2202';
    $goods_name = 'BKASH';
}

// Set up parameters according to gateway spec
$params = [
    'app_id'         => $app_id,
    'mch_order_no'   => $serial,
    'trade_amount'   => (string)intval($ramt),
    'pay_type'       => $pay_type,
    'goods_name'     => $goods_name,
    'notify_url'     => 'https://' . $_SERVER['HTTP_HOST'] . '/pay/webhook.php',
    'page_url'       => 'https://' . $_SERVER['HTTP_HOST'] . '/#/wallet/RechargeHistory',
    'mch_return_msg' => 'Order_' . $uid,
    'order_date'     => $createdate
];

// Sort parameters for signature calculation
ksort($params);
$signatureString = "";
foreach ($params as $k => $v) {
    if ($v !== '' && $v !== null) {
        $signatureString .= $k . "=" . $v . "&";
    }
}
$signatureString .= "key=" . $secretKey;

// MD5 signature generation (lowercase output)
$params['sign']      = strtolower(md5($signatureString));
$params['sign_type'] = 'MD5';

// Send POST request to API
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POSTREDIR, 3);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("Error: " . curl_error($ch));
}

curl_close($ch);
$responseData = json_decode($response, true);

// Check if gateway returned SUCCESS and payInfo URL
if ($responseData && isset($responseData['respCode']) && $responseData['respCode'] === 'SUCCESS' && !empty($responseData['payInfo'])) {
    
    // Save pending payment record in Firebase deposits collection
    $deposit_data = [
        'id' => $serial,
        'userId' => $uid,
        'amount' => (float)$ramt,
        'method' => $goods_name,
        'status' => 'pending',
        'createdAt' => $createdate
    ];
    $firebase->set('deposits/' . $serial, $deposit_data);
    
    // Redirect user to payment checkout page
    header('Location: ' . $responseData['payInfo']);
    exit;
} else {
    echo "Error: Unable to process payment. " . (isset($responseData['tradeMsg']) ? $responseData['tradeMsg'] : 'Gateway response error.') . " Raw Response: " . htmlspecialchars($response);
}
?>
