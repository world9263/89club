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
		if (isset($shonupost['language']) && isset($shonupost['logintype']) && isset($shonupost['phonetype']) && isset($shonupost['pwd'])
			&& isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['username'])) {
			$language = $shonupost['language'];
			$logintype = $shonupost['logintype'];
			$phonetype = $shonupost['phonetype'];
			$pwd = $shonupost['pwd'];
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$username = $shonupost['username'];
			$shonustr = '{"language":'.$language.',"logintype":"'.$logintype.'","phonetype":'.$phonetype.',"pwd":"'.$pwd.'","random":"'.$random.'","username":"'.$username.'"}';
			$shonusign = strtoupper(md5($shonustr));
			if(true){
				if(substr($username, 0, 2) == "91") {
					$username = substr($username, 2);
				}
				
                $shonurow = $firebase->get('users/' . $username);
				if($shonurow != null){
					$password = $shonurow['password'];
					if($password == md5($pwd)){
						if(isset($shonurow['status']) && $shonurow['status'] == 1){
							$data['expiresIn'] = time() + 86400;
							$shnutkn_head = array('alg'=>'HS256','typ'=>'JWT');
							$shnutkn_load = array('id'=>$username,'mobile'=>$username, 'status'=>$shonurow['status'], 'expire'=>$data['expiresIn'], 'ishonup'=>(isset($shonurow['ishonup']) ? $shonurow['ishonup'] : ''), 'codechorkamukala'=>(isset($shonurow['codechorkamukala']) ? $shonurow['codechorkamukala'] : ''));
							$data['tokenHeader'] = 'Bearer ';
							$data['token'] = generate_jwt($shnutkn_head, $shnutkn_load);							
							$shnutkn_head_rfsh = array('alg'=>'HS256','typ'=>'JWT');
							$shnutkn_load_rfsh = array('id'=>$username,'mobile'=>$username, 'status'=>$shonurow['status'], 'expire'=>$data['expiresIn']);
							$data['refreshToken'] = generate_jwt($shnutkn_head_rfsh, $shnutkn_load_rfsh);
							$data['passwordErrorNum'] = 0;
							$data['passwordErrorMaxNum'] = 30;
							
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
							$user_agent = $_SERVER['HTTP_USER_AGENT'];
							
                            $firebase->update('users/' . $username, [
                                'shonupwderr' => 0,
                                'ishonup' => $ipaddress,
                                'shonullgnt' => $shnunc,
                                'akshinak' => $data['token'],
                                'tnegaresunohs' => $user_agent
                            ]);
							
							$res['data'] = $data;
							$res['code'] = 0;
							$res['msg'] = 'Succeed';
							$res['msgCode'] = 0;
							http_response_code(200);
							echo json_encode($res);
						}
						else{
							$res['data'] = null;
							$res['code'] = 1;
							$res['msg'] = 'User suspended';
							$res['msgCode'] = 116;
							http_response_code(200);
							echo json_encode($res);
						}						
					}
					else{
                        $pwderrvalue = isset($shonurow['shonupwderr']) ? $shonurow['shonupwderr'] + 1 : 1;
                        $firebase->update('users/' . $username, ['shonupwderr' => $pwderrvalue]);
						
						$data['tokenHeader'] = 'Bearer ';
						$data['token'] = null;
						$data['expiresIn'] = 0;
						$data['refreshToken'] = null;
						$data['passwordErrorNum'] = $pwderrvalue;
						$data['passwordErrorMaxNum'] = 30;
						
						$res['data'] = $data;
						$res['code'] = 1;
						$res['msg'] = 'Password does not correct';
						$res['msgCode'] = 117;
						http_response_code(200);
						echo json_encode($res);
					}										
				}
				else{
					$res['data'] = null;
					$res['code'] = 1;
					$res['msg'] = 'User not exists';
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
