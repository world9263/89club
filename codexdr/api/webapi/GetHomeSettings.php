<?php
	include "../../conn.php";
			
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
			$shonustr = '{"language":'.$language.',"random":"'.$random.'"}';
			$shonusign = strtoupper(md5($shonustr));
			if(true){
				$is_bdt = false;
				$authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
				if (!empty($authHeader)) {
					$bearer = explode(" ", $authHeader);
					$author = isset($bearer[1]) ? $bearer[1] : '';
					include_once "../../functions2.php";
					$is_jwt_valid = is_jwt_valid($author);
					$data_auth = json_decode($is_jwt_valid, true);
					if (isset($data_auth['status']) && $data_auth['status'] === 'Success') {
						$mobile = $data_auth['payload']['mobile'];
						if (strpos($mobile, '880') === 0 || strpos($mobile, '+880') === 0) {
							$is_bdt = true;
						}
					}
				}
				if (!$is_bdt && isset($language) && ($language === 'bdt' || $language === '"bdt"')) {
					$is_bdt = true;
				}
				if (!$is_bdt) {
					$cf_country = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? strtoupper($_SERVER["HTTP_CF_IPCOUNTRY"]) : '';
					if ($cf_country === 'BD') {
						$is_bdt = true;
					}
				}

				$data['isShowAppDownloadUp'] = true;
				$data['isShowAppDownloadDown'] = true;
				$data['isShowLotteryDragon'] = true;
				$data['isSplitLocalEWallet'] = true;
				$data['jackportMaxReswadAmount'] = 500;
				$data['projectName'] = '89 𝐂𝐋𝐔𝐁';
				$data['projectLogo'] = '/logo.png';
				$data['languages'] = 'en|hd|bdt';
				$data['webIco'] = '/logo.png';
				$data['headLogo'] = '/logo.png';
				$data['dollarSign'] = $is_bdt ? '৳' : '₹';
				$data['upperOrLower'] = '0';
				$data['defaultCurrentLanguage'] = $is_bdt ? 'bdt' : 'en';
				$data['registerMobile'] = '1';
				$data['registerEmail'] = '0';
				$countries = [
					['area' => '+91', 'len' => '9-12'],
					['area' => '+1', 'len' => '10-15'],
					['area' => '+44', 'len' => '10-15'],
					['area' => '+880', 'len' => '10-15'],
					['area' => '+92', 'len' => '10-15'],
					['area' => '+977', 'len' => '10-15'],
					['area' => '+94', 'len' => '9-12'],
					['area' => '+971', 'len' => '9-12'],
					['area' => '+966', 'len' => '9-12'],
					['area' => '+974', 'len' => '8-12'],
					['area' => '+968', 'len' => '8-12'],
					['area' => '+965', 'len' => '8-12'],
					['area' => '+973', 'len' => '8-12'],
					['area' => '+65', 'len' => '8-12'],
					['area' => '+60', 'len' => '9-12'],
					['area' => '+66', 'len' => '9-12'],
					['area' => '+62', 'len' => '9-13'],
					['area' => '+84', 'len' => '9-11'],
					['area' => '+63', 'len' => '10-12'],
					['area' => '+61', 'len' => '9-11'],
					['area' => '+64', 'len' => '8-11'],
					['area' => '+49', 'len' => '10-13'],
					['area' => '+33', 'len' => '9-11'],
					['area' => '+39', 'len' => '10-12'],
					['area' => '+34', 'len' => '9-11'],
					['area' => '+7', 'len' => '10-12'],
					['area' => '+55', 'len' => '10-13'],
					['area' => '+27', 'len' => '9-11'],
					['area' => '+852', 'len' => '8-11'],
					['area' => '+853', 'len' => '8-11'],
					['area' => '+886', 'len' => '9-11'],
					['area' => '+81', 'len' => '10-12'],
					['area' => '+82', 'len' => '9-11']
				];
				$data['areaPhoneLenList'] = $countries;
			
				$data['registerSms'] = '0';
				$data['isOpenLoginChangeLanguage'] = '1';
				$data['rewardValidityTime'] = 30;
				$data['electronicWinRateExternalLink'] = '';
				$data['electronicWinRateImgUrl'] = 'https://ossimg.yuk87k786d.com/91club';
				$data['isShowElectronicWinRateExternalLink'] = false;
				$data['isShowAppHandCodeWashingSwitch'] = true;
				$data['isShowHotGameWinOdds'] = true;
				$data['ossUrl'] = 'https://ossimg.yuk87k786d.com';
				$data['bigTurntableLink'] = null;
				$data['telegramExternalLink'] = null;
				$data['isOpenActivityAward'] = false;
				$data['isOpenTurntable'] = true;
				$data['isPartnerReward'] = true;
				$data['isSelfCustomerService'] = true;
				$data['webSiteUrl'] = '/';
				$data['isOpenFacebookEvent'] = true;
				$data['isOpenRegisterPhoneFirstZeroSwitch'] = false;
				$data['eventRegionConfigList'] = null;
				$data['firstDepositRewardCodeAmount'] = "1";
				$data['isOpenAdjustEvent'] = false;
				
				$res['data'] = $data;
				$res['code'] = 0;
				$res['msg'] = 'Succeed';
				$res['msgCode'] = 0;
				http_response_code(200);
				echo json_encode($res);
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
