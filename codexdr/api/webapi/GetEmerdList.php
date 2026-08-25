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
        
        $recent100 = array_slice($list, 0, 100);
        $stats = [
            ['title' => 'Average Missing', 'val0' => rand(5,25), 'val1' => rand(5,25), 'val2' => rand(5,25), 'val3' => rand(5,25), 'val4' => rand(5,25), 'val5' => rand(5,25), 'val6' => rand(5,25), 'val7' => rand(5,25), 'val8' => rand(5,25), 'val9' => rand(5,25)],
            ['title' => 'Frequency', 'val0' => 0, 'val1' => 0, 'val2' => 0, 'val3' => 0, 'val4' => 0, 'val5' => 0, 'val6' => 0, 'val7' => 0, 'val8' => 0, 'val9' => 0],
            ['title' => 'Max Continued', 'val0' => 1, 'val1' => 1, 'val2' => 1, 'val3' => 1, 'val4' => 1, 'val5' => 1, 'val6' => 1, 'val7' => 1, 'val8' => 1, 'val9' => 1],
            ['title' => 'Missing', 'val0' => 0, 'val1' => 0, 'val2' => 0, 'val3' => 0, 'val4' => 0, 'val5' => 0, 'val6' => 0, 'val7' => 0, 'val8' => 0, 'val9' => 0],
            ['title' => 'Interval Number', 'val0' => rand(5,25), 'val1' => rand(5,25), 'val2' => rand(5,25), 'val3' => rand(5,25), 'val4' => rand(5,25), 'val5' => rand(5,25), 'val6' => rand(5,25), 'val7' => rand(5,25), 'val8' => rand(5,25), 'val9' => rand(5,25)]
        ];
        
        foreach($recent100 as $idx => $r) {
            $num = $r['number'];
            $stats[1]['val'.$num]++;
        }
        
        echo json_encode([
            'code' => 0,
            'msg' => 'Succeed',
            'msgCode' => 0,
            'serviceNowTime' => time(),
            'data' => $stats
        ]);
        exit;
    }
}
echo json_encode(['code' => -1, 'msg' => 'Invalid token']);
?>
