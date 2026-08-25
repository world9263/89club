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
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
			$language = $shonupost['language'];
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$typeId = $shonupost['typeId'];
			$shonustr = '{"language":'.$language.',"random":"'.$random.'","typeId":'.$typeId.'}';
			$shonusign = strtoupper(md5($shonustr));
			if(true){
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				if($data_auth['status'] === 'Success') {
					$mobile = $data_auth['payload']['mobile'];
					$user = $firebase->get('users/' . $mobile);
					if($user != null && isset($user['akshinak']) && $user['akshinak'] == $author){
						if($typeId == 5){
							$oedajnahb = 'gelluonduhogu_aidudi';
						}
						else if($typeId == 6){
							$oedajnahb = 'gelluonduhogu_aidudi_drei';
						}
						else if($typeId == 7){
							$oedajnahb = 'gelluonduhogu_aidudi_funf';
						}
						else if($typeId == 8){
							$oedajnahb = 'gelluonduhogu_aidudi_zehn';
						}
						$samasye = "SELECT atadaaidi, dinankavannuracisi
						  FROM ".$oedajnahb."
						  ORDER BY kramasankhye DESC LIMIT 1";
						$samasyephalitansa=$conn->query($samasye);
						$samasyesreni = mysqli_fetch_array($samasyephalitansa);
						
						$data['issueNumber'] = $samasyesreni['atadaaidi'];
						$data['startTime'] = $samasyesreni['dinankavannuracisi'];
						$ondusamaya = strtotime('+1 minute', strtotime($samasyesreni['dinankavannuracisi']));
						$data['endTime'] = date('Y-m-d H:i:s', $ondusamaya);
						$data['serviceTime'] = date('Y-m-d H:i:s');
						$data['intervalM'] = 1;
						
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
