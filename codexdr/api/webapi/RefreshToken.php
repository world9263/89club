<?php 
	include "../../conn.php";
	include "../../functions2.php";
	global $firebase;
	
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
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
			$language = $shonupost['language'];
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			if(true){
				$authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
				if (empty($authHeader)) {
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
					echo json_encode($res);
					exit;
				}
				$bearer = explode(" ", $authHeader);
				$author = isset($bearer[1]) ? $bearer[1] : '';
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				if($data_auth['status'] === 'Success') {
					if($data_auth['payload']['expire'] >= time()){
						// Token is still valid — generate a fresh one
						$mobile = $data_auth['payload']['mobile'];
						$user = $firebase->get('users/' . $mobile);
						
						if ($user != null) {
							$expiresIn = time() + 86400;
							$shnutkn_head = array('alg'=>'HS256','typ'=>'JWT');
							$shnutkn_load = array('id'=>$mobile,'mobile'=>$mobile, 'status'=>(isset($user['status']) ? $user['status'] : 1), 'expire'=>$expiresIn, 'ishonup'=>(isset($user['ishonup']) ? $user['ishonup'] : ''), 'codechorkamukala'=>(isset($user['codechorkamukala']) ? $user['codechorkamukala'] : ''));
							$newToken = generate_jwt($shnutkn_head, $shnutkn_load);
							
							// Update token in Firebase
							$firebase->update('users/' . $mobile, ['akshinak' => $newToken, 'shonullgnt' => $shnunc]);
							
							$data['tokenHeader'] = 'Bearer ';
							$data['token'] = $newToken;
							$data['expiresIn'] = $expiresIn;
							
							$res['data'] = $data;
							$res['code'] = 0;
							$res['msg'] = 'Succeed';
							$res['msgCode'] = 0;
							http_response_code(200);
							echo json_encode($res);
						} else {
							$res['code'] = 4;
							$res['msg'] = 'User not found';
							$res['msgCode'] = 2;
							http_response_code(401);
							echo json_encode($res);
						}
					}
					else{
						$res['code'] = 4;
						$res['msg'] = 'Login has expired';
						$res['msgCode'] = 10;
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
