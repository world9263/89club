<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
	include "../../conn.php";
	include "../../functions2.php";
	
	header('Content-Type: application/json; charset=utf-8');
	header('Strict-Transport-Security: max-age=31536000');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, AR-REAL-IP');
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
		if (isset($shonupost['domainurl']) && isset($shonupost['invitecode']) && isset($shonupost['language']) && isset($shonupost['phonetype']) && isset($shonupost['pwd']) && isset($shonupost['random']) && isset($shonupost['registerType']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['username'])) {
			$domainurl = htmlspecialchars($shonupost['domainurl']);
			$invitecode = htmlspecialchars($shonupost['invitecode']);
			$language = htmlspecialchars($shonupost['language']);
			$phonetype = htmlspecialchars($shonupost['phonetype']);
			$pwd = htmlspecialchars($shonupost['pwd']);
			$random = htmlspecialchars($shonupost['random']);
			$registerType = htmlspecialchars($shonupost['registerType']);
			$signature = htmlspecialchars($shonupost['signature']);
			$username = htmlspecialchars($shonupost['username']);

			// Strip country code
			if(substr($username, 0, 2) == "91") {
				$username = substr($username, 2);
			}

			// ============================================
			// FIREBASE: Check if invite code exists
			// ============================================
			$allUsers = $firebase->get('users');
			$inviterFound = false;
			$inviterKey = '';
			if ($allUsers && is_array($allUsers)) {
				foreach ($allUsers as $key => $user) {
					if (isset($user['owncode']) && $user['owncode'] == $invitecode) {
						$inviterFound = true;
						$inviterKey = $key;
						break;
					}
				}
			}

			if ($inviterFound) {
				// ============================================
				// FIREBASE: Check if phone already registered
				// ============================================
				$existingUser = $firebase->get('users/' . $username);
				
				if ($existingUser == null) {
					// Generate unique own code
					$owncode = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
					
					// Get IP
					$ipaddress = '';
					if (isset($_SERVER['HTTP_CLIENT_IP']))
						$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
					else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
						$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
					else if(isset($_SERVER['HTTP_X_FORWARDED']))
						$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
					else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
						$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
					else if(isset($_SERVER['HTTP_FORWARDED']))
						$ipaddress = $_SERVER['HTTP_FORWARDED'];
					else if(isset($_SERVER['REMOTE_ADDR']))
						$ipaddress = $_SERVER['REMOTE_ADDR'];
					else
						$ipaddress = 'UNKNOWN';
					
					// Generate member code
					$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$digits = '0123456789';
					$selectedLetters = substr(str_shuffle($letters), 0, 6);
					$selectedDigits = substr(str_shuffle($digits), 0, 2);
					$codechorkamukala = 'Member' . str_shuffle($selectedLetters . $selectedDigits);
					
					$password = md5($pwd);
					
					// ============================================
					// FIREBASE: Create new user
					// ============================================
					$newUser = [
						'mobile' => $username,
						'email' => '',
						'password' => $password,
						'code' => $invitecode,
						'owncode' => $owncode,
						'status' => 1,
						'motta' => 0,
						'createdate' => $shnunc,
						'ishonup' => $ipaddress,
						'shonullgnt' => $shnunc,
						'akshinak' => '',
						'codechorkamukala' => $codechorkamukala
					];
					
					$firebase->set('users/' . $username, $newUser);
					
					// Generate JWT
					$status = 1;
					$expiresIn = time() + 86400;
					$shnutkn_head = array('alg'=>'HS256','typ'=>'JWT');
					$shnutkn_load = array('id'=>$username,'mobile'=>$username, 'status'=>$status, 'expire'=>$expiresIn, 'ishonup'=>$ipaddress, 'codechorkamukala'=>$codechorkamukala);
					$akshinak = generate_jwt($shnutkn_head, $shnutkn_load);
					
					// Update token in Firebase
					$firebase->update('users/' . $username, ['akshinak' => $akshinak]);
					
					$res['data']['tokenHeader'] = 'Bearer ';
					$res['data']['token'] = $akshinak;
					$res['code'] = 0;
					$res['msg'] = 'Succeed';
					$res['msgCode'] = 0;
					http_response_code(200);
					echo json_encode($res);	
				}
				else{
					$res['code'] = 1;
					$res['msg'] = 'Phone number have been registered';
					$res['msgCode'] = 111;
					http_response_code(200);
					echo json_encode($res);
				}			
			}
			else{
				$res['code'] = 8;
				$res['msg'] = 'Invitor Not Existed';
				$res['msgCode'] = 110;
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
