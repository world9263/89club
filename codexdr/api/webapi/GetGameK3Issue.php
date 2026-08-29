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
$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

$typeId = isset($shonupost['typeId']) ? (int)$shonupost['typeId'] : 9;
$period = k3_get_current_period($typeId);

$data = [
    'issueNumber' => (string)$period['periodId'],
    'startTime' => $period['startTime'],
    'endTime' => $period['endTime'],
    'serviceTime' => $period['serviceTime'],
    'intervalM' => ($typeId == 9) ? 1 : (($typeId == 10) ? 3 : (($typeId == 11) ? 5 : 10))
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
exit;
?>
