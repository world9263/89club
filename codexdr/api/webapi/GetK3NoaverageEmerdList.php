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
    if (isset($shonupost['typeId']) && isset($shonupost['pageNo']) && isset($shonupost['pageSize'])) {
        $typeId = (int)$shonupost['typeId'];
        $pageNo = (int)$shonupost['pageNo'];
        $pageSize = (int)$shonupost['pageSize'];
        
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
                // Ensure recent results are present (capped at 10)
                k3_ensure_recent_results($firebase, $typeId, 10);
                
                $fbTypeKey = 'k3_t' . $typeId;
                $allResults = $firebase->get('game_results/' . $fbTypeKey) ?: [];
                
                // Sort descending by periodId
                krsort($allResults);
                
                $offset = ($pageNo - 1) * $pageSize;
                $sliced = array_slice($allResults, $offset, $pageSize, true);
                
                $list = [];
                foreach ($sliced as $pId => $row) {
                    $list[] = [
                        'issueNumber' => (string)$pId,
                        'gameType' => isset($row['gameType']) ? (int)$row['gameType'] : 0,
                        'sumCount' => isset($row['sumCount']) ? (int)$row['sumCount'] : 0,
                        'premium' => isset($row['premium']) ? (string)$row['premium'] : ''
                    ];
                }
                
                $totalCount = count($allResults);
                $totalPage = ceil($totalCount / $pageSize);
                
                $data = [
                    'list' => empty($list) ? null : $list,
                    'pageNo' => $pageNo,
                    'totalPage' => $totalPage,
                    'totalCount' => $totalCount
                ];
                
                $res = [
                    'code' => 0,
                    'msg' => 'Succeed',
                    'msgCode' => 0,
                    'serviceNowTime' => $shnunc,
                    'data' => $data
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
