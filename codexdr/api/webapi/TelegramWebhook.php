<?php
date_default_timezone_set("Asia/Kolkata");
include "../../conn.php";
global $firebase;

// Retrieve the incoming update from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    exit;
}

// Handle Callback Queries (button clicks)
if (isset($update['callback_query'])) {
    $callbackQuery = $update['callback_query'];
    $callbackData = $callbackQuery['data'];
    $callbackQueryId = $callbackQuery['id'];
    $message = $callbackQuery['message'];
    $messageId = $message['message_id'];
    $chatId = $message['chat']['id'];
    
    global $tgBotToken, $tgChatId;
    $botToken = $tgBotToken;
    if ((string)$chatId !== (string)$tgChatId) {
        exit;
    }
    
    // Parse the callback action and ID
    // Format: approve_dep:DEP_123 or reject_dep:DEP_123
    // Format: approve_wd:W_123 or reject_wd:W_123
    $parts = explode(':', $callbackData);
    $action = $parts[0] ?? '';
    $requestId = $parts[1] ?? '';
    
    if (empty($action) || empty($requestId)) {
        exit;
    }
    
    if ($action === 'approve_dep') {
        // Approve Deposit
        $deposit = $firebase->get('deposits/' . $requestId);
        if ($deposit && ($deposit['status'] ?? 'pending') === 'pending') {
            $userId = $deposit['userId'] ?? '';
            $amount = (float)($deposit['amount'] ?? 0.0);
            
            if (!empty($userId)) {
                $user = $firebase->get('users/' . $userId);
                if ($user != null) {
                    $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
                    $newBalance = round($currentBalance + $amount, 2);
                    $currentTotalDeposit = isset($user['total_deposit']) ? (float)$user['total_deposit'] : 0.0;
                    
                    // Update user balance, total deposits, and deposit status
                    $firebase->update('users/' . $userId, [
                        'motta' => $newBalance,
                        'total_deposit' => $currentTotalDeposit + $amount
                    ]);
                    $firebase->update('deposits/' . $requestId, ['status' => 'success']);
                    
                    // Edit message in Telegram
                    $newText = "✅ *Deposit Request Approved!*\n\n";
                    $newText .= "*Deposit ID:* `" . $requestId . "`\n";
                    $newText .= "*Player Mobile:* `" . $userId . "`\n";
                    $newText .= "*Amount Added:* `₹" . $amount . "`\n";
                    $newText .= "*Method:* `" . ($deposit['method'] ?? 'UPI') . "`\n";
                    $newText .= "*UTR / TxID:* `" . ($deposit['utr'] ?? '') . "`\n";
                    $newText .= "*Status:* `SUCCESS (Approved by Admin)`\n";
                    $newText .= "*Processed At:* `" . date('Y-m-d H:i:s') . "`\n";
                    if (!empty($deposit['screenshot'])) {
                        $newText .= "\n🖼 [Click here to view Screenshot](" . $deposit['screenshot'] . ")";
                    }
                    
                    editTelegramMessage($botToken, $chatId, $messageId, $newText);
                    answerCallbackQuery($botToken, $callbackQueryId, "✅ Deposit Approved!");
                } else {
                    answerCallbackQuery($botToken, $callbackQueryId, "❌ Player account not found!");
                }
            }
        } else {
            answerCallbackQuery($botToken, $callbackQueryId, "⚠️ Deposit already processed or not found!");
        }
    }
    elseif ($action === 'reject_dep') {
        // Reject Deposit
        $deposit = $firebase->get('deposits/' . $requestId);
        if ($deposit && ($deposit['status'] ?? 'pending') === 'pending') {
            $userId = $deposit['userId'] ?? '';
            $amount = (float)($deposit['amount'] ?? 0.0);
            
            $firebase->update('deposits/' . $requestId, ['status' => 'failed']);
            
            $newText = "❌ *Deposit Request Rejected!*\n\n";
            $newText .= "*Deposit ID:* `" . $requestId . "`\n";
            $newText .= "*Player Mobile:* `" . $userId . "`\n";
            $newText .= "*Amount Rejected:* `₹" . $amount . "`\n";
            $newText .= "*Method:* `" . ($deposit['method'] ?? 'UPI') . "`\n";
            $newText .= "*UTR / TxID:* `" . ($deposit['utr'] ?? '') . "`\n";
            $newText .= "*Status:* `REJECTED (Declined by Admin)`\n";
            $newText .= "*Processed At:* `" . date('Y-m-d H:i:s') . "`\n";
            if (!empty($deposit['screenshot'])) {
                $newText .= "\n🖼 [Click here to view Screenshot](" . $deposit['screenshot'] . ")";
            }
            
            editTelegramMessage($botToken, $chatId, $messageId, $newText);
            answerCallbackQuery($botToken, $callbackQueryId, "❌ Deposit Rejected!");
        } else {
            answerCallbackQuery($botToken, $callbackQueryId, "⚠️ Deposit already processed or not found!");
        }
    }
    elseif ($action === 'approve_wd') {
        // Approve Withdrawal
        $withdrawal = $firebase->get('withdrawals/' . $requestId);
        if ($withdrawal && ($withdrawal['status'] ?? 'pending') === 'pending') {
            $userId = $withdrawal['userId'] ?? '';
            $amount = (float)($withdrawal['amount'] ?? 0.0);
            
            $firebase->update('withdrawals/' . $requestId, ['status' => 'approved']);
            
            $newText = "✅ *Withdrawal Request Success!*\n\n";
            $newText .= "*Withdrawal ID:* `" . $requestId . "`\n";
            $newText .= "*Player Mobile:* `" . $userId . "`\n";
            $newText .= "*Amount Withdrawn:* `₹" . $amount . "`\n";
            $newText .= "*Method:* `" . ($withdrawal['method'] ?? 'BANK_CARD') . "`\n";
            $newText .= "*Account Details:* `" . ($withdrawal['withdrawNumber'] ?? '') . "`\n";
            $newText .= "*Status:* `SUCCESS (Paid by Admin)`\n";
            $newText .= "*Processed At:* `" . date('Y-m-d H:i:s') . "`\n";
            
            editTelegramMessage($botToken, $chatId, $messageId, $newText);
            answerCallbackQuery($botToken, $callbackQueryId, "✅ Withdrawal Success!");
        } else {
            answerCallbackQuery($botToken, $callbackQueryId, "⚠️ Withdrawal already processed or not found!");
        }
    }
    elseif ($action === 'reject_wd') {
        // Reject Withdrawal
        $withdrawal = $firebase->get('withdrawals/' . $requestId);
        if ($withdrawal && ($withdrawal['status'] ?? 'pending') === 'pending') {
            $userId = $withdrawal['userId'] ?? '';
            $amount = (float)($withdrawal['amount'] ?? 0.0);
            
            if (!empty($userId)) {
                $user = $firebase->get('users/' . $userId);
                if ($user != null) {
                    $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
                    $newBalance = round($currentBalance + $amount, 2);
                    
                    // Refund balance to player and mark status as failed
                    $firebase->update('users/' . $userId, ['motta' => $newBalance]);
                    $firebase->update('withdrawals/' . $requestId, ['status' => 'failed']);
                    
                    $newText = "❌ *Withdrawal Request Rejected!*\n\n";
                    $newText .= "*Withdrawal ID:* `" . $requestId . "`\n";
                    $newText .= "*Player Mobile:* `" . $userId . "`\n";
                    $newText .= "*Amount Refunded:* `₹" . $amount . "`\n";
                    $newText .= "*Method:* `" . ($withdrawal['method'] ?? 'BANK_CARD') . "`\n";
                    $newText .= "*Account/USDT:* `" . ($withdrawal['withdrawNumber'] ?? '') . "`\n";
                    $newText .= "*Status:* `REJECTED (Refunded by Admin)`\n";
                    $newText .= "*Processed At:* `" . date('Y-m-d H:i:s') . "`\n";
                    
                    editTelegramMessage($botToken, $chatId, $messageId, $newText);
                    answerCallbackQuery($botToken, $callbackQueryId, "❌ Withdrawal Rejected & Refunded!");
                } else {
                    answerCallbackQuery($botToken, $callbackQueryId, "❌ Player account not found!");
                }
            }
        } else {
            answerCallbackQuery($botToken, $callbackQueryId, "⚠️ Withdrawal already processed or not found!");
        }
    }
}

function editTelegramMessage($botToken, $chatId, $messageId, $newText) {
    $tgUrl = "https://api.telegram.org/bot" . $botToken . "/editMessageText";
    $postFields = [
        'chat_id' => $chatId,
        'message_id' => $messageId,
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

function answerCallbackQuery($botToken, $callbackQueryId, $text) {
    $tgUrl = "https://api.telegram.org/bot" . $botToken . "/answerCallbackQuery";
    $postFields = [
        'callback_query_id' => $callbackQueryId,
        'text' => $text,
        'show_alert' => false
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
?>
