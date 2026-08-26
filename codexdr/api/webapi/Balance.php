<?php
include "../../conn.php";
global $firebase;
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET["userId"])) {
    echo json_encode(["error" => "Missing userId parameter", "balance" => 0]);
    exit;
}

$userId = $_GET["userId"];

// Find user in Firebase by their ID
$allUsers = $firebase->get('users');
$foundUser = null;
if ($allUsers) {
    foreach ($allUsers as $mobile => $u) {
        if (isset($u['id']) && $u['id'] == $userId) {
            $foundUser = $u;
            break;
        }
    }
}

if ($foundUser) {
    $balance = isset($foundUser['motta']) ? (float)$foundUser['motta'] : 0.0;
    echo json_encode(["balance" => $balance]);
} else {
    echo json_encode(["error" => "User not found", "balance" => 0]);
}
?>
