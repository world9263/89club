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
			$pageNo = $shonupost['pageNo'];
			$pageSize = $shonupost['pageSize'];
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$typeId = $shonupost['typeId'];
			$shonustr = '{"language":'.$language.',"pageNo":'.$pageNo.',"pageSize":'.$pageSize.',"random":"'.$random.'","typeId":'.$typeId.'}';
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
			$pageNo = $shonupost['pageNo'];
			$pageSize = $shonupost['pageSize'];
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$typeId = $shonupost['typeId'];
			$shonustr = '{"language":'.$language.',"pageNo":'.$pageNo.',"pageSize":'.$pageSize.',"random":"'.$random.'","typeId":'.$typeId.'}';
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
							$jayshriram = 'bajikattuttate';
						}
						else if($typeId == 2){
							$jayshriram = 'bajikattuttate_drei';
						}
						else if($typeId == 3){
							$jayshriram = 'bajikattuttate_funf';
						}
						else if($typeId == 4){
							$jayshriram = 'bajikattuttate_zehn';
						}
						
						$samatolana = ($pageNo - 1) * 10;
						$shonuid = $data_auth['payload']['id'];
						$samasye = "SELECT kalaparichaya, ojana, menge, wettanzahl, ketebida, phalaphala, sesabida, ergebnis, zufallig, tiarikala
						  FROM ".$jayshriram." WHERE byabaharkarta = $shonuid
						  ORDER BY parichaya DESC LIMIT $pageSize OFFSET $samatolana";
						$samasyephalitansa = $conn->query($samasye);
						
						if ($samasyephalitansa->num_rows > 0) {
							$i = 0;
							while ($row = $samasyephalitansa->fetch_assoc()) {
								if($row['ojana'] == 10){
									$ojana = 'red';
								}
								else if($row['ojana'] == 11){
									$ojana = 'green';
								}
								else if($row['ojana'] == 12){
									$ojana = 'violet';
								}
								else if($row['ojana'] == 13){
									$ojana = 'big';
								}
								else if($row['ojana'] == 14){
									$ojana = 'small';
								}
								else{
									$ojana = $row['ojana'];
								}									
								if($row['phalaphala'] == 'perte'){
									if($row['ergebnis'] == null){
											$data['list'][$i] = [
											'orderNumber' => $i,
											'issueNumber' => $row['kalaparichaya'],
											'amount' => $row['menge'],
											'betCount' => $row['wettanzahl'],
											'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
											'fee' => $row['ketebida']/100*2,
											'selectType' => $ojana,
											'state' => 2,
											'addTime' => $row['tiarikala'],
										];
									}
									else if($row['ergebnis'] >= 0 && $row['ergebnis'] <= 9){
										$data['list'][$i] = [
											'orderNumber' => $i,
											'issueNumber' => $row['kalaparichaya'],
											'amount' => $row['menge'],
											'betCount' => $row['wettanzahl'],
											'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
											'fee' => $row['ketebida']/100*2,
											'number' => $row['ergebnis'],
											'selectType' => $ojana,
											'state' => 0,
											'profitAmount' => $row['sesabida'],
											'premium' => $row['zufallig'],
											'gameType' => $typeId,
											'addTime' => $row['tiarikala'],
										];
									}
								}
								if($row['phalaphala'] == 'gagner'){
									if($row['ergebnis'] == $row['ojana']){
										$state = 1;
									}
									else if($row['ergebnis'] == 0 && ($row['ojana'] == 10 || $row['ojana'] == 12)){
										$state = 1;
									}
									else if(($row['ergebnis'] == 1 || $row['ergebnis'] == 3 || $row['ergebnis'] == 7 || $row['ergebnis'] == 9)  && ($row['ojana'] == 11)){
										$state = 1;
									}
									else if($row['ergebnis'] == 5 && ($row['ojana'] == 11 || $row['ojana'] == 12)){
										$state = 1;
									}
									else if(($row['ergebnis'] == 2 || $row['ergebnis'] == 4 || $row['ergebnis'] == 6 || $row['ergebnis'] == 8)  && ($row['ojana'] == 10)){
										$state = 1;
									}
									else if(($row['ergebnis'] >= 0 && $row['ergebnis'] <= 4)  && ($row['ojana'] == 14)){
										$state = 1;
									}
									else if(($row['ergebnis'] >= 5 && $row['ergebnis'] <= 9)  && ($row['ojana'] == 13)){
										$state = 1;
									}
									else{
										$state = 0;
									}
									$data['list'][$i] = [
										'orderNumber' => $i,
										'issueNumber' => $row['kalaparichaya'],
										'amount' => $row['menge'],
										'betCount' => $row['wettanzahl'],
										'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
										'fee' => $row['ketebida']/100*2,
										'number' => $row['ergebnis'],
										'selectType' => $ojana,
										'state' => $state,
										'profitAmount' => $row['sesabida'],
										'premium' => $row['zufallig'],
										'gameType' => $typeId,
										'addTime' => $row['tiarikala'],
									];
								}
								$i++;
							}
						}
						else{
							$data['list'] = null;
						}
						
						$samasye_ondu = "SELECT parichaya
						  FROM ".$jayshriram;
						$samasyephalitansa_ondu = $conn->query($samasye_ondu);
						$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
						
						$data['pageNo'] = $pageNo;
						$data['totalPage'] = ceil($samasyephalitansa_sankhye/10);
						$data['totalCount'] = $samasyephalitansa_sankhye;
						
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
			$pageNo = $shonupost['pageNo'];
			$pageSize = $shonupost['pageSize'];
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$typeId = $shonupost['typeId'];
			$shonustr = '{"language":'.$language.',"pageNo":'.$pageNo.',"pageSize":'.$pageSize.',"random":"'.$random.'","typeId":'.$typeId.'}';
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
							$jayshriram = 'bajikattuttate';
						}
						else if($typeId == 2){
							$jayshriram = 'bajikattuttate_drei';
						}
						else if($typeId == 3){
							$jayshriram = 'bajikattuttate_funf';
						}
						else if($typeId == 4){
							$jayshriram = 'bajikattuttate_zehn';
						}
						
						$samatolana = ($pageNo - 1) * 10;
						$shonuid = $data_auth['payload']['id'];
						$samasye = "SELECT kalaparichaya, ojana, menge, wettanzahl, ketebida, phalaphala, sesabida, ergebnis, zufallig, tiarikala
						  FROM ".$jayshriram." WHERE byabaharkarta = $shonuid
						  ORDER BY parichaya DESC LIMIT $pageSize OFFSET $samatolana";
						$samasyephalitansa = $conn->query($samasye);
						
						if ($samasyephalitansa->num_rows > 0) {
							$i = 0;
							while ($row = $samasyephalitansa->fetch_assoc()) {
								if($row['ojana'] == 10){
									$ojana = 'red';
								}
								else if($row['ojana'] == 11){
									$ojana = 'green';
								}
								else if($row['ojana'] == 12){
									$ojana = 'violet';
								}
								else if($row['ojana'] == 13){
									$ojana = 'big';
								}
								else if($row['ojana'] == 14){
									$ojana = 'small';
								}
								else{
									$ojana = $row['ojana'];
								}									
								if($row['phalaphala'] == 'perte'){
									if($row['ergebnis'] == null){
											$data['list'][$i] = [
											'orderNumber' => $i,
											'issueNumber' => $row['kalaparichaya'],
											'amount' => $row['menge'],
											'betCount' => $row['wettanzahl'],
											'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
											'fee' => $row['ketebida']/100*2,
											'selectType' => $ojana,
											'state' => 2,
											'addTime' => $row['tiarikala'],
										];
									}
									else if($row['ergebnis'] >= 0 && $row['ergebnis'] <= 9){
										$data['list'][$i] = [
											'orderNumber' => $i,
											'issueNumber' => $row['kalaparichaya'],
											'amount' => $row['menge'],
											'betCount' => $row['wettanzahl'],
											'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
											'fee' => $row['ketebida']/100*2,
											'number' => $row['ergebnis'],
											'selectType' => $ojana,
											'state' => 0,
											'profitAmount' => $row['sesabida'],
											'premium' => $row['zufallig'],
											'gameType' => $typeId,
											'addTime' => $row['tiarikala'],
										];
									}
								}
								if($row['phalaphala'] == 'gagner'){
									if($row['ergebnis'] == $row['ojana']){
										$state = 1;
									}
									else if($row['ergebnis'] == 0 && ($row['ojana'] == 10 || $row['ojana'] == 12)){
										$state = 1;
									}
									else if(($row['ergebnis'] == 1 || $row['ergebnis'] == 3 || $row['ergebnis'] == 7 || $row['ergebnis'] == 9)  && ($row['ojana'] == 11)){
										$state = 1;
									}
									else if($row['ergebnis'] == 5 && ($row['ojana'] == 11 || $row['ojana'] == 12)){
										$state = 1;
									}
									else if(($row['ergebnis'] == 2 || $row['ergebnis'] == 4 || $row['ergebnis'] == 6 || $row['ergebnis'] == 8)  && ($row['ojana'] == 10)){
										$state = 1;
									}
									else if(($row['ergebnis'] >= 0 && $row['ergebnis'] <= 4)  && ($row['ojana'] == 14)){
										$state = 1;
									}
									else if(($row['ergebnis'] >= 5 && $row['ergebnis'] <= 9)  && ($row['ojana'] == 13)){
										$state = 1;
									}
									else{
										$state = 0;
									}
									$data['list'][$i] = [
										'orderNumber' => $i,
										'issueNumber' => $row['kalaparichaya'],
										'amount' => $row['menge'],
										'betCount' => $row['wettanzahl'],
										'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
										'fee' => $row['ketebida']/100*2,
										'number' => $row['ergebnis'],
										'selectType' => $ojana,
										'state' => $state,
										'profitAmount' => $row['sesabida'],
										'premium' => $row['zufallig'],
										'gameType' => $typeId,
										'addTime' => $row['tiarikala'],
									];
								}
								$i++;
							}
						}
						else{
							$data['list'] = null;
						}
						
						$samasye_ondu = "SELECT parichaya
						  FROM ".$jayshriram;
						$samasyephalitansa_ondu = $conn->query($samasye_ondu);
						$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
						
						$data['pageNo'] = $pageNo;
						$data['totalPage'] = ceil($samasyephalitansa_sankhye/10);
						$data['totalCount'] = $samasyephalitansa_sankhye;
						
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
			$pageNo = $shonupost['pageNo'];
			$pageSize = $shonupost['pageSize'];
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$typeId = $shonupost['typeId'];
			$shonustr = '{"language":'.$language.',"pageNo":'.$pageNo.',"pageSize":'.$pageSize.',"random":"'.$random.'","typeId":'.$typeId.'}';
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
							$jayshriram = 'bajikattuttate';
						}
						else if($typeId == 2){
							$jayshriram = 'bajikattuttate_drei';
						}
						else if($typeId == 3){
							$jayshriram = 'bajikattuttate_funf';
						}
						else if($typeId == 4){
							$jayshriram = 'bajikattuttate_zehn';
						}
						
						$samatolana = ($pageNo - 1) * 10;
						$shonuid = $data_auth['payload']['id'];
						$samasye = "SELECT kalaparichaya, ojana, menge, wettanzahl, ketebida, phalaphala, sesabida, ergebnis, zufallig, tiarikala
						  FROM ".$jayshriram." WHERE byabaharkarta = $shonuid
						  ORDER BY parichaya DESC LIMIT $pageSize OFFSET $samatolana";
						$samasyephalitansa = $conn->query($samasye);
						
						if ($samasyephalitansa->num_rows > 0) {
							$i = 0;
							while ($row = $samasyephalitansa->fetch_assoc()) {
								if($row['ojana'] == 10){
									$ojana = 'red';
								}
								else if($row['ojana'] == 11){
									$ojana = 'green';
								}
								else if($row['ojana'] == 12){
									$ojana = 'violet';
								}
								else if($row['ojana'] == 13){
									$ojana = 'big';
								}
								else if($row['ojana'] == 14){
									$ojana = 'small';
								}
								else{
									$ojana = $row['ojana'];
								}									
								if($row['phalaphala'] == 'perte'){
									if($row['ergebnis'] == null){
											$data['list'][$i] = [
											'orderNumber' => $i,
											'issueNumber' => $row['kalaparichaya'],
											'amount' => $row['menge'],
											'betCount' => $row['wettanzahl'],
											'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
											'fee' => $row['ketebida']/100*2,
											'selectType' => $ojana,
											'state' => 2,
											'addTime' => $row['tiarikala'],
										];
									}
									else if($row['ergebnis'] >= 0 && $row['ergebnis'] <= 9){
										$data['list'][$i] = [
											'orderNumber' => $i,
											'issueNumber' => $row['kalaparichaya'],
											'amount' => $row['menge'],
											'betCount' => $row['wettanzahl'],
											'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
											'fee' => $row['ketebida']/100*2,
											'number' => $row['ergebnis'],
											'selectType' => $ojana,
											'state' => 0,
											'profitAmount' => $row['sesabida'],
											'premium' => $row['zufallig'],
											'gameType' => $typeId,
											'addTime' => $row['tiarikala'],
										];
									}
								}
								if($row['phalaphala'] == 'gagner'){
									if($row['ergebnis'] == $row['ojana']){
										$state = 1;
									}
									else if($row['ergebnis'] == 0 && ($row['ojana'] == 10 || $row['ojana'] == 12)){
										$state = 1;
									}
									else if(($row['ergebnis'] == 1 || $row['ergebnis'] == 3 || $row['ergebnis'] == 7 || $row['ergebnis'] == 9)  && ($row['ojana'] == 11)){
										$state = 1;
									}
									else if($row['ergebnis'] == 5 && ($row['ojana'] == 11 || $row['ojana'] == 12)){
										$state = 1;
									}
									else if(($row['ergebnis'] == 2 || $row['ergebnis'] == 4 || $row['ergebnis'] == 6 || $row['ergebnis'] == 8)  && ($row['ojana'] == 10)){
										$state = 1;
									}
									else if(($row['ergebnis'] >= 0 && $row['ergebnis'] <= 4)  && ($row['ojana'] == 14)){
										$state = 1;
									}
									else if(($row['ergebnis'] >= 5 && $row['ergebnis'] <= 9)  && ($row['ojana'] == 13)){
										$state = 1;
									}
									else{
										$state = 0;
									}
									$data['list'][$i] = [
										'orderNumber' => $i,
										'issueNumber' => $row['kalaparichaya'],
										'amount' => $row['menge'],
										'betCount' => $row['wettanzahl'],
										'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
										'fee' => $row['ketebida']/100*2,
										'number' => $row['ergebnis'],
										'selectType' => $ojana,
										'state' => $state,
										'profitAmount' => $row['sesabida'],
										'premium' => $row['zufallig'],
										'gameType' => $typeId,
										'addTime' => $row['tiarikala'],
									];
								}
								$i++;
							}
						}
						else{
							$data['list'] = null;
						}
						
						$samasye_ondu = "SELECT parichaya
						  FROM ".$jayshriram;
						$samasyephalitansa_ondu = $conn->query($samasye_ondu);
						$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
						
						$data['pageNo'] = $pageNo;
						$data['totalPage'] = ceil($samasyephalitansa_sankhye/10);
						$data['totalCount'] = $samasyephalitansa_sankhye;
						
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
			$pageNo = $shonupost['pageNo'];
			$pageSize = $shonupost['pageSize'];
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$typeId = $shonupost['typeId'];
			$shonustr = '{"language":'.$language.',"pageNo":'.$pageNo.',"pageSize":'.$pageSize.',"random":"'.$random.'","typeId":'.$typeId.'}';
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
							$jayshriram = 'bajikattuttate';
						}
						else if($typeId == 2){
							$jayshriram = 'bajikattuttate_drei';
						}
						else if($typeId == 3){
							$jayshriram = 'bajikattuttate_funf';
						}
						else if($typeId == 4){
							$jayshriram = 'bajikattuttate_zehn';
						}
						
						$samatolana = ($pageNo - 1) * 10;
						$shonuid = $data_auth['payload']['id'];
						$samasye = "SELECT kalaparichaya, ojana, menge, wettanzahl, ketebida, phalaphala, sesabida, ergebnis, zufallig, tiarikala
						  FROM ".$jayshriram." WHERE byabaharkarta = $shonuid
						  ORDER BY parichaya DESC LIMIT $pageSize OFFSET $samatolana";
						$samasyephalitansa = $conn->query($samasye);
						
						if ($samasyephalitansa->num_rows > 0) {
							$i = 0;
							while ($row = $samasyephalitansa->fetch_assoc()) {
								if($row['ojana'] == 10){
									$ojana = 'red';
								}
								else if($row['ojana'] == 11){
									$ojana = 'green';
								}
								else if($row['ojana'] == 12){
									$ojana = 'violet';
								}
								else if($row['ojana'] == 13){
									$ojana = 'big';
								}
								else if($row['ojana'] == 14){
									$ojana = 'small';
								}
								else{
									$ojana = $row['ojana'];
								}									
								if($row['phalaphala'] == 'perte'){
									if($row['ergebnis'] == null){
											$data['list'][$i] = [
											'orderNumber' => $i,
											'issueNumber' => $row['kalaparichaya'],
											'amount' => $row['menge'],
											'betCount' => $row['wettanzahl'],
											'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
											'fee' => $row['ketebida']/100*2,
											'selectType' => $ojana,
											'state' => 2,
											'addTime' => $row['tiarikala'],
										];
									}
									else if($row['ergebnis'] >= 0 && $row['ergebnis'] <= 9){
										$data['list'][$i] = [
											'orderNumber' => $i,
											'issueNumber' => $row['kalaparichaya'],
											'amount' => $row['menge'],
											'betCount' => $row['wettanzahl'],
											'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
											'fee' => $row['ketebida']/100*2,
											'number' => $row['ergebnis'],
											'selectType' => $ojana,
											'state' => 0,
											'profitAmount' => $row['sesabida'],
											'premium' => $row['zufallig'],
											'gameType' => $typeId,
											'addTime' => $row['tiarikala'],
										];
									}
								}
								if($row['phalaphala'] == 'gagner'){
									if($row['ergebnis'] == $row['ojana']){
										$state = 1;
									}
									else if($row['ergebnis'] == 0 && ($row['ojana'] == 10 || $row['ojana'] == 12)){
										$state = 1;
									}
									else if(($row['ergebnis'] == 1 || $row['ergebnis'] == 3 || $row['ergebnis'] == 7 || $row['ergebnis'] == 9)  && ($row['ojana'] == 11)){
										$state = 1;
									}
									else if($row['ergebnis'] == 5 && ($row['ojana'] == 11 || $row['ojana'] == 12)){
										$state = 1;
									}
									else if(($row['ergebnis'] == 2 || $row['ergebnis'] == 4 || $row['ergebnis'] == 6 || $row['ergebnis'] == 8)  && ($row['ojana'] == 10)){
										$state = 1;
									}
									else if(($row['ergebnis'] >= 0 && $row['ergebnis'] <= 4)  && ($row['ojana'] == 14)){
										$state = 1;
									}
									else if(($row['ergebnis'] >= 5 && $row['ergebnis'] <= 9)  && ($row['ojana'] == 13)){
										$state = 1;
									}
									else{
										$state = 0;
									}
									$data['list'][$i] = [
										'orderNumber' => $i,
										'issueNumber' => $row['kalaparichaya'],
										'amount' => $row['menge'],
										'betCount' => $row['wettanzahl'],
										'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
										'fee' => $row['ketebida']/100*2,
										'number' => $row['ergebnis'],
										'selectType' => $ojana,
										'state' => $state,
										'profitAmount' => $row['sesabida'],
										'premium' => $row['zufallig'],
										'gameType' => $typeId,
										'addTime' => $row['tiarikala'],
									];
								}
								$i++;
							}
						}
						else{
							$data['list'] = null;
						}
						
						$samasye_ondu = "SELECT parichaya
						  FROM ".$jayshriram;
						$samasyephalitansa_ondu = $conn->query($samasye_ondu);
						$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
						
						$data['pageNo'] = $pageNo;
						$data['totalPage'] = ceil($samasyephalitansa_sankhye/10);
						$data['totalCount'] = $samasyephalitansa_sankhye;
						
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
							$jayshriram = 'bajikattuttate';
						}
						else if($typeId == 2){
							$jayshriram = 'bajikattuttate_drei';
						}
						else if($typeId == 3){
							$jayshriram = 'bajikattuttate_funf';
						}
						else if($typeId == 4){
							$jayshriram = 'bajikattuttate_zehn';
						}
						
						$samatolana = ($pageNo - 1) * 10;
						$shonuid = $data_auth['payload']['id'];
						$samasye = "SELECT kalaparichaya, ojana, menge, wettanzahl, ketebida, phalaphala, sesabida, ergebnis, zufallig, tiarikala
						  FROM ".$jayshriram." WHERE byabaharkarta = $shonuid
						  ORDER BY parichaya DESC LIMIT $pageSize OFFSET $samatolana";
						$samasyephalitansa = $conn->query($samasye);
						
						if ($samasyephalitansa->num_rows > 0) {
							$i = 0;
							while ($row = $samasyephalitansa->fetch_assoc()) {
								if($row['ojana'] == 10){
									$ojana = 'red';
								}
								else if($row['ojana'] == 11){
									$ojana = 'green';
								}
								else if($row['ojana'] == 12){
									$ojana = 'violet';
								}
								else if($row['ojana'] == 13){
									$ojana = 'big';
								}
								else if($row['ojana'] == 14){
									$ojana = 'small';
								}
								else{
									$ojana = $row['ojana'];
								}									
								if($row['phalaphala'] == 'perte'){
									if($row['ergebnis'] == null){
											$data['list'][$i] = [
											'orderNumber' => $i,
											'issueNumber' => $row['kalaparichaya'],
											'amount' => $row['menge'],
											'betCount' => $row['wettanzahl'],
											'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
											'fee' => $row['ketebida']/100*2,
											'selectType' => $ojana,
											'state' => 2,
											'addTime' => $row['tiarikala'],
										];
									}
									else if($row['ergebnis'] >= 0 && $row['ergebnis'] <= 9){
										$data['list'][$i] = [
											'orderNumber' => $i,
											'issueNumber' => $row['kalaparichaya'],
											'amount' => $row['menge'],
											'betCount' => $row['wettanzahl'],
											'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
											'fee' => $row['ketebida']/100*2,
											'number' => $row['ergebnis'],
											'selectType' => $ojana,
											'state' => 0,
											'profitAmount' => $row['sesabida'],
											'premium' => $row['zufallig'],
											'gameType' => $typeId,
											'addTime' => $row['tiarikala'],
										];
									}
								}
								if($row['phalaphala'] == 'gagner'){
									if($row['ergebnis'] == $row['ojana']){
										$state = 1;
									}
									else if($row['ergebnis'] == 0 && ($row['ojana'] == 10 || $row['ojana'] == 12)){
										$state = 1;
									}
									else if(($row['ergebnis'] == 1 || $row['ergebnis'] == 3 || $row['ergebnis'] == 7 || $row['ergebnis'] == 9)  && ($row['ojana'] == 11)){
										$state = 1;
									}
									else if($row['ergebnis'] == 5 && ($row['ojana'] == 11 || $row['ojana'] == 12)){
										$state = 1;
									}
									else if(($row['ergebnis'] == 2 || $row['ergebnis'] == 4 || $row['ergebnis'] == 6 || $row['ergebnis'] == 8)  && ($row['ojana'] == 10)){
										$state = 1;
									}
									else if(($row['ergebnis'] >= 0 && $row['ergebnis'] <= 4)  && ($row['ojana'] == 14)){
										$state = 1;
									}
									else if(($row['ergebnis'] >= 5 && $row['ergebnis'] <= 9)  && ($row['ojana'] == 13)){
										$state = 1;
									}
									else{
										$state = 0;
									}
									$data['list'][$i] = [
										'orderNumber' => $i,
										'issueNumber' => $row['kalaparichaya'],
										'amount' => $row['menge'],
										'betCount' => $row['wettanzahl'],
										'realAmount' => $row['ketebida'] - $row['ketebida']/100*2,
										'fee' => $row['ketebida']/100*2,
										'number' => $row['ergebnis'],
										'selectType' => $ojana,
										'state' => $state,
										'profitAmount' => $row['sesabida'],
										'premium' => $row['zufallig'],
										'gameType' => $typeId,
										'addTime' => $row['tiarikala'],
									];
								}
								$i++;
							}
						}
						else{
							$data['list'] = null;
						}
						
						$samasye_ondu = "SELECT parichaya
						  FROM ".$jayshriram;
						$samasyephalitansa_ondu = $conn->query($samasye_ondu);
						$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
						
						$data['pageNo'] = $pageNo;
						$data['totalPage'] = ceil($samasyephalitansa_sankhye/10);
						$data['totalCount'] = $samasyephalitansa_sankhye;
						
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
