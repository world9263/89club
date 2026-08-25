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
        
        wingo_ensure_recent_results($firebase, $typeId, 50);
        $results = $firebase->get('game_results/wingo_t' . $typeId);
        $list = [];
        if($results) {
            foreach($results as $periodId => $res) {
                $res['issueNumber'] = $periodId;
                $list[] = $res;
            }
        }
        usort($list, function($a, $b) {
            return (int)$b['issueNumber'] - (int)$a['issueNumber'];
        });
        
        $totalCount = count($list);
        $totalPage = ceil($totalCount / $pageSize);
        $offset = ($pageNo - 1) * $pageSize;
        $pageData = array_slice($list, $offset, $pageSize);
        
        $formattedData = [];
        foreach($pageData as $row) {
            $formattedData[] = [
                'issueNumber' => $row['issueNumber'],
                'number' => $row['number'],
                'colour' => $row['colour'],
                'premium' => $row['premium']
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
