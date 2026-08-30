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
        $issueNumbers = isset($shonupost['issueNumber']) ? $shonupost['issueNumber'] : [];
        if(!is_array($issueNumbers)) {
            $issueNumbers = [$issueNumbers];
        }
        
        $results = [];
        foreach($issueNumbers as $issuenumber) {
            if (strlen($issuenumber) < 13) continue;
            // determine typeId from char at index 12 (assuming standard structure)
            $typeId = substr($issuenumber, 12, 1);
            
            // Map typeId back for helper to trigger generation/settlement
            $typeIdForHelper = ($typeId == 5) ? 4 : (int)$typeId;
            wingo_ensure_recent_results($firebase, $typeIdForHelper, 5);
            
            $result = $firebase->get('game_results/wingo_t' . $typeId . '/' . $issuenumber);
            $bets = $firebase->get('game_bets/wingo_t' . $typeId . '/' . $issuenumber);
            
            $userWinAmount = 0;
            $userState = 0;
            
            if($bets) {
                foreach($bets as $betId => $bet) {
                    if($bet['userId'] == $mobile) {
                        if($bet['status'] == 'win') {
                            $userWinAmount += $bet['winAmount'];
                            $userState = 1;
                        }
                    }
                }
            }
            
            if($result) {
                $results[] = [
                    'issueNumber' => (string)$issuenumber,
                    'number' => $result['number'],
                    'colour' => $result['colour'],
                    'winAmount' => $userWinAmount,
                    'typeName' => "Win Go " . ($typeId == 1 ? "1 Min" : ($typeId == 2 ? "3 Min" : ($typeId == 3 ? "5 Min" : "10 Min"))),
                    'state' => $userState
                ];
            }
        }
        
        echo json_encode([
            'code' => 0,
            'msg' => 'Succeed',
            'msgCode' => 0,
            'serviceNowTime' => date('Y-m-d H:i:s'),
            'data' => $results
        ]);
        exit;
    }
}
echo json_encode(['code' => -1, 'msg' => 'Invalid token']);
?>
