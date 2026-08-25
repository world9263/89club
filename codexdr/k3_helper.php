<?php
// =====================================================
// 89 CLUB — K3 Game Helper (Firebase)
// =====================================================

function k3_get_current_period($typeId) {
    date_default_timezone_set("Asia/Kolkata");
    $now = time();
    $hours = (int)date('H', $now);
    $minutes = (int)date('i', $now);
    $seconds = (int)date('s', $now);
    $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
    
    switch ($typeId) {
        case 9:  $intervalSec = 60;  $sep = "09"; break;    // 1 min
        case 10: $intervalSec = 180; $sep = "10102"; break; // 3 min
        case 11: $intervalSec = 300; $sep = "10103"; break; // 5 min
        case 12: $intervalSec = 600; $sep = "12"; break;    // 10 min
        default: $intervalSec = 60;  $sep = "09";
    }
    
    $sequence = (int)floor($totalSeconds / $intervalSec) + 1;
    $elapsedInPeriod = $totalSeconds % $intervalSec;
    
    $dateStr = date('Ymd', $now);
    $periodId = $dateStr . $sep . sprintf("%04d", $sequence);
    
    $dayStart = strtotime(date('Y-m-d 00:00:00', $now));
    $periodStart = $dayStart + ($sequence - 1) * $intervalSec;
    $periodEnd = $periodStart + $intervalSec;
    
    return [
        'periodId' => $periodId,
        'sequence' => $sequence,
        'startTime' => date('Y-m-d H:i:s', $periodStart),
        'endTime' => date('Y-m-d H:i:s', $periodEnd),
        'serviceTime' => date('Y-m-d H:i:s', $now),
        'intervalSec' => $intervalSec,
        'remaining' => $intervalSec - $elapsedInPeriod,
        'isActive' => true
    ];
}

function k3_classify_dice($d1, $d2, $d3) {
    $sorted = [$d1, $d2, $d3];
    sort($sorted);
    
    if ($d1 === $d2 && $d2 === $d3) {
        return 3; // all same
    }
    if ($d1 === $d2 || $d1 === $d3 || $d2 === $d3) {
        return 2; // any two same
    }
    if (($sorted[2] - $sorted[0] == 2) && ($sorted[1] - $sorted[0] == 1)) {
        return 1; // consecutive
    }
    return 0; // all different
}

function k3_generate_result($firebase, $typeId, $periodId) {
    $fbTypeKey = 'k3_t' . $typeId;
    
    $existing = $firebase->get('game_results/' . $fbTypeKey . '/' . $periodId);
    if ($existing != null) {
        return $existing;
    }
    
    // Generate dice (1 to 6)
    $d1 = rand(1, 6);
    $d2 = rand(1, 6);
    $d3 = rand(1, 6);
    
    $sum = $d1 + $d2 + $d3;
    $gameType = k3_classify_dice($d1, $d2, $d3);
    $premium = (string)($d1 . $d2 . $d3);
    
    $result = [
        'periodId' => $periodId,
        'gameType' => $gameType,
        'sumCount' => $sum,
        'premium' => $premium,
        'createdAt' => date('Y-m-d H:i:s'),
        'type' => 'k3'
    ];
    
    $firebase->set('game_results/' . $fbTypeKey . '/' . $periodId, $result);
    
    // Settle bets (simple placeholder or simulation)
    $bets = $firebase->get('game_bets/' . $fbTypeKey . '/' . $periodId);
    if ($bets != null && is_array($bets)) {
        k3_settle_bets($firebase, $typeId, $periodId, $sum, $gameType, $premium, $bets);
    }
    
    k3_cleanup_old_results($firebase, $fbTypeKey);
    return $result;
}

