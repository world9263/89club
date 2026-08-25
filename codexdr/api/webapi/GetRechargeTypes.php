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
		if (isset($shonupost['language']) || isset($shonupost['payTypeId']) || isset($shonupost['payid']) || isset($shonupost['random']) || isset($shonupost['signature']) || isset($shonupost['timestamp'])) {
			$payid = $shonupost['payid'];
			
			$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
			$author = isset($bearer[1]) ? $bearer[1] : '';
			$is_jwt_valid = is_jwt_valid($author);
			$data_auth = json_decode($is_jwt_valid, 1);
			
			if($data_auth['status'] === 'Success') {
				$mobile = $data_auth['payload']['mobile'];
				$user = $firebase->get('users/' . $mobile);
				if($user != null){
					$sites = 'https://89club-production.up.railway.app';
					$data = ["rechargetypelist" => []];
					
					if ($payid == 2 || $payid == 1 || $payid == 13) {
						// UPI Channel
						$data["rechargetypelist"][0] = [
							"payTypeID" => 1023,
							"payID" => $payid,
							"payName" => "Manual UPI / QR",
							"paySysName" => "ManualUPI",
							"miniPrice" => 200,
							"maxPrice" => 50000,
							"scope" => "200|500|1000|5000|10000|50000",
							"paySendUrl" => $sites . "/pay/manual_deposit.php?method=upi&tyid=1023",
							"parameters" => "",
							"startTime" => "00:00",
							"endTime" => "24:00",
							"rechargeRifts" => 0.00,
							"c2cUnitAmount" => null,
							"quickConfig" => "",
							"quickConfigList" => [
								["rechargeAmount" => 200.0, "giftAmount" => 0.0],
								["rechargeAmount" => 500.0, "giftAmount" => 0.0],
								["rechargeAmount" => 1000.0, "giftAmount" => 0.0],
								["rechargeAmount" => 5000.0, "giftAmount" => 0.0],
								["rechargeAmount" => 10000.0, "giftAmount" => 0.0],
								["rechargeAmount" => 50000.0, "giftAmount" => 0.0],
							],
							"random" => 0.8192,
							"sort" => 90000
						];
					} elseif ($payid == 11) {
						// USDT Channel
						$data["rechargetypelist"][0] = [
							"payTypeID" => 2123,
							"payID" => 11,
							"payName" => "Manual USDT (TRC-20)",
							"paySysName" => "ManualUSDT",
							"miniPrice" => 10,
							"maxPrice" => 50000,
							"scope" => "10|50|100|500|1000|5000",
							"paySendUrl" => $sites . "/pay/manual_deposit.php?method=usdt&tyid=2123",
							"parameters" => "",
							"startTime" => "00:00",
							"endTime" => "24:00",
							"rechargeRifts" => 0.00,
							"c2cUnitAmount" => null,
							"quickConfig" => "",
							"quickConfigList" => [
								["rechargeAmount" => 10.0, "giftAmount" => 0.0],
								["rechargeAmount" => 50.0, "giftAmount" => 0.0],
								["rechargeAmount" => 100.0, "giftAmount" => 0.0],
								["rechargeAmount" => 500.0, "giftAmount" => 0.0],
								["rechargeAmount" => 1000.0, "giftAmount" => 0.0],
								["rechargeAmount" => 5000.0, "giftAmount" => 0.0],
							],
							"random" => 0.7584,
							"sort" => 95000
						];
					}
					
					$data['banklist'] = null;
					$data['localUsdtlist'] = null;
					$data['thirdPayBankList'] = null;
											
					$res['data'] = $data;
					$res['code'] = 0;
					$res['msg'] = 'Succeed';
					$res['msgCode'] = 0;
					http_response_code(200);
					echo json_encode($res);					
				} else {
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
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
