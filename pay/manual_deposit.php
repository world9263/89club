<?php
date_default_timezone_set("Asia/Kolkata");
include "../codexdr/conn.php";
global $firebase;

// Read query params from redirect
$amount = isset($_GET['amount']) ? htmlspecialchars($_GET['amount']) : '0';
$uid = isset($_GET['uid']) ? htmlspecialchars($_GET['uid']) : '';
$method = isset($_GET['method']) ? htmlspecialchars($_GET['method']) : 'upi';

// Fetch current deposit settings from Firebase
$deposit_settings = $firebase->get('deposit_settings');
$upi_id = isset($deposit_settings['upi']['upi_id']) ? $deposit_settings['upi']['upi_id'] : 'yourupi@ybl';
$qr_url = isset($deposit_settings['upi']['qr_url']) ? $deposit_settings['upi']['qr_url'] : 'https://89club-production.up.railway.app/pay/wepay.png';
$usdt_address = isset($deposit_settings['usdt']['usdt_address']) ? $deposit_settings['usdt']['usdt_address'] : 'T9yD14Nj9yXsw1cqSk299m91yXsw1c99m9';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utr = isset($_POST['utr']) ? trim(htmlspecialchars($_POST['utr'])) : '';
    $userId = isset($_POST['uid']) ? trim(htmlspecialchars($_POST['uid'])) : '';
    $payAmount = isset($_POST['amount']) ? trim(htmlspecialchars($_POST['amount'])) : '0';
    $payMethod = isset($_POST['method']) ? trim(htmlspecialchars($_POST['method'])) : 'upi';
    
    if (empty($utr)) {
        $error_msg = "Please enter your UTR / Transaction ID!";
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
                    $screenshot_url = "https://89club-production.up.railway.app/pay/assets/screenshots/" . $new_file_name;
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
        $msgText .= "*Amount:* `₹" . $payAmount . "`\n";
        $msgText .= "*Method:* `" . strtoupper($payMethod) . "`\n";
        $msgText .= "*UTR / Transaction ID:* `" . $utr . "`\n";
        $msgText .= "*Submitted At:* `" . date('Y-m-d H:i:s') . "`\n";
        if (!empty($screenshot_url)) {
            $msgText .= "\n🖼 [Click here to view Screenshot](" . $screenshot_url . ")";
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
        curl_setopt($ch, CURLOPT_URL, $tgUrl);
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
                <a href="https://89club-production.up.railway.app/#/main">Go Back to App</a>
            </div>
        </div>
    <?php else: ?>
        
        <!-- Header Amount & Timer -->
        <div class="amount-header">
            <div class="amount-title">
                <?php if ($method === 'usdt'): ?>
                    $<?php echo number_format((float)$amount, 2); ?>
                <?php else: ?>
                    ₹<?php echo number_format((float)$amount, 2); ?>
                <?php endif; ?>
                <span onclick="copyValue('<?php echo $amount; ?>')" style="color: #6b7280;">
                    <!-- Copy Icon SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </span>
            </div>
            <div class="timer" id="countdown">15:00</div>
        </div>

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

            <?php if ($method === 'usdt'): ?>
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
                        <!-- Use same QR Code for TRC20 if available, or fall back -->
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
                    <!-- Paytm Wake Up -->
                    <div class="method-btn paytm" onclick="wakeUpApp('paytm')">
                        <div class="method-title">Paytm</div>
                        <div class="method-sub">Wake up support</div>
                    </div>
                    <!-- PhonePe Wake Up -->
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
                    <?php else: ?>
                        <li>Do not pay for the same link repeatedly!</li>
                        <li>Paytm is wake up support!</li>
                    <?php endif; ?>
                </ol>
            </div>

            <!-- Sticky Bottom Actions -->
            <div class="bottom-actions">
                <button type="button" class="btn-cancel" onclick="window.location.href='https://89club-production.up.railway.app/#/main'">Cancel</button>
                <button type="submit" id="submit-btn" class="btn-submit-action" disabled>Submit (UTR not entered)</button>
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
        
        document.getElementById("countdown").innerText = minutes + ":" + seconds;
        
        timeRemaining--;
        
        if (timeRemaining < 0) {
            clearInterval(interval);
            localStorage.removeItem(timerKey);
            localStorage.removeItem(startTimeKey);
            alert("Payment timer expired! Please initiate a new deposit if needed.");
            window.location.href = "https://89club-production.up.railway.app/#/main";
        }
    }, 1000);
}

if (document.getElementById("countdown")) {
    startTimer();
}

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
        input.value = text.trim();
        validateUTRInput();
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
        // Paytm target deep link
        window.location.href = "paytmmp://cashier?pa=" + encodeURIComponent(upiId) + "&am=" + encodeURIComponent(amount);
        setTimeout(function() {
            window.location.href = deepLink;
        }, 1000);
    } else if (app === 'phonepe') {
        // PhonePe target deep link
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

function validateUTRInput() {
    if (!utrInput || !submitBtn) return;
    
    var utrValue = utrInput.value.trim();
    var isMethodUsdt = "<?php echo $method; ?>" === "usdt";
    
    // For UPI, validate that it has exactly 12 digits. For USDT, just ensure it's not empty and has a reasonable length.
    var isValid = false;
    if (isMethodUsdt) {
        isValid = utrValue.length >= 10; // TxID are usually longer hash strings
    } else {
        // Match exactly 12 digits
        isValid = /^\d{12}$/.test(utrValue);
    }
    
    if (isValid) {
        submitBtn.disabled = false;
        submitBtn.classList.add("active");
        submitBtn.innerText = "Submit";
        submitBtn.style.cursor = "pointer";
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.remove("active");
        if (isMethodUsdt) {
            submitBtn.innerText = "Submit (TxID not entered)";
        } else {
            submitBtn.innerText = "Submit (UTR not entered)";
        }
        submitBtn.style.cursor = "not-allowed";
    }
}

if (utrInput) {
    utrInput.addEventListener("input", validateUTRInput);
}
</script>
</body>
</html>
