<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../../conn.php";
global $firebase;

header('Content-Type: application/json; charset=utf-8');

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

function handleError($message) {
    echo json_encode(["status" => "error", "error" => $message]);
    exit();
}

// 1. GET /Balance
if ($method === 'GET' && strpos($request, '/Balance') !== false && strpos($request, '/Balance2') === false && strpos($request, '/Balance3') === false && strpos($request, '/Balance4') === false && strpos($request, '/Balance5') === false) {
    $userId = $_GET['userId'] ?? '';
    if (empty($userId)) {
        handleError("Missing userId parameter");
    }

    $user = $firebase->get('users/' . $userId);
    if ($user == null) {
        handleError("User not found");
    }

    $balance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
    echo json_encode(["balance" => $balance]);
    exit();
}

// 2. POST /Balance2 (Real-time Bet & Win combined)
if ($method === 'POST' && strpos($request, '/Balance2') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['userId'] ?? '';
    $betAmount = isset($data['betAmount']) ? (float)$data['betAmount'] : null;
    $winloseAmount = isset($data['winloseAmount']) ? (float)$data['winloseAmount'] : null;

    if (empty($userId) || $betAmount === null || $winloseAmount === null) {
        handleError("Missing required parameters");
    }

    $user = $firebase->get('users/' . $userId);
    if ($user == null) {
        handleError("User not found");
    }

    $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
    $newBalance = $currentBalance - $betAmount + $winloseAmount;

    $firebase->update('users/' . $userId, ['motta' => $newBalance]);
    echo json_encode(["newBalance" => $newBalance]);
    exit();
}

// 3. POST /Balance3 (Conditional Bet or Win)
if ($method === 'POST' && strpos($request, '/Balance3') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['userId'] ?? '';
    $betAmount = isset($data['betAmount']) ? (float)$data['betAmount'] : null;
    $winloseAmount = isset($data['winloseAmount']) ? (float)$data['winloseAmount'] : null;
    $type = isset($data['type']) ? (int)$data['type'] : null;

    if (empty($userId) || $betAmount === null || $winloseAmount === null || $type === null) {
        handleError("Missing required parameters");
    }

    $user = $firebase->get('users/' . $userId);
    if ($user == null) {
        handleError("User not found");
    }

    $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
    $newBalance = ($type == 1) ? ($currentBalance - $betAmount) : ($currentBalance + $winloseAmount);

    $firebase->update('users/' . $userId, ['motta' => $newBalance]);
    echo json_encode(["newBalance" => $newBalance]);
    exit();
}

// 4. POST /Balance4 (Refund Bet)
if ($method === 'POST' && strpos($request, '/Balance4') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['userId'] ?? '';
    $betAmount = isset($data['betAmount']) ? (float)$data['betAmount'] : null;

    if (empty($userId) || $betAmount === null) {
        handleError("Missing required parameters");
    }

    $user = $firebase->get('users/' . $userId);
    if ($user == null) {
        handleError("User not found");
    }

    $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
    $newBalance = $currentBalance + $betAmount;

    $firebase->update('users/' . $userId, ['motta' => $newBalance]);
    echo json_encode(["newBalance" => $newBalance, "message" => "Bet canceled"]);
    exit();
}

// 5. POST /Balance5 (Refund Session)
if ($method === 'POST' && strpos($request, '/Balance5') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['userId'] ?? '';
    $betAmount = isset($data['betAmount']) ? (float)$data['betAmount'] : null;

    if (empty($userId) || $betAmount === null) {
        handleError("Missing required parameters");
    }

    $user = $firebase->get('users/' . $userId);
    if ($user == null) {
        handleError("User not found");
    }

    $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
    $newBalance = $currentBalance + $betAmount;

    $firebase->update('users/' . $userId, ['motta' => $newBalance]);
    echo json_encode(["newBalance" => $newBalance, "message" => "Session bet canceled"]);
    exit();
}

handleError("Invalid request");
?>
