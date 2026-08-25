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
    if (isset($shonupost['issueNumber'])) {
        $issueNumbers = $shonupost['issueNumber'];
        if (!is_array($issueNumbers)) {
            $issueNumbers = [$issueNumbers];
        }
        
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
                $list = [];
                
                foreach ($issueNumbers as $issueNumber) {
                    // Determine game typeId from separator string
                    $typeId = 13;
                    if (strpos($issueNumber, '10302') !== false) $typeId = 14;
                    elseif (strpos($issueNumber, '10303') !== false) $typeId = 15;
                    elseif (strpos($issueNumber, '10304') !== false) $typeId = 16;
                    
                    $fbTypeKey = 'trx_t' . $typeId;
                    
                    // Ensure recent results are generated and settled
                    trx_ensure_recent_results($firebase, $typeId, 5);
                    
                    // Fetch result
                    $result = $firebase->get('game_results/' . $fbTypeKey . '/' . $issueNumber);
                    if ($result != null) {
                        // Fetch user bets for this period
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
                        
                        $typeNames = [13 => 'TRX 1 Minute', 14 => 'TRX 3 Minute', 15 => 'TRX 5 Minute', 16 => 'TRX 10 Minute'];
                        
                        $list[] = [
                            'issueNumber' => (string)$issueNumber,
                            'number' => isset($result['number']) ? (int)$result['number'] : 0,
                            'colour' => isset($result['color']) ? (string)$result['color'] : '',
                            'winAmount' => $winAmount,
                            'typeName' => isset($typeNames[$typeId]) ? $typeNames[$typeId] : 'TRX Game',
                            'state' => $state
                        ];
                    }
                }
                
                $res = [
                    'code' => 0,
                    'msg' => 'Succeed',
                    'msgCode' => 0,
                    'serviceNowTime' => $shnunc,
                    'data' => empty($list) ? null : $list
                ];
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
