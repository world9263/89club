<?php 
	include "../../conn.php";
	include "../../functions2.php";
	global $firebase;
	// FIX: avoid open_basedir issue — include relative to this file
	@include __DIR__ . "/apifiles/apibaseurl.php";
	
	// ──────────────── CORS & Headers ────────────────
	header('Content-Type: application/json; charset=utf-8');
	header('Strict-Transport-Security: max-age=31536000');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
	header('Access-Control-Allow-Credentials: true');
	$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
	header('Access-Control-Allow-Origin: ' . $origin);
	header('Vary: Origin');
	
	// ──────────────── Timestamp & Default Response ────────────────
	date_default_timezone_set("Asia/Kolkata");
	$shnunc = date("Y-m-d H:i:s");
	$res = [
		'code' => 11,
		'msg' => 'Method not allowed',
		'msgCode' => 12,
		'serviceNowTime' => $shnunc,
	];

	// ─────────────── Decode input for GET or POST ───────────────
	$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
	if ($method === 'GET') {
	    $shonupost = $_GET;
	} else {
	    $shonubody = file_get_contents("php://input");
	    $shonupost = json_decode($shonubody, true) ?: [];
	}

	// ─────────────── URL helpers ───────────────
	function scheme_host() {
		$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
		$scheme = $https ? 'https://' : 'http://';
		$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
		return $scheme . $host;
	}
	function generateUrl($fileName) {
	    $base = scheme_host();
	    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
	    $parts = explode("/", trim($uri, "/"));
	    if (!empty($parts)) array_pop($parts);
	    $prefix = implode("/", $parts);
	    return rtrim($base . "/" . $prefix, "/") . "/" . ltrim($fileName, "/");
	}
	// Folders under same directory
	$jili      = generateUrl("apifiles/jili.php");
	$jdb       = generateUrl("apifiles/jdb.php");
	$jdbpro    = generateUrl("apifiles/jdbpro.php");
	$aio       = generateUrl("apifiles/aio.php");
	$evoslots  = generateUrl("apifiles/evoslots.php");
	$evo       = generateUrl("apifiles/evo.php");
	$cq9       = generateUrl("apifiles/cq9.php");
	$mt        = generateUrl("apifiles/mt.php");
	$bal       = generateUrl("apifiles/balance.php");
	$ninesgame = generateUrl("apifiles/ninesgame.php"); // local proxy if needed

	// If apibaseurl.php didn't set $apibaseurl OR it's empty, fall back to same-host routing
	if (empty($apibaseurl)) {
		// e.g., https://domain/path/apifiles/ (so that "{$apibaseurl}cq9?action=..." works)
		$apibaseurl = rtrim(generateUrl("apifiles/"), "/") . "/";
	}

	// ─────────────── Simple HTTP GET via cURL ───────────────
	function http_get($url) {
	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
	    $resp = curl_exec($ch);
	    curl_close($ch);
	    return $resp ?: '';
	}

	// ─────────────── Always attempt to process if params present ───────────────
	if (
	    isset($shonupost['language'], $shonupost['random'], $shonupost['signature'], $shonupost['timestamp'])
	) {
		$language  = $shonupost['language'];
		$random    = $shonupost['random'];
		$signature = $shonupost['signature'];
		// keep original signing structure
		$shonustr  = '{"language":'.$language.',"random":"'.$random.'"}';
		$shonusign = strtoupper(md5($shonustr));

		if ($shonusign === $signature) {
			$bearer       = explode(" ", $_SERVER['HTTP_AUTHORIZATION'] ?? '');
			$author       = $bearer[1] ?? '';
			$jwt_response = is_jwt_valid($author);
			$data_auth    = json_decode($jwt_response, true);

			if (!empty($data_auth['status']) && $data_auth['status'] === 'Success') {
					$mobile = $data_auth['payload']['mobile'];
					$user = $firebase->get('users/' . $mobile);
					if($user != null && isset($user['akshinak']) && $user['akshinak'] == $author){
					// fetch stored balances
					$uid       = (int)($data_auth['payload']['id'] ?? 0);
					$balarr    = ['motta' => isset($user['motta']) ? $user['motta'] : 0, 'wll_jdb' => isset($user['wll_jdb']) ? $user['wll_jdb'] : 0, 'wll_jili' => isset($user['wll_jili']) ? $user['wll_jili'] : 0];

					// CQ9 balance
					$cq9_url  = "{$apibaseurl}cq9?action=balance"
					          . "&userid={$uid}"
					          . "&callbackurl=" . urlencode($cq9)
					          . "&accessKey=allow";
					$cq9_data = json_decode(http_get($cq9_url), true) ?: [];
					$xd_cq9   = isset($cq9_data['balance']) ? (int)$cq9_data['balance'] : 0;

					// EVO (EDB) balance
					$evo_url    = "{$apibaseurl}evo?action=balance&userid={$uid}";
					$evo_data   = json_decode(http_get($evo_url), true) ?: [];
					$edbBalance = (!empty($evo_data['success']) ? floatval($evo_data['balance']) : 0);

					// AIO balance
					$aio_url    = "{$apibaseurl}aio?action=balance"
					           . "&callbackurl=" . urlencode($aio)
					           . "&userid={$uid}";
					$aio_data   = json_decode(http_get($aio_url), true) ?: [];
					$AIOBalance = (!empty($aio_data['success']) ? floatval($aio_data['balance']) : 0);

					// Spribe (JDB proxy) balance
					$spr_url     = "{$apibaseurl}spribe?action=balance"
					            . "&callbackurl=" . urlencode($jdbpro)
					            . "&userid={$uid}";
					$spr_data    = json_decode(http_get($spr_url), true) ?: [];
					$spribebal   = (!empty($spr_data['success']) ? floatval($spr_data['balance']) : 0);

					// EVO Slots balance
					$slot_url   = "{$apibaseurl}evoslots?action=balance"
					           . "&callbackurl=" . urlencode($evoslots)
					           . "&userid={$uid}";
					$slot_data  = json_decode(http_get($slot_url), true) ?: [];
					$slotBal    = (!empty($slot_data['success']) ? floatval($slot_data['balance']) : 0);

					// NinesGame (use http_get to ensure scheme)
					$ninesgame_url = "{$apibaseurl}ninesgame?action=balance&userid={$uid}";
					$ng_resp = http_get($ninesgame_url);
					$ninesgameBal = is_numeric(trim($ng_resp)) ? (float)trim($ng_resp) : 0;

					// All balance (separate var to avoid clobbering $spr_url)
					$allbal_url  = "{$bal}?user={$uid}";
					$allbal_data = json_decode(http_get($allbal_url), true) ?: [];
					$spribebalwith = (!empty($allbal_data['success']) ? floatval($allbal_data['balance']) : 0);
					// (use $spribebalwith if/where you actually need it)

					// assemble list
					$list = [];
					$list[] = ['vendorCode'=>'Lottery',    'balance'=> (int)$balarr['motta']];
					$list[] = ['vendorCode'=>'TB_Chess',   'balance'=> $AIOBalance];
					$list[] = ['vendorCode'=>'Wickets9',   'balance'=> 0];
					$list[] = ['vendorCode'=>'CQ9',        'balance'=> $xd_cq9];
					$list[] = ['vendorCode'=>'MG',         'balance'=> 0];
					$list[] = ['vendorCode'=>'JDB',        'balance'=> $spribebal];
					$list[] = ['vendorCode'=>'DG',         'balance'=> $ninesgameBal];
					$list[] = ['vendorCode'=>'CMD',        'balance'=> 0];
					$list[] = ['vendorCode'=>'SaBa',       'balance'=> 0];
					$list[] = ['vendorCode'=>'EVO_Video',  'balance'=> $edbBalance];
					$list[] = ['vendorCode'=>'JILI',       'balance'=> (int)$balarr['wll_jili']];
					$list[] = ['vendorCode'=>'Card365',    'balance'=> 0];
					$list[] = ['vendorCode'=>'V8Card',     'balance'=> 0];
					$list[] = ['vendorCode'=>'AG_Video',   'balance'=> 0];
					$list[] = ['vendorCode'=>'PG',         'balance'=> 0];
					$list[] = ['vendorCode'=>'TB',         'balance'=> 0];
					$list[] = ['vendorCode'=>'WM_Video',   'balance'=> 0];
					$list[] = ['vendorCode'=>'SEXY_Video', 'balance'=> 0];
					$list[] = ['vendorCode'=>'EVO_Slots',  'balance'=> $slotBal];

					$data = [
						'thidGameBalanceList' => $list,
						'totalWithdraw'       => 0,
						'totalRecharge'       => 0
					];

					$res = [
						'code'            => 0,
						'msg'             => 'Succeed',
						'msgCode'         => 0,
						'serviceNowTime'  => $shnunc,
						'data'            => $data
					];
					http_response_code(200);
					echo json_encode($res);
					exit;
				}
				// session row missing
				http_response_code(401);
				$res['code']     = 4;
				$res['msg']      = 'No operation permission';
				$res['msgCode']  = 2;
				echo json_encode($res);
				exit;
			}
			// invalid JWT
			http_response_code(401);
			$res['code']     = 4;
			$res['msg']      = 'No operation permission';
			$res['msgCode']  = 2;
			echo json_encode($res);
			exit;
		}
		// wrong signature
		http_response_code(200);
		$res['code']     = 5;
		$res['msg']      = 'Wrong signature';
		$res['msgCode']  = 3;
		echo json_encode($res);
		exit;
	}

	// missing params
	http_response_code(200);
	$res['code']     = 7;
	$res['msg']      = 'Param is Invalid';
	$res['msgCode']  = 6; 
	echo json_encode($res);
	exit;
