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
	
	$shonubody = file_get_contents("php://input");
	$shonupost = json_decode($shonubody, true);
	$payid = isset($shonupost['payid']) ? (int)$shonupost['payid'] : 2;
	
	$sites = '';
	$data = ["rechargetypelist" => []];
	
	$is_bdt = false;
	$cf_country = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? strtoupper($_SERVER["HTTP_CF_IPCOUNTRY"]) : '';
	if ($cf_country === 'BD') {
		$is_bdt = true;
	}
	
	if ($is_bdt) {
		if ($payid == 2) {
			// Nagad Channels (matching tab index)
			$channels = [
				"RolezPayPS-Nagad Balance:300 - 50K",
				"OpPay-Nagad Balance:100 - 50K",
				"GoPayPS-Nagad Balance:100 - 10K",
				"PopoPay-Nagad Balance:500 - 50K",
				"StarPago-Nagad Balance:1000 - 10K",
				"KaroPay-Nagad Balance:100 - 30K"
			];
			$minPrices = [300, 100, 100, 500, 1000, 100];
			
			foreach ($channels as $idx => $chanName) {
				$data["rechargetypelist"][$idx] = [
					"payTypeID" => 1024 + $idx,
					"payID" => $payid,
					"payName" => $chanName,
					"paySysName" => "Nagad",
					"miniPrice" => $minPrices[$idx],
					"maxPrice" => 50000,
					"scope" => "300|500|1000|5000|10000|50000",
					"paySendUrl" => $sites . "/pay/manual_deposit.php?method=nagad&tyid=" . (1024 + $idx),
					"parameters" => "",
					"startTime" => "00:00",
					"endTime" => "24:00",
					"rechargeRifts" => 0.00,
					"c2cUnitAmount" => null,
					"quickConfig" => "",
					"quickConfigList" => [
						["rechargeAmount" => 300.0, "giftAmount" => 0.0],
						["rechargeAmount" => 500.0, "giftAmount" => 0.0],
						["rechargeAmount" => 1000.0, "giftAmount" => 0.0],
						["rechargeAmount" => 5000.0, "giftAmount" => 0.0],
						["rechargeAmount" => 10000.0, "giftAmount" => 0.0],
						["rechargeAmount" => 50000.0, "giftAmount" => 0.0],
					],
					"random" => 0.8100 + ($idx / 100.0),
					"sort" => 90000 - $idx
				];
			}
		} elseif ($payid == 1) {
			// bKash Channels
			$channels = [
				"RolezPayPS-BKASH Balance:300 - 50K",
				"OpPay-BKASH Balance:100 - 50K",
				"GoPayPS-BKASH Balance:100 - 10K",
				"PopoPay-BKASH Balance:500 - 50K",
				"StarPago-BKASH Balance:1000 - 10K",
				"KaroPay-BKASH Balance:100 - 30K"
			];
			$minPrices = [300, 100, 100, 500, 1000, 100];
			
			foreach ($channels as $idx => $chanName) {
				$data["rechargetypelist"][$idx] = [
					"payTypeID" => 1030 + $idx,
					"payID" => $payid,
					"payName" => $chanName,
					"paySysName" => "bKash",
					"miniPrice" => $minPrices[$idx],
					"maxPrice" => 50000,
					"scope" => "100|500|1000|5000|10000|50000",
					"paySendUrl" => $sites . "/pay/manual_deposit.php?method=bkash&tyid=" . (1030 + $idx),
					"parameters" => "",
					"startTime" => "00:00",
					"endTime" => "24:00",
					"rechargeRifts" => 0.00,
					"c2cUnitAmount" => null,
					"quickConfig" => "",
					"quickConfigList" => [
						["rechargeAmount" => 100.0, "giftAmount" => 0.0],
						["rechargeAmount" => 500.0, "giftAmount" => 0.0],
						["rechargeAmount" => 1000.0, "giftAmount" => 0.0],
						["rechargeAmount" => 5000.0, "giftAmount" => 0.0],
						["rechargeAmount" => 10000.0, "giftAmount" => 0.0],
						["rechargeAmount" => 50000.0, "giftAmount" => 0.0],
					],
					"random" => 0.7100 + ($idx / 100.0),
					"sort" => 80000 - $idx
				];
			}
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
				"rechargeRifts" => 0.02,
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
	} else {
		// India Version (UPI / USDT)
		if ($payid == 2 || $payid == 1 || $payid == 13) {
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
