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
include "../../conn.php";

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
$phone = $requestData['uid'] ?? '';

if (empty($phone) || !ctype_alnum($phone) || strlen($phone) > 30) {
    $response = [
        'status' => '0001',
        'balance' => 0,
        'err_text' => 'Invalid UID'
    ];
    echo json_encode($response);
    exit();
}

// Query to get user ID by phone
$query = "SELECT id FROM shonu_subjects WHERE mobile = '$phone'";
$result = mysqli_query($conn, $query);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
} else {
    $response = [
        'status' => '0001',
        'balance' => 0,
        'err_text' => 'User not found'
    ];
    echo json_encode($response);
    exit();
}

// Define $amount globally for all cases
$amount = $requestData['amount'] ?? 0;
$bet = $requestData['bet'] ?? null;
$win = $requestData['win'] ?? null;

// Switch statement based on action
switch ($requestData['action'] ?? null) {
    case 6:
        // Case 6: Retrieve balance
        $query = "SELECT motta FROM shonu_kaichila WHERE balakedara = $id";
        $result = mysqli_query($conn, $query);

        if ($result && $row = mysqli_fetch_assoc($result)) {
            $balance = $row['motta'];
            $response = [
                'status' => '0000',
                'balance' => $balance,
                'err_text' => ''
            ];
        } else {
            $response = [
                'status' => '0001',
                'balance' => 0,
                'err_text' => 'User not found'
            ];
        }
        break;

    case 8:
        if ($bet === null || $win === null || !is_numeric($bet) || !is_numeric($win)) {
            $response = [
                'status' => '0001',
                'balance' => 0,
                'err_text' => 'Invalid parameters'
            ];
            echo json_encode($response);
            exit();
        }

        // Check if bet has already been processed
        $checkBetQuery = "SELECT bet_status FROM shonu_kaichila WHERE balakedara = $id";
        $checkBetResult = mysqli_query($conn, $checkBetQuery);
        

        // Retrieve the current balance
        $query = "SELECT motta FROM shonu_kaichila WHERE balakedara = $id";
        $result = mysqli_query($conn, $query);

        if ($result && $row = mysqli_fetch_assoc($result)) {
            $currentBalance = $row['motta'];
            $newBalance = $currentBalance + $win - abs($bet);

            // Update the balance
            $updateQuery = "UPDATE shonu_kaichila SET motta = $newBalance WHERE balakedara = $id";
            $updateResult = mysqli_query($conn, $updateQuery);

            // Update the 'vip' and 'shonu_kaichila' tables for expe and rebet
            $updateVipQuery = "UPDATE vip SET expe = expe + $bet WHERE userid = $id";
            $updateRebetQuery = "UPDATE shonu_kaichila SET rebet = rebet + $bet WHERE balakedara = $id";

            mysqli_query($conn, $updateVipQuery);
            mysqli_query($conn, $updateRebetQuery);

            if ($updateResult) {
                $response = [
                    'status' => '0000',
                    'balance' => $newBalance,
                    'err_text' => ''
                ];
            } else {
                $response = [
                    'status' => '0001',
                    'balance' => 0,
                    'err_text' => 'Balance update failed'
                ];
            }
        } else {
            $response = [
                'status' => '0001',
                'balance' => 0,
                'err_text' => 'User not found'
            ];
        }
        break;

    case 9:
        if ($amount <= 0) {
            $response = [
                'status' => '0001',
                'balance' => 0,
                'err_text' => 'Invalid amount'
            ];
        } else {
            $updateQuery = "UPDATE shonu_kaichila SET motta = motta - $amount WHERE balakedara = $id AND motta >= $amount";
            $updateResult = mysqli_query($conn, $updateQuery);

            // Update the 'vip' and 'shonu_kaichila' tables for expe and rebet on bet cancel
            $updateVipQuery = "UPDATE vip SET expe = expe + $amount WHERE userid = $id";
            $updateRebetQuery = "UPDATE shonu_kaichila SET rebet = rebet + $amount WHERE balakedara = $id";

            mysqli_query($conn, $updateVipQuery);
            mysqli_query($conn, $updateRebetQuery);

            if (mysqli_affected_rows($conn) > 0) {
                $query = "SELECT motta FROM shonu_kaichila WHERE balakedara = $id";
                $result = mysqli_query($conn, $query);
                $row = mysqli_fetch_assoc($result);
                $balance = $row['motta'];

                $response = [
                    'status' => '0000',
                    'balance' => $balance,
                    'err_text' => ''
                ];
            } else {
                $response = [
                    'status' => '0001',
                    'balance' => 0,
                    'err_text' => 'Insufficient balance or user not found'
                ];
            }
        }
        break;

    case 10:
        // Case 10: Add specified amount to user's balance in shonu_kaichila
        logData(['action' => $requestData['action'], 'parameters' => $requestData], 'action_logs');

        $amount = $requestData['amount'] ?? 0;

        if (!isset($id) || $amount <= 0) {
            $response = [
                'status' => '0001',
                'balance' => 0,
                'err_text' => 'Invalid parameters'
            ];
        } else {
            // Fetch user's current balance
            $query = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$id'";
            $result = mysqli_query($conn, $query);

            if ($result && $row = mysqli_fetch_assoc($result)) {
                $currentBalance = $row['motta'];
                $newBalance = $currentBalance + $amount;

                // Update balance
                $updateQuery = "UPDATE shonu_kaichila SET motta = $newBalance WHERE balakedara = '$id'";
                $updateResult = mysqli_query($conn, $updateQuery);

                if ($updateResult) {
                    $response = [
                        'status' => '0000',
                        'balance' => $newBalance,
                        'err_text' => ''
                    ];
                } else {
                    error_log('Database query failed: ' . mysqli_error($conn));
                    $response = [
                        'status' => '0002',
                        'balance' => 0,
                        'err_text' => 'Database error'
                    ];
                }
            } else {
                $response = [
                    'status' => '0001',
                    'balance' => 0,
                    'err_text' => 'User not found'
                ];
            }
        }
        break;

    case 11:
        // Case 11: Add specified amount only if balance allows cancellation in shonu_kaichila
        logData(['action' => $requestData['action'], 'parameters' => $requestData], 'action_logs');

        $amount = $requestData['amount'] ?? 0;

        if (!isset($id) || $amount <= 0) {
            $response = [
                'status' => '0001',
                'balance' => 0,
                'err_text' => 'Invalid parameters'
            ];
        } else {
            // Start transaction
            mysqli_begin_transaction($conn);

            try {
                // Fetch user's current balance
                $query = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$id'";
                $result = mysqli_query($conn, $query);

                if ($result && $row = mysqli_fetch_assoc($result)) {
                    $currentBalance = $row['motta'];

                    // Check if balance is sufficient for cancellation
                    if ($currentBalance >= $amount) {
                        $newBalance = $currentBalance + $amount;

                        // Update balance
                        $updateQuery = "UPDATE shonu_kaichila SET motta = $newBalance WHERE balakedara = '$id'";
                        $updateResult = mysqli_query($conn, $updateQuery);

                        if ($updateResult) {
                            // Commit transaction
                            mysqli_commit($conn);
                            $response = [
                                'status' => '0000',
                                'balance' => $newBalance,
                                'err_text' => ''
                            ];
                        } else {
                            throw new Exception("Balance update failed.");
                        }
                    } else {
                        // Rollback if insufficient balance
                        mysqli_rollback($conn);
                        $response = [
                            'status' => '0001',
                            'balance' => 0,
                            'err_text' => 'Insufficient balance'
                        ];
                    }
                } else {
                    // Rollback if user is not found
                    mysqli_rollback($conn);
                    $response = [
                        'status' => '0001',
                        'balance' => 0,
                        'err_text' => 'User not found'
                    ];
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                mysqli_rollback($conn);
                error_log('Transaction failed: ' . $e->getMessage());
                $response = [
                    'status' => '0002',
                    'balance' => 0,
                    'err_text' => 'Database error'
                ];
            }
        }
        break;

    default:
        $response = [
            'status' => '0003',
            'balance' => 0,
            'err_text' => 'Invalid action'
        ];
        break;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
