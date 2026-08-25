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
header('Vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");

$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

function generateUrl(string $fileName): string {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
    $scheme   = $isHttps ? 'https://' : 'http://';
    $host     = $_SERVER['HTTP_HOST'];
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $scheme . $host . ($basePath !== '/' ? $basePath : '') . '/' . ltrim($fileName, '/');
}

$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

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
                    
                    // Initialize Jili wallet session balance by transferring all money to wll_jili
                    $wll_jdb = isset($user['wll_jdb']) ? (float)$user['wll_jdb'] : 0.0;
                    $wll_jili = isset($user['wll_jili']) ? (float)$user['wll_jili'] : 0.0;
                    $motta = isset($user['motta']) ? (float)$user['motta'] : 0.0;
                    
                    $firebase->update('users/' . $mobile, [
                        'wll_jili' => $wll_jdb + $wll_jili + $motta,
                        'motta' => 0.0,
                        'wll_jdb' => 0.0
                    ]);

                    // Route to mock pages locally
                    if ($vendorCode == 'SPRIBE' || $vendorCode == 23 || strpos(strtolower($gameCode), 'aviator') !== false) {
                        $game = generateUrl("apifiles/mock_aviator.php") . "?userid=" . urlencode($mobile) . "&gameid=" . urlencode($gameCode);
                    } else {
                        $game = generateUrl("apifiles/mock_slots.php") . "?userid=" . urlencode($mobile) . "&gameid=" . urlencode($gameCode);
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
?>
