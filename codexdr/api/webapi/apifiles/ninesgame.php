<?php
date_default_timezone_set("Asia/Kolkata");
header("Content-Type: application/json; charset=utf-8");
include "../../../conn.php";
global $firebase;

if ($firebase == null) {
    echo json_encode(["ok" => false, "err" => "Firebase DB connection failed"]);
    exit();
}

$raw = file_get_contents("php://input");
$body = json_decode($raw, true);
if (!is_array($body)) {
    $body = $_POST;
}

$op = $body['op'] ?? null;
$username = isset($body['username']) ? trim((string)$body['username']) : null;

if (!$op || $username === null || $username === '') {
    http_response_code(400);
    echo json_encode(["ok" => false, "err" => "missing_params"]);
    exit();
}

$user = $firebase->get('users/' . $username);
if ($user == null) {
    http_response_code(404);
    echo json_encode(["ok" => false, "err" => "user_not_found"]);
    exit();
}

switch ($op) {
    case "ensure_row":
        echo json_encode(["ok" => true]);
        break;

    case "get_amount":
        $amount = isset($user['motta']) ? (float)$user['motta'] : 0.0;
        echo json_encode(["ok" => true, "amount" => $amount]);
        break;

    case "set_amount":
        if (!isset($body['amount'])) {
            http_response_code(400);
            echo json_encode(["ok" => false, "err" => "missing_amount"]);
            exit();
        }
        $amount = (float)$body['amount'];
        $firebase->update('users/' . $username, ['motta' => $amount]);
        echo json_encode(["ok" => true, "amount" => $amount]);
        break;

    case "add_amount":
        if (!isset($body['delta'])) {
            http_response_code(400);
            echo json_encode(["ok" => false, "err" => "missing_delta"]);
            exit();
        }
        $delta = (float)$body['delta'];
        $currentMotta = isset($user['motta']) ? (float)$user['motta'] : 0.0;
        $newMotta = $currentMotta + $delta;
        $firebase->update('users/' . $username, ['motta' => $newMotta]);
        echo json_encode(["ok" => true, "delta" => $delta]);
        break;

    case "save_betlog":
        $NoPrimary = (string)($body['NoPrimary'] ?? '');
        if ($NoPrimary === '') {
            http_response_code(400);
            echo json_encode(["ok" => false, "err" => "missing_NoPrimary"]);
            exit();
        }
        $logData = [
            "NoPrimary" => $NoPrimary,
            "uidIndex" => $body['uidIndex'] ?? $username,
            "gameDateIndex" => $body['gameDateIndex'] ?? null,
            "bet" => isset($body['bet']) ? (float)$body['bet'] : 0.0,
            "validbet" => isset($body['validbet']) ? (float)$body['validbet'] : 0.0,
            "win" => isset($body['win']) ? (float)$body['win'] : 0.0,
            "netWin" => isset($body['netWin']) ? (float)$body['netWin'] : 0.0,
            "gameName" => $body['gameName'] ?? null,
            "gameCode" => $body['gameCode'] ?? null,
            "PreAmount" => isset($body['PreAmount']) ? (float)$body['PreAmount'] : 0.0,
            "AftAmount" => isset($body['AftAmount']) ? (float)$body['AftAmount'] : 0.0,
            "createdAt" => date("Y-m-d H:i:s")
        ];
        $firebase->set("nines_betlogs/{$username}/{$NoPrimary}", $logData);
        echo json_encode(["ok" => true]);
        break;

    case "save_betlogs_bulk":
        $rows = $body['rows'] ?? null;
        if (!is_array($rows) || empty($rows)) {
            http_response_code(400);
            echo json_encode(["ok" => false, "err" => "missing_rows"]);
            exit();
        }
        $saved = 0;
        foreach ($rows as $r) {
            $NoPrimary = (string)($r['NoPrimary'] ?? '');
            if ($NoPrimary === '') continue;
            
            $logData = [
                "NoPrimary" => $NoPrimary,
                "uidIndex" => $r['uidIndex'] ?? $username,
                "gameDateIndex" => $r['gameDateIndex'] ?? null,
                "bet" => isset($r['bet']) ? (float)$r['bet'] : 0.0,
                "validbet" => isset($r['validbet']) ? (float)$r['validbet'] : 0.0,
                "win" => isset($r['win']) ? (float)$r['win'] : 0.0,
                "netWin" => isset($r['netWin']) ? (float)$r['netWin'] : 0.0,
                "gameName" => $r['gameName'] ?? null,
                "gameCode" => $r['gameCode'] ?? null,
                "PreAmount" => isset($r['PreAmount']) ? (float)$r['PreAmount'] : 0.0,
                "AftAmount" => isset($r['AftAmount']) ? (float)$r['AftAmount'] : 0.0,
                "createdAt" => date("Y-m-d H:i:s")
            ];
            $firebase->set("nines_betlogs/{$username}/{$NoPrimary}", $logData);
            $saved++;
        }
        echo json_encode(["ok" => true, "saved" => $saved]);
        break;

    case "get_betlog_checkpoint":
        $cp = $firebase->get("nines_betlogs_checkpoint/{$username}");
        echo json_encode(["ok" => true, "ts" => $cp ? (int)$cp : null]);
        break;

    case "set_betlog_checkpoint":
        if (!isset($body['ts'])) {
            http_response_code(400);
            echo json_encode(["ok" => false, "err" => "missing_ts"]);
            exit();
        }
        $ts = (int)$body['ts'];
        $firebase->set("nines_betlogs_checkpoint/{$username}", $ts);
        echo json_encode(["ok" => true, "ts" => $ts]);
        break;

    default:
        http_response_code(400);
        echo json_encode(["ok" => false, "err" => "unknown_op"]);
        break;
}
?>
