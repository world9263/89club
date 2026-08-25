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
include "../../k3_helper.php";
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
                // Determine K3 typeId from issueNumber
                $typeId = 9;
                if (strpos($issueNumber, '10102') !== false) $typeId = 10;
                elseif (strpos($issueNumber, '10103') !== false) $typeId = 11;
                elseif (strpos($issueNumber, '12') !== false) $typeId = 12;
                
                $fbTypeKey = 'k3_t' . $typeId;
                
                // Ensure recent results are generated and settled
                k3_ensure_recent_results($firebase, $typeId, 5);
                
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
                    
                    $data = [
                        'issueNumber' => (string)$issueNumber,
                        'number' => isset($result['sumCount']) ? (int)$result['sumCount'] : 0,
                        'winAmount' => $winAmount,
                        'typeName' => (string)$typeId,
                        'state' => $state,
                        'premium' => isset($result['premium']) ? (string)$result['premium'] : ''
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
