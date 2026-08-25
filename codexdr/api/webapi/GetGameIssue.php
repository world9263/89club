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
        $period = wingo_get_current_period($typeId);
        wingo_ensure_recent_results($firebase, $typeId, 5);
        $data['issueNumber'] = $period['periodId'];
        $data['startTime'] = $period['startTime'];
        $data['endTime'] = $period['endTime'];
        $data['serviceTime'] = $period['serviceTime'];
        $intervalMap = [1=>1, 2=>3, 3=>5, 4=>0.5];
        $data['intervalM'] = isset($intervalMap[$typeId]) ? $intervalMap[$typeId] : 1;
        
        echo json_encode([
            'code' => 0,
            'msg' => 'Succeed',
            'msgCode' => 0,
            'serviceNowTime' => date('Y-m-d H:i:s'),
            'data' => $data
        ]);
        exit;
    }
}
echo json_encode(['code' => -1, 'msg' => 'Invalid token']);
?>
