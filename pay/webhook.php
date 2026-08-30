<?php
include("../serive/samparka.php");
global $firebase;

function logError($message) {
    file_put_contents('log.txt', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

$config = require 'config.php';
$system_settings = $firebase->get('system_settings');
$mchKey = isset($system_settings['gateway_secret_key']) && !empty($system_settings['gateway_secret_key']) ? $system_settings['gateway_secret_key'] : $config['xsbdwin']['secret_key'];

$data = $_POST;
logError("Received data: " . print_r($data, true));

if (empty($data)) {
    logError("Error: Empty POST request payload");
    die("fail");
}

$mchOrderNo = $data['mchOrderNo'] ?? $data['order_sn'] ?? null;
$tradeResult = $data['tradeResult'] ?? $data['status'] ?? null;

if (!$mchOrderNo) {
    logError("Error: Order number missing");
    die("fail");
}

// MD5 Signature Verification
if (isset($data['sign']) && !empty($data['sign'])) {
    $resSign = $data['sign'];
    $filteredParams = $data;
    unset($filteredParams['sign']);
    unset($filteredParams['sign_type']);

    $filteredParams = array_filter($filteredParams, function($value) {
        return $value !== null && $value !== '';
    });

    ksort($filteredParams);

    $md5str = '';
    foreach ($filteredParams as $key => $value) {
        $md5str .= "$key=$value&";
    }
    $md5str .= "key=" . $mchKey;
    
    $calculatedSignLower = strtolower(md5($md5str));
    $calculatedSignUpper = strtoupper(md5($md5str));

    if (strtolower($resSign) !== $calculatedSignLower && $resSign !== $calculatedSignUpper) {
        logError("Error: Signature verification failed. Calculated: $calculatedSignLower, Received: $resSign");
        die("fail");
    }
}

// Check payment status ('1' indicates successful capture in xsbdwin)
if ($tradeResult !== '1' && $tradeResult !== 1) {
    logError("Error: Payment tradeResult/status not successful. Received: " . var_export($tradeResult, true));
    die("fail");
}

// Fetch deposit details from Firebase
$deposit = $firebase->get('deposits/' . $mchOrderNo);

if ($deposit) {
    $status = isset($deposit['status']) ? $deposit['status'] : 'pending';
    
    if ($status === 'pending') {
        $userId = $deposit['userId'];
        $amount = (float)$deposit['amount'];
        
        // Fetch user from Firebase
        $user = $firebase->get('users/' . $userId);
        if ($user) {
            $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
            $newBalance = round($currentBalance + $amount, 2);
            
            // Update user balance in Firebase
            $firebase->update('users/' . $userId, [
                'motta' => $newBalance
            ]);
            
            // Update deposit status to success in Firebase
            $firebase->update('deposits/' . $mchOrderNo, [
                'status' => 'request on gateway',
                'updatedAt' => date('Y-m-d H:i:s')
            ]);
            
            logError("Success: Deposit order {$mchOrderNo} approved. User {$userId} credited {$amount}. New balance: {$newBalance}");
            echo "success";
            exit;
        } else {
            logError("Error: User {$userId} not found for deposit order {$mchOrderNo}");
            die("fail");
        }
    } else {
        logError("Notice: Deposit order {$mchOrderNo} is already processed. Status: {$status}");
        echo "success";
        exit;
    }
} else {
    logError("Error: Deposit order {$mchOrderNo} not found in Firebase");
    die("fail");
}
?>
