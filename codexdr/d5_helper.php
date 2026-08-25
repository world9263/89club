<?php
// =====================================================
// 89 CLUB — 5D Game Helper (Firebase)
// =====================================================

function d5_get_current_period($typeId) {
    date_default_timezone_set("Asia/Kolkata");
    $now = time();
    $hours = (int)date('H', $now);
    $minutes = (int)date('i', $now);
    $seconds = (int)date('s', $now);
    $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
    
    switch ($typeId) {
        case 5: $intervalSec = 60;  $sep = "10201"; break;  // 1 min
        case 6: $intervalSec = 180; $sep = "10202"; break;  // 3 min
        case 7: $intervalSec = 300; $sep = "10203"; break;  // 5 min
        case 8: $intervalSec = 600; $sep = "10204"; break;  // 10 min
        default: $intervalSec = 60;  $sep = "10201";
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

function d5_generate_result($firebase, $typeId, $periodId) {
    $fbTypeKey = 'd5_t' . $typeId;
    
    $existing = $firebase->get('game_results/' . $fbTypeKey . '/' . $periodId);
    if ($existing != null) {
        return $existing;
    }
    
    // Check for admin override
    $override = $firebase->get('admin_overrides/d5_t' . $typeId);
    if ($override != null && isset($override['premium']) && isset($override['active']) && $override['active'] == true) {
        $digits = str_split($override['premium']);
        $d1 = isset($digits[0]) ? (int)$digits[0] : 0;
        $d2 = isset($digits[1]) ? (int)$digits[1] : 0;
        $d3 = isset($digits[2]) ? (int)$digits[2] : 0;
        $d4 = isset($digits[3]) ? (int)$digits[3] : 0;
        $d5 = isset($digits[4]) ? (int)$digits[4] : 0;
        $firebase->update('admin_overrides/d5_t' . $typeId, ['active' => false]);
    } else {
        $bets = $firebase->get('game_bets/' . $fbTypeKey . '/' . $periodId);
        $autoProfit = $firebase->get('system_settings/auto_profit_d5');
        if (($autoProfit === true || $autoProfit === 'true' || $autoProfit === 1 || $autoProfit === '1') && $bets != null && is_array($bets) && count($bets) > 0) {
            list($d1, $d2, $d3, $d4, $d5) = d5_calculate_house_optimal($bets);
        } else {
            $d1 = rand(0, 9);
            $d2 = rand(0, 9);
            $d3 = rand(0, 9);
            $d4 = rand(0, 9);
            $d5 = rand(0, 9);
        }
    }
    
    $sum = $d1 + $d2 + $d3 + $d4 + $d5;
    $premium = (string)($d1 . $d2 . $d3 . $d4 . $d5);
    
    $result = [
        'periodId' => $periodId,
        'sumCount' => $sum,
        'premium' => $premium,
        'createdAt' => date('Y-m-d H:i:s'),
        'type' => 'd5'
    ];
    
    $firebase->set('game_results/' . $fbTypeKey . '/' . $periodId, $result);
    
    // Settle bets
    if (!isset($bets)) {
        $bets = $firebase->get('game_bets/' . $fbTypeKey . '/' . $periodId);
    }
    if ($bets != null && is_array($bets)) {
        d5_settle_bets($firebase, $typeId, $periodId, $sum, $premium, $bets);
    }
    
    d5_cleanup_old_results($firebase, $fbTypeKey);
    return $result;
}

function d5_settle_bets($firebase, $typeId, $periodId, $sum, $premium, $bets) {
    $fbTypeKey = 'd5_t' . $typeId;
    $userPayouts = [];
    
    foreach ($bets as $betKey => $bet) {
        if (!isset($bet['selectType']) || !isset($bet['contractAmount'])) continue;
        
        $st = $bet['selectType'];
        $contractAmt = (float)$bet['contractAmount'];
        $userId = $bet['userId'];
        $won = false;
        $multiplier = 2; // Default 2x
        
        // Simple 5D settlement logic matching first digit or sum
        if (is_numeric($st) && (int)$st == $sum) {
            $won = true;
            $multiplier = 9;
        } elseif ($st == 'big' && $sum >= 23) {
            $won = true;
        } elseif ($st == 'small' && $sum <= 22) {
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

function d5_cleanup_old_results($firebase, $fbTypeKey) {
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

function d5_ensure_recent_results($firebase, $typeId, $count = 10) {
    $count = min($count, 10);
    $current = d5_get_current_period($typeId);
    $currentSeq = $current['sequence'];
    $dateStr = date('Ymd');
    
    switch ($typeId) {
        case 5: $sep = "10201"; break;
        case 6: $sep = "10202"; break;
        case 7: $sep = "10203"; break;
        case 8: $sep = "10204"; break;
        default: $sep = "10201";
    }
    
    $fbTypeKey = 'd5_t' . $typeId;
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
            $d1 = rand(0, 9);
            $d2 = rand(0, 9);
            $d3 = rand(0, 9);
            $d4 = rand(0, 9);
            $d5 = rand(0, 9);
            $sum = $d1 + $d2 + $d3 + $d4 + $d5;
            $premium = (string)($d1 . $d2 . $d3 . $d4 . $d5);
            
            $result = [
                'periodId' => $pastPeriodId,
                'sumCount' => $sum,
                'premium' => $premium,
                'createdAt' => date('Y-m-d H:i:s'),
                'type' => 'd5'
            ];
            
            $updates[$pastPeriodId] = $result;
            $results[] = $result;
            $hasNewResults = true;
            
            $bets = isset($allBets[$pastPeriodId]) ? $allBets[$pastPeriodId] : null;
            if ($bets != null && is_array($bets)) {
                d5_settle_bets($firebase, $typeId, $pastPeriodId, $sum, $premium, $bets);
            }
        }
    }
    
    if ($hasNewResults && !empty($updates)) {
        $firebase->update('game_results/' . $fbTypeKey, $updates);
        d5_cleanup_old_results($firebase, $fbTypeKey);
    }
    
    return $results;
}

function d5_simulate_payout_for_sum($bets, $sum) {
    $totalPayout = 0;
    foreach ($bets as $bet) {
        if (!isset($bet['selectType']) || !isset($bet['contractAmount'])) continue;
        $st = $bet['selectType'];
        $amt = (float)$bet['contractAmount'];
        $won = false;
        $multiplier = 2.0;
        
        if (is_numeric($st) && (int)$st === $sum) {
            $won = true;
            $multiplier = 9.0;
        } elseif ($st === 'big' && $sum >= 23) {
            $won = true;
        } elseif ($st === 'small' && $sum <= 22) {
            $won = true;
        }
        
        if ($won) {
            $totalPayout += ($amt * $multiplier);
        }
    }
    return $totalPayout;
}

function d5_calculate_house_optimal($bets) {
    $minPayout = null;
    $bestSum = 22;
    for ($sum = 0; $sum <= 45; $sum++) {
        $payout = d5_simulate_payout_for_sum($bets, $sum);
        if ($minPayout === null || $payout < $minPayout) {
            $minPayout = $payout;
            $bestSum = $sum;
        }
    }
    
    // Distribute bestSum across 5 digits (0-9)
    $d = array_fill(0, 5, 0);
    $rem = $bestSum;
    for ($i = 0; $i < 5; $i++) {
        $val = min(9, $rem);
        $d[$i] = $val;
        $rem -= $val;
    }
    shuffle($d);
    return $d;
}
?>
