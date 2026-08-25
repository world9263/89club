<?php
$botToken = "8690061817:AAHl73PLbjwBV2hkE37seE6aE_YV7uzuz8A";
$webhookUrl = "https://89club-production.up.railway.app/codexdr/api/webapi/TelegramWebhook.php";

$url = "https://api.telegram.org/bot" . $botToken . "/setWebhook?url=" . urlencode($webhookUrl);
$response = file_get_contents($url);

echo "Response from Telegram: " . $response . "\n";
?>
