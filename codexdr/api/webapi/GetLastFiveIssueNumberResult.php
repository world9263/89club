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
				$authHeader = isset(<?php 
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
					if($user != null){
						if($typeId == 1){
							$jayshriram = 'gellaluhogiondu_phalitansa';
						}
						else if($typeId == 2){
							$jayshriram = 'gellaluhogiondu_phalitansa_drei';
						}
						else if($typeId == 3){
							$jayshriram = 'gellaluhogiondu_phalitansa_funf';
						}
						else if($typeId == 4){
							$jayshriram = 'gellaluhogiondu_phalitansa_zehn';
						}
						
						$samasye = "SELECT phalitansa
						  FROM ".$jayshriram."
						  ORDER BY shonu DESC LIMIT 5";
						$samasyephalitansa = $conn->query($samasye);
						$data = array();
						if ($samasyephalitansa->num_rows > 0) {
							$i = 0;
							while ($row = $samasyephalitansa->fetch_assoc()) {									
								$data['number'] = (isset($data['number'])) ? $data['number'] . $row['phalitansa'] . ',' : $row['phalitansa'] . ',';
								$i++;
							}
							$data['number'] = rtrim($data['number'], ",");
						}
						else{
							$data['number'] = null;
						}
						
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
SERVER['HTTP_AUTHORIZATION']) ? <?php 
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
					if($user != null){
						if($typeId == 1){
							$jayshriram = 'gellaluhogiondu_phalitansa';
						}
						else if($typeId == 2){
							$jayshriram = 'gellaluhogiondu_phalitansa_drei';
						}
						else if($typeId == 3){
							$jayshriram = 'gellaluhogiondu_phalitansa_funf';
						}
						else if($typeId == 4){
							$jayshriram = 'gellaluhogiondu_phalitansa_zehn';
						}
						
						$samasye = "SELECT phalitansa
						  FROM ".$jayshriram."
						  ORDER BY shonu DESC LIMIT 5";
						$samasyephalitansa = $conn->query($samasye);
						$data = array();
						if ($samasyephalitansa->num_rows > 0) {
							$i = 0;
							while ($row = $samasyephalitansa->fetch_assoc()) {									
								$data['number'] = (isset($data['number'])) ? $data['number'] . $row['phalitansa'] . ',' : $row['phalitansa'] . ',';
								$i++;
							}
							$data['number'] = rtrim($data['number'], ",");
						}
						else{
							$data['number'] = null;
						}
						
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
SERVER['HTTP_AUTHORIZATION'] : (isset(<?php 
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
					if($user != null){
						if($typeId == 1){
							$jayshriram = 'gellaluhogiondu_phalitansa';
						}
						else if($typeId == 2){
							$jayshriram = 'gellaluhogiondu_phalitansa_drei';
						}
						else if($typeId == 3){
							$jayshriram = 'gellaluhogiondu_phalitansa_funf';
						}
						else if($typeId == 4){
							$jayshriram = 'gellaluhogiondu_phalitansa_zehn';
						}
						
						$samasye = "SELECT phalitansa
						  FROM ".$jayshriram."
						  ORDER BY shonu DESC LIMIT 5";
						$samasyephalitansa = $conn->query($samasye);
						$data = array();
						if ($samasyephalitansa->num_rows > 0) {
							$i = 0;
							while ($row = $samasyephalitansa->fetch_assoc()) {									
								$data['number'] = (isset($data['number'])) ? $data['number'] . $row['phalitansa'] . ',' : $row['phalitansa'] . ',';
								$i++;
							}
							$data['number'] = rtrim($data['number'], ",");
						}
						else{
							$data['number'] = null;
						}
						
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
SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? <?php 
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
					if($user != null){
						if($typeId == 1){
							$jayshriram = 'gellaluhogiondu_phalitansa';
						}
						else if($typeId == 2){
							$jayshriram = 'gellaluhogiondu_phalitansa_drei';
						}
						else if($typeId == 3){
							$jayshriram = 'gellaluhogiondu_phalitansa_funf';
						}
						else if($typeId == 4){
							$jayshriram = 'gellaluhogiondu_phalitansa_zehn';
						}
						
						$samasye = "SELECT phalitansa
						  FROM ".$jayshriram."
						  ORDER BY shonu DESC LIMIT 5";
						$samasyephalitansa = $conn->query($samasye);
						$data = array();
						if ($samasyephalitansa->num_rows > 0) {
							$i = 0;
							while ($row = $samasyephalitansa->fetch_assoc()) {									
								$data['number'] = (isset($data['number'])) ? $data['number'] . $row['phalitansa'] . ',' : $row['phalitansa'] . ',';
								$i++;
							}
							$data['number'] = rtrim($data['number'], ",");
						}
						else{
							$data['number'] = null;
						}
						
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
SERVER['REDIRECT_HTTP_AUTHORIZATION'] : ''); $bearer = explode(' ', $authHeader);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				if($data_auth['status'] === 'Success') {
					$mobile = $data_auth['payload']['mobile'];
					$user = $firebase->get('users/' . $mobile);
					if($user != null){
						if($typeId == 1){
							$jayshriram = 'gellaluhogiondu_phalitansa';
						}
						else if($typeId == 2){
							$jayshriram = 'gellaluhogiondu_phalitansa_drei';
						}
						else if($typeId == 3){
							$jayshriram = 'gellaluhogiondu_phalitansa_funf';
						}
						else if($typeId == 4){
							$jayshriram = 'gellaluhogiondu_phalitansa_zehn';
						}
						
						$samasye = "SELECT phalitansa
						  FROM ".$jayshriram."
						  ORDER BY shonu DESC LIMIT 5";
						$samasyephalitansa = $conn->query($samasye);
						$data = array();
						if ($samasyephalitansa->num_rows > 0) {
							$i = 0;
							while ($row = $samasyephalitansa->fetch_assoc()) {									
								$data['number'] = (isset($data['number'])) ? $data['number'] . $row['phalitansa'] . ',' : $row['phalitansa'] . ',';
								$i++;
							}
							$data['number'] = rtrim($data['number'], ",");
						}
						else{
							$data['number'] = null;
						}
						
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
