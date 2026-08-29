<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

include "../../conn.php";
include "../../functions2.php";
include "../../wingo_helper.php";

global $firebase;

$shonupost = json_decode(file_get_contents('php://input'), true);
$typeId = isset($shonupost['typeId']) ? (int)$shonupost['typeId'] : 1;

$period = wingo_get_current_period($typeId);
$data['issueNumber'] = (string)$period['periodId'];
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
?>
