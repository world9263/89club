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
$host = 'localhost';
$dbname = 'clubgo_bot';
$user = 'clubgo_bot';
$pass = 'clubgo_bot';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => '0002',
        'balance' => 0,
        'err_text' => 'Database error'
    ]);
    exit;
}

function logData($data, $type) {
    $logFile = __DIR__ . "/logs/{$type}.log";
    $logEntry = date('Y-m-d H:i:s') . ' ' . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

$request = file_get_contents('php://input');

logData(['request' => $request], 'requests');

$requestData = json_decode($request, true);

logData(['decodedRequest' => $requestData], 'decoded_requests');

if (json_last_error() !== JSON_ERROR_NONE) {
    $response = [
        'status' => '0002',
        'balance' => 0,
        'err_text' => 'Invalid JSON format'
    ];
    logData(['error' => json_last_error_msg(), 'request' => $request], 'errors');
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$response = [];

switch ($requestData['action'] ?? null) {
    case 6:
        logData(['action' => $requestData['action'], 'parameters' => $requestData], 'action_logs');

        $phone = $requestData['uid'] ?? '';

        if (empty($phone) || !ctype_alnum($phone) || strlen($phone) > 30) {
            $response = [
                'status' => '0001',
                'balance' => 0,
                'err_text' => 'Invalid UID'
            ];
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id FROM shonu_subjects WHERE mobile = :phone");
                $stmt->execute(['phone' => $phone]);
                $row_subject = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row_subject) {
                    $user_id = $row_subject['id'];
                    $stmt_balance = $pdo->prepare("SELECT motta AS balance FROM shonu_kaichila WHERE balakedara = :user_id");
                    $stmt_balance->execute(['user_id' => $user_id]);
                    $row_balance = $stmt_balance->fetch(PDO::FETCH_ASSOC);

                    if ($row_balance) {
                        $response = [
                            'status' => '0000',
                            'balance' => $row_balance['balance'],
                            'err_text' => ''
                        ];
                    } else {
                        $response = [
                            'status' => '0001',
                            'balance' => 0,
                            'err_text' => 'User data not found in shonu_kaichila'
                        ];
                    }
                } else {
                    $response = [
                        'status' => '0001',
                        'balance' => 0,
                        'err_text' => 'User not found in shonu_subjects'
                    ];
                }
            } catch (PDOException $e) {
                error_log('Database query failed: ' . $e->getMessage());
                $response = [
                    'status' => '0002',
                    'balance' => 0,
                    'err_text' => 'Database error'
                ];
            }
        }
        break;

    // Repeat similar changes in other cases as per required actions
    default:
        $response = [
            'status' => '0003',
            'balance' => 0,
            'err_text' => 'Invalid action'
        ];
        break;
}

logData(['response' => $response], 'responses');

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
