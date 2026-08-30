<?php
include("../serive/samparka.php");
global $firebase;

// Read incoming JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Log callback details
$logFile = 'callback_log.txt';
$logData = date("Y-m-d H:i:s") . " - USDT Callback Received: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
file_put_contents($logFile, $logData, FILE_APPEND);

if (!isset($data['merchantOrderNo']) || !isset($data['status'])) {
    file_put_contents($logFile, date("Y-m-d H:i:s") . " - Error: Missing merchantOrderNo or status in payload\n", FILE_APPEND);
    die(json_encode(["status" => false, "message" => "Invalid callback data"]));
}

$merchantOrderNo = trim($data['merchantOrderNo']);
$status = intval($data['status']);

// Status 1 indicates successful capture at UPAY gateway
if ($status !== 1) {
    file_put_contents($logFile, date("Y-m-d H:i:s") . " - Notice: Transaction not successful. Status: {$status}\n", FILE_APPEND);
    die(json_encode(["status" => false, "message" => "Recharge Pending"]));
}

// Fetch deposit details from Firebase
$deposit = $firebase->get('deposits/' . $merchantOrderNo);

if ($deposit) {
    $currentStatus = isset($deposit['status']) ? $deposit['status'] : 'pending';
    
    if ($currentStatus === 'initiated') {
        $userId = $deposit['userId'];
        
        // FIX: The amount saved in the database during creation is ALREADY multiplied by 93.
        // We retrieve it directly, without multiplying by 93 again to prevent crediting 93x too much!
        $amount_inr = (float)$deposit['amount'];
        
        // Fetch user profile from Firebase
        $user = $firebase->get('users/' . $userId);
        if ($user) {
            $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
            $newBalance = round($currentBalance + $amount_inr, 2);
            
            // Update user wallet balance in Firebase
            $firebase->update('users/' . $userId, [
                'motta' => $newBalance
            ]);
            
            // Update deposit status to success in Firebase
            $firebase->update('deposits/' . $merchantOrderNo, [
                'status' => 'request on gateway',
                'updatedAt' => date('Y-m-d H:i:s')
            ]);
            
            file_put_contents($logFile, date("Y-m-d H:i:s") . " - Success: USDT Deposit {$merchantOrderNo} approved. User {$userId} credited {$amount_inr} BDT/INR.\n", FILE_APPEND);
            echo json_encode(["status" => true, "message" => "Transaction processed successfully"]);
            exit;
        } else {
            file_put_contents($logFile, date("Y-m-d H:i:s") . " - Error: User {$userId} not found for USDT order {$merchantOrderNo}\n", FILE_APPEND);
            die(json_encode(["status" => false, "message" => "User not found"]));
        }
    } else {
        file_put_contents($logFile, date("Y-m-d H:i:s") . " - Notice: USDT Order {$merchantOrderNo} already processed. Status: {$currentStatus}\n", FILE_APPEND);
        echo json_encode(["status" => true, "message" => "Transaction already processed"]);
        exit;
    }
} else {
    file_put_contents($logFile, date("Y-m-d H:i:s") . " - Error: USDT order {$merchantOrderNo} not found in Firebase\n", FILE_APPEND);
    die(json_encode(["status" => false, "message" => "Recharge not found"]));
}
?>
