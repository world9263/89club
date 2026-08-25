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

function k3_settle_bets($firebase, $typeId, $periodId, $sum, $gameTypeClassification, $premium, $bets) {
    $fbTypeKey = 'k3_t' . $typeId;
    $userPayouts = [];
    
    // Sort rolled digits for easier matching
    $digits = str_split((string)$premium);
    sort($digits);
    $sorted_premium = implode('', $digits);
    
    $isAllSame = ($digits[0] === $digits[1] && $digits[1] === $digits[2]);
    $isConsecutive = ($digits[0] + 1 == $digits[1] && $digits[1] + 1 == $digits[2]);
    $isTwoSame = ($digits[0] === $digits[1] || $digits[1] === $digits[2]);
    $isAllDifferent = ($digits[0] !== $digits[1] && $digits[1] !== $digits[2] && $digits[0] !== $digits[2]);
    
    foreach ($bets as $betKey => $bet) {
        if (!isset($bet['selectType']) || !isset($bet['contractAmount']) || !isset($bet['gameType'])) continue;
        
        $st = $bet['selectType'];
        $gt = (string)$bet['gameType']; // gameType from bet data
        $contractAmt = (float)$bet['contractAmount'];
        $userId = $bet['userId'];
        $won = false;
        $multiplier = 1.0;
        
        // 1. Sum bets (gameType == '1')
        if ($gt === '1') {
            if (is_numeric($st) && (int)$st === $sum) {
                $won = true;
                $sumMultipliers = [
                    3 => 207.36, 18 => 207.36,
                    4 => 69.12, 17 => 69.12,
                    5 => 34.56, 16 => 34.56,
                    6 => 20.74, 15 => 20.74,
                    7 => 13.83, 14 => 13.83,
                    8 => 9.88,  13 => 9.88,
                    9 => 8.3,   12 => 8.3,
                    10 => 7.68, 11 => 7.68
                ];
                $multiplier = isset($sumMultipliers[$sum]) ? $sumMultipliers[$sum] : 1.92;
            }
        }
        // 2. Big/Small bets (gameType == '2')
        elseif ($gt === '2') {
            // 'H' (Haute) = Big, 'B' (Bas) = Small
            if ($st === 'H' && $sum >= 11) {
                $won = true;
                $multiplier = 1.92;
            } elseif ($st === 'B' && $sum <= 10) {
                $won = true;
                $multiplier = 1.92;
            }
        }
        // 3. Odd/Even bets (gameType == '3')
        elseif ($gt === '3') {
            // 'O' (Impair/Odd) = Odd, 'E' (Pair/Even) = Even
            if ($st === 'O' && $sum % 2 !== 0) {
                $won = true;
                $multiplier = 1.92;
            } elseif ($st === 'E' && $sum % 2 === 0) {
                $won = true;
                $multiplier = 1.92;
            }
        }
        // 4. 2 Same Double bets (gameType == '4' or '6')
        elseif ($gt === '4' || $gt === '6') {
            // e.g. selectType '11', '22', etc.
            if ($isTwoSame) {
                $rolled_double = ($digits[0] === $digits[1]) ? ($digits[0].$digits[0]) : ($digits[1].$digits[1]);
                if ($st === $rolled_double) {
                    $won = true;
                    $multiplier = 13.83;
                }
            }
        }
        // 5. 3 Same Single (specific triple) bets (gameType == '7')
        elseif ($gt === '7') {
            // e.g. selectType '111', '222', etc.
            if ($isAllSame && $st === $premium) {
                $won = true;
                $multiplier = 207.36;
            }
        }
        // 6. 3 Same Double (any triple) bets (gameType == '8')
        elseif ($gt === '8') {
            if ($isAllSame) {
                $won = true;
                $multiplier = 34.56;
            }
        }
        // 7. 3 Consecutive bets (gameType == '10')
        elseif ($gt === '10') {
            if ($isConsecutive) {
                $won = true;
                $multiplier = 8.64;
            }
        }
        // 8. 3 Different bets (gameType == '9')
        elseif ($gt === '9') {
            // Check if all selected digits are different and exist in the rolled result
            $selected_digits = str_split((string)$st);
            sort($selected_digits);
            $sorted_selected = implode('', $selected_digits);
            if ($isAllDifferent && $sorted_selected === $sorted_premium) {
                $won = true;
                $multiplier = 34.56;
            }
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
