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
		if (isset($shonupost['language']) && isset($shonupost['pageNo']) && isset($shonupost['pageSize']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
			$language = $shonupost['language'];
			$pageNo = $shonupost['pageNo'];
			$pageSize = $shonupost['pageSize'];
			$random = $shonupost['random'];
			$signature = $shonupost['signature'];
			$shonustr = '{"language":'.$language.',"pageNo":'.$pageNo.',"pageSize":'.$pageSize.',"random":"'.$random.'"}';
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
                        $data["list"][0]["bannerTitle"] = "First Deposit Bonus";
                        $data["list"][0]["bannerID"] = 71;
                        $data["list"][0]["bannerUrl"] = "https://89club-production.up.railway.app/Banners/1.png";
                        $data["list"][0]["jumpType"] = 2;
                        $data["list"][0]["contents"] = "/activity/FirstRecharge";
                        
                        $data["list"][1]["bannerTitle"] = "Invitation Bonus";
                        $data["list"][1]["bannerID"] = 62;
                        $data["list"][1]["bannerUrl"] = "https://89club-production.up.railway.app/Banners/2.png";
                        $data["list"][1]["jumpType"] = 2;
                        $data["list"][1]["contents"] = "/main/InvitationBonus";
                        
                        $data["list"][2]["bannerTitle"] = "Win Streak 2X Price Happy Hour";
                        $data["list"][2]["bannerID"] = 53;
                        $data["list"][2]["bannerUrl"] = "https://89club-production.up.railway.app/Banners/6.png";
                        $data["list"][2]["jumpType"] = 1;
                        $data["list"][2]["contents"] = "";
                        
                        $data["list"][3]["bannerTitle"] = "Lucky Spin To Win Iphone 16 Pro Max";
                        $data["list"][3]["bannerID"] = 59;
                        $data["list"][3]["bannerUrl"] = "https://89club-production.up.railway.app/Banners/4.png";
                        $data["list"][3]["jumpType"] = 2;
                        $data["list"][3]["contents"] = "/activity/Turntable";
                        
                        $data["list"][4]["bannerTitle"] = "Daily Bonus Until 1 CRORE";
                        $data["list"][4]["bannerID"] = 69;
                        $data["list"][4]["bannerUrl"] = "https://89club-production.up.railway.app/Banners/5.png";
                        $data["list"][4]["jumpType"] = 1;
                        $data["list"][4]["contents"] = "";


						
						$data['pageNo'] = $pageNo;
						$data['totalPage'] = 1;
						$data['totalCount'] = 20;
						
						$res['data'] = $data;
						$res['code'] = 0;
						$res['msg'] = 'Succeed';
						$res['msgCode'] = 0;
						http_response_code(200);
						echo json_encode($res);			
					}
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
