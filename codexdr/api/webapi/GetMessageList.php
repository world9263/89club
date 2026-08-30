<?php 
include "../../conn.php";
include "../../functions2.php";
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
$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];
$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
        $language = $shonupost['language'];
        $random = $shonupost['random'];
        $signature = $shonupost['signature'];
        $pageNo = isset($shonupost['pageNo']) ? intval($shonupost['pageNo']) : 1;
        $pageSize = isset($shonupost['pageSize']) ? intval($shonupost['pageSize']) : 10;
        
        if ($pageNo < 1) {
            $pageNo = 1;
        }
        
        $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION'] ?? '');
        $author = $bearer[1] ?? '';				
        $is_jwt_valid = is_jwt_valid($author);
        $data_auth = json_decode($is_jwt_valid, 1);
        
        if($data_auth['status'] === 'Success') {
            $mobile = $data_auth['payload']['mobile'];
            $user = $firebase->get('users/' . $mobile);
            if($user != null){
                // Fetch notifications from Firebase path notifications
                $allNotifications = $firebase->get('notifications') ?: [];
                $filtered = [];
                foreach ($allNotifications as $nId => $n) {
                    if (isset($n['userId']) && $n['userId'] == $mobile) {
                        $n['id'] = $nId;
                        $filtered[] = $n;
                    }
                }
                
                // Sort by createdAt DESC
                usort($filtered, function($a, $b) {
                    return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
                });
                
                $totalCount = count($filtered);
                $totalPage = ceil($totalCount / $pageSize);
                $offset = ($pageNo - 1) * $pageSize;
                $paginated = array_slice($filtered, $offset, $pageSize);
                
                $list = [];
                foreach ($paginated as $n) {
                    $list[] = [
                        'messageID' => $n['id'] ?? '',
                        'addTime' => $n['created_at'] ?? $shnunc,
                        'state' => isset($n['state']) ? (int)$n['state'] : 1,
                        'stateName' => (isset($n['state']) && $n['state'] == 1) ? 'have read' : 'unread',
                        'title' => $n['title'] ?? 'Notification',
                        'messages' => $n['message'] ?? "Your account was just logged in on " . ($n['created_at'] ?? $shnunc) . ". If you have any questions, please feel free to contact online customer service!"
                    ];
                }
                
                $res = [
                    'code' => 0,
                    'msg' => 'Succeed',
                    'msgCode' => 0,
                    'serviceNowTime' => $shnunc,
                    'data' => [
                        'list' => $list,
                        'pageNo' => $pageNo,
                        'totalPage' => $totalPage,
                        'totalCount' => $totalCount
                    ]
                ];
                http_response_code(200);
                echo json_encode($res);
                exit;
            } else {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
                exit;
            }					
        } else {					
            $res['code'] = 4;
            $res['msg'] = 'No operation permission';
            $res['msgCode'] = 2;
            http_response_code(401);
            echo json_encode($res);
            exit;
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);
        exit;
    }		
} else {		
    http_response_code(405);
    echo json_encode($res);
}
?>
