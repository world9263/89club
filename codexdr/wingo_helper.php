<?php
// =====================================================
// 89 CLUB — WinGo Game Helper (Firebase)
// =====================================================
// Shared logic for period calculation, result generation,
// bet settlement. NO MySQL, NO cron needed.
// Results are generated on-demand (lazy evaluation).
// =====================================================

/**
 * Calculate current period info for a given typeId
 * Period format: YYYYMMDD1000TXXXX (T=typeId, XXXX=sequence)
 */
function wingo_get_current_period($typeId) {
    date_default_timezone_set("Asia/Kolkata");
    $now = time();
    $hours = (int)date('H', $now);
    $minutes = (int)date('i', $now);
    $seconds = (int)date('s', $now);
    $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
    
    switch ($typeId) {
        case 1: $intervalSec = 60; $typeChar = '1'; break;    // 1 min
        case 2: $intervalSec = 180; $typeChar = '2'; break;   // 3 min
        case 3: $intervalSec = 300; $typeChar = '3'; break;   // 5 min
        case 4: $intervalSec = 30; $typeChar = '5'; break;    // 30 sec -> uses 5
        default: $intervalSec = 60; $typeChar = '1';
    }
    
    $sequence = (int)floor($totalSeconds / $intervalSec) + 1;
    $elapsedInPeriod = $totalSeconds % $intervalSec;
    
    $dateStr = date('Ymd', $now);
    $periodId = $dateStr . "1000" . $typeChar . sprintf("%04d", $sequence);
    
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

/**
 * Get color string for a winning number
 */
function wingo_get_color($number) {
    $number = (int)$number;
    switch ($number) {
        case 0: return 'red,violet';
        case 5: return 'green,violet';
        case 1: case 3: case 7: case 9: return 'green';
        case 2: case 4: case 6: case 8: return 'red';
        default: return 'red';
    }
}

/**
 * Generate a 5-digit premium number with winning digit at end
 */
function wingo_generate_premium($winningDigit) {
    $digits = '';
    for ($i = 0; $i < 4; $i++) {
        $digits .= rand(1, 9);
    }
    $digits .= $winningDigit;
    return $digits;
}

/**
 * Generate result for a completed period (lazy evaluation)
 * Checks if result already exists, if not, generates one.
 * Uses house-optimal algorithm when bets exist.
 */
function wingo_generate_result($firebase, $typeId, $periodId) {
    $typeId = ($typeId == 4) ? 5 : $typeId;
    $fbTypeKey = 'wingo_t' . $typeId;
    
    // Check if result already exists
    $existing = $firebase->get('game_results/' . $fbTypeKey . '/' . $periodId);
    if ($existing != null) {
        return $existing;
    }
    
    // Check for admin override
    $override = $firebase->get('admin_overrides/wingo_t' . $typeId);
    if ($override != null && isset($override['number']) && isset($override['active']) && $override['active'] == true) {
        $winningDigit = (int)$override['number'];
        // Reset override
        $firebase->update('admin_overrides/wingo_t' . $typeId, ['active' => false]);
    } else {
        // Check if any bets exist for this period
        $bets = $firebase->get('game_bets/' . $fbTypeKey . '/' . $periodId);
        
        if ($bets != null && is_array($bets) && count($bets) > 0) {
            // House-optimal: find the number with minimum payout
            $winningDigit = wingo_calculate_house_optimal($bets);
        } else {
            // No bets: pure random
            $winningDigit = rand(0, 9);
        }
    }
    
    $color = wingo_get_color($winningDigit);
    $premium = wingo_generate_premium($winningDigit);
    
    $result = [
        'periodId' => $periodId,
        'number' => $winningDigit,
        'color' => $color,
        'premium' => $premium,
        'createdAt' => date('Y-m-d H:i:s'),
        'type' => 'wingo'
    ];
    
    // Save result
    $firebase->set('game_results/' . $fbTypeKey . '/' . $periodId, $result);
    
    // Settle bets
    if ($bets != null && is_array($bets)) {
        wingo_settle_bets($firebase, $typeId, $periodId, $winningDigit, $premium, $bets);
    }
    
    // Cleanup: keep only last 50 results
    wingo_cleanup_old_results($firebase, $fbTypeKey);
    
    return $result;
}

/**
 * House-optimal algorithm: choose the digit that costs the house least
 */
function wingo_calculate_house_optimal($bets) {
    // Aggregate bets by selectType
    $betTotals = array_fill(0, 15, 0); // 0-9 numbers, 10=red, 11=green, 12=violet, 13=big, 14=small
    
    foreach ($bets as $bet) {
        if (!isset($bet['selectType']) || !isset($bet['contractAmount'])) continue;
        $st = (int)$bet['selectType'];
        $amt = (float)$bet['contractAmount'];
        if ($st >= 0 && $st <= 14) {
            $betTotals[$st] += $amt;
        }
    }
    
    // Calculate payout for each possible winning digit
    $payouts = [];
    for ($d = 0; $d <= 9; $d++) {
        $payout = $betTotals[$d] * 9; // Direct number bet: 9x
        
        // Color bets
        if ($d == 0) {
            $payout += $betTotals[10] * 1.5; // Red gets 1.5x for 0
            $payout += $betTotals[12] * 4.5; // Violet gets 4.5x
            $payout += $betTotals[14] * 2;   // Small gets 2x
        } elseif ($d == 5) {
            $payout += $betTotals[11] * 1.5; // Green gets 1.5x for 5
            $payout += $betTotals[12] * 4.5; // Violet gets 4.5x
            $payout += $betTotals[13] * 2;   // Big gets 2x
        } elseif (in_array($d, [1, 3, 7, 9])) {
            $payout += $betTotals[11] * 2;   // Green gets 2x
            if ($d <= 4) $payout += $betTotals[14] * 2; // Small
            else $payout += $betTotals[13] * 2;          // Big
        } else { // 2, 4, 6, 8
            $payout += $betTotals[10] * 2;   // Red gets 2x
            if ($d <= 4) $payout += $betTotals[14] * 2; // Small
            else $payout += $betTotals[13] * 2;          // Big
        }
        
        $payouts[$d] = $payout;
    }
    
    // Return digit with minimum payout
    return array_search(min($payouts), $payouts);
}

/**
 * Settle all bets for a completed period
 */
function wingo_settle_bets($firebase, $typeId, $periodId, $winningDigit, $premium, $bets) {
    $typeId = ($typeId == 4) ? 5 : $typeId;
    $fbTypeKey = 'wingo_t' . $typeId;
    $color = wingo_get_color($winningDigit);
    $isBig = $winningDigit >= 5;
    $isSmall = $winningDigit <= 4;
    $isRed = in_array($winningDigit, [0, 2, 4, 6, 8]);
    $isGreen = in_array($winningDigit, [1, 3, 5, 7, 9]);
    $isViolet = in_array($winningDigit, [0, 5]);
    
    $userPayouts = []; // Track total payouts per user
    
    foreach ($bets as $betKey => $bet) {
        if (!isset($bet['selectType']) || !isset($bet['contractAmount'])) continue;
        
        $st = (int)$bet['selectType'];
        $contractAmt = (float)$bet['contractAmount'];
        $userId = $bet['userId'];
        $won = false;
        $multiplier = 0;
        
        // Check if bet wins
        if ($st >= 0 && $st <= 9 && $st == $winningDigit) {
            $won = true; $multiplier = 9;
        } elseif ($st == 10 && $isRed) { // Red bet
            $multiplier = ($winningDigit == 0) ? 1.5 : 2;
            $won = true;
        } elseif ($st == 11 && $isGreen) { // Green bet
            $multiplier = ($winningDigit == 5) ? 1.5 : 2;
            $won = true;
        } elseif ($st == 12 && $isViolet) { // Violet bet
            $won = true; $multiplier = 4.5;
        } elseif ($st == 13 && $isBig) { // Big bet
            $won = true; $multiplier = 2;
        } elseif ($st == 14 && $isSmall) { // Small bet
            $won = true; $multiplier = 2;
        }
        
        $winAmount = $won ? round($contractAmt * $multiplier, 2) : 0;
        $status = $won ? 'win' : 'lose';
        
        // Update bet record
        $firebase->update('game_bets/' . $fbTypeKey . '/' . $periodId . '/' . $betKey, [
            'status' => $status,
            'resultNumber' => $winningDigit,
            'premium' => $premium,
            'winAmount' => $winAmount
        ]);
        
        // Track payouts per user
        if ($won && $winAmount > 0) {
            if (!isset($userPayouts[$userId])) $userPayouts[$userId] = 0;
            $userPayouts[$userId] += $winAmount;
        }
    }
    
    // Credit winnings to user wallets
    foreach ($userPayouts as $mobile => $payout) {
        $user = $firebase->get('users/' . $mobile);
        if ($user != null) {
            $currentBalance = isset($user['motta']) ? (float)$user['motta'] : 0;
            $newBalance = round($currentBalance + $payout, 2);
            $firebase->update('users/' . $mobile, ['motta' => $newBalance]);
        }
    }
}

/**
 * Cleanup: keep only last 10 results, delete older ones
 */
function wingo_cleanup_old_results($firebase, $fbTypeKey) {
    $allResults = $firebase->get('game_results/' . $fbTypeKey);
    if ($allResults && is_array($allResults) && count($allResults) > 10) {
        // Sort by periodId (they're date-based so lexicographic sort works)
        ksort($allResults);
        $keys = array_keys($allResults);
        $toDelete = count($keys) - 10;
        for ($i = 0; $i < $toDelete; $i++) {
            $firebase->delete('game_results/' . $fbTypeKey . '/' . $keys[$i]);
        }
    }
}

/**
 * Ensure results exist for recently completed periods
 * Generates any missing results for the last N periods
 */
function wingo_ensure_recent_results($firebase, $typeId, $count = 10) {
    // Only generate/ensure up to 10 periods (history list only shows 10 anyway)
    $count = min($count, 10);
    
    $current = wingo_get_current_period($typeId);
    $currentSeq = $current['sequence'];
    $dateStr = date('Ymd');
    $typeChar = ($typeId == 4) ? '5' : (string)$typeId;
    $typeIdMapped = ($typeId == 4) ? 5 : $typeId;
    $fbTypeKey = 'wingo_t' . $typeIdMapped;
    
    // Fetch all existing results and bets for this type at once (2 HTTP requests total)
    $existingResults = $firebase->get('game_results/' . $fbTypeKey) ?: [];
    $allBets = $firebase->get('game_bets/' . $fbTypeKey) ?: [];
    
    $results = [];
    $updates = [];
    $hasNewResults = false;
    
    for ($i = 1; $i <= $count && ($currentSeq - $i) >= 1; $i++) {
        $seq = $currentSeq - $i;
        $pastPeriodId = $dateStr . "1000" . $typeChar . sprintf("%04d", $seq);
        
        if (isset($existingResults[$pastPeriodId])) {
            $results[] = $existingResults[$pastPeriodId];
        } else {
            // Generate missing result
            // Check for admin override (only check once per generation run)
            static $override = null;
            static $overrideChecked = false;
            if (!$overrideChecked) {
                $override = $firebase->get('admin_overrides/wingo_t' . $typeIdMapped);
                $overrideChecked = true;
            }
            
            if ($override != null && isset($override['number']) && isset($override['active']) && $override['active'] == true && $i == 1) {
                $winningDigit = (int)$override['number'];
                // Reset override
                $firebase->update('admin_overrides/wingo_t' . $typeIdMapped, ['active' => false]);
                $override = null; // Clear static cache
            } else {
                // Check if any bets exist for this period
                $bets = isset($allBets[$pastPeriodId]) ? $allBets[$pastPeriodId] : null;
                if ($bets != null && is_array($bets) && count($bets) > 0) {
                    $winningDigit = wingo_calculate_house_optimal($bets);
                } else {
                    $winningDigit = rand(0, 9);
                }
            }
            
            $color = wingo_get_color($winningDigit);
            $premium = wingo_generate_premium($winningDigit);
            
            $result = [
                'periodId' => $pastPeriodId,
                'number' => $winningDigit,
                'color' => $color,
                'premium' => $premium,
                'createdAt' => date('Y-m-d H:i:s'),
                'type' => 'wingo'
            ];
            
            $updates[$pastPeriodId] = $result;
            $results[] = $result;
            $hasNewResults = true;
            
            // Settle bets for this period if any exist
            $bets = isset($allBets[$pastPeriodId]) ? $allBets[$pastPeriodId] : null;
            if ($bets != null && is_array($bets)) {
                wingo_settle_bets($firebase, $typeIdMapped, $pastPeriodId, $winningDigit, $premium, $bets);
            }
        }
    }
    
    // Save all new results in a single PATCH update
    if ($hasNewResults && !empty($updates)) {
        $firebase->update('game_results/' . $fbTypeKey, $updates);
        // Trigger clean up
        wingo_cleanup_old_results($firebase, $fbTypeKey);
    }
    
    return $results;
}

/**
 * Get payout multiplier description for selectType
 */
function wingo_select_type_name($selectType) {
    $map = [
        0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4',
        5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9',
        10 => 'red', 11 => 'green', 12 => 'violet',
        13 => 'big', 14 => 'small'
    ];
    return isset($map[$selectType]) ? $map[$selectType] : (string)$selectType;
}
?>
