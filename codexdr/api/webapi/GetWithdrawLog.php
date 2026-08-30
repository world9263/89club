<?php
include "../../conn.php";
include "../../functions2.php";
global $firebase;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

date_default_timezone_set("Asia/Kolkata");
$currentDate = date("Y-m-d H:i:s");
$response = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $currentDate,
];

// Handle OPTIONS request for preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$requestBody = file_get_contents("php://input");
$requestData = json_decode($requestBody, true);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    if (isset($requestData['language'], $requestData['random'], $requestData['signature'], $requestData['timestamp'], $requestData['type'], $requestData['startDate'], $requestData['endDate'], $requestData['pageNo'], $requestData['pageSize'])) {
        
        $withdrawid = $requestData['type']; // 1 = bank, 3 = usdt, -1 = all
        $pageNo = (int)$requestData['pageNo'];
        $pageSize = isset($requestData['pageSize']) ? (int)$requestData['pageSize'] : 10;
        $pageSize = min($pageSize, 100);

        $token = explode(" ", $_SERVER['HTTP_AUTHORIZATION'])[1] ?? '';
        $isValidJWT = is_jwt_valid($token);
        $authData = json_decode($isValidJWT, true);

        if ($authData['status'] === 'Success') {
            $mobile = $authData['payload']['mobile'];
            
            try {
                // Fetch withdrawals from Firebase
                $allWithdrawals = $firebase->get('withdrawals');
                $filtered = [];
                
                if ($allWithdrawals) {
                    foreach ($allWithdrawals as $wd) {
                        // Filter by user ID (mobile)
                        if (($wd['userId'] ?? '') !== $mobile) {
                            continue;
                        }
                        
                        // Filter by type
                        $wdType = ($wd['method'] ?? 'BANK_CARD') === 'USDT' ? 3 : 1;
                        if ($withdrawid != -1 && $wdType != $withdrawid) {
                            continue;
                        }
                        
                        $filtered[] = $wd;
                    }
                }
                
                // Sort by createdAt DESC
                usort($filtered, function($a, $b) {
                    return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? '');
                });
                
                $totalCount = count($filtered);
                $totalPage = ceil($totalCount / $pageSize);
                $offset = ($pageNo - 1) * $pageSize;
                $paginated = array_slice($filtered, $offset, $pageSize);
                
                $withdrawalslist = [];
                foreach ($paginated as $wd) {
                    $state = 0; // pending
                    if (($wd['status'] ?? 'pending') === 'approved') {
                        $state = 1; // success
                    } elseif (($wd['status'] ?? 'pending') === 'failed') {
                        $state = 2; // failed
                    }
                    
                    $type = ($wd['method'] ?? 'BANK_CARD') === 'USDT' ? 3 : 1;
                    
                    $is_bdt_user = false;
                    $cf_country = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? strtoupper($_SERVER["HTTP_CF_IPCOUNTRY"]) : '';
                    if ($cf_country === 'BD') {
                        $is_bdt_user = true;
                    }
                    $withdrawName = $type === 1 ? ($is_bdt_user ? 'E-Wallet' : 'BANK CARD') : 'USDT';
                    
                    $withdrawalslist[] = [
                        'withdrawID' => $wd['id'] ?? '',
                        'type' => $type,
                        'withdrawNumber' => $wd['withdrawNumber'] ?? '',
                        'withdrawName' => $withdrawName,
                        'price' => (int)($wd['amount'] ?? 0),
                        'addTime' => $wd['createdAt'] ?? '',
                        'realityAmount' => (int)($wd['amount'] ?? 0),
                        'remark' => $wd['remark'] ?? '',
                        'state' => $state,
                        'thirdpartyState' => $state
                    ];
                }
                
                $response = [
                    "data" => [
                        "list" => $withdrawalslist,
                        "pageNo" => $pageNo,
                        "totalPage" => $totalPage,
                        "totalCount" => $totalCount
                    ],
                    "code" => 0,
                    "msg" => "Succeed",
                    "msgCode" => 0,
                    "serviceNowTime" => $currentDate
                ];
                http_response_code(200);
                
            } catch (Exception $e) {
                $response['code'] = 6;
                $response['msg'] = $e->getMessage();
                $response['msgCode'] = 4;
                http_response_code(500);
            }
        } else {
            $response['code'] = 5;
            $response['msg'] = 'Invalid JWT';
            $response['msgCode'] = 3;
            http_response_code(401);
        }
    } else {
        $response['code'] = 8;
        $response['msg'] = 'Required parameters missing';
        $response['msgCode'] = 7;
        http_response_code(400);
    }
} else {
    http_response_code(405);
}

echo json_encode($response);
?>
