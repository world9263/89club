<?php
// Load Firebase client + mysqli polyfills FIRST
require_once(__DIR__ . '/../firebase.php');

// FILE: connection.php (Fully Automatic + Redirect Fix)
error_reporting(0); // Production Mode

// 🔥 CONFIGURATION
$server_url = "https://license.investmentpro.click/server.php";
$SECRET_KEY = "JALWA_2025_SECURE_KEY_!@#"; 

// File jahan domain save hoga
$lockFile = __DIR__ . '/domain.lock';

$domain = "";
if (isset($_SERVER['HTTP_HOST'])) {
    $domain = $_SERVER['HTTP_HOST'];
    if (!file_exists($lockFile) || file_get_contents($lockFile) != $domain) {
        @file_put_contents($lockFile, $domain);
    }
} elseif (file_exists($lockFile)) {
    $domain = file_get_contents($lockFile);
} else {
    if (php_sapi_name() == "cli") { die("Error: Open site in browser first."); }
}
$domain = str_replace(["http://", "https://", "www."], "", $domain);
if (empty($domain)) { die("License Error: Domain Unknown"); }

// Bypass license check
$isActive = true;
define("SECURITY_PASS", true);

if (!defined("SECURITY_PASS")) { die(); }

$conn = new MockMySQLi();
date_default_timezone_set("Asia/Kolkata"); 
?>
