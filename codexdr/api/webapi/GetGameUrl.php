<?php
include "../../conn.php";
include "../../functions2.php";
global $firebase;
include "apifiles/apibaseurl.php";

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");

$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

$playwinGameCodes = [
    "92b24e4c25107367a80e0fe1a97c24e4",
];

function generateUrl(string $fileName): string {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
    $scheme   = $isHttps ? 'https://' : 'http://';
    $host     = $_SERVER['HTTP_HOST'];
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $scheme . $host . ($basePath !== '/' ? $basePath : '') . '/' . ltrim($fileName, '/');
}

$jili      = generateUrl('apifiles/jili.php');
$jdb       = generateUrl('apifiles/jdbpro.php');
$spribe    = generateUrl('apifiles/jdbpro.php');
$aio       = generateUrl('apifiles/aio.php');
$evoslots  = generateUrl('apifiles/evoslots.php');
$evo       = generateUrl('apifiles/evo.php');
$cq9       = generateUrl('apifiles/cq9.php');
$mt        = generateUrl('apifiles/mt.php');
$bal       = generateUrl('apifiles/balance.php');
$chicken   = generateUrl('apifiles/inout.php');
$ninesgame = generateUrl('apifiles/ninesgame.php');

$vendors = [
    18               => ['ep'=>'jili','pre'=>'','upd'=>'jili'],
    'JILI'           => ['ep'=>'jili','pre'=>'','upd'=>'jili'],
    23               => ['ep'=>'spribe','pre'=>'50_','upd'=>null],
    'JDB'            => ['ep'=>'spribe','pre'=>'50_','upd'=>null],
    'SPRIBE'         => ['ep'=>'spribe','pre'=>'22_','upd'=>null],
    'AIO'            => ['ep'=>'aio','pre'=>'22_','upd'=>null],
    'EVO_Electronic' => ['ep'=>'evoslots','pre'=>'','upd'=>null],
    'EVO_Video'      => ['ep'=>'evo','pre'=>'','upd'=>null],
    'CQ9'            => ['ep'=>'cq9','pre'=>'','upd'=>null],
    'MT'             => ['ep'=>'cq9','pre'=>'','upd'=>null],
    'G9'             => ['ep'=>'ninesgame','pre'=>'','upd'=>null],
];

$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

