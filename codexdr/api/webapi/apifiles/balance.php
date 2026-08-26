<?php declare(strict_types=1);
date_default_timezone_set('Asia/Kolkata');
header('Content-Type: application/json; charset=utf-8');

include "../../../conn.php";
global $firebase;

if ($firebase == null) {
    http_response_code(500);
    echo json_encode(["error" => "Firebase connection failed"]);
    exit();
}

function generateUrl(string $fileName): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $scheme = rtrim($_SERVER['HTTP_X_FORWARDED_PROTO'], ':') . '://';
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = explode('/', trim($uri, '/'));
    if (count($parts) > 1) array_pop($parts);
    return $scheme . $host . '/' . implode('/', $parts) . '/' . $fileName;
}

function prepareWithdrawHandle(string $service, string $callbackUrl, string $userName) {
    $encodedUser = rawurlencode($userName);
    $encodedCb   = rawurlencode($callbackUrl);
    $url = "https://wuttsghdijsbbsh.yrehdjsfiafkjgkjgfsasc.yachts/sjcftrnicgfhfvfghdvhfvytyhthvrthtrhvrthrthrfrthtrhvrthvrthvcrthrthvctrvhtrhvrhcyhtyhrthr/{$service}?action=withdraw&callbackurl={$encodedCb}&userid={$encodedUser}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 2,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTPHEADER     => ['Accept: */*','User-Agent: wallet-merge/1.0'],
    ]);
    return $ch;
}

$jili      = generateUrl("jili.php");
$jdb       = generateUrl("jdb.php");
$jdbpro    = generateUrl("jdbpro.php");
$aio       = generateUrl("aio.php");
$evoslots  = generateUrl("evoslots.php");
$evo       = generateUrl("evo.php");
$cq9       = generateUrl("cq9.php");
$mt        = generateUrl("mt.php");
$ninesgame = generateUrl("ninesgame.php");

$userName = trim((string)($_GET['user'] ?? ''));
if ($userName === '') {
    http_response_code(400);
    echo json_encode(["error" => "Missing `user` parameter"]);
    exit();
}

$user = $firebase->get('users/' . $userName);
if ($user == null) {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
    exit();
}

$userBalance = isset($user['motta']) ? (float)$user['motta'] : 0.0;
$wllJdb      = isset($user['wll_jdb']) ? max(0.0, (float)$user['wll_jdb']) : 0.0;
$wllJili     = isset($user['wll_jili']) ? max(0.0, (float)$user['wll_jili']) : 0.0;

$totalThirdParty = $wllJdb + $wllJili;
$newBalance = $userBalance + $totalThirdParty;

$firebase->update('users/' . $userName, [
    'motta' => $newBalance,
    'wll_jdb' => 0.0,
    'wll_jili' => 0.0
]);

$handles = [];

echo json_encode([
    "status"              => "success",
    "oldBalance"          => $userBalance,
    "addedFromThirdParty" => $totalThirdParty,
    "newBalance"          => $newBalance,
    "message"             => "Balances merged and third-party withdrawals triggered in parallel."
], JSON_UNESCAPED_SLASHES);
?>
