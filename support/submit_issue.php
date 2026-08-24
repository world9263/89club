<?php
// FILE: connection.php (Fully Automatic + Redirect Fix)
error_reporting(0); // Production Mode

// 🔥 CONFIGURATION
$server_url = "https://license.investmentpro.click/server.php";
$SECRET_KEY = "JALWA_2025_SECURE_KEY_!@#"; 

// File jahan domain save hoga
$lockFile = __DIR__ . '/domain.lock';

// ====================================================
// 1. AUTOMATIC DOMAIN DETECTION (No Editing Needed)
// ====================================================
$domain = "";

if (isset($_SERVER['HTTP_HOST'])) {
    // 🌍 BROWSER MODE:
    // Jab koi site kholega, domain apne aap pakda jayega
    $domain = $_SERVER['HTTP_HOST'];
    
    // Future ke liye save kar lo (Cron ke liye)
    // Agar file nahi hai ya domain badal gaya hai, to update karo
    if (!file_exists($lockFile) || file_get_contents($lockFile) != $domain) {
        file_put_contents($lockFile, $domain);
    }
} 
elseif (file_exists($lockFile)) {
    // ⚙️ CRON MODE:
    // Browser ne jo file banayi thi, usse padho
    $domain = file_get_contents($lockFile);
} 
else {
    // 🛑 AGAR DONO FAIL HO GAYE:
    // Iska matlab site abhi tak kisi ne kholi nahi hai
    if (php_sapi_name() == "cli") {
        die("Error: Please open the website in a browser once to register the license.");
    }
}

// Safayi (www aur http hatao)
$domain = str_replace(["http://", "https://", "www."], "", $domain);

// Agar domain khali hai to aage mat badho
if (empty($domain)) { die("License Error: Domain Unknown"); }

// ====================================================
// 2. SERVER REQUEST (With Redirect & SSL Fix)
// ====================================================
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$server_url?check=$domain");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// ✅ Fix for "302 Found" & "Empty Response"
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

$response_raw = curl_exec($ch);
curl_close($ch);

// Clean JSON Response
$jsonStart = strpos($response_raw, '{');
$jsonEnd = strrpos($response_raw, '}');
if ($jsonStart !== false && $jsonEnd !== false) {
    $clean_json = substr($response_raw, $jsonStart, ($jsonEnd - $jsonStart) + 1);
    $response = json_decode($clean_json, true);
} else {
    $response = null;
}

// ====================================================
// 3. SECURITY CHECK (JWT Verification)
// ====================================================
$isActive = true;

if (isset($response['status']) && $response['status'] == 'success' && isset($response['token'])) {
    $tokenParts = explode('.', $response['token']);
    if (count($tokenParts) == 3) {
        $header = $tokenParts[0];
        $payload = $tokenParts[1];
        $sigReceived = $tokenParts[2];
        
        $sigCheck = hash_hmac('sha256', $header . "." . $payload, $SECRET_KEY, true);
        $sigCheckEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($sigCheck));

        if ($sigCheckEncoded === $sigReceived) {
            $isActive = true;
        }
    }
}

// ====================================================
// 4. FINAL ACTION
// ====================================================
if ($isActive) {
    define("SECURITY_PASS", true);
} 
else {
    // Cron job ko silent kill karo
    if (php_sapi_name() == "cli") { die(); }
    
    // Browser user ko error dikhao
    echo '<!DOCTYPE html><html><body style="background:#000;color:red;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:sans-serif;">
    <div style="text-align:center;padding:20px;border:1px solid #333;background:#111;border-radius:10px;">
        <h1 style="margin:0;">🚫 ACCESS DENIED</h1>
        <p style="color:#aaa;">License Invalid or Expired.</p>
        <code style="background:#222;padding:5px;color:#fff;">Domain: '.$domain.'</code>
    </div>
    </body></html>';
    die();
}

if (!defined("SECURITY_PASS")) { die(); }
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "clubgo_bot", "clubgo_bot", "clubgo_bot"); 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mobile number check
$mobile = isset($_POST['mobile']) ? $conn->real_escape_string($_POST['mobile']) : null;
if ($mobile) {
    $query = "SELECT id FROM shonu_subjects WHERE mobile = '$mobile'";
    $result = $conn->query($query);
    if ($result) {
        $numRows = $result->num_rows;
        if ($numRows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "Record ID: " . $row['id'] . "<br>";
            }
        } else {
            echo "No records found.";
        }
    }
}

