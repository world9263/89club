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
include "../../d5_helper.php";
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
    if (isset($shonupost['issueNumber'])) {
        $issueNumber = (string)$shonupost['issueNumber'];
        
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
                // Determine 5D typeId from separator
                $typeId = 5;
                if (strpos($issueNumber, '10202') !== false) $typeId = 6;
                elseif (strpos($issueNumber, '10203') !== false) $typeId = 7;
                elseif (strpos($issueNumber, '10204') !== false) $typeId = 8;
                
                $fbTypeKey = 'd5_t' . $typeId;
                
                // Ensure recent results are generated and settled
                d5_ensure_recent_results($firebase, $typeId, 5);
                
                $result = $firebase->get('game_results/' . $fbTypeKey . '/' . $issueNumber);
                if ($result != null) {
                    $bets = $firebase->get('game_bets/' . $fbTypeKey . '/' . $issueNumber) ?: [];
                    $winAmount = 0;
                    $state = 0;
                    
                    foreach ($bets as $bet) {
                        if (isset($bet['userId']) && $bet['userId'] == $mobile) {
                            $winAmount += (float)$bet['winAmount'];
                            if ($bet['status'] == 'win') {
                                $state = 1;
                            }
                        }
                    }
                    
                    $typeNames = [5 => '5D 1 Minute', 6 => '5D 3 Minute', 7 => '5D 5 Minute', 8 => '5D 10 Minute'];
                    
                    $data = [
                        'issueNumber' => (string)$issueNumber,
                        'typeID' => $typeId,
                        'typeName' => isset($typeNames[$typeId]) ? $typeNames[$typeId] : '5D Game',
                        'state' => $state,
                        'winAmount' => $winAmount,
                        'premium' => isset($result['premium']) ? (string)$result['premium'] : '',
                        'sumCount' => isset($result['sumCount']) ? (int)$result['sumCount'] : 0
                    ];
                    
                    $res = [
                        'code' => 0,
                        'msg' => 'Succeed',
                        'msgCode' => 0,
                        'serviceNowTime' => $shnunc,
                        'data' => $data
                    ];
                } else {
                    $res = [
                        'code' => 1,
                        'msg' => 'Result not found',
                        'msgCode' => 0,
                        'serviceNowTime' => $shnunc,
                        'data' => null
                    ];
                }
                http_response_code(200);
                echo json_encode($res);
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
