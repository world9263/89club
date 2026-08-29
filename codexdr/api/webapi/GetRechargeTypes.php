<?php 
	include "../../conn.php";
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
	
	$shonubody = file_get_contents("php://input");
	$shonupost = json_decode($shonubody, true);
	$payid = isset($shonupost['payid']) ? (int)$shonupost['payid'] : 2;
	
	$sites = '';
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
							
	$res = [
		'code' => 0,
		'msg' => 'Succeed',
		'msgCode' => 0,
		'serviceNowTime' => $shnunc,
		'data' => $data
	];
	
	http_response_code(200);
	echo json_encode($res);
?>
