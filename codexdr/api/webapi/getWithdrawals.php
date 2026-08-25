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
	
	function replaceWithAsterisks($inputString) {
		if (strlen($inputString) < 10) {
			return $inputString;
		}
		$before = substr($inputString, 0, 6);
		$toReplace = substr($inputString, 6, 4);
		$after = substr($inputString, 10);
		$replaced = str_repeat('*', strlen($toReplace));
		return $before . $replaced . $after;
	}
	
	if ($_SERVER['REQUEST_METHOD'] != 'GET') {
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['withdrawid'])) {
			$withdrawid = (int)$shonupost['withdrawid']; // 1 = bank card, 3 = usdt
			
			$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
			$author = $bearer[1] ?? '';				
			$is_jwt_valid = is_jwt_valid($author);
			$data_auth = json_decode($is_jwt_valid, 1);
			if($data_auth['status'] === 'Success') {
				$mobile = $data_auth['payload']['mobile'];
				$user = $firebase->get('users/' . $mobile);
				if($user != null){
					$currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
					
					// Fetch linked withdrawal accounts
					$accounts = $firebase->get('users/' . $mobile . '/withdrawal_accounts');
					$withdrawalslist = [];
					$lastBankCardName = null;
					
					if ($accounts) {
						// Sort by createdAt DESC to get the latest first
						usort($accounts, function($a, $b) {
							return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? '');
						});
						
						foreach ($accounts as $acc) {
							if (($acc['type'] ?? 1) == $withdrawid) {
								if ($lastBankCardName === null) {
									$lastBankCardName = $acc['beneficiaryName'] ?? '';
								}
								
								$withdrawalslist[] = [
									'bid' => $acc['id'] ?? '',
									'bankName' => $acc['bankName'] ?? '',
									'beneficiaryName' => $acc['beneficiaryName'] ?? '',
									'accountNo' => replaceWithAsterisks($acc['accountNo'] ?? ''),
									'ifsCode' => $acc['ifsCode'] ?? '',
									'withType' => 1,
									'mobileNo' => replaceWithAsterisks($acc['mobileNo'] ?? ''),
									'bankProvince' => '',
									'bankCity' => '',
									'bankAddress' => ''
								];
							}
						}
					}
					
					$data['lastBandCarkName'] = $lastBankCardName;
					$data['withdrawalslist'] = $withdrawalslist;
					
					// Calculate today's withdrawal count
					$allWithdrawals = $firebase->get('withdrawals');
					$todayDateStr = date('Y-m-d');
					$withdrawCount = 0;
					if ($allWithdrawals) {
						foreach ($allWithdrawals as $wd) {
							if (($wd['userId'] ?? '') === $mobile) {
								$createdDate = substr($wd['createdAt'] ?? '', 0, 10);
								if ($createdDate === $todayDateStr) {
									$withdrawCount++;
								}
							}
						}
					}
					
					$limit = ($withdrawid === 3) ? 5 : 3;
					$withdrawRemainingCount = max(0, $limit - $withdrawCount);
					
					// Wagering checks
					$total_deposit = isset($user['total_deposit']) ? (float)$user['total_deposit'] : 0.0;
					$total_bet = isset($user['total_bet']) ? (float)$user['total_bet'] : 0.0;
					
					if ($total_deposit == 0.0) {
						$amountofCode = 0;
						$wiwo = $currentBalance;
					} else {
						if ($total_bet >= $total_deposit) {
							$amountofCode = 0;
							$wiwo = $currentBalance;
						} else {
							$amountofCode = round($total_deposit - $total_bet, 2);
							$wiwo = 0;
						}
					}
					
					$data["withdrawalsrule"]["withdrawCount"] = $withdrawCount;
					$data["withdrawalsrule"]["withdrawRemainingCount"] = $withdrawRemainingCount;
					$data["withdrawalsrule"]["startTime"] = "00:00";
					$data["withdrawalsrule"]["endTime"] = "23:59";
					$data["withdrawalsrule"]["fee"] = 0;
					$data["withdrawalsrule"]["minPrice"] = 110;
					$data["withdrawalsrule"]["maxPrice"] = 50000;
					$data["withdrawalsrule"]["amount"] = $currentBalance;
					$data["withdrawalsrule"]["amountofCode"] = (int)$amountofCode;
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