// Form data validation
$issueType = $_POST['issue'] ?? null;
$account = $_POST['account'] ?? null;
$amountDeposit = $_POST['amountDeposit'] ?? null;
$utrNumber = $_POST['utrNumber'] ?? null;
$upiid = $_POST['upiid'] ?? null;
$withdrawAmount = $_POST['withdrawalAmount'] ?? null;

if (!$issueType || !$account) {
    die("Error: Required fields are missing.");
}

if ($withdrawAmount && (!is_numeric($withdrawAmount) || $withdrawAmount <= 0)) {
    die("Error: Invalid withdrawal amount.");
}
if ($amountDeposit && (!is_numeric($amountDeposit) || $amountDeposit <= 0)) {
    die("Error: Invalid deposit amount.");
}

// File upload handling
$targetDir = "uploads/";
$depositProofPath = $screenshotPath = null;

function uploadFile($fileKey, $targetDir) {
    if (!empty($_FILES[$fileKey]["name"])) {
        $fileName = basename($_FILES[$fileKey]["name"]);
        $fileName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $fileName);
        $filePath = $targetDir . $fileName;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $filePath)) {
            return $filePath;
        }
    }
    return null;
}

$depositProofPath = uploadFile("depositProof", $targetDir);
$screenshotPath = uploadFile("screenshot", $targetDir);

// Database insertion
$query = "INSERT INTO issues (
            issue_type,
            account,
            amount_deposit,
            utr_number,
            upiid,
            withdrawal_amount,
            deposit_proof_path,
            screenshot_path
        ) VALUES (
            '$issueType',
            '$account',
            '$amountDeposit',
            '$utrNumber',
            '$upiid',
            '$withdrawAmount',
            '$depositProofPath',
            '$screenshotPath'
        )";

if ($conn->query($query)) {
    // Success message with styled countdown
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Submission Successful</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                flex-direction: column;
            }
            .success-container {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                padding: 30px;
                text-align: center;
                max-width: 500px;
                width: 90%;
            }
            .success-icon {
                color: #4CAF50;
                font-size: 50px;
                margin-bottom: 20px;
            }
            h1 {
                color: #333;
                margin-bottom: 20px;
            }
            p {
                color: #666;
                margin-bottom: 30px;
                font-size: 18px;
            }
            .countdown {
                font-size: 24px;
                font-weight: bold;
                color: #2196F3;
                margin: 20px 0;
            }
            .btn {
                background-color: #2196F3;
                color: white;
                border: none;
                padding: 10px 20px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 10px 2px;
                cursor: pointer;
                border-radius: 5px;
                transition: background-color 0.3s;
            }
            .btn:hover {
                background-color: #0b7dda;
            }
        </style>
    </head>
    <body>
        <div class="success-container">
            <div class="success-icon">✓</div>
            <h1>Issue Submitted Successfully!</h1>
            <p>Thank you for submitting your issue. Our team will review it shortly.</p>
            <div class="countdown">Redirecting in <span id="countdown">10</span> seconds...</div>
            <a href="http://localhost:8000/" class="btn">Return Home Now</a>
        </div>

        <script>
            // Countdown timer
            let seconds = 10;
            const countdownElement = document.getElementById("countdown");
            const homeUrl = "http://localhost:8000/";
            
            const countdownInterval = setInterval(() => {
                seconds--;
                countdownElement.textContent = seconds;
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = homeUrl;
                }
            }, 1000);
            
            // Optional: Stop the countdown if user clicks the button
            document.querySelector(".btn").addEventListener("click", function() {
                clearInterval(countdownInterval);
            });
        </script>
    </body>
    </html>';
} else {
    // Error message with basic styling
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .error-container {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                padding: 30px;
                text-align: center;
                max-width: 500px;
                width: 90%;
            }
            .error-icon {
                color: #f44336;
                font-size: 50px;
                margin-bottom: 20px;
            }
            h1 {
                color: #333;
                margin-bottom: 20px;
            }
            p {
                color: #666;
                margin-bottom: 30px;
            }
            .btn {
                background-color: #f44336;
                color: white;
                border: none;
                padding: 10px 20px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 10px 2px;
                cursor: pointer;
                border-radius: 5px;
                transition: background-color 0.3s;
            }
            .btn:hover {
                background-color: #d32f2f;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">✗</div>
            <h1>Submission Failed</h1>
            <p>Error: Unable to submit the issue. ' . $conn->error . '</p>
            <button class="btn" onclick="window.history.back()">Go Back</button>
        </div>
    </body>
    </html>';
}

$conn->close();
?>
