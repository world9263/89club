<?php
date_default_timezone_set("Asia/Kolkata");
include "../codexdr/conn.php";
global $firebase;

// Read query params from redirect
$amount = isset($_GET['amount']) ? htmlspecialchars($_GET['amount']) : '0';
$uid = isset($_GET['uid']) ? htmlspecialchars($_GET['uid']) : '';
$method = isset($_GET['method']) ? strtolower(htmlspecialchars($_GET['method'])) : 'upi';

// Fetch current deposit settings from Firebase
$deposit_settings = $firebase->get('deposit_settings');
$upi_id = isset($deposit_settings['upi']['upi_id']) ? $deposit_settings['upi']['upi_id'] : 'yourupi@ybl';
$qr_url = isset($deposit_settings['upi']['qr_url']) ? $deposit_settings['upi']['qr_url'] : '/pay/wepay.png';
$usdt_address = isset($deposit_settings['usdt']['usdt_address']) ? $deposit_settings['usdt']['usdt_address'] : 'T9yD14Nj9yXsw1cqSk299m91yXsw1c99m9';
$bkash_wallet = isset($deposit_settings['bkash']['wallet_no']) ? $deposit_settings['bkash']['wallet_no'] : '01354743800';
$nagad_wallet = isset($deposit_settings['nagad']['wallet_no']) ? $deposit_settings['nagad']['wallet_no'] : '01942136883';

$success_msg = "";
$error_msg = "";

