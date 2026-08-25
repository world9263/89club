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
        $amount = $shonupost['amount'];
        $betCount = $shonupost['betCount'];
        $issuenumber = $shonupost['issuenumber'];
        $selectType = $shonupost['selectType'];
        $typeId = $shonupost['typeId'];
        
        $period = wingo_get_current_period($typeId);
        if ($period['periodId'] != $issuenumber) {
            echo json_encode(['code' => -1, 'msg' => 'Period mismatch']);
            exit;
        }
        
        $totalAmount = $amount * $betCount;
        $contractAmount = $totalAmount * 0.98;
        
        $userBalance = isset($user['motta']) ? (float)$user['motta'] : 0;
        if ($userBalance < $totalAmount) {
            echo json_encode(['code' => -1, 'msg' => 'Insufficient balance']);
            exit;
        }
        
        $user['motta'] = $userBalance - $totalAmount;
        $currentTotalBet = isset($user['total_bet']) ? (float)$user['total_bet'] : 0.0;
        $firebase->update('users/' . $mobile, [
            'motta' => $user['motta'],
            'total_bet' => $currentTotalBet + $totalAmount
        ]);
        
        $betData = [
            'userId' => $mobile,
            'selectType' => (int)$selectType,
            'unitAmount' => (float)$amount,
            'betCount' => (int)$betCount,
            'totalAmount' => (float)$totalAmount,
            'contractAmount' => (float)$contractAmount,
            'status' => 'pending',
            'resultNumber' => null,
            'premium' => null,
            'winAmount' => 0,
            'createdAt' => date('Y-m-d H:i:s')
        ];
        
        $push_id = uniqid();
        $typeIdMapped = ($typeId == 4) ? 5 : $typeId;
        $firebase->set('game_bets/wingo_t' . $typeIdMapped . '/' . $issuenumber . '/' . $push_id, $betData);
        
        echo json_encode([
            'code' => 0,
            'msg' => 'Succeed',
            'msgCode' => 0,
            'serviceNowTime' => date('Y-m-d H:i:s'),
            'data' => []
        ]);
        exit;
    }
}
echo json_encode(['code' => -1, 'msg' => 'Invalid token']);
?>
