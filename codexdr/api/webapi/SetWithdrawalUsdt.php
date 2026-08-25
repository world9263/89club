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
		if (isset($shonupost['bankid']) && isset($shonupost['codeType']) && isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {			
			$bankid = htmlspecialchars($shonupost['bankid']);
			$codeType = htmlspecialchars($shonupost['codeType']);
			$usdtRemarkName = htmlspecialchars($shonupost['usdtRemarkName']);
			$usdtaddress = htmlspecialchars($shonupost['usdtaddress']);
			
			$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
			$author = $bearer[1] ?? '';				
			$is_jwt_valid = is_jwt_valid($author);
			$data_auth = json_decode($is_jwt_valid, 1);
			if($data_auth['status'] === 'Success') {
				$mobile = $data_auth['payload']['mobile'];
				$user = $firebase->get('users/' . $mobile);
				if($user != null){
					$usdt_id = "USDT_" . time() . rand(1000, 9999);
					$usdt_data = [
						'id' => $usdt_id,
						'type' => 3,
						'accountNo' => $usdtaddress,
						'bankid' => $bankid,
						'bankName' => 'TRC',
						'beneficiaryName' => $usdtRemarkName,
						'codeType' => $codeType,
						'ifsCode' => '',
						'mobileNo' => '',
						'email' => '',
						'createdAt' => $shnunc
					];
					
					$firebase->set('users/' . $mobile . '/withdrawal_accounts/' . $usdt_id, $usdt_data);
					
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
