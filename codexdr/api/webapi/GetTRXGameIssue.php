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
include "../../trx_helper.php";
global $firebase;

$shnunc = date("Y-m-d H:i:s");
$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

$typeId = isset($shonupost['typeId']) ? (int)$shonupost['typeId'] : 13;

trx_ensure_recent_results($firebase, $typeId, 1);
$period = trx_get_current_period($typeId);

$fbTypeKey = 'trx_t' . $typeId;
$allResults = $firebase->get('game_results/' . $fbTypeKey) ?: [];
$latest = null;
if (!empty($allResults)) {
    krsort($allResults);
    $latest = reset($allResults);
}

$predraw = [
    'issueNumber' => (string)$period['periodId'],
    'startTime' => $period['startTime'],
    'endTime' => $period['endTime'],
    'serviceTime' => $period['serviceTime'],
    'intervalM' => ($typeId == 13) ? 1 : (($typeId == 14) ? 3 : (($typeId == 15) ? 5 : 10))
];

$settled = [
    'issueNumber' => $latest ? (string)$latest['periodId'] : "",
    'sumCount' => null,
    'premium' => 1,
    'blockID' => $latest ? (string)$latest['blockID'] : "",
    'number' => $latest ? (int)$latest['blockNumber'] : 0
];

$data = [
    'predraw' => $predraw,
    'settled' => $settled
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
