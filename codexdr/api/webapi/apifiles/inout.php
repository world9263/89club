<?php
header("Content-Type: application/json");
include "../../../conn.php";
global $firebase;

if ($firebase == null) {
    http_response_code(500);
    echo json_encode(["error" => "Firebase DB connection failed"]);
    exit();
}

$action = $_GET['action'] ?? '';

function findUserByToken($firebase, $token) {
    $allUsers = $firebase->get('users');
    if ($allUsers && is_array($allUsers)) {
        foreach ($allUsers as $mobile => $user) {
            if (isset($user['token']) && $user['token'] === $token) {
                return [
                    "id" => $mobile,
                    "nickname" => $user['codechorkamukala'] ?? 'Player',
                    "motta" => (float)($user['motta'] ?? 0.0)
                ];
            }
        }
    }
    return null;
}

try {
    switch ($action) {
        case "get_user":
            $token = $_GET['token'] ?? '';
            $user = findUserByToken($firebase, $token);
            if ($user) {
                echo json_encode($user);
            } else {
                echo json_encode(["error" => "User not found"]);
            }
            break;

        case "adjust_balance":
            $userId = $_GET['userId'] ?? '';
            $amount = (float)($_GET['amount'] ?? 0);
            
            if (empty($userId)) {
                echo json_encode(["error" => "Invalid userId"]);
                break;
            }

            $user = $firebase->get('users/' . $userId);
            if ($user == null) {
                echo json_encode(["error" => "User not found"]);
                break;
            }

            $currentMotta = (float)($user['motta'] ?? 0.0);
            if ($amount < 0 && ($currentMotta + $amount) < 0) {
                throw new Exception("INSUFFICIENT_FUNDS");
            }

            $newMotta = $currentMotta + $amount;
            $firebase->update('users/' . $userId, ['motta' => $newMotta]);
            
            echo json_encode(["balance" => $newMotta]);
            break;

        case "update_token":
            $userId = $_GET['userId'] ?? '';
            if (empty($userId)) {
                echo json_encode(["error" => "Invalid userId"]);
                break;
            }

            $user = $firebase->get('users/' . $userId);
            if ($user == null) {
                echo json_encode(["error" => "User not found"]);
                break;
            }

            $newToken = bin2hex(random_bytes(16));
            $firebase->update('users/' . $userId, ['token' => $newToken]);
            
            echo json_encode(["token" => $newToken]);
            break;

        default:
            echo json_encode(["error" => "Invalid action"]);
            break;
    }
} catch (Exception $e) {
    $msg = $e->getMessage();
    echo json_encode([
        "error" => ($msg === "INSUFFICIENT_FUNDS") ? "INSUFFICIENT_FUNDS" : "UNKNOWN_ERROR"
    ]);
}
?>
