<?php
// =====================================================
// 89 CLUB — TRX WinGo Game Helper (Firebase)
// =====================================================

function trx_get_current_period($typeId) {
    date_default_timezone_set("Asia/Kolkata");
    $now = time();
    $hours = (int)date('H', $now);
    $minutes = (int)date('i', $now);
    $seconds = (int)date('s', $now);
    $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
    
    switch ($typeId) {
        case 13: $intervalSec = 60;  $sep = "10301"; break;  // 1 min
        case 14: $intervalSec = 180; $sep = "10302"; break;  // 3 min
        case 15: $intervalSec = 300; $sep = "10303"; break;  // 5 min
        case 16: $intervalSec = 600; $sep = "10304"; break;  // 10 min
        default: $intervalSec = 60;  $sep = "10301";
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

function trx_get_color($winningDigit) {
    if ($winningDigit == 0) return 'red,violet';
    if ($winningDigit == 5) return 'green,violet';
    if (in_array($winningDigit, [1, 3, 7, 9])) return 'green';
    return 'red';
}

function trx_generate_result($firebase, $typeId, $periodId) {
    $fbTypeKey = 'trx_t' . $typeId;
    
    $existing = $firebase->get('game_results/' . $fbTypeKey . '/' . $periodId);
    if ($existing != null) {
        return $existing;
    }
    
    // Generate simulated block hash and block number
    $blockNumber = (int)(rand(60000000, 69900000));
    $winningDigit = rand(0, 9);
    $hash = md5(uniqid() . $blockNumber) . $winningDigit;
    $color = trx_get_color($winningDigit);
    
    $result = [
        'periodId' => $periodId,
        'number' => $winningDigit,
        'color' => $color,
        'premium' => $hash,
        'blockNumber' => $blockNumber,
        'blockID' => $hash,
        'blockTime' => date('Y-m-d H:i:s'),
        'createdAt' => date('Y-m-d H:i:s'),
        'type' => 'trx'
    ];
    
    $firebase->set('game_results/' . $fbTypeKey . '/' . $periodId, $result);
    
    // Settle bets
    $bets = $firebase->get('game_bets/' . $fbTypeKey . '/' . $periodId);
    if ($bets != null && is_array($bets)) {
        trx_settle_bets($firebase, $typeId, $periodId, $winningDigit, $color, $hash, $bets);
    }
    
    trx_cleanup_old_results($firebase, $fbTypeKey);
    return $result;
}

function trx_settle_bets($firebase, $typeId, $periodId, $winningDigit, $color, $hash, $bets) {
    $fbTypeKey = 'trx_t' . $typeId;
    $userPayouts = [];
    
    foreach ($bets as $betKey => $bet) {
        if (!isset($bet['selectType']) || !isset($bet['contractAmount'])) continue;
        
        $st = $bet['selectType'];
        $contractAmt = (float)$bet['contractAmount'];
        $userId = $bet['userId'];
        $won = false;
        $multiplier = 2;
        
        if (is_numeric($st)) {
            if ((int)$st == $winningDigit) {
                $won = true;
                $multiplier = 9;
            }
        } else {
            $colors = explode(',', $color);
            if (in_array($st, $colors)) {
                $won = true;
                if ($st == 'violet') {
                    $multiplier = 4.5;
                } elseif (($winningDigit == 0 || $winningDigit == 5) && ($st == 'red' || $st == 'green')) {
                    $multiplier = 1.5;
                }
            } elseif ($st == 'big' && $winningDigit >= 5) {
                $won = true;
            } elseif ($st == 'small' && $winningDigit <= 4) {
                $won = true;
            }
        }
        
        $winAmount = $won ? round($contractAmt * $multiplier, 2) : 0;
        $status = $won ? 'win' : 'lose';
        
        $firebase->update('game_bets/' . $fbTypeKey . '/' . $periodId . '/' . $betKey, [
            'status' => $status,
            'resultNumber' => $winningDigit,
            'premium' => $hash,
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

function trx_cleanup_old_results($firebase, $fbTypeKey) {
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

function trx_ensure_recent_results($firebase, $typeId, $count = 10) {
    $count = min($count, 10);
    $current = trx_get_current_period($typeId);
    $currentSeq = $current['sequence'];
    $dateStr = date('Ymd');
    
    switch ($typeId) {
        case 13: $sep = "10301"; break;
        case 14: $sep = "10302"; break;
        case 15: $sep = "10303"; break;
        case 16: $sep = "10304"; break;
        default: $sep = "10301";
    }
    
    $fbTypeKey = 'trx_t' . $typeId;
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
            $blockNumber = (int)(rand(60000000, 69900000));
            $winningDigit = rand(0, 9);
            $hash = md5(uniqid() . $blockNumber) . $winningDigit;
            $color = trx_get_color($winningDigit);
            
            $result = [
                'periodId' => $pastPeriodId,
                'number' => $winningDigit,
                'color' => $color,
                'premium' => $hash,
                'blockNumber' => $blockNumber,
                'blockID' => $hash,
                'blockTime' => date('Y-m-d H:i:s'),
                'createdAt' => date('Y-m-d H:i:s'),
                'type' => 'trx'
            ];
            
            $updates[$pastPeriodId] = $result;
            $results[] = $result;
            $hasNewResults = true;
            
            $bets = isset($allBets[$pastPeriodId]) ? $allBets[$pastPeriodId] : null;
            if ($bets != null && is_array($bets)) {
                trx_settle_bets($firebase, $typeId, $pastPeriodId, $winningDigit, $color, $hash, $bets);
            }
        }
    }
    
    if ($hasNewResults && !empty($updates)) {
        $firebase->update('game_results/' . $fbTypeKey, $updates);
        trx_cleanup_old_results($firebase, $fbTypeKey);
    }
    
    return $results;
}
?>
