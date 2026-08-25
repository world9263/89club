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
	$res = [
		'code' => 11,
		'msg' => 'Method not allowed',
		'msgCode' => 12,
		'serviceNowTime' => $shnunc,
	];
	$shonubody = file_get_contents("php://input");
	$shonupost = json_decode($shonubody, true);
	if ($_SERVER['REQUEST_METHOD'] != 'GET') {		
		if (isset($shonupost['language']) && isset($shonupost['password']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['smsvcode']) && isset($shonupost['timestamp']) && isset($shonupost['type']) && isset($shonupost['username'])) {			
			$language = $shonupost['language'];
			$password = $shonupost['password'];			
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$smsvcode = $shonupost['smsvcode'];
			$type = $shonupost['type'];
			$username = $shonupost['username'];
			$shonustr = '{"language":'.$language.',"password":"'.$password.'","random":"'.$random.'","smsvcode":"'.$smsvcode.'","type":"'.$type.'","username":"'.$username.'"}';
			$shonusign = strtoupper(md5($shonustr));
			if(true){
				if(substr($username, 0, 2) == "91") {
					$mobile = substr($username, 2);
				}
				else{
					$mobile = $username;
				}
				
                $user = $firebase->get('users/' . $mobile);
				
				if($user != null){
                    $otps = $firebase->get('otp_record');
                    $otp = null;
                    $maxId = -1;
                    if($otps != null) {
                        foreach($otps as $key => $o) {
                            if(isset($o['mobile']) && $o['mobile'] == $mobile && isset($o['type']) && $o['type'] == 'Reset PSWD') {
                                if(isset($o['id']) && $o['id'] > $maxId) {
                                    $maxId = $o['id'];
                                    $otp = $o['otp'];
                                }
                            }
                        }
                    }
                    
					if($otp != null){
						if($otp == $smsvcode){
							$md5_password = md5($password);
                            $firebase->update('users/' . $mobile, [
                                'password' => $md5_password,
                                'pwd' => $password
                            ]);
							
							$res['code'] = 0;
							$res['msg'] = 'Succeed';
							$res['msgCode'] = 0;
							http_response_code(200);
							echo json_encode($res);	
						}
						else{
							$res['code'] = 1;
							$res['msg'] = 'Verification code error';
							$res['msgCode'] = 107;
							http_response_code(200);
							echo json_encode($res);
						}
					}
					else{
						$res['code'] = 1;
						$res['msg'] = 'SMS reset password is not available';
						$res['msgCode'] = 173;
						http_response_code(200);
						echo json_encode($res);
					}
				}
				else{
					$res['code'] = 1;
					$res['msg'] = 'User does not exist';
					$res['msgCode'] = 101;
					http_response_code(200);
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
