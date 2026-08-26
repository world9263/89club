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
    $scheme   = 'https://';
    $host     = $_SERVER['HTTP_HOST'];
    return $scheme . $host . '/' . ltrim($fileName, '/');
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
                    
                    // Check if user has deposited
                    $hasDeposited = false;
                    if (isset($user['has_deposited']) && $user['has_deposited'] == true) {
                        $hasDeposited = true;
                    } else if (isset($user['total_deposit']) && (float)$user['total_deposit'] > 0) {
                        $hasDeposited = true;
                        $firebase->update('users/' . $mobile, ['has_deposited' => true]);
                    } else {
                        // Check if there is any approved deposit for this user in Firebase
                        $allDeposits = $firebase->get('deposits');
                        if ($allDeposits) {
                            foreach ($allDeposits as $dep) {
                                $status = strtolower($dep['status'] ?? '');
                                if (isset($dep['userId']) && $dep['userId'] == $mobile && ($status == 'approved' || $status == 'success')) {
                                    $hasDeposited = true;
                                    // Cache this on the user profile so next check is instant
                                    $firebase->update('users/' . $mobile, ['has_deposited' => true]);
                                    break;
                                }
                            }
                        }
                    }

                    if (!$hasDeposited) {
                        // Redirect to the recharge page
                        $scheme = 'https://';
                        $host = $_SERVER['HTTP_HOST'];
                        $game = $scheme . $host . '/#/wallet/Recharge';
                        
                        $res['data'] = ["url"=>$game, "returnType"=>1];
                        $res['code'] = 0;
                        $res['msg'] = 'Succeed';
                        $res['msgCode'] = 0;
                        http_response_code(200);
                        echo json_encode($res);
                        exit;
                    }

                    // Keep players balance inside the central motta key so home screen and game screen balance are identical.
                    if ($vendorCode == 'SPRIBE' || $vendorCode == 23 || $gameCode == 22001 || $gameCode == '22001' || strpos(strtolower($gameCode), 'aviator') !== false) {
                        $game = generateUrl("mock_aviator.php") . "?userid=" . urlencode($mobile) . "&gameid=" . urlencode($gameCode);
                    } else {
                        $game = generateUrl("mock_slots.php") . "?userid=" . urlencode($mobile) . "&gameid=" . urlencode($gameCode);
                    }

                    $res['data'] = ["url"=>$game, "returnType"=>1];
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    http_response_code(200);
                    echo json_encode($res);
                    exit;
                } else {
                    // Redirect to registration if user not found
                    $scheme = 'https://';
                    $host = $_SERVER['HTTP_HOST'];
                    $game = $scheme . $host . '/#/register';
                    $res['data'] = ["url"=>$game, "returnType"=>1];
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    http_response_code(200);
                    echo json_encode($res);
                    exit;
                }
            } else {
                // Redirect to registration if token invalid
                $scheme = 'https://';
                $host = $_SERVER['HTTP_HOST'];
                $game = $scheme . $host . '/#/register';
                $res['data'] = ["url"=>$game, "returnType"=>1];
                $res['code'] = 0;
                $res['msg'] = 'Succeed';
                $res['msgCode'] = 0;
                http_response_code(200);
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
