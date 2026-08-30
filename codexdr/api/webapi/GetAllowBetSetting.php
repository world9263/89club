<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

include "../../conn.php";
include "../../functions2.php";
include "../../wingo_helper.php";

global $firebase;

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
        
        // Count successful deposits from Firebase
        $deposits = $firebase->get('deposits');
        $userRechargeTimes = 0;
        $userRechargeAmount = 0.0;
        
        if ($deposits) {
            foreach ($deposits as $dep) {
                $is_user_dep = isset($dep['userId']) && (
                    $dep['userId'] == $mobile || 
                    $dep['userId'] == '91' . $mobile || 
                    $dep['userId'] == '880' . $mobile ||
                    $dep['userId'] == '+91' . $mobile || 
                    $dep['userId'] == '+880' . $mobile
                );
                $is_success = isset($dep['status']) && ($dep['status'] === 'success' || $dep['status'] === 'request success');
                
                if ($is_user_dep && $is_success) {
                    $userRechargeTimes++;
                    $userRechargeAmount += (float)($dep['amount'] ?? 0.0);
                }
            }
        }
        
        $canDirectToGame = ($userRechargeTimes > 0);
        
        echo json_encode([
            'code' => 0,
            'msg' => 'Succeed',
            'msgCode' => 0,
            'serviceNowTime' => date('Y-m-d H:i:s'),
            'data' => [
                'allowNoRechargeGame' => '0',
                'lowestRechargeAmountToGame' => 0,
                'userRechargeTimes' => $userRechargeTimes,
                'userRechargeAmount' => $userRechargeAmount,
                'canDirectToGame' => $canDirectToGame
            ]
        ]);
        exit;
    }
}
echo json_encode(['code' => -1, 'msg' => 'Invalid token']);
?>
