<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

include "../../conn.php";
include "../../functions2.php";
include "../../wingo_helper.php";

global $firebase;

$shonupost = json_decode(file_get_contents('php://input'), true);

$authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
if (empty($authHeader)) {
    echo json_encode(['code' => -1, 'msg' => 'Unauthorized']);
    exit;
}
$bearer = explode(' ', $authHeader);
$author = isset($bearer[1]) ? $bearer[1] : '';
$is_jwt_valid = is_jwt_valid($author);
$data_auth = json_decode($is_jwt_valid, 1);
if($data_auth['status'] === 'Success') {
    $mobile = $data_auth['payload']['mobile'];
    $user = $firebase->get('users/' . $mobile);
    if($user != null) {
        $typeId = isset($shonupost['typeId']) ? $shonupost['typeId'] : 1;
        $pageNo = isset($shonupost['pageNo']) ? (int)$shonupost['pageNo'] : 1;
        $pageSize = isset($shonupost['pageSize']) ? (int)$shonupost['pageSize'] : 10;
        
        $allPeriods = $firebase->get('game_bets/wingo_t' . $typeId);
        $myBets = [];
        if($allPeriods) {
            foreach($allPeriods as $periodId => $bets) {
                foreach($bets as $betId => $bet) {
                    if($bet['userId'] == $mobile) {
                        $bet['issueNumber'] = $periodId;
                        $bet['id'] = $betId;
                        $myBets[] = $bet;
                    }
                }
            }
        }
        
        usort($myBets, function($a, $b) {
            return strtotime($b['createdAt']) - strtotime($a['createdAt']);
        });
        
        $totalCount = count($myBets);
        $totalPage = ceil($totalCount / $pageSize);
        $offset = ($pageNo - 1) * $pageSize;
        $pageData = array_slice($myBets, $offset, $pageSize);
        
        $formattedData = [];
        foreach($pageData as $row) {
            $state = 2; // pending
            if($row['status'] == 'win') $state = 1;
            else if($row['status'] == 'lose') $state = 0;
            
            $selectType = $row['selectType'];
            $mappedSelect = $selectType;
            if($selectType == 10) $mappedSelect = 'red';
            else if($selectType == 11) $mappedSelect = 'green';
            else if($selectType == 12) $mappedSelect = 'violet';
            else if($selectType == 13) $mappedSelect = 'big';
            else if($selectType == 14) $mappedSelect = 'small';
            
            $formattedData[] = [
                'orderNumber' => $row['id'],
                'issueNumber' => $row['issueNumber'],
                'amount' => $row['unitAmount'],
                'betCount' => $row['betCount'],
                'realAmount' => $row['contractAmount'],
                'fee' => $row['totalAmount'] - $row['contractAmount'],
                'number' => $row['resultNumber'] !== null ? $row['resultNumber'] : -1,
                'selectType' => $mappedSelect,
                'state' => $state,
                'profitAmount' => $row['winAmount'],
                'premium' => $row['premium'] !== null ? $row['premium'] : -1,
                'gameType' => 1,
                'addTime' => $row['createdAt']
            ];
        }
        
        echo json_encode([
            'code' => 0,
            'msg' => 'Succeed',
            'msgCode' => 0,
            'serviceNowTime' => time(),
            'data' => [
                'pageNo' => $pageNo,
                'pageSize' => $pageSize,
                'totalCount' => $totalCount,
                'totalPage' => $totalPage,
                'list' => $formattedData
            ]
        ]);
        exit;
    }
}
echo json_encode(['code' => -1, 'msg' => 'Invalid token']);
?>
