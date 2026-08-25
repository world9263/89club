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
		if (isset($shonupost['endDate']) && isset($shonupost['language']) && isset($shonupost['pageNo']) && isset($shonupost['pageSize']) && isset($shonupost['payId']) && isset($shonupost['payTypeId']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['startDate']) && isset($shonupost['state']) && isset($shonupost['timestamp'])) {
			$endDate = $shonupost['endDate'];
			$pageNo = (int)$shonupost['pageNo'];
			$pageSize = (int)$shonupost['pageSize'];
			$startDate = $shonupost['startDate'];
			$state = (int)$shonupost['state']; // -1 = all, 1 = success, 0 = failed, 2 = pending
			
			$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
			$author = isset($bearer[1]) ? $bearer[1] : '';
			$is_jwt_valid = is_jwt_valid($author);
			$data_auth = json_decode($is_jwt_valid, 1);
			
			if($data_auth['status'] === 'Success') {
				$mobile = $data_auth['payload']['mobile'];
				$user = $firebase->get('users/' . $mobile);
				if($user != null){
					
					// Fetch all deposits from Firebase
					$allDeposits = $firebase->get('deposits');
					$userDeposits = [];
					
					if (is_array($allDeposits)) {
						foreach ($allDeposits as $dep) {
							if (!isset($dep['userId']) || $dep['userId'] !== $mobile) continue;
							
							// Map status to integer codes: success=1, pending=2, failed=0
							$mappedState = 2; // Default pending
							if (isset($dep['status'])) {
								if ($dep['status'] === 'success') $mappedState = 1;
								elseif ($dep['status'] === 'failed') $mappedState = 0;
								elseif ($dep['status'] === 'pending') $mappedState = 2;
							}
							
							// State filtering
							if ($state !== -1 && $mappedState !== $state) continue;
							
							// Date filtering
							if (!empty($startDate) && !empty($endDate)) {
								$depDate = date('Y-m-d', strtotime($dep['createdAt']));
								if ($depDate < $startDate || $depDate > $endDate) continue;
							}
							
							$userDeposits[] = [
								'rechargeNumber' => $dep['id'],
								'addTime' => $dep['createdAt'],
								'type' => ($dep['method'] === 'USDT') ? 2123 : 1023,
								'price' => (string)$dep['amount'],
								'state' => $mappedState,
								'uRate' => null,
								'uGold' => 0,
								'payID' => ($dep['method'] === 'USDT') ? 11 : 2,
								'payName' => ($dep['method'] === 'USDT') ? 'Manual USDT' : 'Manual UPI'
							];
						}
					}
					
					// Sort by addTime DESC
					usort($userDeposits, function($a, $b) {
						return strcmp($b['addTime'], $a['addTime']);
					});
					
					$totalCount = count($userDeposits);
					$offset = ($pageNo - 1) * $pageSize;
					$paginatedDeposits = array_slice($userDeposits, $offset, $pageSize);
					
					$data = [
						'list' => $paginatedDeposits,
						'pageNo' => $pageNo,
						'totalPage' => ceil($totalCount / $pageSize),
						'totalCount' => $totalCount
					];
					
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
