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
	
	header('Content-Type: application/json; charset=utf-8');
	header('Strict-Transport-Security: max-age=31536000');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
	header('Access-Control-Allow-Credentials: true');
	$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
	header('Access-Control-Allow-Origin: ' . $origin);
	header('vary: Origin');
	
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
	
	function replaceWithAsterisks($inputString) {
		if (strlen($inputString) < 10) {
			return $inputString;
		}
		$before = substr($inputString, 0, 6);
		$toReplace = substr($inputString, 6, 4);
		$after = substr($inputString, 10);
		$replaced = str_repeat('*', strlen($toReplace));
		$resultString = $before . $replaced . $after;
		return $resultString;
	}
	
	
	if ($_SERVER['REQUEST_METHOD'] != 'GET') {
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['withdrawid'])) {
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			$withdrawid = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['withdrawid']));
			$shonustr = '{"language":'.$language.',"random":"'.$random.'","withdrawid":'.$withdrawid.'}';
			$shonusign = strtoupper(md5($shonustr));
			if(true){
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				if($data_auth['status'] === 'Success') {
					$sesquery = "SELECT akshinak
					  FROM shonu_subjects
					  WHERE akshinak = '$author'";
					$sesresult=$conn->query($sesquery);
					$sesnum = mysqli_num_rows($sesresult);
					if($sesnum == 1){
						$shonuid = $data_auth['payload']['id'];
						if($withdrawid == 1){
							$samasye = "SELECT phalanubhavi
							  FROM khate WHERE byabaharkarta = $shonuid AND khatehesaru != 'TRC'
							  ORDER BY shonu DESC LIMIT 1";
							$samasyephalitansa = $conn->query($samasye);
							$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);	
							if($samasyephalitansa_dhadi >= 1){
								$samasyephalitansa_sreni = mysqli_fetch_array($samasyephalitansa);						
								$data['lastBandCarkName'] = $samasyephalitansa_sreni['phalanubhavi'];
								
								$samasye = "SELECT shonu, khatehesaru, khatesankhye, kod, duravani
								  FROM khate WHERE byabaharkarta = $shonuid AND khatehesaru != 'TRC'
								  ORDER BY shonu DESC";
								$samasyephalitansa = $conn->query($samasye);
								$i = 0;
								while($row = mysqli_fetch_array($samasyephalitansa)){
									$data['withdrawalslist'][$i]['bid'] = $row['shonu'];
									$data['withdrawalslist'][$i]['bankName'] = $row['khatehesaru'];
									$data['withdrawalslist'][$i]['beneficiaryName'] = '';
									
									$data['withdrawalslist'][$i]['accountNo'] = replaceWithAsterisks($row['khatesankhye']);
									$data['withdrawalslist'][$i]['ifsCode'] = $row['kod'];
									$data['withdrawalslist'][$i]['withType'] = 1;
									$data['withdrawalslist'][$i]['mobileNo'] = replaceWithAsterisks($row['duravani']);
									$data['withdrawalslist'][$i]['bankProvince'] = '';
									$data['withdrawalslist'][$i]['bankCity'] = '';
									$data['withdrawalslist'][$i]['bankAddress'] = '';
									$i++;
								}
							}
							else{
								$data['lastBandCarkName'] = null;
								$data['withdrawalslist'] = [];
							}
						}
						elseif($withdrawid == 3){
							$samasye = "SELECT phalanubhavi
							  FROM khate WHERE byabaharkarta = $shonuid AND khatehesaru = 'TRC'
							  ORDER BY shonu DESC LIMIT 1";
							$samasyephalitansa = $conn->query($samasye);
							$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);	
							if($samasyephalitansa_dhadi >= 1){
								$samasyephalitansa_sreni = mysqli_fetch_array($samasyephalitansa);						
								$data['lastBandCarkName'] = $samasyephalitansa_sreni['phalanubhavi'];
								
								$samasye = "SELECT shonu, khatehesaru, khatesankhye, kod, duravani
								  FROM khate WHERE byabaharkarta = $shonuid AND khatehesaru = 'TRC'
								  ORDER BY shonu DESC";
								$samasyephalitansa = $conn->query($samasye);
								$i = 0;
								while($row = mysqli_fetch_array($samasyephalitansa)){
									$data['withdrawalslist'][$i]['bid'] = $row['shonu'];
									$data['withdrawalslist'][$i]['bankName'] = $row['khatehesaru'];
									$data['withdrawalslist'][$i]['beneficiaryName'] = '';
									
									$data['withdrawalslist'][$i]['accountNo'] = replaceWithAsterisks($row['khatesankhye']);
									$data['withdrawalslist'][$i]['ifsCode'] = $row['kod'];
									$data['withdrawalslist'][$i]['withType'] = 1;
									$data['withdrawalslist'][$i]['mobileNo'] = replaceWithAsterisks($row['duravani']);
									$data['withdrawalslist'][$i]['bankProvince'] = '';
									$data['withdrawalslist'][$i]['bankCity'] = '';
									$data['withdrawalslist'][$i]['bankAddress'] = '';
									$i++;
								}
							}
							else{
								$data['lastBandCarkName'] = null;
								$data['withdrawalslist'] = [];
							}
						}
						
						// Query to retrieve data based on user ID and date
$samasye_1 = "SELECT shonu
              FROM hintegedukolli 
              WHERE balakedara = '".$shonuid."' 
              AND DATE(dinankavannuracisi) = date('".$shnunc."')";
              
$samasyephalitansa_1 = $conn->query($samasye_1);
$shelly = mysqli_num_rows($samasyephalitansa_1);
if ($withdrawid == 3) {
    $shelly_1 = 5 - $shelly;
} elseif ($withdrawid == 1) {
    $shelly_1 = 3 - $shelly;
}


// Withdrawal rules array
$data["withdrawalsrule"]["withdrawCount"] = $shelly;
$data["withdrawalsrule"]["withdrawRemainingCount"] = $shelly_1;
$data["withdrawalsrule"]["startTime"] = "00:00";
$data["withdrawalsrule"]["endTime"] = "23:59";
$data["withdrawalsrule"]["fee"] = (int)"0";
$data["withdrawalsrule"]["minPrice"] = (int)"110";
$data["withdrawalsrule"]["maxPrice"] = (int)"50000";

// Query to fetch withdrawal amount
$balquery = "SELECT motta
             FROM shonu_kaichila
             WHERE balakedara = ".$data_auth['payload']['id'];

$balresult = $conn->query($balquery);
$balarr = mysqli_fetch_array($balresult);

// Assign withdrawal amount
$data["withdrawalsrule"]["amount"] = $balarr["motta"];
						
						$rtatqr = "SELECT SUM(motta) as sote
						  FROM thevani
						  WHERE balakedara = '".$shonuid."' AND sthiti = '1'";
						$rtatresult = $conn->query($rtatqr);
						$rtat_ar = mysqli_fetch_array($rtatresult);
						
						$rtatqr_a = "SELECT SUM(price) as sote
						  FROM hodike_balakedara
						  WHERE userkani = '".$shonuid."'";
						$rtatresult_a = $conn->query($rtatqr_a);
						$rtat_ar_a = mysqli_fetch_array($rtatresult_a);
						
						$sotek = $rtat_ar['sote'] + $rtat_ar_a['sote'] + 0;						
						
                        $bet_wingo_trx = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_trx` where byabaharkarta = '".$shonuid."'"));
                        $bet_wingo_trx3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_trx3` where byabaharkarta = '".$shonuid."'"));
                        $bet_wingo_trx5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_trx5` where byabaharkarta = '".$shonuid."'"));
                        $bet_wingo_trx10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_trx10` where byabaharkarta = '".$shonuid."'"));
						$bet_wingo_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate` where byabaharkarta = '".$shonuid."'"));
						$bet_wingo_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_drei` where byabaharkarta = '".$shonuid."'"));
						$bet_wingo_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_funf` where byabaharkarta = '".$shonuid."'"));
						$bet_wingo_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_zehn` where byabaharkarta = '".$shonuid."'"));
						$bet_k3_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru` where byabaharkarta = '".$shonuid."'"));
						$bet_k3_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_drei` where byabaharkarta = '".$shonuid."'"));
						$bet_k3_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_funf` where byabaharkarta = '".$shonuid."'"));
						$bet_k3_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_zehn` where byabaharkarta = '".$shonuid."'"));
						$bet_5d_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi` where byabaharkarta = '".$shonuid."'"));
						$bet_5d_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_drei` where byabaharkarta = '".$shonuid."'"));
						$bet_5d_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_funf` where byabaharkarta = '".$shonuid."'"));
						$bet_5d_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_zehn` where byabaharkarta = '".$shonuid."'"));
						$total_bet = $bet_wingo_trx['total']+$bet_wingo_trx3['total']+$bet_wingo_trx5['total']+$bet_wingo_trx10['total']+$bet_wingo_1['total'] + $bet_wingo_3['total'] + $bet_wingo_5['total'] + $bet_wingo_10['total'] + $bet_k3_1['total'] + $bet_k3_3['total'] + $bet_k3_5['total'] + $bet_k3_10['total'] + $bet_5d_1['total'] + $bet_5d_3['total'] + $bet_5d_5['total'] + $bet_5d_10['total'];												
						
						
$extraFundsQuery = "SELECT 
    COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END), 0) AS extra_amount 