function launchPlaywinGame($userId, $walletBalance, $providerId, $token, $secret) {
    $timestamp = time() * 1000;
    $requestData = [
        "user_id" => $userId,
        "wallet_amount" => $walletBalance,
        "game_uid" => $providerId,
        "token" => $token,
        "timestamp" => $timestamp,
    ];

    $url = 'https://playwin6.com/encryptedNow?' . http_build_query(array_merge($requestData, ['client_secret'=>$secret]));
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $gameUrl = "https://playwin6.com/launchGame?user_id=" . urlencode($userId) .
               "&wallet_amount=" . urlencode($walletBalance) .
               "&game_uid=" . urlencode($providerId) .
               "&token=" . urlencode($token) .
               "&timestamp=" . urlencode($timestamp) .
               "&payload=" . urlencode($data['encrypted'] ?? '');
    return $gameUrl;
}

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (
        isset($shonupost['language']) &&
        isset($shonupost['random']) &&
        isset($shonupost['signature']) &&
        isset($shonupost['timestamp'])
    ) {
        $language = $shonupost['language'];
        $random = $shonupost['random'];
        $signature = $shonupost['signature'];
        $vendorCode = $shonupost['vendorCode'];
        $gameCode = $shonupost['gameCode'];
        $phonetype = $shonupost['phonetype'];

        $shonustr = '{"gameCode":"' . $gameCode . '","language":' . $language . ',"phonetype":' . $phonetype . ',"random":"' . $random . '","vendorCode":' . $vendorCode . '}';
        $shonusign = strtoupper(md5($shonustr));

        if ($shonusign != $signature) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION'] ?? '');
            $author = $bearer[1] ?? '';

            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, true);

            if ($data_auth['status'] === 'Success') {
                $mobile = $data_auth['payload']['mobile'];
                $user = $firebase->get('users/' . $mobile);

                if ($user != null) {
                    $uid = $mobile;
                    $amt = isset($user['motta']) ? (float)$user['motta'] : 0.0;
                    $kramasankhye = isset($user['codechorkamukala']) ? $user['codechorkamukala'] : 'Member' . $mobile;

                    if ($vendorCode == 18 || $vendorCode == "JILI" || $gameCode == 'vip_ak_cricket_sabasports') {
                        $gameLaunchUrl = "https://apisrental.zoh6n-fahydcide.in/?post&gameId=".$gameCode."&code=laxmiexchclubxxyashucltxxx290828&mobile=".$mobile."&agentId=laxmiexchclubxxyashucltxxx290828_seamless&agentKey=laxmiexchclubxxyashucltxxx290828gnion9bi6734bfo8fgyvb&referrerUrl=https://laxmiexch.club/";
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gameLaunchUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
                        $response = curl_exec($ch);
                        if(curl_errno($ch)) {
                            exit;
                        }
                        curl_close($ch);
                        $responseData = json_decode($response, true);
                        if(isset($responseData['gameurl']) && !empty($responseData['gameurl'])) {
                            $res['code'] = 0;
                            $res['msg'] = 'Game launched successfully';
                            $res['data'] = [
                                'url' => "https://laxmiexch.club/apigames.php?game=live&type=api&quality=hd&token=hn0382cn34tpnyt58ny534tn8yt4ny2b7t0xdb1s2br61sdxr6b4d13dx3892cdn9t234nt70325d7tbd23btr34sd12b0rt834sd1280rb67t9324sd&apigames=".$responseData['gameurl']
                            ];
                            http_response_code(200);
                        } else {
                            $res['code'] = 0;
                            $res['msg'] = 'Failed to open game - some error try again later';
                            $res['msgCode'] = 3;
                            $res['et'] = $response;
                            http_response_code(200);
                        }
                        echo json_encode($res);
                        die();
                    }

                    if (in_array($gameCode, $playwinGameCodes)) {
                        $token = "4fd14d9d-e4b7-4fdf-9c36-3ef4ad0c2701";     
                        $secret = "e0497c2aafaa5a3c77202492544b8956";   
                        $game = launchPlaywinGame($uid, $amt, $gameCode, $token, $secret);
                    } else if (isset($vendors[$vendorCode])) {
                        $c = $vendors[$vendorCode];
                        $callback = ${strtolower($c['ep'])};
                        
                        if ($c['upd'] === 'jili') {
                            $wll_jdb = isset($user['wll_jdb']) ? (float)$user['wll_jdb'] : 0.0;
                            $wll_jili = isset($user['wll_jili']) ? (float)$user['wll_jili'] : 0.0;
                            $motta = isset($user['motta']) ? (float)$user['motta'] : 0.0;
                            
                            $firebase->update('users/' . $mobile, [
                                'wll_jili' => $wll_jdb + $wll_jili + $motta,
                                'motta' => 0.0,
                                'wll_jdb' => 0.0
                            ]);
                        }
                        
                        $game = fetchEffectiveUrl(
                            "{$apibaseurl}{$c['ep']}?userid={$mobile}&gameid={$c['pre']}{$gameCode}&callbackurl={$callback}"
                        );
                    } else {
                        $game = "{$apibaseurl}inout?userid=$mobile&gameid=$gameCode&callbackurl=$chicken";
                    }

                    $res['data'] = ["url"=>$game, "returnType"=>1];
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    http_response_code(200);
                    echo json_encode($res);
                    exit;
                } else {
                    $res['code'] = 4;
                    $res['msg'] = 'No operation permission';
                    $res['msgCode'] = 2;
                    http_response_code(401);
                    echo json_encode($res);
                    exit;
                }
            } else {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
                exit;
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);
            exit;
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode($res);
    exit;
}

function fetchEffectiveUrl(string $url): string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 1,
    ]);
    curl_exec($ch);
    $final = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return $final;
}
?>
