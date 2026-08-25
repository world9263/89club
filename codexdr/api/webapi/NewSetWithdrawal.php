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
		if (isset($shonupost['amount']) && isset($shonupost['bid']) && isset($shonupost['language']) && isset($shonupost['pwd']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['type'])) {
			$amount = $shonupost['amount'];
			$bid = $shonupost['bid'];
			$language = $shonupost['language'];			
			$pwd = $shonupost['pwd'];			
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$type = $shonupost['type'];
			
			$shonustr = '{"amount":'.$amount.',"bid":"'.$bid.'","language":'.$language.',"pwd":"'.$pwd.'","random":"'.$random.'","type":'.$type.'}';
			$shonusign = strtoupper(md5($shonustr));
			
			if ($shonusign) {
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
		if (isset($shonupost['amount']) && isset($shonupost['bid']) && isset($shonupost['language']) && isset($shonupost['pwd']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['type'])) {
			$amount = $shonupost['amount'];
			$bid = $shonupost['bid'];
			$language = $shonupost['language'];			
			$pwd = $shonupost['pwd'];			
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$type = $shonupost['type'];
			
			$shonustr = '{"amount":'.$amount.',"bid":"'.$bid.'","language":'.$language.',"pwd":"'.$pwd.'","random":"'.$random.'","type":'.$type.'}';
			$shonusign = strtoupper(md5($shonustr));
			
			if ($shonusign) {
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, true);
				if ($data_auth['status'] === 'Success') {
					$shonuid = $data_auth['payload']['id'];
					
					// Check if user is a demo user
					$checkDemoQuery = "SELECT 1 FROM demo WHERE balakedara = '$shonuid'";
					$isDemoUser = mysqli_num_rows($conn->query($checkDemoQuery)) > 0;

					$balquery = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$shonuid'";
					$balresult = $conn->query($balquery);
					$balarr = mysqli_fetch_array($balresult);

					if ($amount >= 110 && $amount <= 50000 && $amount <= $balarr['motta']) {
						$mottanutan = $balarr['motta'] - $amount;
						$conn->query("UPDATE shonu_kaichila SET motta='$mottanutan' WHERE balakedara='$shonuid'");

						$date = date("Ymd");
						$time = time();
						$serial = 'W' . $date . $time . rand(1000, 9999);

						$sthiti = $isDemoUser ? 1 : 0;
						$tathya = $conn->query("INSERT INTO `hintegedukolli` (`balakedara`, `motta`, `dharavahi`, `khateshonu`, `dinankavannuracisi`, `madari`, `tike`, `sthiti`) 
							VALUES ('$shonuid', '$amount', '$serial', '$bid', '$shnunc', '$type', 'Applied', '$sthiti')");

						if ($tathya) {
							$res['data'] = [
								'shonuid' => $shonuid,
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
							$res['msg'] = 'Database insertion error: ' . mysqli_error($conn);
							$res['msgCode'] = 101;
							http_response_code(500);
						}
					} else {
						$res['code'] = 1;
						$res['msg'] = 'Insufficient balance or invalid amount range';
						$res['msgCode'] = 142;
						http_response_code(200);
					}
				} else {
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
				}
			} else {
				$res['code'] = 5;
				$res['msg'] = 'Wrong signature';
				$res['msgCode'] = 3;
				http_response_code(200);
			}
		} else {
			$res['code'] = 7;
			$res['msg'] = 'Param is Invalid';
			$res['msgCode'] = 6;
			http_response_code(200);
		}
	} else {
		http_response_code(405);
		echo json_encode($res);
		exit;
	}
	
	echo json_encode($res);
	mysqli_close($conn);
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
		if (isset($shonupost['amount']) && isset($shonupost['bid']) && isset($shonupost['language']) && isset($shonupost['pwd']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['type'])) {
			$amount = $shonupost['amount'];
			$bid = $shonupost['bid'];
			$language = $shonupost['language'];			
			$pwd = $shonupost['pwd'];			
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$type = $shonupost['type'];
			
			$shonustr = '{"amount":'.$amount.',"bid":"'.$bid.'","language":'.$language.',"pwd":"'.$pwd.'","random":"'.$random.'","type":'.$type.'}';
			$shonusign = strtoupper(md5($shonustr));
			
			if ($shonusign) {
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, true);
				if ($data_auth['status'] === 'Success') {
					$shonuid = $data_auth['payload']['id'];
					
					// Check if user is a demo user
					$checkDemoQuery = "SELECT 1 FROM demo WHERE balakedara = '$shonuid'";
					$isDemoUser = mysqli_num_rows($conn->query($checkDemoQuery)) > 0;

					$balquery = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$shonuid'";
					$balresult = $conn->query($balquery);
					$balarr = mysqli_fetch_array($balresult);

					if ($amount >= 110 && $amount <= 50000 && $amount <= $balarr['motta']) {
						$mottanutan = $balarr['motta'] - $amount;
						$conn->query("UPDATE shonu_kaichila SET motta='$mottanutan' WHERE balakedara='$shonuid'");

						$date = date("Ymd");
						$time = time();
						$serial = 'W' . $date . $time . rand(1000, 9999);

						$sthiti = $isDemoUser ? 1 : 0;
						$tathya = $conn->query("INSERT INTO `hintegedukolli` (`balakedara`, `motta`, `dharavahi`, `khateshonu`, `dinankavannuracisi`, `madari`, `tike`, `sthiti`) 
							VALUES ('$shonuid', '$amount', '$serial', '$bid', '$shnunc', '$type', 'Applied', '$sthiti')");

						if ($tathya) {
							$res['data'] = [
								'shonuid' => $shonuid,
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
							$res['msg'] = 'Database insertion error: ' . mysqli_error($conn);
							$res['msgCode'] = 101;
							http_response_code(500);
						}
					} else {
						$res['code'] = 1;
						$res['msg'] = 'Insufficient balance or invalid amount range';
						$res['msgCode'] = 142;
						http_response_code(200);
					}
				} else {
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
				}
			} else {
				$res['code'] = 5;
				$res['msg'] = 'Wrong signature';
				$res['msgCode'] = 3;
				http_response_code(200);
			}
		} else {
			$res['code'] = 7;
			$res['msg'] = 'Param is Invalid';
			$res['msgCode'] = 6;
			http_response_code(200);
		}
	} else {
		http_response_code(405);
		echo json_encode($res);
		exit;
	}
	
	echo json_encode($res);
	mysqli_close($conn);
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
		if (isset($shonupost['amount']) && isset($shonupost['bid']) && isset($shonupost['language']) && isset($shonupost['pwd']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['type'])) {
			$amount = $shonupost['amount'];
			$bid = $shonupost['bid'];
			$language = $shonupost['language'];			
			$pwd = $shonupost['pwd'];			
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$type = $shonupost['type'];
			
			$shonustr = '{"amount":'.$amount.',"bid":"'.$bid.'","language":'.$language.',"pwd":"'.$pwd.'","random":"'.$random.'","type":'.$type.'}';
			$shonusign = strtoupper(md5($shonustr));
			
			if ($shonusign) {
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, true);
				if ($data_auth['status'] === 'Success') {
					$shonuid = $data_auth['payload']['id'];
					
					// Check if user is a demo user
					$checkDemoQuery = "SELECT 1 FROM demo WHERE balakedara = '$shonuid'";
					$isDemoUser = mysqli_num_rows($conn->query($checkDemoQuery)) > 0;

					$balquery = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$shonuid'";
					$balresult = $conn->query($balquery);
					$balarr = mysqli_fetch_array($balresult);

					if ($amount >= 110 && $amount <= 50000 && $amount <= $balarr['motta']) {
						$mottanutan = $balarr['motta'] - $amount;
						$conn->query("UPDATE shonu_kaichila SET motta='$mottanutan' WHERE balakedara='$shonuid'");

						$date = date("Ymd");
						$time = time();
						$serial = 'W' . $date . $time . rand(1000, 9999);

						$sthiti = $isDemoUser ? 1 : 0;
						$tathya = $conn->query("INSERT INTO `hintegedukolli` (`balakedara`, `motta`, `dharavahi`, `khateshonu`, `dinankavannuracisi`, `madari`, `tike`, `sthiti`) 
							VALUES ('$shonuid', '$amount', '$serial', '$bid', '$shnunc', '$type', 'Applied', '$sthiti')");

						if ($tathya) {
							$res['data'] = [
								'shonuid' => $shonuid,
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
							$res['msg'] = 'Database insertion error: ' . mysqli_error($conn);
							$res['msgCode'] = 101;
							http_response_code(500);
						}
					} else {
						$res['code'] = 1;
						$res['msg'] = 'Insufficient balance or invalid amount range';
						$res['msgCode'] = 142;
						http_response_code(200);
					}
				} else {
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
				}
			} else {
				$res['code'] = 5;
				$res['msg'] = 'Wrong signature';
				$res['msgCode'] = 3;
				http_response_code(200);
			}
		} else {
			$res['code'] = 7;
			$res['msg'] = 'Param is Invalid';
			$res['msgCode'] = 6;
			http_response_code(200);
		}
	} else {
		http_response_code(405);
		echo json_encode($res);
		exit;
	}
	
	echo json_encode($res);
	mysqli_close($conn);
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
		if (isset($shonupost['amount']) && isset($shonupost['bid']) && isset($shonupost['language']) && isset($shonupost['pwd']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['type'])) {
			$amount = $shonupost['amount'];
			$bid = $shonupost['bid'];
			$language = $shonupost['language'];			
			$pwd = $shonupost['pwd'];			
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$type = $shonupost['type'];
			
			$shonustr = '{"amount":'.$amount.',"bid":"'.$bid.'","language":'.$language.',"pwd":"'.$pwd.'","random":"'.$random.'","type":'.$type.'}';
			$shonusign = strtoupper(md5($shonustr));
			
			if ($shonusign) {
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, true);
				if ($data_auth['status'] === 'Success') {
					$shonuid = $data_auth['payload']['id'];
					
					// Check if user is a demo user
					$checkDemoQuery = "SELECT 1 FROM demo WHERE balakedara = '$shonuid'";
					$isDemoUser = mysqli_num_rows($conn->query($checkDemoQuery)) > 0;

					$balquery = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$shonuid'";
					$balresult = $conn->query($balquery);
					$balarr = mysqli_fetch_array($balresult);

					if ($amount >= 110 && $amount <= 50000 && $amount <= $balarr['motta']) {
						$mottanutan = $balarr['motta'] - $amount;
						$conn->query("UPDATE shonu_kaichila SET motta='$mottanutan' WHERE balakedara='$shonuid'");

						$date = date("Ymd");
						$time = time();
						$serial = 'W' . $date . $time . rand(1000, 9999);

						$sthiti = $isDemoUser ? 1 : 0;
						$tathya = $conn->query("INSERT INTO `hintegedukolli` (`balakedara`, `motta`, `dharavahi`, `khateshonu`, `dinankavannuracisi`, `madari`, `tike`, `sthiti`) 
							VALUES ('$shonuid', '$amount', '$serial', '$bid', '$shnunc', '$type', 'Applied', '$sthiti')");

						if ($tathya) {
							$res['data'] = [
								'shonuid' => $shonuid,
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
							$res['msg'] = 'Database insertion error: ' . mysqli_error($conn);
							$res['msgCode'] = 101;
							http_response_code(500);
						}
					} else {
						$res['code'] = 1;
						$res['msg'] = 'Insufficient balance or invalid amount range';
						$res['msgCode'] = 142;
						http_response_code(200);
					}
				} else {
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
				}
			} else {
				$res['code'] = 5;
				$res['msg'] = 'Wrong signature';
				$res['msgCode'] = 3;
				http_response_code(200);
			}
		} else {
			$res['code'] = 7;
			$res['msg'] = 'Param is Invalid';
			$res['msgCode'] = 6;
			http_response_code(200);
		}
	} else {
		http_response_code(405);
		echo json_encode($res);
		exit;
	}
	
	echo json_encode($res);
	mysqli_close($conn);
?>
SERVER['REDIRECT_HTTP_AUTHORIZATION'] : ''); $bearer = explode(' ', $authHeader);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, true);
				if ($data_auth['status'] === 'Success') {
					$shonuid = $data_auth['payload']['id'];
					
					// Check if user is a demo user
					$checkDemoQuery = "SELECT 1 FROM demo WHERE balakedara = '$shonuid'";
					$isDemoUser = mysqli_num_rows($conn->query($checkDemoQuery)) > 0;

					$balquery = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$shonuid'";
					$balresult = $conn->query($balquery);
					$balarr = mysqli_fetch_array($balresult);

					if ($amount >= 110 && $amount <= 50000 && $amount <= $balarr['motta']) {
						$mottanutan = $balarr['motta'] - $amount;
						$conn->query("UPDATE shonu_kaichila SET motta='$mottanutan' WHERE balakedara='$shonuid'");

						$date = date("Ymd");
						$time = time();
						$serial = 'W' . $date . $time . rand(1000, 9999);

						$sthiti = $isDemoUser ? 1 : 0;
						$tathya = $conn->query("INSERT INTO `hintegedukolli` (`balakedara`, `motta`, `dharavahi`, `khateshonu`, `dinankavannuracisi`, `madari`, `tike`, `sthiti`) 
							VALUES ('$shonuid', '$amount', '$serial', '$bid', '$shnunc', '$type', 'Applied', '$sthiti')");

						if ($tathya) {
							$res['data'] = [
								'shonuid' => $shonuid,
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
							$res['msg'] = 'Database insertion error: ' . mysqli_error($conn);
							$res['msgCode'] = 101;
							http_response_code(500);
						}
					} else {
						$res['code'] = 1;
						$res['msg'] = 'Insufficient balance or invalid amount range';
						$res['msgCode'] = 142;
						http_response_code(200);
					}
				} else {
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
				}
			} else {
				$res['code'] = 5;
				$res['msg'] = 'Wrong signature';
				$res['msgCode'] = 3;
				http_response_code(200);
			}
		} else {
			$res['code'] = 7;
			$res['msg'] = 'Param is Invalid';
			$res['msgCode'] = 6;
			http_response_code(200);
		}
	} else {
		http_response_code(405);
		echo json_encode($res);
		exit;
	}
	
	echo json_encode($res);
	mysqli_close($conn);
?>
