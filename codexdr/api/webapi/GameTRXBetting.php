<?php
header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('vary: Origin');

include "../../conn.php";
include "../../functions2.php";
include "../../trx_helper.php";
global $firebase;

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
    if (isset($shonupost['amount']) && isset($shonupost['betCount']) && isset($shonupost['gameType']) && isset($shonupost['issuenumber']) && isset($shonupost['selectType']) && isset($shonupost['typeId'])) {
        $amount = (float)$shonupost['amount'];
        $betCount = (int)$shonupost['betCount'];
        $gameType = $shonupost['gameType'];
        $issuenumber = $shonupost['issuenumber'];
        $selectType = $shonupost['selectType'];
        $typeId = (int)$shonupost['typeId'];
        
        $authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
        if (empty($authHeader)) {
            $res['code'] = 4;
            $res['msg'] = 'Token missing';
            http_response_code(401);
            echo json_encode($res);
            exit;
        }
        
        $bearer = explode(" ", $authHeader);
        $author = isset($bearer[1]) ? $bearer[1] : '';
        $is_jwt_valid = is_jwt_valid($author);
        $data_auth = json_decode($is_jwt_valid, 1);
        
        if ($data_auth['status'] === 'Success') {
            $mobile = $data_auth['payload']['mobile'];
            $user = $firebase->get('users/' . $mobile);
            
            if ($user != null) {
                // Check if target period is current period
                $currentPeriod = trx_get_current_period($typeId);
                if ($currentPeriod['periodId'] != $issuenumber) {
                    $res['code'] = 1;
                    $res['msg'] = 'The current period is settled';
                    $res['msgCode'] = 404;
                    http_response_code(200);
                    echo json_encode($res);
                    exit;
                }
                
                // Calculate total amount
                $stplode = explode(",", $selectType);
                $stcnt = count($stplode);
                $totalamount = $amount * $betCount * $stcnt;
                
                $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0;
                if ($currentBalance >= $totalamount) {
                    $newBalance = round($currentBalance - $totalamount, 2);
                    $firebase->update('users/' . $mobile, ['motta' => $newBalance]);
                    
                    $contractAmount = $totalamount * 0.98;
                    $fbTypeKey = 'trx_t' . $typeId;
                    
                    $betData = [
                        'userId' => $mobile,
                        'selectType' => $selectType,
                        'unitAmount' => $amount,
                        'betCount' => $betCount,
                        'totalAmount' => $totalamount,
                        'contractAmount' => $contractAmount,
                        'status' => 'pending',
                        'resultNumber' => null,
                        'premium' => null,
                        'winAmount' => 0,
                        'gameType' => $gameType,
                        'createdAt' => date('Y-m-d H:i:s')
                    ];
                    
                    $firebase->push('game_bets/' . $fbTypeKey . '/' . $issuenumber, $betData);
                    
                    $res = [
                        'code' => 0,
                        'msg' => 'Succeed',
                        'msgCode' => 0,
                        'serviceNowTime' => $shnunc,
                        'data' => null
                    ];
                    http_response_code(200);
                    echo json_encode($res);
                } else {
                    $res['code'] = 1;
                    $res['msg'] = 'Balance is not enough';
                    $res['msgCode'] = 142;
                    http_response_code(200);
                    echo json_encode($res);
                }
            } else {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                http_response_code(401);
                echo json_encode($res);
            }
        } else {
            $res['code'] = 4;
            $res['msg'] = 'No operation permission';
            http_response_code(401);
            echo json_encode($res);
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        http_response_code(200);
        echo json_encode($res);
    }
} else {
    http_response_code(405);
    echo json_encode($res);
}
?>
