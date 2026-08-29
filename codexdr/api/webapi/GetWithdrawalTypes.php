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
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
			
			$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
			$author = $bearer[1] ?? '';				
			$is_jwt_valid = is_jwt_valid($author);
			$data_auth = json_decode($is_jwt_valid, 1);
			if($data_auth['status'] === 'Success') {
				$mobile = $data_auth['payload']['mobile'];
				$user = $firebase->get('users/' . $mobile);
				if($user != null){
					// Fetch accounts from Firebase to check if cards are added
					$accounts = $firebase->get('users/' . $mobile . '/withdrawal_accounts');
					$hasBank = 0;
					$hasUsdt = 0;
					if ($accounts) {
						foreach ($accounts as $acc) {
							if (($acc['type'] ?? 1) == 1) {
								$hasBank = 1;
							}
							if (($acc['type'] ?? 1) == 3) {
								$hasUsdt = 1;
							}
						}
					}
					
					$is_bdt = (strpos($mobile, '880') === 0 || strpos($mobile, '+880') === 0);
					if (!$is_bdt) {
						$cf_country = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? strtoupper($_SERVER["HTTP_CF_IPCOUNTRY"]) : '';
						if ($cf_country === 'BD') {
							$is_bdt = true;
						}
					}
					
					$data['withdrawlist'][0]['withdrawID'] = 1;
					$data['withdrawlist'][0]['name'] = $is_bdt ? 'E-Wallet' : 'BANK CARD';
					$data['withdrawlist'][0]['isAdd'] = $hasBank;
					$data['withdrawlist'][0]['withBeforeImgUrl'] = 'https://ossimg.bdg123456.com/BDGWin/payNameIcon/WithBeforeImgIcon_202403161624569ini.png';
					$data['withdrawlist'][0]['withAfterImgUrl'] = 'https://ossimg.bdg123456.com/BDGWin/payNameIcon/WithBeforeImgIcon2_20240316162456if4s.png';
					
					$data['withdrawlist'][1]['withdrawID'] = 3;
					$data['withdrawlist'][1]['name'] = 'USDT';
					$data['withdrawlist'][1]['isAdd'] = $hasUsdt;
					$data['withdrawlist'][1]['withBeforeImgUrl'] = 'https://ossimg.bdg123456.com/BDGWin/payNameIcon/WithBeforeImgIcon_20240323183235bhef.png';
					$data['withdrawlist'][1]['withAfterImgUrl'] = 'https://ossimg.bdg123456.com/BDGWin/payNameIcon/WithBeforeImgIcon2_20240323183236ntpr.png';
											
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