$is_bd = (strpos($uid, '880') === 0 || strpos($uid, '+880') === 0 || $method === 'bkash' || $method === 'nagad');
$currency = $is_bd ? "৳" : "₹";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utr = isset($_POST['utr']) ? trim(htmlspecialchars($_POST['utr'])) : '';
    $userId = isset($_POST['uid']) ? trim(htmlspecialchars($_POST['uid'])) : '';
    $payAmount = isset($_POST['amount']) ? trim(htmlspecialchars($_POST['amount'])) : '0';
    $payMethod = isset($_POST['method']) ? trim(htmlspecialchars($_POST['method'])) : 'upi';
    
    if (empty($utr)) {
        $error_msg = "Please enter your Transaction ID / UTR!";
    } else {
        // Handle screenshot upload
        $screenshot_url = "";
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['screenshot']['tmp_name'];
            $file_name = $_FILES['screenshot']['name'];
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            
            // Allow only image formats
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array(strtolower($ext), $allowed)) {
                $target_dir = __DIR__ . "/assets/screenshots/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $new_file_name = "scr_" . time() . "_" . rand(1000, 9999) . "." . $ext;
                $target_file = $target_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $screenshot_url = "/pay/assets/screenshots/" . $new_file_name;
                }
            }
        }
        
        // Save request in Firebase
        $depositId = "DEP_" . time() . rand(100, 999);
        $deposit_data = [
            'id' => $depositId,
            'userId' => $userId,
            'amount' => (float)$payAmount,
            'method' => strtoupper($payMethod),
            'utr' => $utr,
            'screenshot' => $screenshot_url,
            'status' => 'pending',
            'createdAt' => date('Y-m-d H:i:s')
        ];
        
        $firebase->set('deposits/' . $depositId, $deposit_data);
        
        // Notify via Telegram Bot
        global $tgBotToken, $tgChatId;
        $botToken = $tgBotToken;
        $chatId = $tgChatId;
        
        $msgText = "🔔 *New Deposit Request!*\n\n";
        $msgText .= "*Deposit ID:* `" . $depositId . "`\n";
        $msgText .= "*Player Mobile:* `" . $userId . "`\n";
        $msgText .= "*Amount:* `" . $currency . $payAmount . "`\n";
        $msgText .= "*Method:* `" . strtoupper($payMethod) . "`\n";
        $msgText .= "*UTR / Transaction ID:* `" . $utr . "`\n";
        $msgText .= "*Submitted At:* `" . date('Y-m-d H:i:s') . "`\n";
        if (!empty($screenshot_url)) {
            // Reconstruct absolute URL for telegram preview
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $absolute_screenshot = $protocol . "://" . $_SERVER['HTTP_HOST'] . $screenshot_url;
            $msgText .= "\n🖼 [Click here to view Screenshot](" . $absolute_screenshot . ")";
        } else {
            $msgText .= "\n🖼 *Screenshot:* Not Uploaded";
        }
        
        // Inline keyboard for approval/rejection
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Approve', 'callback_data' => 'approve_dep:' . $depositId],
                    ['text' => '❌ Reject', 'callback_data' => 'reject_dep:' . $depositId]
                ]
            ]
        ];
        
        $tgUrl = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
        $postFields = [
            'chat_id' => $chatId,
            'text' => $msgText,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ];
        
        $ch = curl_init();
        $curl_url = $tgUrl;
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $tgResponse = curl_exec($ch);
        curl_close($ch);
        
        $tgResData = json_decode($tgResponse, true);
        if (isset($tgResData['result']['message_id'])) {
            $firebase->update('deposits/' . $depositId, ['message_id' => $tgResData['result']['message_id']]);
        }
        
        $success_msg = "Your deposit request has been submitted successfully! It will be verified and credited within 10-15 minutes.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>89 CLUB — Deposit Portal</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f7f9fc;
            color: #1f2937;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 480px;
            background-color: #ffffff;
            box-sizing: border-box;
            padding: 16px 16px 100px 16px; /* Space for sticky bottom buttons */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Amount Header Bar */
        .amount-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f3f4f6;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .amount-title {
            font-size: 24px;
            font-weight: 800;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .amount-title span {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
        }
        .timer {
            font-size: 16px;
            font-weight: bold;
            color: #ef4444;
        }
        
        /* Section Headlines */
        .section-headline {
            font-size: 15px;
            font-weight: 700;
            color: #1e3a8a; /* Deep blue-gray */
            margin: 16px 0 10px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .section-headline::before {
            content: "•";
            color: #ef4444; /* Red bullet */
            font-size: 20px;
        }
        
        /* Choose payment method */
        .payment-methods-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }
        .method-btn {
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.1s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .method-btn:active {
            transform: scale(0.97);
        }
        .method-btn.paytm {
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            border: 1px solid #bae6fd;
        }
        .method-btn.phonepe {
            background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
            border: 1px solid #e9d5ff;
        }
        .method-btn.usdt {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 1px solid #fde68a;
            grid-column: span 2;
        }
        .method-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 2px;
        }
        .method-btn.paytm .method-title { color: #012b72; }
        .method-btn.phonepe .method-title { color: #5f259f; }
        .method-btn.usdt .method-title { color: #b45309; }
        
        .method-sub {
            font-size: 11px;
            color: #4b5563;
        }
        
        /* QR Code Container Card */
        .qr-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px 16px;
            text-align: center;
            margin-bottom: 16px;
        }
        .qr-wrapper {
            background-color: #f3f4f6;
            padding: 12px;
            display: inline-block;
            border-radius: 12px;
            margin-bottom: 14px;
        }
        .qr-wrapper img {
            max-width: 170px;
            display: block;
            background-color: #ffffff;
            padding: 6px;
            border-radius: 6px;
        }
        .qr-instructions {
            font-size: 12px;
            color: #4b5563;
            line-height: 1.6;
            text-align: left;
            padding: 0 10px;
        }
        .qr-instructions p {
            margin: 6px 0;
        }
        
        /* Alert/Fail Box text */
        .alert-text-red {
            color: #dc2626;
            font-size: 13px;
            font-style: italic;
            margin: 4px 0 12px 0;
            font-weight: 500;
        }
        
        /* Address Row */
        .address-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 16px;
        }
        .address-box-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 6px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .address-row {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .address-text {
            flex: 1;
            font-size: 13px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-family: monospace;
            color: #1f2937;
            text-align: left;
        }
        .copy-btn-inner {
            background-color: #ff3c40;
            color: #ffffff;
            border: none;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 8px;
        }
        
        /* UTR Input Form */
        .input-wrapper {
            position: relative;
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 30px;
            display: flex;
            align-items: center;
            padding: 4px 6px;
            margin-bottom: 20px;
            transition: border-color 0.2s;
        }
        .input-wrapper:focus-within {
            border-color: #ff3c40;
            background-color: #ffffff;
        }
        .utr-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 10px 14px;
            font-size: 14px;
            color: #1f2937;
            outline: none;
            width: 100%;
        }
        .paste-btn {
            background-color: #ff5252;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .paste-btn:active {
            opacity: 0.8;
        }
        
        .screenshot-group {
            margin-bottom: 20px;
        }
        .screenshot-group label {
            display: block;
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .screenshot-group input[type="file"] {
            width: 100%;
            box-sizing: border-box;
            background-color: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            padding: 10px;
            font-size: 13px;
            cursor: pointer;
        }
        
        /* Reminders Block */
        .reminders-box {
            font-size: 12px;
            color: #4b5563;
            line-height: 1.6;
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 24px;
        }
        .reminders-box ol {
            padding-left: 20px;
            margin: 6px 0 0 0;
            color: #1e40af;
        }
        
        /* Sticky Bottom Buttons */
        .bottom-actions {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 480px;
            background-color: #ffffff;
            border-top: 1px solid #e5e7eb;
            padding: 14px 16px;
            box-sizing: border-box;
            display: flex;
            gap: 12px;
            z-index: 100;
        }
        .btn-cancel {
            flex: 1;
            background-color: #f3f4f6;
            color: #4b5563;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .btn-submit-action {
            flex: 2;
            background-color: #cbd5e1; /* Disabled state color */
            color: #94a3b8;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 14px;
            font-weight: bold;
            cursor: not-allowed;
            text-align: center;
            transition: all 0.2s;
        }
        .btn-submit-action.active {
            background: linear-gradient(to right, #ff7b6c, #ff3c40);
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
        }
        
        .alert {
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 16px;
            line-height: 1.5;
            text-align: center;
        }
        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }
        .alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .back-btn-container {
            text-align: center;
            margin-top: 40px;
        }
        .back-btn-container a {
            background: linear-gradient(to right, #ff7b6c, #ff3c40);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 20px;
            font-weight: bold;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
        }

        /* custom Nagad Theme styles */
        .nagad-theme {
            background-color: #f58e20;
            color: white;
            padding: 20px;
            border-radius: 14px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(245, 142, 32, 0.15);
        }
        .nagad-white-card {
            background-color: white;
            color: #1f2937;
            border-radius: 12px;
            padding: 16px;
            margin: 16px 0;
        }
        .nagad-white-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .nagad-white-card-row:last-child {
            border-bottom: none;
        }
        .nagad-white-card-label {
            font-size: 14px;
            font-weight: bold;
            color: #4b5563;
        }
        .nagad-white-card-value {
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nagad-submit-btn {
            background-color: #e55325;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            text-align: center;
        }

        /* custom bKash Theme styles */
        .bkash-theme-header {
            background-color: #005c30;
            color: white;
            padding: 14px 18px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .bkash-alert-pink {
            color: #d11266;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 18px;
            line-height: 1.5;
        }
        .bkash-magenta-btn {
            background-color: #d11266;
            color: white;
            font-weight: bold;
            padding: 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 16px;
            font-size: 15px;
        }
        .bkash-circle {
            background-color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d11266;
            font-weight: 900;
            font-size: 10px;
        }
        .bkash-label {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 4px;
        }
        .bkash-sublabel {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .bkash-number-box {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }
        .bkash-number-val {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            letter-spacing: 0.5px;
        }
        .bkash-input-trx-label {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 6px;
        }
        .bkash-confirm-btn {
            background-color: #ffffff;
            color: #4b5563;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }
        .bkash-confirm-btn.active {
            background-color: #d11266;
            color: white;
            border-color: #d11266;
        }
        .bkash-alert-warning {
            color: #d11266;
            font-size: 12px;
            font-weight: bold;
            margin-top: 14px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if (!empty($success_msg)): ?>
        <div style="margin-top: 60px;">
            <div class="alert alert-success">
                <?php echo $success_msg; ?>
            </div>
            <div class="back-btn-container">
                <a href="/#/main">Go Back to App</a>
            </div>
        </div>
    <?php else: ?>
        
        <?php if ($method !== 'bkash' && $method !== 'nagad'): ?>
            <!-- Header Amount & Timer (Default/USDT/UPI) -->
            <div class="amount-header">
                <div class="amount-title">
                    <?php if ($method === 'usdt'): ?>
                        $<?php echo number_format((float)$amount, 2); ?>
                    <?php else: ?>
                        ₹<?php echo number_format((float)$amount, 2); ?>
                    <?php endif; ?>
                    <span onclick="copyValue('<?php echo $amount; ?>')" style="color: #6b7280;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                    </span>
                </div>
                <div class="timer" id="countdown">15:00</div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <!-- Form wraps the submission -->
        <form id="payment-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="uid" value="<?php echo $uid; ?>">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="method" value="<?php echo $method; ?>">

            <?php if ($method === 'nagad'): ?>
                <!-- NAGAD BENGALI LAYOUT -->
                <div style="text-align: center; margin-bottom: 12px;">
                    <h2 style="font-size: 28px; font-weight: 800; margin: 0; color: #111827;">Payment</h2>
                    <span style="font-size: 13px; color: #6b7280; font-weight: bold;"><?php echo date('n/j/Y, g:i:s A'); ?></span>
                </div>
                
                <div class="nagad-theme">
                    <div style="font-size: 14px; font-weight: bold; line-height: 1.6; text-align: left;">
                        অনুগ্রহ করে একই পরিমাণ স্থানান্তর করুন এবং ব্যর্থ এড়াতে সঠিক trxID পূরণ করুন
                    </div>
                    <div style="font-size: 14px; font-weight: bold; line-height: 1.6; text-align: left; margin-top: 14px;">
                        এই নাগদ এজেন্ট অ্যাকাউন্টে অর্থ প্রদান করতে <strong>ক্যাশআউট</strong> ব্যবহার করুন
                    </div>
                    
                    <div class="nagad-white-card">
                        <div class="nagad-white-card-row">
                            <span class="nagad-white-card-label">ওয়ালেট</span>
                            <span class="nagad-white-card-value" style="color: #e55325;">
                                <img src="https://logologogo.github.io/logos/nagad.png" style="height: 18px; display:none;" onerror="this.style.display='none'"> 
                                Nagad
                            </span>
                        </div>
                        <div class="nagad-white-card-row">
                            <span class="nagad-white-card-label">সংখ্যা</span>
                            <span class="nagad-white-card-value" style="color: #1e40af; font-family: monospace;">
                                <?php echo $nagad_wallet; ?>
                                <span onclick="copyValue('<?php echo $nagad_wallet; ?>')" style="cursor: pointer; color: #6b7280; display: inline-flex; align-items: center;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                </span>
                            </span>
                        </div>
                        <div class="nagad-white-card-row">
                            <span class="nagad-white-card-label">পরিমাণ</span>
                            <span class="nagad-white-card-value" style="color: #15803d;">
                                ৳<?php echo number_format((float)$amount, 2); ?>
                                <span onclick="copyValue('<?php echo $amount; ?>')" style="cursor: pointer; color: #6b7280; display: inline-flex; align-items: center;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                </span>
                            </span>
                        </div>
                    </div>

                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 10px; text-align: left;">
                        সম্পূর্ণ করতে দয়া করে TxnID লিখুন
                    </div>
                    
                    <div class="input-wrapper" style="border-radius: 8px; margin-bottom: 10px;">
                        <input type="text" id="utr" name="utr" class="utr-input" placeholder="এখানে TxID লিখুন" required autocomplete="off">
                        <button type="button" class="paste-btn" style="border-radius: 6px;" onclick="pasteClipboard()">Paste</button>
                    </div>
                    
                    <button type="submit" id="nagad-submit" class="nagad-submit-btn" style="opacity: 0.6; cursor: not-allowed;" disabled>জমা দিন</button>
                </div>

            <?php elseif ($method === 'bkash'): ?>
                <!-- BKASH BENGALI LAYOUT -->
                <div class="bkash-theme-header">
                    <span>BDT <?php echo number_format((float)$amount, 2); ?></span>
                    <span style="background-color: white; color: #005c30; padding: 2px 6px; border-radius: 4px; font-size: 11px;">PAY SERVICE</span>
                    <span style="font-size: 11px;">পাঠান করবেন না</span>
                </div>
                
                <div class="bkash-alert-pink">
                    আপনি যদি টাকার পরিমাণ পরিবর্তন করেন (BDT <?php echo number_format((float)$amount, 2); ?>), আপনি ক্রেডিট পেতে সক্ষম হবেন না।
                </div>

                <div class="bkash-magenta-btn">
                    <span class="bkash-circle">bKash</span>
                    <span>BKASH Deposit</span>
                </div>

                <div class="bkash-label">Wallet No *</div>
                <div class="bkash-sublabel">এই BKASH নাম্বারে শুধুমাত্র <strong>সেন্ড মানি</strong> গ্রহণ করা হয়</div>
                
                <div class="bkash-number-box">
                    <span class="bkash-number-val" id="bkash-num"><?php echo $bkash_wallet; ?></span>
                    <span onclick="copyValue('<?php echo $bkash_wallet; ?>')" style="cursor: pointer; color: #6b7280; display: inline-flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                    </span>
                </div>

                <div class="bkash-input-trx-label">সেন্ট মানির TrxID নাম্বারটি লিখুন(প্রয়োজন)</div>
                
                <div class="input-wrapper" style="border-radius: 6px; border: 1px solid #d1d5db; margin-bottom: 20px;">
                    <input type="text" id="utr" name="utr" class="utr-input" placeholder="TrxID অবশ্যই পূরণ করতে হবে" required autocomplete="off">
                </div>

                <div style="text-align: center; margin-bottom: 20px;">
                    <button type="submit" id="bkash-submit" class="bkash-confirm-btn" disabled>নিশ্চিত</button>
                </div>

                <div class="bkash-alert-warning">
                    সতর্কতা: লেনদেন আইডি সঠিকভাবে পূরণ করতে হবে, অন্যথায় স্কোর ব্যর্থ হবে! !
                </div>
                
                <div style="font-size: 11px; color: #6b7280; line-height: 1.6; text-align: left; margin-bottom: 20px;">
                    अनुग्रह करे নিশ্চিত হয়ে নিন যে আপনি BKASH deposit ওয়ালেট নাম্বারে সেন্ড মানি করছেন। এই নাম্বারের অন্য কোন ওয়ালেট থেকে টাকা পাঠাবেন না।
                </div>

            <?php elseif ($method === 'usdt'): ?>
                <!-- USDT Layout -->
                <div class="section-headline">Choose payment channel</div>
                <div class="payment-methods-grid">
                    <div class="method-btn usdt">
                        <div class="method-title">USDT TRC-20</div>
                        <div class="method-sub">Wake up support</div>
                    </div>
                </div>

                <div class="section-headline">Scan QR to pay</div>
                <div class="qr-card">
                    <div class="qr-wrapper">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($usdt_address); ?>" alt="USDT QR Code">
                    </div>
                    <div class="qr-instructions">
                        <p>1. Open your crypto exchange or wallet (Binance, Trust Wallet, etc.).</p>
                        <p>2. Send the exact amount of USDT TRC-20 to the address below.</p>
                    </div>
                </div>

                <div class="address-box">
                    <div class="address-box-label">USDT TRC-20 Wallet Address</div>
                    <div class="address-row">
                        <div class="address-text" id="target-address"><?php echo $usdt_address; ?></div>
                        <button type="button" class="copy-btn-inner" onclick="copyValue('<?php echo $usdt_address; ?>')">Copy</button>
                    </div>
                </div>

                <div class="section-headline">Input TxID/ Paste TxID</div>
                <div class="alert-text-red">If you do not back fill TxID/ paste TxID, 100% will fail.</div>
                
                <div class="input-wrapper">
                    <input type="text" id="utr" name="utr" class="utr-input" placeholder="Enter USDT Transaction Hash / TxID" required autocomplete="off">
                    <button type="button" class="paste-btn" onclick="pasteClipboard()">Paste</button>
                </div>
            <?php else: ?>
                <!-- UPI Layout -->
                <div class="section-headline">Choose a payment method to pay</div>
                <div class="payment-methods-grid">
                    <div class="method-btn paytm" onclick="wakeUpApp('paytm')">
                        <div class="method-title">Paytm</div>
                        <div class="method-sub">Wake up support</div>
                    </div>
                    <div class="method-btn phonepe" onclick="wakeUpApp('phonepe')">
                        <div class="method-title">PhonePe</div>
                        <div class="method-sub">Wake up support</div>
                    </div>
                </div>

                <div class="section-headline">Use Mobile Scan code to pay</div>
                <div class="qr-card">
                    <div class="qr-wrapper">
                        <img src="<?php echo $qr_url; ?>" alt="UPI QR Code">
                    </div>
                    <div class="qr-instructions">
                        <p>1. Please use another device to scan the QR code with your payment app.</p>
                        <p>2. If you scan the QR code from this device's gallery, the payment amount may be limited (≤2000).</p>
                    </div>
                </div>

                <div class="address-box">
                    <div class="address-box-label">UPI ID</div>
                    <div class="address-row">
                        <div class="address-text" id="target-address"><?php echo $upi_id; ?></div>
                        <button type="button" class="copy-btn-inner" onclick="copyValue('<?php echo $upi_id; ?>')">Copy</button>
                    </div>
                </div>

                <div class="section-headline">Input UTR/ Paste UTR</div>
                <div class="alert-text-red">If you do not back fill UTR/ paste UTR, 100% will fail.</div>
                
                <div class="input-wrapper">
                    <input type="text" id="utr" name="utr" class="utr-input" placeholder="Input 12 digits here" required autocomplete="off" maxlength="12" pattern="\d{12}">
                    <button type="button" class="paste-btn" onclick="pasteClipboard()">Paste</button>
                </div>
            <?php endif; ?>

            <!-- Optional Screenshot Upload -->
            <div class="screenshot-group">
                <label for="screenshot">Upload Payment Screenshot (Optional)</label>
                <input type="file" id="screenshot" name="screenshot" accept="image/*">
            </div>

            <!-- Important Reminder Box -->
            <div class="reminders-box">
                <strong>Important reminder:</strong>
                <ol>
                    <?php if ($method === 'usdt'): ?>
                        <li>Do not pay for the same wallet address repeatedly!</li>
                        <li>Always ensure the network is TRC-20 (TRON).</li>
                    <?php elseif ($method === 'bkash' || $method === 'nagad'): ?>
                        <li>Do not make duplicate submissions for the same TrxID!</li>
                        <li>Always verify the wallet number before transferring funds.</li>
                    <?php else: ?>
                        <li>Do not pay for the same link repeatedly!</li>
                        <li>Paytm is wake up support!</li>
                    <?php endif; ?>
                </ol>
            </div>

            <!-- Sticky Bottom Actions -->
            <div class="bottom-actions">
                <button type="button" class="btn-cancel" onclick="window.location.href='/#/main'">Cancel</button>
                <?php if ($method === 'nagad' || $method === 'bkash'): ?>
                    <!-- Hidden backup submit button so standard form submission still triggers validateUTRInput -->
                    <button type="submit" id="submit-btn" style="display:none;"></button>
                <?php else: ?>
                    <button type="submit" id="submit-btn" class="btn-submit-action" disabled>Submit (UTR not entered)</button>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
// Timer Countdown (15 minutes)
function startTimer() {
    var timerKey = "recharge_timer_val";
    var startTimeKey = "recharge_timer_start";
    
    var timeRemaining = 15 * 60; // default 15 mins
    var now = Math.floor(Date.now() / 1000);
    
    if (localStorage.getItem(timerKey) && localStorage.getItem(startTimeKey)) {
        var start = parseInt(localStorage.getItem(startTimeKey));
        var elapsed = now - start;
        var savedVal = parseInt(localStorage.getItem(timerKey));
        timeRemaining = savedVal - elapsed;
        
        if (timeRemaining <= 0) {
            timeRemaining = 15 * 60;
            localStorage.setItem(startTimeKey, now.toString());
            localStorage.setItem(timerKey, timeRemaining.toString());
        }
    } else {
        localStorage.setItem(startTimeKey, now.toString());
        localStorage.setItem(timerKey, timeRemaining.toString());
    }
    
    var interval = setInterval(function() {
        var minutes = Math.floor(timeRemaining / 60);
        var seconds = timeRemaining % 60;
        
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        
        var countdownEl = document.getElementById("countdown");
        if (countdownEl) {
            countdownEl.innerText = minutes + ":" + seconds;
        }
        
        timeRemaining--;
        
        if (timeRemaining < 0) {
            clearInterval(interval);
            localStorage.removeItem(timerKey);
            localStorage.removeItem(startTimeKey);
            alert("Payment timer expired! Please initiate a new deposit if needed.");
            window.location.href = "/#/main";
        }
    }, 1000);
}

startTimer();

// Clipboard copying utility
function copyValue(val) {
    navigator.clipboard.writeText(val).then(function() {
        alert("Copied successfully!");
    }, function() {
        // Fallback
        var temp = document.createElement("input");
        document.body.appendChild(temp);
        temp.value = val;
        temp.select();
        document.execCommand("copy");
        document.body.removeChild(temp);
        alert("Copied!");
    });
}

// Clipboard paste functionality for the paste button
function pasteClipboard() {
    navigator.clipboard.readText().then(function(text) {
        var input = document.getElementById("utr");
        if (input) {
            input.value = text.trim();
            validateUTRInput();
        }
    }).catch(function(err) {
        alert("Could not read from clipboard. Please enter manually.");
    });
}

// Deep linking to payment apps (Wake Up Support)
function wakeUpApp(app) {
    var upiId = "<?php echo $upi_id; ?>";
    var amount = "<?php echo $amount; ?>";
    var note = "89ClubRecharge";
    
    // Copy the UPI ID first so the user has it in clipboard
    navigator.clipboard.writeText(upiId);
    
    var deepLink = "upi://pay?pa=" + encodeURIComponent(upiId) + "&pn=Merchant&am=" + encodeURIComponent(amount) + "&cu=INR&tn=" + encodeURIComponent(note);
    
    if (app === 'paytm') {
        window.location.href = "paytmmp://cashier?pa=" + encodeURIComponent(upiId) + "&am=" + encodeURIComponent(amount);
        setTimeout(function() {
            window.location.href = deepLink;
        }, 1000);
    } else if (app === 'phonepe') {
        window.location.href = "phonepe://pay?pa=" + encodeURIComponent(upiId) + "&am=" + encodeURIComponent(amount);
        setTimeout(function() {
            window.location.href = deepLink;
        }, 1000);
    } else {
        window.location.href = deepLink;
    }
}

// Input validation to enable/disable Submit button dynamically
var utrInput = document.getElementById("utr");
var submitBtn = document.getElementById("submit-btn");
var nagadSubmit = document.getElementById("nagad-submit");
var bkashSubmit = document.getElementById("bkash-submit");

function validateUTRInput() {
    if (!utrInput) return;
    
    var utrValue = utrInput.value.trim();
    var method = "<?php echo $method; ?>";
    
    var isValid = false;
    if (method === "usdt" || method === "bkash" || method === "nagad") {
        isValid = utrValue.length >= 8; // TxID/TrxID are usually hashes or numbers of reasonable length
    } else {
        // Match exactly 12 digits
        isValid = /^\d{12}$/.test(utrValue);
    }
    
    if (nagadSubmit) {
        if (isValid) {
            nagadSubmit.disabled = false;
            nagadSubmit.style.opacity = "1";
            nagadSubmit.style.cursor = "pointer";
        } else {
            nagadSubmit.disabled = true;
            nagadSubmit.style.opacity = "0.6";
            nagadSubmit.style.cursor = "not-allowed";
        }
    }

    if (bkashSubmit) {
        if (isValid) {
            bkashSubmit.disabled = false;
            bkashSubmit.classList.add("active");
            bkashSubmit.style.cursor = "pointer";
        } else {
            bkashSubmit.disabled = true;
            bkashSubmit.classList.remove("active");
            bkashSubmit.style.cursor = "not-allowed";
        }
    }
    
    if (submitBtn) {
        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.classList.add("active");
            submitBtn.innerText = "Submit";
            submitBtn.style.cursor = "pointer";
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove("active");
            if (method === "usdt") {
                submitBtn.innerText = "Submit (TxID not entered)";
            } else {
                submitBtn.innerText = "Submit (UTR not entered)";
            }
            submitBtn.style.cursor = "not-allowed";
        }
    }
}

if (utrInput) {
    utrInput.addEventListener("input", validateUTRInput);
}
</script>
</body>
</html>
