<?php
date_default_timezone_set("Asia/Kolkata");
include "../../conn.php";
global $firebase;

$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';
$depositId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

if (empty($action) || empty($depositId)) {
    die("<h3>Invalid Request Parameters!</h3>");
}

// Fetch deposit details from Firebase
$deposit = $firebase->get('deposits/' . $depositId);

if ($deposit == null) {
    die("<h3>Error: Deposit request not found!</h3>");
}

$status = isset($deposit['status']) ? $deposit['status'] : 'pending';
$userId = isset($deposit['userId']) ? $deposit['userId'] : '';
$amount = isset($deposit['amount']) ? (float)$deposit['amount'] : 0.0;
$method = isset($deposit['method']) ? $deposit['method'] : '';
$utr = isset($deposit['utr']) ? $deposit['utr'] : '';
$message_id = isset($deposit['message_id']) ? $deposit['message_id'] : '';
$screenshot_url = isset($deposit['screenshot']) ? $deposit['screenshot'] : '';

if ($status !== 'pending') {
    die("<h3>This deposit request has already been processed! Current Status: " . strtoupper($status) . "</h3>");
}

global $tgBotToken, $tgChatId;
$botToken = $tgBotToken;
$chatId = $tgChatId;
$htmlResponse = "";

if ($action === 'approve') {
    // Approve flow: Add balance to user
    if (!empty($userId)) {
        $user = $firebase->get('users/' . $userId);
        if ($user != null) {
            $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
            $newBalance = round($currentBalance + $amount, 2);
            $currentTotalDeposit = isset($user['total_deposit']) ? (float)$user['total_deposit'] : 0.0;
            
            // Update user balance and total deposits
            $firebase->update('users/' . $userId, [
                'motta' => $newBalance,
                'total_deposit' => $currentTotalDeposit + $amount
            ]);
            
            // Mark deposit as success
            $firebase->update('deposits/' . $depositId, ['status' => 'success']);
            
            // Edit Telegram message to notify approval
            $newText = "✅ *Deposit Request Approved!*\n\n";
            $newText .= "*Deposit ID:* `" . $depositId . "`\n";
            $newText .= "*Player Mobile:* `" . $userId . "`\n";
            $newText .= "*Amount Added:* `₹" . $amount . "`\n";
            $newText .= "*Method:* `" . $method . "`\n";
            $newText .= "*UTR / TxID:* `" . $utr . "`\n";
            $newText .= "*Status:* `SUCCESS (Approved by Admin)`\n";
            $newText .= "*Processed At:* `" . date('Y-m-d H:i:s') . "`\n";
            if (!empty($screenshot_url)) {
                $newText .= "\n🖼 [Click here to view Screenshot](" . $screenshot_url . ")";
            }
            
            if (!empty($message_id)) {
                $tgUrl = "https://api.telegram.org/bot" . $botToken . "/editMessageText";
                $postFields = [
                    'chat_id' => $chatId,
                    'message_id' => $message_id,
                    'text' => $newText,
                    'parse_mode' => 'Markdown'
                ];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $tgUrl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($ch);
                curl_close($ch);
            }
            
            $htmlResponse = "<h2>✅ Deposit Request #" . $depositId . " Approved!</h2><p>Amount of ₹" . $amount . " has been successfully credited to player " . $userId . "'s wallet.</p>";
        } else {
            $htmlResponse = "<h2>❌ Error</h2><p>Player account not found in database.</p>";
        }
    } else {
        $htmlResponse = "<h2>❌ Error</h2><p>Player mobile number missing in deposit record.</p>";
    }
} elseif ($action === 'reject') {
    // Reject flow: Mark failed
    $firebase->update('deposits/' . $depositId, ['status' => 'failed']);
    
    // Edit Telegram message to notify rejection
    $newText = "❌ *Deposit Request Rejected!*\n\n";
    $newText .= "*Deposit ID:* `" . $depositId . "`\n";
    $newText .= "*Player Mobile:* `" . $userId . "`\n";
    $newText .= "*Amount Rejected:* `₹" . $amount . "`\n";
    $newText .= "*Method:* `" . $method . "`\n";
    $newText .= "*UTR / TxID:* `" . $utr . "`\n";
    $newText .= "*Status:* `REJECTED (Declined by Admin)`\n";
    $newText .= "*Processed At:* `" . date('Y-m-d H:i:s') . "`\n";
    if (!empty($screenshot_url)) {
        $newText .= "\n🖼 [Click here to view Screenshot](" . $screenshot_url . ")";
    }
    
    if (!empty($message_id)) {
        $tgUrl = "https://api.telegram.org/bot" . $botToken . "/editMessageText";
        $postFields = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => $newText,
            'parse_mode' => 'Markdown'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tgUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }
    
    $htmlResponse = "<h2>❌ Deposit Request #" . $depositId . " Rejected!</h2><p>The transaction has been marked as failed/rejected.</p>";
} else {
    $htmlResponse = "<h2>Invalid Action!</h2>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>89 CLUB — Admin Action</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0c0a09;
            color: #e7e5e4;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 90%;
            max-width: 400px;
            background-color: #1c1917;
            border: 1px solid #2e2a24;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5);
        }
        h2 {
            margin-top: 0;
            color: #f59e0b;
        }
        p {
            color: #a8a29e;
            line-height: 1.5;
            font-size: 14px;
        }
        .close-btn {
            display: inline-block;
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            margin-top: 16px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <?php echo $htmlResponse; ?>
    <button class="close-btn" onclick="window.close()">Close Window</button>
</div>
</body>
</html>
