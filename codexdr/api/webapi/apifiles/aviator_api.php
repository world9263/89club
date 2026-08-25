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
    respondJson(["error" => "Firebase DB connection failed"], 500);
}

$action = $_GET['action'] ?? '';
$userId = $_GET['userId'] ?? '';

if (empty($userId)) {
    respondJson(["error" => "Missing userId"], 400);
}

$user = $firebase->get('users/' . $userId);
if ($user == null) {
    respondJson(["error" => "User not found"], 404);
}

// Deterministic round generation
date_default_timezone_set("Asia/Kolkata");
$now = time();
$roundLength = 30; // 30 seconds per round
$roundId = floor($now / $roundLength);
$elapsed = $now % $roundLength;

// Generate multiplier distribution
function generateRandomMultiplier() {
    $rand = mt_rand(1, 1000) / 10;
    if ($rand < 15.0) return 1.0; // 15% instant crash
    if ($rand < 75.0) return round(1.0 + (mt_rand(1, 100) / 100) * 1.5, 2); // 60% low crash (1.0 - 2.5)
    if ($rand < 95.0) return round(2.5 + (mt_rand(1, 100) / 100) * 7.5, 2); // 20% medium crash (2.5 - 10)
    return round(10.0 + (mt_rand(1, 100) / 100) * 90.0, 2); // 5% high crash (10 - 100)
}

switch ($action) {
    case 'get_state':
        // Ensure crash multiplier exists for the current round
        $roundData = $firebase->get("aviator_rounds/{$roundId}");
        if ($roundData == null) {
            $crashMultiplier = generateRandomMultiplier();
            $roundData = [
                "roundId" => $roundId,
                "crashMultiplier" => $crashMultiplier,
                "startTime" => $roundId * $roundLength + 8 // 8s betting phase
            ];
            $firebase->set("aviator_rounds/{$roundId}", $roundData);

            // Add previous round outcome to history
            $prevRoundId = $roundId - 1;
            $prevRound = $firebase->get("aviator_rounds/{$prevRoundId}");
            if ($prevRound != null) {
                $history = $firebase->get("aviator_history") ?: [];
                array_unshift($history, $prevRound['crashMultiplier']);
                $history = array_slice($history, 0, 15);
                $firebase->set("aviator_history", $history);
            }
        }

        $history = $firebase->get("aviator_history") ?: [1.25, 2.45, 1.05, 5.12, 1.88, 3.41, 1.02];
        $bets = $firebase->get("aviator_bets/{$roundId}") ?: [];

        respondJson([
            "roundId" => $roundId,
            "elapsed" => $elapsed,
            "crashMultiplier" => $roundData['crashMultiplier'],
            "history" => $history,
            "bets" => $bets,
            "balance" => isset($user['motta']) ? (float)$user['motta'] : 0.0,
            "serverTimeMs" => microtime(true) * 1000
        ]);
        break;

    case 'place_bet':
        $data = json_decode(file_get_contents("php://input"), true);
        $amount = isset($data['amount']) ? (float)$data['amount'] : 0.0;
        $panelId = $data['panelId'] ?? 'panel1';

        if ($amount < 10 || $amount > 10000) {
            respondJson(["error" => "Invalid bet amount"], 400);
        }

        if ($elapsed >= 8) {
            respondJson(["error" => "Betting phase has ended for this round"], 400);
        }

        $balance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
        if ($balance < $amount) {
            respondJson(["error" => "Insufficient balance"], 400);
        }

        // Deduct balance
        $newBalance = $balance - $amount;
        $firebase->update('users/' . $userId, ['motta' => $newBalance]);

        // Record bet
        $betRecord = [
            "userId" => $userId,
            "amount" => $amount,
            "status" => "pending",
            "winAmount" => 0.0,
            "cashoutMultiplier" => 0.0,
            "panelId" => $panelId,
            "createdAt" => date("Y-m-d H:i:s")
        ];
        $firebase->set("aviator_bets/{$roundId}/{$userId}_{$panelId}", $betRecord);

        respondJson([
            "success" => true,
            "balance" => $newBalance
        ]);
        break;

    case 'cashout':
        $data = json_decode(file_get_contents("php://input"), true);
        $panelId = $data['panelId'] ?? 'panel1';
        $clientMultiplier = isset($data['multiplier']) ? (float)$data['multiplier'] : 0.0;

        if ($elapsed < 8) {
            respondJson(["error" => "Round has not started flying"], 400);
        }

        // Fetch round details
        $roundData = $firebase->get("aviator_rounds/{$roundId}");
        if ($roundData == null) {
            respondJson(["error" => "Round data not found"], 404);
        }

        $crashMultiplier = (float)$roundData['crashMultiplier'];
        
        // Calculate server multiplier at this exact moment
        $flightElapsed = $elapsed - 8;
        $serverMultiplier = round(1.0 + Math.pow($flightElapsed, 1.8) * 0.06, 2);

        // Check if plane has already crashed
        if ($serverMultiplier >= $crashMultiplier) {
            respondJson(["error" => "Plane has already crashed"], 400);
        }

        // Check if the client cashed out after the crash
        if ($clientMultiplier >= $crashMultiplier) {
            respondJson(["error" => "Cashed out after crash"], 400);
        }

        // Get the active bet
        $bet = $firebase->get("aviator_bets/{$roundId}/{$userId}_{$panelId}");
        if ($bet == null) {
            respondJson(["error" => "Active bet not found"], 404);
        }

        if ($bet['status'] !== 'pending') {
            respondJson(["error" => "Bet already settled"], 400);
        }

        // Use the smaller of server/client multipliers to prevent injection cheating
        $finalMultiplier = min($serverMultiplier, $clientMultiplier);
        $winAmount = round($bet['amount'] * $finalMultiplier, 2);

        // Update player balance
        $balance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
        $newBalance = $balance + $winAmount;
        $firebase->update('users/' . $userId, ['motta' => $newBalance]);

        // Settle bet
        $firebase->update("aviator_bets/{$roundId}/{$userId}_{$panelId}", [
            "status" => "won",
            "winAmount" => $winAmount,
            "cashoutMultiplier" => $finalMultiplier
        ]);

        respondJson([
            "success" => true,
            "winAmount" => $winAmount,
            "balance" => $newBalance
        ]);
        break;

    default:
        respondJson(["error" => "Invalid action"], 400);
        break;
}
?>
