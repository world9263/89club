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
        wingo_ensure_recent_results($firebase, $typeId, 5);
        $typeIdMapped = ($typeId == 4) ? 5 : $typeId;
        $results = $firebase->get('game_results/wingo_t' . $typeIdMapped);
        
        $list = [];
        if($results) {
            foreach($results as $periodId => $res) {
                $res['issueNumber'] = $periodId;
                $list[] = $res;
            }
        }
        usort($list, function($a, $b) {
            return strcmp($b['issueNumber'], $a['issueNumber']);
        });
        
        $last5 = array_slice($list, 0, 5);
        $numbers = array_map(function($r) { return $r['number']; }, $last5);
        $resStr = implode(",", $numbers);
        
        echo json_encode([
            'code' => 0,
            'msg' => 'Succeed',
            'msgCode' => 0,
            'serviceNowTime' => date('Y-m-d H:i:s'),
            'data' => [
                'number' => $resStr
            ]
        ]);
        exit;
    }
}
echo json_encode(['code' => -1, 'msg' => 'Invalid token']);
?>