FROM user_extra_funds 
WHERE userid = $shonuid";
$extraFundsResult = $conn->query($extraFundsQuery);
$extraFunds = $extraFundsResult->fetch_assoc()['extra_amount'];


$sotek_with_extra = $sotek + $extraFunds;

if ($sotek_with_extra > $total_bet) {
    $wiwo = 0;
    $data["withdrawalsrule"]["amountofCode"] = $sotek_with_extra - $total_bet; 
} else if ($sotek_with_extra <= $total_bet) {
    if ($rtat_ar['sote'] == null || $rtat_ar['sote'] == '') {
        $wiwo = 0;
    } else {
        $wiwo = $balarr["motta"];
    }
    $data["withdrawalsrule"]["amountofCode"] = 0; 
}

				// Additional Data Assignments
				$data["withdrawalsrule"]["canWithdrawAmount"] = $wiwo;
				$data["withdrawalsrule"]["c2cUnitAmount"] = 0;
				$data["withdrawalsrule"]["uRate"] = 93;
				$data["withdrawalsrule"]["uGold"] = 0;
						
						$res['data'] = $data;
						$res['code'] = 0;
						$res['msg'] = 'Succeed';
						$res['msgCode'] = 0;
						http_response_code(200);
						echo json_encode($res);					
					}
					else{
						$res['code'] = 4;
						$res['msg'] = 'No operation permission';
						$res['msgCode'] = 2;
						http_response_code(401);
						echo json_encode($res);
					}					
				}
				else{					
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
					echo json_encode($res);					
				}
			}
			else{
				$res['code'] = 5;
				$res['msg'] = 'Wrong signature';
				$res['msgCode'] = 3;
				http_response_code(200);
				echo json_encode($res);				
			}
		}
		else{
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
