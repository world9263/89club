<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../../conn.php";
global $firebase;

header('Content-Type: application/json; charset=utf-8');

function respondJson($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit();
}

if ($firebase == null) {
    respondJson(["status" => "error", "error" => "Firebase DB connection failed"], 500);
}

$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$uid = isset($_GET['uid']) ? trim($_GET['uid']) : '';

if (empty($uid)) {
    respondJson(["status" => "error", "error" => "Missing required parameter: uid"], 400);
}

if (!in_array($action, ['get_balance', 'clear_balance', 'add_balance', 'get_prefix'])) {
    respondJson(["status" => "error", "error" => "Invalid action"], 400);
}

$user = $firebase->get('users/' . $uid);
if ($user == null) {
    respondJson(["status" => "error", "error" => "User not found"], 404);
}

switch ($action) {
    case 'get_balance':
        $balance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
        respondJson([
            "status" => "success",
            "balance" => $balance
        ]);
        break;

    case 'clear_balance':
        $firebase->update('users/' . $uid, ['motta' => 0.0]);
        respondJson([
            "status" => "success"
        ]);
        break;

    case 'add_balance':
        $amount = isset($_GET['amount']) && is_numeric($_GET['amount']) ? (float)$_GET['amount'] : null;
        if ($amount === null) {
            respondJson(["status" => "error", "error" => "Missing or invalid amount"], 400);
        }
        
        $currentMotta = isset($user['motta']) ? (float)$user['motta'] : 0.0;
        $newMotta = $currentMotta + $amount;
        
        $firebase->update('users/' . $uid, ['motta' => $newMotta]);
        respondJson([
            "status" => "success"
        ]);
        break;

    case 'get_prefix':
        $prefix = isset($user['codechorkamukala']) ? $user['codechorkamukala'] : 'abcd';
        respondJson([
            "status" => "success",
            "prefix" => $prefix
        ]);
        break;
}
?>
