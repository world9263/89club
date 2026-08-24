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

	define('DB_SERVER', 'localhost');
	define('DB_USERNAME', 'clubgo_bot');
	define('DB_PASSWORD', 'clubgo_bot');
	define('DB_NAME', 'clubgo_bot');

	mysqli_report(MYSQLI_REPORT_OFF);
	if (!class_exists('MockMySQLi')) {
		class MockMySQLi extends mysqli {
			public function __construct() {}
			public function query($q, $r = MYSQLI_STORE_RESULT) { return false; }
			public function prepare($q) { return false; }
			public function close() { return true; }
			public function real_escape_string($s) { return $s; }
		}
	}
	$conn = new MockMySQLi();
	if($conn == false){
		dir('Error: Cannot connect');
		// echo "Fail";
	}
	
	$numbermappings = array("zero", "one","two","three", "four","five","six","seven","eight","nine");
	
	function getusercount($a,$periodid,$value)
	{
		$selectquery=mysqli_query($a,"select * from `bajikattuttate` where `kalaparichaya`='$periodid' and `ojana`in($value) group by `byabaharkarta`");
		$row=mysqli_num_rows($selectquery);
		return $row;
	}
	
	function winner($conn,$periodid,$column)
	{
		$query=mysqli_query($conn,"SELECT 
		SUM(CASE
			WHEN prakar = '0' THEN ketebida
		END) button,
		
		SUM(CASE
			WHEN ojana = '11' THEN ketebida
		END) as green,
		
		(SUM(CASE
			WHEN ojana = '11' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '11' THEN ketebida
		END)/100*2))*2 as greenwinamount,
		
		(SUM(CASE
			WHEN ojana = '11' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '11' THEN ketebida
		END)/100*2))*1.5 as greenwinamountwithviolet,
		
		SUM(CASE
			WHEN ojana = '12' THEN ketebida
		END) violet,
		
		(SUM(CASE
			WHEN ojana = '12' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '12' THEN ketebida
		END)/100*2))*4.5 as violetwinamount,
		
		SUM(CASE
			WHEN ojana = '10' THEN ketebida
		END) red,
		
		(SUM(CASE
			WHEN ojana = '10' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '10' THEN ketebida
		END)/100*2))*2 as redwinamount,
		(SUM(CASE
			WHEN ojana = '10' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '10' THEN ketebida
		END)/100*2))*1.5 as redwinamountwithviolet,
		
		SUM(CASE
			WHEN prakar = '1' THEN ketebida
		END) number,
		SUM(CASE
			WHEN ojana = '0' THEN ketebida
		END) `zero`,
		(SUM(CASE
			WHEN ojana = '0' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '0' THEN ketebida
		END)/100*2))*9 as zerowinamount,
		
		SUM(CASE
			WHEN ojana = '1' THEN ketebida
		END) `one`,
		(SUM(CASE
			WHEN ojana = '1' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '1' THEN ketebida
		END)/100*2))*9 as onewinamount,
		
		SUM(CASE
			WHEN ojana = '2' THEN ketebida
		END) `two`,
		(SUM(CASE
			WHEN ojana = '2' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '2' THEN ketebida
		END)/100*2))*9 as twowinamount,
		
		SUM(CASE
			WHEN ojana = '3' THEN ketebida
		END) `three`,
		(SUM(CASE
			WHEN ojana = '3' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '3' THEN ketebida
		END)/100*2))*9 as threewinamount,
		
		SUM(CASE
			WHEN ojana = '4' THEN ketebida
		END) `four`,
		(SUM(CASE
			WHEN ojana = '4' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '4' THEN ketebida
		END)/100*2))*9 as fourwinamount,
		
		SUM(CASE
			WHEN ojana = '5' THEN ketebida
		END) `five`,
		(SUM(CASE
			WHEN ojana = '5' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '5' THEN ketebida
		END)/100*2))*9 as fivewinamount,
		
		SUM(CASE
			WHEN ojana = '6' THEN ketebida
		END) `six`,
		(SUM(CASE
			WHEN ojana = '6' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '6' THEN ketebida
		END)/100*2))*9 as sixwinamount,
		
		SUM(CASE
			WHEN ojana = '7' THEN ketebida
		END) `seven`,
		(SUM(CASE
			WHEN ojana = '7' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '7' THEN ketebida
		END)/100*2))*9 as sevenwinamount,
		
		SUM(CASE
			WHEN ojana = '8' THEN ketebida
		END) `eight`,
		(SUM(CASE
			WHEN ojana = '8' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '8' THEN ketebida
		END)/100*2))*9 as eightwinamount,
		
		SUM(CASE
			WHEN ojana = '9' THEN ketebida
		END) `nine`,
		(SUM(CASE
			WHEN ojana = '9' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '9' THEN ketebida
		END)/100*2))*9 as ninewinamount,
		
		(SUM(CASE
			WHEN ojana = '13' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '13' THEN ketebida
		END)/100*2))*2 as bigwinamount,
		
		(SUM(CASE
			WHEN ojana = '14' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '14' THEN ketebida
		END)/100*2))*2 as smallwinamount
			
		FROM
		`bajikattuttate` where `kalaparichaya`='$periodid'");
		$result=mysqli_fetch_array($query);	
		return $result["$column"];	
	}
	
	function rlamt($conn,$periodid,$column)
	{
		$query=mysqli_query($conn,"SELECT 
		SUM(CASE
			WHEN prakar = '0' THEN ketebida
		END) button,
		
		SUM(CASE
			WHEN ojana = '11' THEN ketebida
		END) as green,
		
		(SUM(CASE
			WHEN ojana = '11' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '11' THEN ketebida
		END)/100*2)) as greenwinamount,
		
		(SUM(CASE
			WHEN ojana = '11' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '11' THEN ketebida
		END)/100*2)) as greenwinamountwithviolet,
		
		SUM(CASE
			WHEN ojana = '12' THEN ketebida
		END) violet,
		
		(SUM(CASE
			WHEN ojana = '12' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '12' THEN ketebida
		END)/100*2)) as violetwinamount,
		
		SUM(CASE
			WHEN ojana = '10' THEN ketebida
		END) red,
		
		(SUM(CASE
			WHEN ojana = '10' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '10' THEN ketebida
		END)/100*2)) as redwinamount,
		(SUM(CASE
			WHEN ojana = '10' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '10' THEN ketebida
		END)/100*2)) as redwinamountwithviolet,
		
		SUM(CASE
			WHEN prakar = '1' THEN ketebida
		END) number,
		SUM(CASE
			WHEN ojana = '0' THEN ketebida
		END) `zero`,
		(SUM(CASE
			WHEN ojana = '0' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '0' THEN ketebida
		END)/100*2)) as zerowinamount,
		
		SUM(CASE
			WHEN ojana = '1' THEN ketebida
		END) `one`,
		(SUM(CASE
			WHEN ojana = '1' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '1' THEN ketebida
		END)/100*2)) as onewinamount,
		
		SUM(CASE
			WHEN ojana = '2' THEN ketebida
		END) `two`,
		(SUM(CASE
			WHEN ojana = '2' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '2' THEN ketebida
		END)/100*2)) as twowinamount,
		
		SUM(CASE
			WHEN ojana = '3' THEN ketebida
		END) `three`,
		(SUM(CASE
			WHEN ojana = '3' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '3' THEN ketebida
		END)/100*2)) as threewinamount,
		
		SUM(CASE
			WHEN ojana = '4' THEN ketebida
		END) `four`,
		(SUM(CASE
			WHEN ojana = '4' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '4' THEN ketebida
		END)/100*2)) as fourwinamount,
		
		SUM(CASE
			WHEN ojana = '5' THEN ketebida
		END) `five`,
		(SUM(CASE
			WHEN ojana = '5' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '5' THEN ketebida
		END)/100*2)) as fivewinamount,
		
		SUM(CASE
			WHEN ojana = '6' THEN ketebida
		END) `six`,
		(SUM(CASE
			WHEN ojana = '6' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '6' THEN ketebida
		END)/100*2)) as sixwinamount,
		
		SUM(CASE
			WHEN ojana = '7' THEN ketebida
		END) `seven`,
		(SUM(CASE
			WHEN ojana = '7' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '7' THEN ketebida
		END)/100*2)) as sevenwinamount,
		
		SUM(CASE
			WHEN ojana = '8' THEN ketebida
		END) `eight`,
		(SUM(CASE
			WHEN ojana = '8' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '8' THEN ketebida
		END)/100*2)) as eightwinamount,
		
		SUM(CASE
			WHEN ojana = '9' THEN ketebida
		END) `nine`,
		(SUM(CASE
			WHEN ojana = '9' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '9' THEN ketebida
		END)/100*2)) as ninewinamount,
		
		(SUM(CASE
			WHEN ojana = '13' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '13' THEN ketebida
		END)/100*2)) as bigwinamount,
		
		(SUM(CASE
			WHEN ojana = '14' THEN ketebida
		END)-(SUM(CASE
			WHEN ojana = '14' THEN ketebida
		END)/100*2)) as smallwinamount
			
		FROM
		`bajikattuttate` where `kalaparichaya`='$periodid'");
		$result=mysqli_fetch_array($query);	
		return $result["$column"];	
	}
	
	function encryptor($action, $string) {
		$output = false;

		$encrypt_method = "AES-256-CBC";
		$secret_key = 'shonu';
		$secret_iv = 'kani123';

		$key = hash('sha256', $secret_key);

		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		if( $action == 'encrypt' ) {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		}
		else if( $action == 'decrypt' ){
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}

		return $output;
	}

?>
