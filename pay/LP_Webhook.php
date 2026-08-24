<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Location: https://89club-production.up.railway.app/#/wallet/RechargeHistory');
    exit;
}

include "../serive/samparka.php";
$config      = require __DIR__ . '/LP_Config.php';
$secretKey = $config['secretKey'];

$DEBUG_MODE = true;
function logError($msg) {
    global $DEBUG_MODE;
    if ($DEBUG_MODE) {
        file_put_contents('LP_Webhook.debug.log', date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
    }
}

$raw      = file_get_contents('php://input');
$payload  = json_decode($raw, true);
logError('Raw input: ' . $raw);

$statusRaw = strtolower(trim($payload['status'] ?? ''));
logError("Raw status value: [" . var_export($payload['status'] ?? null, true) . "], normalized: '{$statusRaw}'");

$data     = $payload;
if (empty($data['sign'])) {
    logError("missing sign");
    echo 'fail(sign missing)';
    exit;
}
$received = $data['sign'];
unset($data['sign']);

$data = array_filter($data, function($v) { return $v !== null && $v !== ''; });
ksort($data);
$qs   = http_build_query($data) . '&secret=' . $secretKey;
$calc = strtoupper(md5($qs));

if (!hash_equals($calc, $received)) {
    logError("Signature mismatch: got {$received}, expected {$calc}");
    echo 'fail(sign mismatch)';
    exit;
}

if ($statusRaw === 'success') {
    $orderId = $payload['orderId'];
    logError(">>> Entering success block for order {$orderId}");

    $selectSql  = "
        SELECT motta, balakedara
          FROM thevani
         WHERE ullekha = '{$orderId}'
           AND sthiti    = '0'
    ";
    logError("Running SELECT: {$selectSql}");
    $chk = mysqli_query($conn, $selectSql);
    if (! $chk) {
        logError("SELECT failed: " . mysqli_error($conn));
    } else {
        $num = mysqli_num_rows($chk);
        logError("SELECT returned {$num} row(s)");
    }

    if ($chk && $num > 0) {
        $row = mysqli_fetch_assoc($chk);
        logError("Fetched row: " . json_encode($row));

        $update1Sql = "
            UPDATE shonu_kaichila
               SET motta = ROUND(motta + '{$row['motta']}', 2)
             WHERE balakedara = '{$row['balakedara']}'
        ";
        logError("Running UPDATE1: {$update1Sql}");
        $res1 = mysqli_query($conn, $update1Sql);
        if (! $res1) {
            logError("UPDATE1 failed: " . mysqli_error($conn));
        } else {
            $aff1 = mysqli_affected_rows($conn);
            logError("UPDATE1 affected {$aff1} row(s)");
        }

        $update2Sql = "
            UPDATE thevani
               SET sthiti = '1'
             WHERE ullekha = '{$orderId}'
        ";
        logError("Running UPDATE2: {$update2Sql}");
        $res2 = mysqli_query($conn, $update2Sql);
        if (! $res2) {
            logError("UPDATE2 failed: " . mysqli_error($conn));
        } else {
            $aff2 = mysqli_affected_rows($conn);
            logError("UPDATE2 affected {$aff2} row(s)");
        }
    } else {
        logError("No rows to process for order {$orderId}");
    }
} else {
    logError("Skipping processing: status is '{$statusRaw}'");
}

logError("Signature: got {$received}, expected {$calc}");
logError("echo success");
echo 'success';
exit;