function k3_settle_bets($firebase, $typeId, $periodId, $sum, $gameType, $premium, $bets) {
    $fbTypeKey = 'k3_t' . $typeId;
    $userPayouts = [];
    
    foreach ($bets as $betKey => $bet) {
        if (!isset($bet['selectType']) || !isset($bet['contractAmount'])) continue;
        
        $st = $bet['selectType'];
        $contractAmt = (float)$bet['contractAmount'];
        $userId = $bet['userId'];
        $won = false;
        $multiplier = 2; // Default 2x
        
        // Simple settlement logic: check if selected sum or type matches
        if (is_numeric($st) && (int)$st == $sum) {
            $won = true;
            $multiplier = 9;
        } elseif ($st == 'big' && $sum >= 11) {
            $won = true;
        } elseif ($st == 'small' && $sum <= 10) {
            $won = true;
        }
        
        $winAmount = $won ? round($contractAmt * $multiplier, 2) : 0;
        $status = $won ? 'win' : 'lose';
        
        $firebase->update('game_bets/' . $fbTypeKey . '/' . $periodId . '/' . $betKey, [
            'status' => $status,
            'resultNumber' => $sum,
            'premium' => $premium,
            'winAmount' => $winAmount
        ]);
        
        if ($won && $winAmount > 0) {
            if (!isset($userPayouts[$userId])) $userPayouts[$userId] = 0;
            $userPayouts[$userId] += $winAmount;
        }
    }
    
    foreach ($userPayouts as $mobile => $payout) {
        $user = $firebase->get('users/' . $mobile);
        if ($user != null) {
            $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0;
            $newBalance = round($currentBalance + $payout, 2);
            $firebase->update('users/' . $mobile, ['motta' => $newBalance]);
        }
    }
}

function k3_cleanup_old_results($firebase, $fbTypeKey) {
    $allResults = $firebase->get('game_results/' . $fbTypeKey);
    if ($allResults && is_array($allResults) && count($allResults) > 10) {
        ksort($allResults);
        $keys = array_keys($allResults);
        $toDelete = count($keys) - 10;
        for ($i = 0; $i < $toDelete; $i++) {
            $firebase->delete('game_results/' . $fbTypeKey . '/' . $keys[$i]);
        }
    }
}

function k3_ensure_recent_results($firebase, $typeId, $count = 10) {
    $count = min($count, 10);
    $current = k3_get_current_period($typeId);
    $currentSeq = $current['sequence'];
    $dateStr = date('Ymd');
    
    switch ($typeId) {
        case 9:  $sep = "09"; break;
        case 10: $sep = "10102"; break;
        case 11: $sep = "10103"; break;
        case 12: $sep = "12"; break;
        default: $sep = "09";
    }
    
    $fbTypeKey = 'k3_t' . $typeId;
    $existingResults = $firebase->get('game_results/' . $fbTypeKey) ?: [];
    $allBets = $firebase->get('game_bets/' . $fbTypeKey) ?: [];
    
    $results = [];
    $updates = [];
    $hasNewResults = false;
    
    for ($i = 1; $i <= $count && ($currentSeq - $i) >= 1; $i++) {
        $seq = $currentSeq - $i;
        $pastPeriodId = $dateStr . $sep . sprintf("%04d", $seq);
        
        if (isset($existingResults[$pastPeriodId])) {
            $results[] = $existingResults[$pastPeriodId];
        } else {
            // Generate missing result
            $d1 = rand(1, 6);
            $d2 = rand(1, 6);
            $d3 = rand(1, 6);
            $sum = $d1 + $d2 + $d3;
            $gameType = k3_classify_dice($d1, $d2, $d3);
            $premium = (string)($d1 . $d2 . $d3);
            
            $result = [
                'periodId' => $pastPeriodId,
                'gameType' => $gameType,
                'sumCount' => $sum,
                'premium' => $premium,
                'createdAt' => date('Y-m-d H:i:s'),
                'type' => 'k3'
            ];
            
            $updates[$pastPeriodId] = $result;
            $results[] = $result;
            $hasNewResults = true;
            
            $bets = isset($allBets[$pastPeriodId]) ? $allBets[$pastPeriodId] : null;
            if ($bets != null && is_array($bets)) {
                k3_settle_bets($firebase, $typeId, $pastPeriodId, $sum, $gameType, $premium, $bets);
            }
        }
    }
    
    if ($hasNewResults && !empty($updates)) {
        $firebase->update('game_results/' . $fbTypeKey, $updates);
        k3_cleanup_old_results($firebase, $fbTypeKey);
    }
    
    return $results;
}
?>
