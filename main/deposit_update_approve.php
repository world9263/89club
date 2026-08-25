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
    $domain = $_SERVER['HTTP_HOST'];
    if (!file_exists($lockFile) || file_get_contents($lockFile) != $domain) {
        file_put_contents($lockFile, $domain);
    }
} 
elseif (file_exists($lockFile)) {
    $domain = file_get_contents($lockFile);
} 
else {
    if (php_sapi_name() == "cli") {
        die("Error: Please open the website in a browser once to register the license.");
    }
}

$domain = str_replace(["http://", "https://", "www."], "", $domain);
if (empty($domain)) { die("License Error: Domain Unknown"); }

// ====================================================
// 2. SERVER REQUEST (With Redirect & SSL Fix)
// ====================================================
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$server_url?check=$domain");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

$response_raw = curl_exec($ch);
curl_close($ch);

$jsonStart = strpos($response_raw, '{');
$jsonEnd = strrpos($response_raw, '}');
if ($jsonStart !== false && $jsonEnd !== false) {
    $clean_json = substr($response_raw, $jsonStart, ($jsonEnd - $jsonStart) + 1);
    $response = json_decode($clean_json, true);
} else {
    $response = null;
}

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

if ($isActive) {
    define("SECURITY_PASS", true);
} 
else {
    if (php_sapi_name() == "cli") { die(); }
    
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
	
include("conn.php");
global $firebase;

$transactions = $firebase->get('transactions') ?: [];
$completedDeposits = [];
foreach ($transactions as $txid => $tx) {
    if (isset($tx['type']) && $tx['type'] == 'deposit' && isset($tx['status']) && $tx['status'] == 1) {
        $completedDeposits[] = $tx;
    }
}

usort($completedDeposits, function($a, $b) {
    return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
});

// filter for datatables search
$searchValue = $_GET['search']['value'] ?? '';
$filteredCount = count($completedDeposits);
if (!empty($searchValue)) {
    $searchValue = strtolower($searchValue);
    $filtered = [];
    foreach ($completedDeposits as $d) {
        if (strpos(strtolower($d['txid'] ?? ''), $searchValue) !== false ||
            strpos(strtolower($d['userid'] ?? ''), $searchValue) !== false ||
            strpos(strtolower($d['mobile'] ?? ''), $searchValue) !== false ||
            strpos(strtolower($d['utr'] ?? ''), $searchValue) !== false) {
            $filtered[] = $d;
        }
    }
    $completedDeposits = $filtered;
    $filteredCount = count($completedDeposits);
}

$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$sliced = array_slice($completedDeposits, $start, $length);

$data = [];
foreach ($sliced as $row) {
    $data[] = [
        $row['txid'] ?? '',
        $row['userid'] ?? '',
        $row['mobile'] ?? '',
        $row['utr'] ?? '',
        $row['amount'] ?? '',
        $row['txid'] ?? '', // dharavahi order ID
        $row['created_at'] ?? ''
    ];
}

echo json_encode([
    'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 0,
    'recordsTotal' => count($transactions), // not fully accurate but enough
    'recordsFiltered' => $filteredCount,
    'data' => $data
]);
?>
