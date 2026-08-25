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
include "../../functions2.php";
	global $firebase;

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");
$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['language'], $shonupost['random'], $shonupost['signature'], $shonupost['timestamp'])) {
        $language = $shonupost['language'];
        $random = $shonupost['random'];
        $signature = $shonupost['signature'];
        
        $shonustr = '{"language":'.$language.',"random":"'.$random.'"}';
        $shonusign = strtoupper(md5($shonustr));
        
        if ($shonusign) {
            $authHeader = isset(<?php 
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
include "../../functions2.php";
	global $firebase;

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");
$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['language'], $shonupost['random'], $shonupost['signature'], $shonupost['timestamp'])) {
        $language = $shonupost['language'];
        $random = $shonupost['random'];
        $signature = $shonupost['signature'];
        
        $shonustr = '{"language":'.$language.',"random":"'.$random.'"}';
        $shonusign = strtoupper(md5($shonustr));
        
        if ($shonusign) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];                
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, true);
            if ($data_auth['status']) {
                $sesquery = "SELECT id, akshinak FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);
                
                if ($sesnum == 1) {
                    $sesresult = $sesresult->fetch_assoc();
                    $userId = $sesresult['id'];

                    // Fetch the level from the VIP table
                    $lvlquery = "SELECT lvl FROM vip WHERE userid = '$userId'";
                    $lvlresult = $conn->query($lvlquery);
                    $lvldata = $lvlresult->fetch_assoc();
                    $lvl = $lvldata['lvl'];

                    // Determine wash rate based on level
                    $washRate = null;
                    if (in_array($lvl, [0,1, 2])) {
                        $washRate = 0.05;
                    } elseif (in_array($lvl, [3, 4, 5])) {
                        $washRate = 0.1;
                    } elseif (in_array($lvl, [6, 7, 8])) {
                        $washRate = 0.15;
                    } elseif ($lvl == 9) {
                        $washRate = 0.2;
                    } elseif ($lvl == 10) {
                        $washRate = 0.3;
                    }

                    // Fetch the rebated amount
                    $reamountq = "SELECT rebet, motta FROM shonu_kaichila WHERE balakedara = '$userId'";
                    $reamount = $conn->query($reamountq);
                    $rc = $reamount->fetch_assoc();
                    $rebet = $rc['rebet'];
                    $motta = $rc['motta'];

                    // Calculate the new motta
                    $calculatedMotta = ($rebet / 100) * $washRate;
                    $newMotta = $motta + $calculatedMotta;

                    // Update motta and set rebet to 0
                    $updateQuery = "UPDATE shonu_kaichila SET motta = '$newMotta', rebet = 0 WHERE balakedara = '$userId'";
                    $conn->query($updateQuery);


                   if ($rebet > 0) {
    
                   $insertVyavahara = "INSERT INTO rebetrec (user_id, rebet, lvl, motta, rate, created_at) 
                    VALUES ('$userId', '$rebet', 'LVLCOMM$lvl', '$calculatedMotta', '$washRate', '$shnunc')";
                   $conn->query($insertVyavahara);

                      }

                    // Prepare the response
                    $res['data'] = [
                        'rebateAmount' => (int)$calculatedMotta
                    ];
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    $res['serviceNowTime'] = $shnunc;

                    http_response_code(200);
                    echo json_encode($res);
                } else {
                    // Existing response structure
                    $res['data'] = [
                        'msgCode' => 904,
                        'bindingType' => 1,
                        'rewardType' => null,
                        'rewardSetting' => "",
                        'sumRotateNum' => 0,
                        'surplusRotateNum' => 0,
                    ];
                    $res['code'] = 1;
                    $res['msg'] = 'no user fetch';
                    $res['msgCode'] = 904;
                    $res['serviceNowTime'] = $shnunc;

                    http_response_code(200);
                    echo json_encode($res);
                }
            } else {                    
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);                    
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);                
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);            
    }        
} else {        
    http_response_code(405);
    echo json_encode($res);
}
?>
SERVER['HTTP_AUTHORIZATION']) ? <?php 
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
include "../../functions2.php";
	global $firebase;

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");
$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['language'], $shonupost['random'], $shonupost['signature'], $shonupost['timestamp'])) {
        $language = $shonupost['language'];
        $random = $shonupost['random'];
        $signature = $shonupost['signature'];
        
        $shonustr = '{"language":'.$language.',"random":"'.$random.'"}';
        $shonusign = strtoupper(md5($shonustr));
        
        if ($shonusign) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];                
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, true);
            if ($data_auth['status']) {
                $sesquery = "SELECT id, akshinak FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);
                
                if ($sesnum == 1) {
                    $sesresult = $sesresult->fetch_assoc();
                    $userId = $sesresult['id'];

                    // Fetch the level from the VIP table
                    $lvlquery = "SELECT lvl FROM vip WHERE userid = '$userId'";
                    $lvlresult = $conn->query($lvlquery);
                    $lvldata = $lvlresult->fetch_assoc();
                    $lvl = $lvldata['lvl'];

                    // Determine wash rate based on level
                    $washRate = null;
                    if (in_array($lvl, [0,1, 2])) {
                        $washRate = 0.05;
                    } elseif (in_array($lvl, [3, 4, 5])) {
                        $washRate = 0.1;
                    } elseif (in_array($lvl, [6, 7, 8])) {
                        $washRate = 0.15;
                    } elseif ($lvl == 9) {
                        $washRate = 0.2;
                    } elseif ($lvl == 10) {
                        $washRate = 0.3;
                    }

                    // Fetch the rebated amount
                    $reamountq = "SELECT rebet, motta FROM shonu_kaichila WHERE balakedara = '$userId'";
                    $reamount = $conn->query($reamountq);
                    $rc = $reamount->fetch_assoc();
                    $rebet = $rc['rebet'];
                    $motta = $rc['motta'];

                    // Calculate the new motta
                    $calculatedMotta = ($rebet / 100) * $washRate;
                    $newMotta = $motta + $calculatedMotta;

                    // Update motta and set rebet to 0
                    $updateQuery = "UPDATE shonu_kaichila SET motta = '$newMotta', rebet = 0 WHERE balakedara = '$userId'";
                    $conn->query($updateQuery);


                   if ($rebet > 0) {
    
                   $insertVyavahara = "INSERT INTO rebetrec (user_id, rebet, lvl, motta, rate, created_at) 
                    VALUES ('$userId', '$rebet', 'LVLCOMM$lvl', '$calculatedMotta', '$washRate', '$shnunc')";
                   $conn->query($insertVyavahara);

                      }

                    // Prepare the response
                    $res['data'] = [
                        'rebateAmount' => (int)$calculatedMotta
                    ];
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    $res['serviceNowTime'] = $shnunc;

                    http_response_code(200);
                    echo json_encode($res);
                } else {
                    // Existing response structure
                    $res['data'] = [
                        'msgCode' => 904,
                        'bindingType' => 1,
                        'rewardType' => null,
                        'rewardSetting' => "",
                        'sumRotateNum' => 0,
                        'surplusRotateNum' => 0,
                    ];
                    $res['code'] = 1;
                    $res['msg'] = 'no user fetch';
                    $res['msgCode'] = 904;
                    $res['serviceNowTime'] = $shnunc;

                    http_response_code(200);
                    echo json_encode($res);
                }
            } else {                    
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);                    
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);                
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);            
    }        
} else {        
    http_response_code(405);
    echo json_encode($res);
}
?>
SERVER['HTTP_AUTHORIZATION'] : (isset(<?php 
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
include "../../functions2.php";
	global $firebase;

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");
$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['language'], $shonupost['random'], $shonupost['signature'], $shonupost['timestamp'])) {
        $language = $shonupost['language'];
        $random = $shonupost['random'];
        $signature = $shonupost['signature'];
        
        $shonustr = '{"language":'.$language.',"random":"'.$random.'"}';
        $shonusign = strtoupper(md5($shonustr));
        
        if ($shonusign) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];                
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, true);
            if ($data_auth['status']) {
                $sesquery = "SELECT id, akshinak FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);
                
                if ($sesnum == 1) {
                    $sesresult = $sesresult->fetch_assoc();
                    $userId = $sesresult['id'];

                    // Fetch the level from the VIP table
                    $lvlquery = "SELECT lvl FROM vip WHERE userid = '$userId'";
                    $lvlresult = $conn->query($lvlquery);
                    $lvldata = $lvlresult->fetch_assoc();
                    $lvl = $lvldata['lvl'];

                    // Determine wash rate based on level
                    $washRate = null;
                    if (in_array($lvl, [0,1, 2])) {
                        $washRate = 0.05;
                    } elseif (in_array($lvl, [3, 4, 5])) {
                        $washRate = 0.1;
                    } elseif (in_array($lvl, [6, 7, 8])) {
                        $washRate = 0.15;
                    } elseif ($lvl == 9) {
                        $washRate = 0.2;
                    } elseif ($lvl == 10) {
                        $washRate = 0.3;
                    }

                    // Fetch the rebated amount
                    $reamountq = "SELECT rebet, motta FROM shonu_kaichila WHERE balakedara = '$userId'";
                    $reamount = $conn->query($reamountq);
                    $rc = $reamount->fetch_assoc();
                    $rebet = $rc['rebet'];
                    $motta = $rc['motta'];

                    // Calculate the new motta
                    $calculatedMotta = ($rebet / 100) * $washRate;
                    $newMotta = $motta + $calculatedMotta;

                    // Update motta and set rebet to 0
                    $updateQuery = "UPDATE shonu_kaichila SET motta = '$newMotta', rebet = 0 WHERE balakedara = '$userId'";
                    $conn->query($updateQuery);


                   if ($rebet > 0) {
    
                   $insertVyavahara = "INSERT INTO rebetrec (user_id, rebet, lvl, motta, rate, created_at) 
                    VALUES ('$userId', '$rebet', 'LVLCOMM$lvl', '$calculatedMotta', '$washRate', '$shnunc')";
                   $conn->query($insertVyavahara);

                      }

                    // Prepare the response
                    $res['data'] = [
                        'rebateAmount' => (int)$calculatedMotta
                    ];
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    $res['serviceNowTime'] = $shnunc;

                    http_response_code(200);
                    echo json_encode($res);
                } else {
                    // Existing response structure
                    $res['data'] = [
                        'msgCode' => 904,
                        'bindingType' => 1,
                        'rewardType' => null,
                        'rewardSetting' => "",
                        'sumRotateNum' => 0,
                        'surplusRotateNum' => 0,
                    ];
                    $res['code'] = 1;
                    $res['msg'] = 'no user fetch';
                    $res['msgCode'] = 904;
                    $res['serviceNowTime'] = $shnunc;

                    http_response_code(200);
                    echo json_encode($res);
                }
            } else {                    
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);                    
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);                
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);            
    }        
} else {        
    http_response_code(405);
    echo json_encode($res);
}
?>
SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? <?php 
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
include "../../functions2.php";
	global $firebase;

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");
$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['language'], $shonupost['random'], $shonupost['signature'], $shonupost['timestamp'])) {
        $language = $shonupost['language'];
        $random = $shonupost['random'];
        $signature = $shonupost['signature'];
        
        $shonustr = '{"language":'.$language.',"random":"'.$random.'"}';
        $shonusign = strtoupper(md5($shonustr));
        
        if ($shonusign) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];                
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, true);
            if ($data_auth['status']) {
                $sesquery = "SELECT id, akshinak FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);
                
                if ($sesnum == 1) {
                    $sesresult = $sesresult->fetch_assoc();
                    $userId = $sesresult['id'];

                    // Fetch the level from the VIP table
                    $lvlquery = "SELECT lvl FROM vip WHERE userid = '$userId'";
                    $lvlresult = $conn->query($lvlquery);
                    $lvldata = $lvlresult->fetch_assoc();
                    $lvl = $lvldata['lvl'];

                    // Determine wash rate based on level
                    $washRate = null;
                    if (in_array($lvl, [0,1, 2])) {
                        $washRate = 0.05;
                    } elseif (in_array($lvl, [3, 4, 5])) {
                        $washRate = 0.1;
                    } elseif (in_array($lvl, [6, 7, 8])) {
                        $washRate = 0.15;
                    } elseif ($lvl == 9) {
                        $washRate = 0.2;
                    } elseif ($lvl == 10) {
                        $washRate = 0.3;
                    }

                    // Fetch the rebated amount
                    $reamountq = "SELECT rebet, motta FROM shonu_kaichila WHERE balakedara = '$userId'";
                    $reamount = $conn->query($reamountq);
                    $rc = $reamount->fetch_assoc();
                    $rebet = $rc['rebet'];
                    $motta = $rc['motta'];

                    // Calculate the new motta
                    $calculatedMotta = ($rebet / 100) * $washRate;
                    $newMotta = $motta + $calculatedMotta;

                    // Update motta and set rebet to 0
                    $updateQuery = "UPDATE shonu_kaichila SET motta = '$newMotta', rebet = 0 WHERE balakedara = '$userId'";
                    $conn->query($updateQuery);


                   if ($rebet > 0) {
    
                   $insertVyavahara = "INSERT INTO rebetrec (user_id, rebet, lvl, motta, rate, created_at) 
                    VALUES ('$userId', '$rebet', 'LVLCOMM$lvl', '$calculatedMotta', '$washRate', '$shnunc')";
                   $conn->query($insertVyavahara);

                      }

                    // Prepare the response
                    $res['data'] = [
                        'rebateAmount' => (int)$calculatedMotta
                    ];
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    $res['serviceNowTime'] = $shnunc;

                    http_response_code(200);
                    echo json_encode($res);
                } else {
                    // Existing response structure
                    $res['data'] = [
                        'msgCode' => 904,
                        'bindingType' => 1,
                        'rewardType' => null,
                        'rewardSetting' => "",
                        'sumRotateNum' => 0,
                        'surplusRotateNum' => 0,
                    ];
                    $res['code'] = 1;
                    $res['msg'] = 'no user fetch';
                    $res['msgCode'] = 904;
                    $res['serviceNowTime'] = $shnunc;

                    http_response_code(200);
                    echo json_encode($res);
                }
            } else {                    
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);                    
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);                
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);            
    }        
} else {        
    http_response_code(405);
    echo json_encode($res);
}
?>
SERVER['REDIRECT_HTTP_AUTHORIZATION'] : ''); $bearer = explode(' ', $authHeader);
            $author = $bearer[1];                
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, true);
            if ($data_auth['status']) {
                $sesquery = "SELECT id, akshinak FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);
                
                if ($sesnum == 1) {
                    $sesresult = $sesresult->fetch_assoc();
                    $userId = $sesresult['id'];

                    // Fetch the level from the VIP table
                    $lvlquery = "SELECT lvl FROM vip WHERE userid = '$userId'";
                    $lvlresult = $conn->query($lvlquery);
                    $lvldata = $lvlresult->fetch_assoc();
                    $lvl = $lvldata['lvl'];

                    // Determine wash rate based on level
                    $washRate = null;
                    if (in_array($lvl, [0,1, 2])) {
                        $washRate = 0.05;
                    } elseif (in_array($lvl, [3, 4, 5])) {
                        $washRate = 0.1;
                    } elseif (in_array($lvl, [6, 7, 8])) {
                        $washRate = 0.15;
                    } elseif ($lvl == 9) {
                        $washRate = 0.2;
                    } elseif ($lvl == 10) {
                        $washRate = 0.3;
                    }

                    // Fetch the rebated amount
                    $reamountq = "SELECT rebet, motta FROM shonu_kaichila WHERE balakedara = '$userId'";
                    $reamount = $conn->query($reamountq);
                    $rc = $reamount->fetch_assoc();
                    $rebet = $rc['rebet'];
                    $motta = $rc['motta'];

                    // Calculate the new motta
                    $calculatedMotta = ($rebet / 100) * $washRate;
                    $newMotta = $motta + $calculatedMotta;

                    // Update motta and set rebet to 0
                    $updateQuery = "UPDATE shonu_kaichila SET motta = '$newMotta', rebet = 0 WHERE balakedara = '$userId'";
                    $conn->query($updateQuery);


                   if ($rebet > 0) {
    
                   $insertVyavahara = "INSERT INTO rebetrec (user_id, rebet, lvl, motta, rate, created_at) 
                    VALUES ('$userId', '$rebet', 'LVLCOMM$lvl', '$calculatedMotta', '$washRate', '$shnunc')";
                   $conn->query($insertVyavahara);

                      }

                    // Prepare the response
                    $res['data'] = [
                        'rebateAmount' => (int)$calculatedMotta
                    ];
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    $res['serviceNowTime'] = $shnunc;

                    http_response_code(200);
                    echo json_encode($res);
                } else {
                    // Existing response structure
                    $res['data'] = [
                        'msgCode' => 904,
                        'bindingType' => 1,
                        'rewardType' => null,
                        'rewardSetting' => "",
                        'sumRotateNum' => 0,
                        'surplusRotateNum' => 0,
                    ];
                    $res['code'] = 1;
                    $res['msg'] = 'no user fetch';
                    $res['msgCode'] = 904;
                    $res['serviceNowTime'] = $shnunc;

                    http_response_code(200);
                    echo json_encode($res);
                }
            } else {                    
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);                    
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);                
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);            
    }        
} else {        
    http_response_code(405);
    echo json_encode($res);
}
?>
