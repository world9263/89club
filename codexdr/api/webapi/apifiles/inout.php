<?php header("Content-Type: application/json");
include "../../../conn.php";
if (!$conn || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection error"]);
    exit();
}
$result = $conn->query("SHOW COLUMNS FROM shonu_kaichila LIKE 'token'");
if ($result->num_rows === 0) {
    $conn->query(
        "ALTER TABLE shonu_kaichila ADD COLUMN token CHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL"
    );
}
$action = $_GET["action"] ?? "";
function getUser(mysqli $db, string $token): ?array
{
    $stmt = $db->prepare(
        "SELECT kramasankhye AS id, balakedara AS nickname, motta FROM shonu_kaichila WHERE token = ?"
    );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}
function adjustBalance(mysqli $db, int $userId, float $delta): float
{
    $db->begin_transaction();
    try {
        if ($delta < 0) {
            $check = $db->prepare(
                "SELECT motta FROM shonu_kaichila WHERE kramasankhye = ? FOR UPDATE"
            );
            $check->bind_param("i", $userId);
            $check->execute();
            $row = $check->get_result()->fetch_assoc();
            if ($row["motta"] + $delta < 0) {
                throw new Exception("INSUFFICIENT_FUNDS");
            }
        }
        $upd = $db->prepare(
            "UPDATE shonu_kaichila SET motta = motta + ? WHERE kramasankhye = ?"
        );
        $upd->bind_param("di", $delta, $userId);
        $upd->execute();
        $db->commit();
        $res = $db->prepare(
            "SELECT motta FROM shonu_kaichila WHERE kramasankhye = ?"
        );
        $res->bind_param("i", $userId);
        $res->execute();
        return (float) $res->get_result()->fetch_assoc()["motta"];
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}
function updateToken(mysqli $db, int $userId): string
{
    $token = bin2hex(random_bytes(16));
    $stmt = $db->prepare(
        "UPDATE shonu_kaichila SET token = ? WHERE kramasankhye = ?"
    );
    if (!$stmt) {
        throw new Exception("SQL prepare error: " . $db->error);
    }
    $stmt->bind_param("si", $token, $userId);
    if (!$stmt->execute()) {
        throw new Exception("SQL exec error: " . $stmt->error);
    }
    $stmt->close();
    return $token;
}
try {
    switch ($action) {
        case "get_user":
            $token = $_GET["token"] ?? "";
            $user = getUser($conn, $token);
            echo json_encode($user ?: ["error" => "User not found"]);
            break;
        case "adjust_balance":
            $userId = (int) ($_GET["userId"] ?? 0);
            $amount = (float) ($_GET["amount"] ?? 0);
            if ($userId <= 0) {
                echo json_encode(["error" => "Invalid userId"]);
                break;
            }
            $newBal = adjustBalance($conn, $userId, $amount);
            echo json_encode(["balance" => $newBal]);
            break;
        case "update_token":
            $userId = (int) ($_GET["userId"] ?? 0);
            if ($userId <= 0) {
                echo json_encode(["error" => "Invalid userId"]);
                break;
            }
            $newToken = updateToken($conn, $userId);
            echo json_encode(["token" => $newToken]);
            break;
        default:
            echo json_encode(["error" => "Invalid action"]);
            break;
    }
} catch (Exception $e) {
    $msg = $e->getMessage();
    echo json_encode([
        "error" =>
            $msg === "INSUFFICIENT_FUNDS"
                ? "INSUFFICIENT_FUNDS"
                : "UNKNOWN_ERROR",
    ]);
}
