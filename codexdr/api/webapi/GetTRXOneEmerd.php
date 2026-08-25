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
    if (isset($shonupost['typeId'])) {
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
                // Ensure recent results are present (generate if missing)
                $results = trx_ensure_recent_results($firebase, $typeId, 1);
                
                $fbTypeKey = 'trx_t' . $typeId;
                $latest = null;
                
                if (!empty($results)) {
                    $latest = $results[0];
                } else {
                    $allResults = $firebase->get('game_results/' . $fbTypeKey) ?: [];
                    if (!empty($allResults)) {
                        krsort($allResults);
                        $latest = reset($allResults);
                    }
                }
                
                if ($latest != null) {
                    $data = [
                        'issueNumber' => (string)$latest['periodId'],
                        'number' => isset($latest['number']) ? (int)$latest['number'] : 0,
                        'colour' => isset($latest['color']) ? (string)$latest['color'] : '',
                        'premium' => isset($latest['premium']) ? (string)$latest['premium'] : '',
                        'blockID' => isset($latest['blockID']) ? (string)$latest['blockID'] : '',
                        'blockNumber' => isset($latest['blockNumber']) ? (int)$latest['blockNumber'] : 0,
                        'blockTime' => isset($latest['blockTime']) ? (string)$latest['blockTime'] : ''
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
                        'msg' => 'No results found',
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
