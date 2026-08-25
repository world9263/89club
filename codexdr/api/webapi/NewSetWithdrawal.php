<?php 
	include "../../conn.php";
	include "../../functions2.php";
	global $firebase;
	
	header('Content-Type: application/json; charset=utf-8');
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
		if (isset($shonupost['amount']) && isset($shonupost['bid']) && isset($shonupost['language']) && isset($shonupost['pwd']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['type'])) {
			$amount = (float)$shonupost['amount'];
			$bid = htmlspecialchars($shonupost['bid']);
			$type = (int)$shonupost['type']; // 1 = bank, 3 = usdt
			
			$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
			$author = $bearer[1] ?? '';				
			$is_jwt_valid = is_jwt_valid($author);
			$data_auth = json_decode($is_jwt_valid, true);
			
			if ($data_auth['status'] === 'Success') {
				$mobile = $data_auth['payload']['mobile'];
				$user = $firebase->get('users/' . $mobile);
				
				if ($user != null) {
					$currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
					$isDemoUser = isset($user['is_demo']) ? (bool)$user['is_demo'] : false;
					
					// Validate amount
					if ($amount >= 110 && $amount <= 50000 && $amount <= $currentBalance) {
						// Deduct balance in Firebase
						$newBalance = round($currentBalance - $amount, 2);
						$firebase->update('users/' . $mobile, ['motta' => $newBalance]);
						
						$date = date("Ymd");
						$time = time();
						$serial = 'W' . $date . $time . rand(1000, 9999);
						
						// Save withdrawal request in Firebase
						$withdrawal_data = [
							'id' => $serial,
							'userId' => $mobile,
							'amount' => $amount,
							'status' => 'pending',
							'method' => $type === 3 ? 'USDT' : 'BANK_CARD',
							'withdrawNumber' => $bid,
							'isDemo' => $isDemoUser,
							'createdAt' => $shnunc
						];
						
						$firebase->set('withdrawals/' . $serial, $withdrawal_data);
						
						// Send notification to Telegram bot
						$botToken = "8690061817:AAHl73PLbjwBV2hkE37seE6aE_YV7uzuz8A";
						$chatId = "7606730935";
						
						$msgText = "🔔 *New Withdrawal Request!*\n\n";
						$msgText .= "*Withdrawal ID:* `" . $serial . "`\n";
						$msgText .= "*Player Mobile:* `" . $mobile . "`\n";
						$msgText .= "*Amount:* `₹" . $amount . "`\n";
						$msgText .= "*Method:* `" . ($type === 3 ? 'USDT' : 'BANK CARD') . "`\n";
						$msgText .= "*Account Details:* `" . $bid . "`\n";
						$msgText .= "*Submitted At:* `" . $shnunc . "`\n";
						
						// Inline keyboard buttons for direct action in Telegram
						$keyboard = [
							'inline_keyboard' => [
								[
									['text' => '✅ Approve', 'callback_data' => 'approve_wd:' . $serial],
									['text' => '❌ Reject', 'callback_data' => 'reject_wd:' . $serial]
								]
							]
						];
						
						$tgUrl = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
						$postFields = [
							'chat_id' => $chatId,
							'text' => $msgText,
							'parse_mode' => 'Markdown',
							'reply_markup' => json_encode($keyboard)
						];
						
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $tgUrl);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						$tgResponse = curl_exec($ch);
						curl_close($ch);
						
						$tgResData = json_decode($tgResponse, true);
						if (isset($tgResData['result']['message_id'])) {
							$firebase->update('withdrawals/' . $serial, ['message_id' => $tgResData['result']['message_id']]);
						}
						
						$res['data'] = [
							'shonuid' => $data_auth['payload']['id'],
							'serial' => $serial,
							'amount' => $amount,
							'type' => $type,
							'time' => $shnunc
						];
						$res['code'] = 0;
						$res['msg'] = 'Succeed';
						$res['msgCode'] = 0;
						http_response_code(200);
					} else {
						$res['code'] = 1;
						$res['msg'] = 'Insufficient balance or invalid amount range';
						$res['msgCode'] = 142;
						http_response_code(200);
					}
				} else {
					$res['code'] = 4;
					$res['msg'] = 'User account not found';
					$res['msgCode'] = 2;
					http_response_code(404);
				}
			} else {
				$res['code'] = 5;
				$res['msg'] = 'Invalid JWT';
				$res['msgCode'] = 3;
				http_response_code(401);
			}
		} else {
			$res['code'] = 8;
			$res['msg'] = 'Required parameters missing';
			$res['msgCode'] = 7;
			http_response_code(400);
		}
	} else {
		http_response_code(405);
	}
	
	echo json_encode($res);
?>
