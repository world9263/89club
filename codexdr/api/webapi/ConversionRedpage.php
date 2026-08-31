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
		if (isset($shonupost['giftCode']) && isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
			$giftCode = trim($shonupost['giftCode']);	
			$language = $shonupost['language'];		
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];			
			$shonustr = '{"giftCode":"'.$giftCode.'","language":'.$language.',"random":"'.$random.'"}';	
			$shonusign = strtoupper(md5($shonustr));
			if(true){
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1] ?? '';				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				if(isset($data_auth['status']) && $data_auth['status'] === 'Success') {
					$mobile = $data_auth['payload']['mobile'];
					$user = $firebase->get('users/' . $mobile);
					if($user != null){
						$shonuid = $data_auth['payload']['id'] ?? $mobile;
						
						// FIREBASE: Get gift code details
						$gift_ref = $firebase->get('gift_codes/' . $giftCode);
						
						if ($gift_ref !== null && isset($gift_ref['status']) && $gift_ref['status'] == 1) {
							$max_users = isset($gift_ref['max_users']) ? (int)$gift_ref['max_users'] : 0;
							$redeemed_count = isset($gift_ref['redeemed_count']) ? (int)$gift_ref['redeemed_count'] : 0;
							$min_deposit_req = isset($gift_ref['min_deposit_req']) ? (float)$gift_ref['min_deposit_req'] : 0.0;
							
							// Dynamic Country & Currency Detection: BD vs India
							$is_bdt_user = false;
							$cf_country = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? strtoupper($_SERVER["HTTP_CF_IPCOUNTRY"]) : '';
							if ($cf_country === 'BD') {
								$is_bdt_user = true;
							}
							if (!$is_bdt_user && isset($language) && ($language === 'bdt' || $language === '"bdt"')) {
								$is_bdt_user = true;
							}
							if (!$is_bdt_user && (strpos($mobile, '880') === 0 || strpos($mobile, '+880') === 0)) {
								$is_bdt_user = true;
							}
							if (!$is_bdt_user && (isset($user['country_code']) && strtoupper($user['country_code']) === 'BD')) {
								$is_bdt_user = true;
							}
							
							$curr_sym = $is_bdt_user ? '৳' : '₹';
							
							// Check minimum deposit requirement if configured
							if ($min_deposit_req > 0) {
								$deposits = $firebase->get('deposits');
								$userTotalDeposit = 0.0;
								if ($deposits) {
									foreach ($deposits as $dep) {
										$depUser = isset($dep['userId']) ? (string)$dep['userId'] : '';
										$is_user_dep = (
											$depUser === (string)$mobile || 
											$depUser === ('91' . $mobile) || 
											$depUser === ('880' . $mobile) ||
											$depUser === ('+91' . $mobile) || 
											$depUser === ('+880' . $mobile) ||
											$depUser === (string)$shonuid ||
											(isset($user['mobile']) && $depUser === (string)$user['mobile'])
										);
										$statusLower = isset($dep['status']) ? strtolower(trim((string)$dep['status'])) : '';
										$is_success = ($statusLower === 'success' || $statusLower === 'request success' || $statusLower === 'approved');
										
										if ($is_user_dep && $is_success) {
											$userTotalDeposit += (float)($dep['amount'] ?? 0.0);
										}
									}
								}
								
								if ($userTotalDeposit < $min_deposit_req) {
									$data = null;
									$res['data'] = $data;
									$res['code'] = 1;
									$res['msg'] = 'minimum deposit require for this code ' . $curr_sym . $min_deposit_req;
									$res['msgCode'] = 233;
									http_response_code(200);
									echo json_encode($res);
									exit;
								}
							}
							
							if ($redeemed_count < $max_users) {
								// FIREBASE: Check if this user has already redeemed this code
								$has_redeemed = $firebase->get('gift_redemptions/' . $giftCode . '/' . $mobile);
								
								if ($has_redeemed === null) {
									$prix = (float)$gift_ref['amount'];
									$turnover_req = isset($gift_ref['turnover_req']) ? (float)$gift_ref['turnover_req'] : 0.0;
									$crdt = date("Y-m-d H:i:s");
									
									// 1. Update user balance and required turnover in Firebase FIRST
									$userMotta = isset($user['motta']) ? (float)$user['motta'] : 0.0;
									$newMotta = round($userMotta + $prix, 2);
									
									$userTurnover = isset($user['required_turnover']) ? (float)$user['required_turnover'] : 0.0;
									$newTurnover = round($userTurnover + $turnover_req, 2);
									
									$firebase->update('users/' . $mobile, [
										'motta' => $newMotta,
										'required_turnover' => $newTurnover
									]);
									
									// 2. Increment redeemed count in Firebase
									$new_redeemed_count = $redeemed_count + 1;
									$firebase->update('gift_codes/' . $giftCode, [
										'redeemed_count' => $new_redeemed_count
									]);
									
									// 3. Save redemption log in Firebase (per code duplicate checker)
									$firebase->set('gift_redemptions/' . $giftCode . '/' . $mobile, [
										'userId' => $mobile,
										'amount' => $prix,
										'redeemed_at' => $crdt
									]);
									
									// 4. Save redemption in user's personal log (for fast history queries!)
									$redKey = 'red_' . time() . '_' . rand(100, 999);
									$firebase->set('user_redemptions/' . $mobile . '/' . $redKey, [
										'code' => $giftCode,
										'amount' => $prix,
										'redeemed_at' => $crdt
									]);
									
									$data = null;
									$res['data'] = $data;
									$res['code'] = 0;
									$res['msg'] = 'Succeed';
									$res['msgCode'] = 0;
									http_response_code(200);
									echo json_encode($res);
								}
								else {
									$data = null;
									$res['data'] = $data;
									$res['code'] = 1;
									$res['msg'] = 'You have already redeemed this code';
									$res['msgCode'] = 231;
									http_response_code(200);
									echo json_encode($res);
								}								
							}
							else {
								$data = null;
								$res['data'] = $data;
								$res['code'] = 1;
								$res['msg'] = 'Redemption limit reached';
								$res['msgCode'] = 232;
								http_response_code(200);
								echo json_encode($res);
							}							
						}
						else {
							$data = null;
							$res['data'] = $data;
							$res['code'] = 1;
							$res['msg'] = 'Invalid code';
							$res['msgCode'] = 230;
							http_response_code(200);
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