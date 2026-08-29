<?php 
	include "../../conn.php";
	include "../../functions2.php";
	global $firebase;
	
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
	
	if ($_SERVER['REQUEST_METHOD'] != 'GET') {
		if (isset($shonupost['accountno']) && isset($shonupost['bankid']) && isset($shonupost['beneficiaryname'])) {
			$accountno = htmlspecialchars($shonupost['accountno']);
			$bankid = htmlspecialchars($shonupost['bankid']);
			$beneficiaryname = htmlspecialchars($shonupost['beneficiaryname']);
			$codeType = htmlspecialchars($shonupost['codeType'] ?? '');
			$email = htmlspecialchars($shonupost['email'] ?? '');
			$ifsccode = htmlspecialchars($shonupost['ifsccode'] ?? '');
			$language = htmlspecialchars($shonupost['language'] ?? 'en');
			$mobileno = htmlspecialchars($shonupost['mobileno'] ?? '');			
			
			$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
			$author = $bearer[1] ?? '';				
			$is_jwt_valid = is_jwt_valid($author);
			$data_auth = json_decode($is_jwt_valid, 1);
			if($data_auth['status'] === 'Success') {
				$mobile = $data_auth['payload']['mobile'];
				$user = $firebase->get('users/' . $mobile);
				if($user != null){
					$bankName = 'Bank Card';
					if ($bankid == 1001) {
						$bankName = 'BKASH';
					} elseif ($bankid == 1002) {
						$bankName = 'NAGAD';
					} elseif ($bankid == 1003) {
						$bankName = 'ROCKET';
					} elseif ($bankid == 1004) {
						$bankName = 'UPAY';
					} else {
						// Fetch Bank List using curl
						$url = 'https://api.skywin786.in/api/webapi/GetBankList';
						$payld = array(
							'language' => 0,							
							'random' => 'bbfdf080be3d4529aee34c148e0ad9f8',
							'signature' => 'FD7919EAADBA695B1C123E396B9786A2',
							'timestamp' => 1718516163,
							'withdrawid' => 1
						);
						$jsonData = json_encode($payld);
						$ch = curl_init($url);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array(
							'Content-Type: application/json',
							'Content-Length: ' . strlen($jsonData),
							'Authorization: ' . $_SERVER['HTTP_AUTHORIZATION']
						));
						$response = curl_exec($ch);
						curl_close($ch);
						
						if ($response) {
							$rcvdt = json_decode($response, true);
							$banklist = $rcvdt['data']['banklist'] ?? [];
							foreach ($banklist as $bank) {
								if (isset($bank["bankID"]) && $bank["bankID"] == $bankid) {
									$bankName = $bank['bankName'];
									break;
								}
							}
						}
					}
					
					// Save bank card details in Firebase under user profile
					$card_id = "BC_" . time() . rand(1000, 9999);
					$card_data = [
						'id' => $card_id,
						'type' => 1,
						'accountNo' => $accountno,
						'bankid' => $bankid,
						'bankName' => $bankName,
						'beneficiaryName' => $beneficiaryname,
						'codeType' => $codeType,
						'ifsCode' => $ifsccode,
						'mobileNo' => $mobileno,
						'email' => $email,
						'createdAt' => $shnunc
					];
					
					$firebase->set('users/' . $mobile . '/withdrawal_accounts/' . $card_id, $card_data);
					
					$res['data'] = null;
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
