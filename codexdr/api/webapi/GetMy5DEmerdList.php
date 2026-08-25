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
                $fbTypeKey = 'd5_t' . $typeId;
                $allBetsPeriods = $firebase->get('game_bets/' . $fbTypeKey) ?: [];
                
                $userBets = [];
                foreach ($allBetsPeriods as $periodId => $bets) {
                    if (is_array($bets)) {
                        foreach ($bets as $betKey => $bet) {
                            if (isset($bet['userId']) && $bet['userId'] == $mobile) {
                                $bet['periodId'] = $periodId;
                                $userBets[] = $bet;
                            }
                        }
                    }
                }
                
                // Sort descending by createdAt
                usort($userBets, function($a, $b) {
                    return strcmp($b['createdAt'], $a['createdAt']);
                });
                
                $offset = ($pageNo - 1) * $pageSize;
                $sliced = array_slice($userBets, $offset, $pageSize);
                
                $list = [];
                $orderNumber = 1 + $offset;
                foreach ($sliced as $bet) {
                    $state = 2; // pending
                    if ($bet['status'] == 'win') $state = 1;
                    elseif ($bet['status'] == 'lose') $state = 0;
                    
                    $total = (float)$bet['totalAmount'];
                    $contract = (float)$bet['contractAmount'];
                    $fee = round($total - $contract, 2);
                    
                    $list[] = [
                        'orderNumber' => $orderNumber++,
                        'issueNumber' => (string)$bet['periodId'],
                        'amount' => $total,
                        'betCount' => (int)$bet['betCount'],
                        'realAmount' => $contract,
                        'fee' => $fee,
                        'number' => isset($bet['resultNumber']) ? (string)$bet['resultNumber'] : null,
                        'selectType' => (string)$bet['selectType'],
                        'state' => $state,
                        'profitAmount' => isset($bet['winAmount']) ? (float)$bet['winAmount'] : 0.0,
                        'premium' => isset($bet['premium']) ? (string)$bet['premium'] : null,
                        'gameType' => isset($bet['gameType']) ? (string)$bet['gameType'] : '1',
                        'addTime' => $bet['createdAt']
                    ];
                }
                
                $totalCount = count($userBets);
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
