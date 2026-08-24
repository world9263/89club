<?php
$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file = $_SERVER["DOCUMENT_ROOT"] . $uri;

if (file_exists($file) && !is_dir($file)) {
    return false; // serve the requested file as-is.
}

// Check if appending .php makes it exist
$php_file = $_SERVER["DOCUMENT_ROOT"] . $uri . ".php";
if (file_exists($php_file)) {
    $_SERVER["SCRIPT_FILENAME"] = $php_file;
    $_SERVER["SCRIPT_NAME"] = $uri . ".php";
    $_SERVER["PHP_SELF"] = $uri . ".php";
    include($php_file);
    exit();
}

return false;
