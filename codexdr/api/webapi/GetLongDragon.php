<?php 
include "../../conn.php";
include "../../functions2.php";
include "../../wingo_helper.php";
include "../../d5_helper.php";
include "../../k3_helper.php";
global $firebase;

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");

// Calculate current period info using Firebase helpers
$period5D = d5_get_current_period(5);
$periodK3 = k3_get_current_period(9);
$periodWingo = wingo_get_current_period(1);

$data5D = [
    'lotteryGameType' => 0,
    'issueNumber' => $period5D['periodId'],
    'startTime' => $period5D['startTime'],
    'endTime' => $period5D['endTime'],
    'type' => 5,
    'lotteryName' => '5D 1 Minute',
    'issue' => 5,
    'gameType' => 0,
    'remark' => 'BIG,SMALL',
    'gameResult' => 'L',
    'intervalM' => 1.0,
    'scope' => '1|10|100|1000',
    'betMultiple' => '1|5|10|20|50|100',
    'playRate' => null,
    'bettingGameType' => 1
];

$dataK3 = [
    'lotteryGameType' => 0,
    'issueNumber' => $periodK3['periodId'],
    'startTime' => $periodK3['startTime'],
    'endTime' => $periodK3['endTime'],
    'type' => 3,
    'lotteryName' => 'K3 Game',
    'issue' => 5,
    'gameType' => 4,
    'remark' => 'TIE,BIG,SMALL',
    'gameResult' => 'L',
    'intervalM' => 1.0,
    'scope' => '5|10|100|1000',
    'betMultiple' => '1|5|10|20|50|100',
    'playRate' => null,
    'bettingGameType' => 2
];

$dataWingo = [
    'lotteryGameType' => 0,
    'issueNumber' => $periodWingo['periodId'],
    'startTime' => $periodWingo['startTime'],
    'endTime' => $periodWingo['endTime'],
    'type' => 7,
    'lotteryName' => 'Wingo 1 Min',
    'issue' => 5,
    'gameType' => 2,
    'remark' => 'TIE,BIG,SMALL',
    'gameResult' => 'L',
    'intervalM' => 1.0,
    'scope' => '10|50|100|500|1000',
    'betMultiple' => '1|2|5|10|20|50|100',
    'playRate' => null,
    'bettingGameType' => 3
];

$res = [
    'code' => 0,
    'msg' => 'Succeed',
    'msgCode' => 0,
    'serviceNowTime' => $shnunc,
    'data' => [
        'list' => [$data5D, $dataK3, $dataWingo],
        'lotteryGameType' => [],
        'serviceTime' => time() * 1000,
        'amount' => null,
        'isLogin' => 1,
        'isDaman' => 0
    ]
];

echo json_encode($res);
exit;
?>
