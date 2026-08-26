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
			$giftCode = $shonupost['giftCode'];	
			$language = $shonupost['language'];		
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];			
			$shonustr = '{"giftCode":"'.$giftCode.'","language":'.$language.',"random":"'.$random.'"}';	
			$shonusign = strtoupper(md5($shonustr));
			if(true){
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				if($data_auth['status'] === 'Success') {
					$mobile = $data_auth['payload']['mobile'];
					$user = $firebase->get('users/' . $mobile);
					if($user != null){
						$shonuid = $data_auth['payload']['id'];
						
						$checkcode = mysqli_query($conn,"SELECT `identite`, `utilisateurmax`, `prix`, `nombredutilisateurs` FROM `hodike_nirvahaka` WHERE `enserie`='".$giftCode."' AND `shonu`='1'");
						$checkcoderow = mysqli_num_rows($checkcode);
						
						$checkuser = mysqli_query($conn,"SELECT `kani` from `hodike_balakedara` where `serial`='".$giftCode."' AND `userkani`='".$shonuid."'");
						$checkuserrow = mysqli_num_rows($checkuser);
						
						if($checkcoderow>0){
							$checkcodearray = mysqli_fetch_array($checkcode);
							$utilisateurmax = $checkcodearray['utilisateurmax'];
							$nombredutilisateurs = $checkcodearray['nombredutilisateurs'];
							if($nombredutilisateurs < $utilisateurmax){
								if($checkuserrow == 0){
									$prix = $checkcodearray['prix'];
									$nombredutilisateurs = $nombredutilisateurs + 1;
									$sql2= mysqli_query($conn,"UPDATE `hodike_nirvahaka` SET `nombredutilisateurs` = '".$nombredutilisateurs."' WHERE `enserie` = '".$giftCode."'");
									$crdt = date("Y-m-d H:i:m");
									$sql= mysqli_query($conn,"INSERT INTO `hodike_balakedara` (`userkani`, `serial`, `price`,`shonu`) VALUES ('".$shonuid."','".$giftCode."','".$prix."','".$crdt."')");
									
									// Update user balance in Firebase
									$userMotta = isset($user['motta']) ? (float)$user['motta'] : 0.0;
									$newMotta = round($userMotta + $prix, 2);
									$firebase->update('users/' . $mobile, ['motta' => $newMotta]);
									
									// Add wagering turnover requirement on gift code reward
									add_turnover($mobile, $prix);
									$data = null;
									$res['data'] = $data;
									$res['code'] = 0;
									$res['msg'] = 'Succeed';
									$res['msgCode'] = 0;
									http_response_code(200);
									echo json_encode($res);
								}
								else{
									$data = null;
									$res['data'] = $data;
									$res['code'] = 1;
									$res['msg'] = 'Redemption code error';
									$res['msgCode'] = 230;
									http_response_code(200);
									echo json_encode($res);
								}								
							}
							else{
								$data = null;
								$res['data'] = $data;
								$res['code'] = 1;
								$res['msg'] = 'Redemption code error';
								$res['msgCode'] = 230;
								http_response_code(200);
								echo json_encode($res);
							}							
						}
						else{
							$data = null;
							$res['data'] = $data;
							$res['code'] = 1;
							$res['msg'] = 'Redemption code error';
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
