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
    } elseif (empty($userId)) {
        $error_msg = "User ID missing! Please reload the page from the app.";
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
        $botToken = "8690061817:AAHl73PLbjwBV2hkE37seE6aE_YV7uzuz8A";
        $chatId = "7606730935";
        
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
        $sites = "https://89club-production.up.railway.app";
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Approve', 'url' => $sites . "/codexdr/api/webapi/HandleTelegramDeposit.php?action=approve&id=" . $depositId],
                    ['text' => '❌ Reject', 'url' => $sites . "/codexdr/api/webapi/HandleTelegramDeposit.php?action=reject&id=" . $depositId]
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
    <title>89 CLUB — Manual Deposit</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6fa;
            color: #374151;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 450px;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 24px;
            box-sizing: border-box;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin: 16px;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .header h2 {
            margin: 0;
            font-size: 22px;
            color: #ff3c40; /* Matching the orange-red theme */
            letter-spacing: 0.5px;
            font-weight: bold;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #6b7280;
        }
        .amount-box {
            background-color: #fff5f5;
            border: 1px solid #fee2e2;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            margin-bottom: 20px;
        }
        .amount-box span {
            font-size: 12px;
            color: #dc2626;
            display: block;
            text-transform: uppercase;
            font-weight: 600;
        }
        .amount-box .value {
            font-size: 28px;
            font-weight: 800;
            color: #ef4444; /* Red accent */
            margin-top: 4px;
        }
        .payment-details {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid #f3f4f6;
        }
        .payment-details img {
            max-width: 180px;
            border-radius: 8px;
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
            padding: 4px;
        }
        .address-row {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 8px;
        }
        .address-text {
            flex: 1;
            font-size: 13px;
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-family: monospace;
            color: #1f2937;
        }
        .copy-btn {
            background-color: #ff3c40;
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .copy-btn:active {
            opacity: 0.8;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .form-group input[type="text"] {
            width: 100%;
            box-sizing: border-box;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px;
            color: #1f2937;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input[type="text"]:focus {
            border-color: #ff3c40;
        }
        .form-group input[type="file"] {
            font-size: 13px;
            color: #4b5563;
            background-color: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
            cursor: pointer;
        }
        .btn-submit {
            width: 100%;
            background: linear-gradient(to right, #ff7b6c, #ff3c40);
            color: #ffffff;
            border: none;
            padding: 14px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity 0.2s;
            margin-top: 10px;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
        }
        .btn-submit:hover {
            opacity: 0.95;
        }
        .alert {
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
            text-align: center;
        }
        .alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .instructions {
            margin-top: 24px;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.6;
            background-color: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #f3f4f6;
        }
        .instructions ol {
            padding-left: 20px;
            margin: 8px 0 0;
            color: #4b5563;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #ff3c40;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Manual Payment Portal</h2>
        <p>Deposit safely via UPI or USDT</p>
    </div>
    
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success">
            <?php echo $success_msg; ?>
        </div>
        <div class="back-link">
            <a href="https://89club-production.up.railway.app/#/main">Go Back to App</a>
        </div>
    <?php else: ?>
        
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>
        
        <div class="amount-box">
            <span>Amount to Recharge</span>
            <div class="value">
                <?php if ($method === 'usdt'): ?>
                    $<?php echo number_format((float)$amount, 2); ?> USDT
                <?php else: ?>
                    ₹<?php echo number_format((float)$amount, 2); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="payment-details">
            <?php if ($method === 'usdt'): ?>
                <span style="font-size: 13px; color: #a8a29e; display:block; margin-bottom: 8px;">Pay to USDT (TRC-20) Address:</span>
                <div class="address-row">
                    <div class="address-text" id="pay-address"><?php echo $usdt_address; ?></div>
                    <button class="copy-btn" onclick="copyAddress()">Copy</button>
                </div>
            <?php else: ?>
                <span style="font-size: 13px; color: #a8a29e; display:block; margin-bottom: 12px;">Scan the QR Code or copy the UPI ID below to pay:</span>
                <img src="<?php echo $qr_url; ?>" alt="UPI QR Code"><br>
                <div class="address-row">
                    <div class="address-text" id="pay-address"><?php echo $upi_id; ?></div>
                    <button class="copy-btn" onclick="copyAddress()">Copy</button>
                </div>
            <?php endif; ?>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="uid" value="<?php echo $uid; ?>">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="method" value="<?php echo $method; ?>">
            
            <div class="form-group">
                <label for="utr">
                    <?php if ($method === 'usdt'): ?>
                        Transaction Hash / TxID
                    <?php else: ?>
                        UTR / 12-Digit Reference Number
                    <?php endif; ?>
                </label>
                <input type="text" id="utr" name="utr" placeholder="Enter Transaction Reference" required>
            </div>
            
            <div class="form-group">
                <label for="screenshot">Upload Payment Receipt / Screenshot (Optional)</label>
                <input type="file" id="screenshot" name="screenshot" accept="image/*">
            </div>
            
            <button type="submit" class="btn-submit">Submit Payment Proof</button>
        </form>
        
        <div class="instructions">
            <strong>How to pay:</strong>
            <ol>
                <?php if ($method === 'usdt'): ?>
                    <li>Copy the USDT TRC-20 wallet address.</li>
                    <li>Open your exchange or wallet (Binance, Trust Wallet, etc.).</li>
                    <li>Withdraw the exact USDT amount shown above to our address.</li>
                    <li>Copy the TxID (Transaction Hash), paste it above, and submit.</li>
                <?php else: ?>
                    <li>Scan the QR code or copy the UPI ID shown above.</li>
                    <li>Open your payment app (PhonePe, Paytm, GPay, Bhim).</li>
                    <li>Pay the exact amount: ₹<?php echo number_format((float)$amount, 2); ?>.</li>
                    <li>Find the UTR / Ref Number in your payment history, paste it above, and submit.</li>
                <?php endif; ?>
            </ol>
        </div>
    <?php endif; ?>
</div>

<script>
function copyAddress() {
    var text = document.getElementById("pay-address").innerText;
    navigator.clipboard.writeText(text).then(function() {
        alert("Payment address copied to clipboard!");
    }, function() {
        // Fallback
        var temp = document.createElement("input");
        document.body.appendChild(temp);
        temp.value = text;
        temp.select();
        document.execCommand("copy");
        document.body.removeChild(temp);
        alert("Payment address copied!");
    });
}
</script>
</body>
</html>
