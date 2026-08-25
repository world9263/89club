<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../../conn.php";
global $firebase;

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    $userId = $_POST['userId'] ?? '';
    $account = $_POST['account'] ?? '';
    $password = $_POST['password'] ?? '';
    $nickname = $_POST['nickname'] ?? '';

    if (empty($userId) || empty($account) || empty($password) || empty($nickname)) {
        echo json_encode(["status" => "false", "msg" => "Missing required parameters"]);
        exit();
    }

    $user = $firebase->get('users/' . $userId);
    if ($user == null) {
        echo json_encode(["status" => "false", "msg" => "User not found"]);
        exit();
    }

    $balance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
    
    // Transfer balance to CQ9 session (zero out local motta wallet)
    $firebase->update('users/' . $userId, ['motta' => 0.0]);

    echo json_encode([
        "status" => "true",
        "msg" => "Account handled, balance returned, and reset",
        "account" => $account,
        "password" => $password,
        "amount" => $balance
    ]);
    exit();
}

if ($method === 'GET') {
    header('Content-Type: text/html; charset=utf-8');
    
    $status = $_GET['status'] ?? '';
    $amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0.0;
    $balance = isset($_GET['balance']) ? (float)$_GET['balance'] : 0.0;
    $user_id = $_GET['user_id'] ?? '';
    $msg = $_GET['msg'] ?? '';

    if ($status === 'success' && !empty($user_id)) {
        $user = $firebase->get('users/' . $user_id);
        if ($user != null) {
            $currentMotta = isset($user['motta']) ? (float)$user['motta'] : 0.0;
            $newMotta = $currentMotta + $amount;
            
            $firebase->update('users/' . $user_id, ['motta' => $newMotta]);
            
            echo "<h2>✅ Withdraw Success</h2>";
            echo "<p>Amount: ₹" . number_format($amount, 2) . "</p>";
            echo "<p>New CQ9 Balance: ₹" . number_format($balance, 2) . "</p>";
        } else {
            echo "<h2>❌ User profile not found in Firebase</h2>";
        }
    } else {
        echo "<h2>❌ Withdraw Failed</h2>";
        echo "<p>Reason: " . htmlspecialchars($msg) . "</p>";
    }
    exit();
}
?>
