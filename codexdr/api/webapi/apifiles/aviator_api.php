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
    respondJson(["success" => false, "error" => "Firebase DB connection failed"]);
}

$action = $_GET['action'] ?? '';
$userId = $_GET['userId'] ?? '';

if (empty($userId)) {
    respondJson(["success" => false, "error" => "Missing userId"]);
}

$user = $firebase->get('users/' . $userId);
if ($user == null) {
    respondJson(["success" => false, "error" => "User not found"]);
}

date_default_timezone_set("Asia/Kolkata");
$now = microtime(true);

function generateRandomMultiplier() {
    $rand = mt_rand(1, 1000) / 10;
    if ($rand < 15.0) return 1.0; // 15% instant crash
    if ($rand < 75.0) return round(1.0 + (mt_rand(1, 100) / 100) * 1.5, 2); // 60% low crash (1.0 - 2.5)
    if ($rand < 95.0) return round(2.5 + (mt_rand(1, 100) / 100) * 7.5, 2); // 20% medium crash (2.5 - 10)
    return round(10.0 + (mt_rand(1, 100) / 100) * 90.0, 2); // 5% high crash (10 - 100)
}

// Fetch current state
$state = $firebase->get('aviator_current_state');
if ($state == null) {
    $state = [
        'roundId' => 100001,
        'status' => 'betting',
        'phaseStartTime' => $now,
        'crashMultiplier' => generateRandomMultiplier(),
        'history' => [1.25, 2.45, 1.05, 5.12, 1.88, 3.41, 1.02]
    ];
    $firebase->set('aviator_current_state', $state);
}

$phaseStartTime = (float)$state['phaseStartTime'];
$elapsed = $now - $phaseStartTime;

if ($elapsed > 60.0) {
    // Reset to fresh betting round if inactive for over a minute
    $state['status'] = 'betting';
    $state['phaseStartTime'] = $now;
    $state['crashMultiplier'] = generateRandomMultiplier();
    $firebase->set('aviator_current_state', $state);
    
    $phaseStartTime = $now;
    $elapsed = 0.0;
} else {
    // standard state machine ticking
    $changed = false;
    if ($state['status'] === 'betting') {
        if ($elapsed >= 8.0) {
            $state['status'] = 'flying';
            $state['phaseStartTime'] = $now;
            $changed = true;
            
            $phaseStartTime = $now;
            $elapsed = 0.0;
        }
    } else if ($state['status'] === 'flying') {
        $currentMultiplier = round(1.0 + pow($elapsed, 1.8) * 0.06, 2);
        if ($currentMultiplier >= (float)$state['crashMultiplier']) {
            $state['status'] = 'crashed';
            $state['phaseStartTime'] = $now;
            $changed = true;
            
            $phaseStartTime = $now;
            $elapsed = 0.0;
        }
    } else if ($state['status'] === 'crashed') {
        if ($elapsed >= 2.0) {
            $state['roundId'] = (int)$state['roundId'] + 1;
            $state['status'] = 'betting';
            $state['phaseStartTime'] = $now;
            
            $history = $state['history'] ?? [];
            array_unshift($history, (float)$state['crashMultiplier']);
            $state['history'] = array_slice($history, 0, 15);
            
            $state['crashMultiplier'] = generateRandomMultiplier();
            $changed = true;
            
            $phaseStartTime = $now;
            $elapsed = 0.0;
        }
    }
    
    if ($changed) {
        $firebase->set('aviator_current_state', $state);
    }
}

$roundId = $state['roundId'];
$crashMultiplier = (float)$state['crashMultiplier'];

// Compute dynamic elapsed seconds for client
$clientElapsed = 0.0;
if ($state['status'] === 'betting') {
    $clientElapsed = $now - $phaseStartTime;
} else if ($state['status'] === 'flying') {
    $clientElapsed = 8.0 + ($now - $phaseStartTime);
} else if ($state['status'] === 'crashed') {
    $flightTimeToCrash = pow(($crashMultiplier - 1.0) / 0.06, 1 / 1.8);
    $clientElapsed = 8.0 + $flightTimeToCrash + ($now - $phaseStartTime);
}

switch ($action) {
    case 'get_state':
        $history = $state['history'] ?? [1.25, 2.45, 1.05, 5.12, 1.88, 3.41, 1.02];
        $bets = $firebase->get("aviator_bets/{$roundId}") ?: [];

        respondJson([
            "roundId" => $roundId,
            "elapsed" => $clientElapsed,
            "crashMultiplier" => $crashMultiplier,
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
            respondJson(["success" => false, "error" => "Invalid bet amount"]);
        }

        if ($state['status'] !== 'betting') {
            respondJson(["success" => false, "error" => "Betting phase has ended for this round"]);
        }

        $balance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
        if ($balance < $amount) {
            respondJson(["success" => false, "error" => "Insufficient balance"]);
        }

        // Deduct balance
        $newBalance = round($balance - $amount, 2);
        $firebase->update('users/' . $userId, ['motta' => $newBalance]);
        deduct_turnover($userId, $amount);

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

        if ($state['status'] !== 'flying') {
            respondJson(["success" => false, "error" => "Round has not started flying or has already crashed"]);
        }

        // Calculate server multiplier at this exact moment
        $flightElapsed = $now - $phaseStartTime;
        $serverMultiplier = round(1.0 + pow($flightElapsed, 1.8) * 0.06, 2);

        // Check if plane has already crashed
        if ($serverMultiplier >= $crashMultiplier) {
            respondJson(["success" => false, "error" => "Plane has already crashed"]);
        }

        // Check if the client cashed out after the crash
        if ($clientMultiplier >= $crashMultiplier) {
            respondJson(["success" => false, "error" => "Cashed out after crash"]);
        }

        // Get the active bet
        $bet = $firebase->get("aviator_bets/{$roundId}/{$userId}_{$panelId}");
        if ($bet == null) {
            respondJson(["success" => false, "error" => "Active bet not found"]);
        }

        if ($bet['status'] !== 'pending') {
            respondJson(["success" => false, "error" => "Bet already settled"]);
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
        respondJson(["success" => false, "error" => "Invalid action"]);
        break;
}
?>
