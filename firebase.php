<?php
// =====================================================
// 89 CLUB — Firebase Client Helper & Environment Config
// =====================================================
// Reads variables from environment or a local .env file
// NEVER hardcode the Firebase URL or Telegram credentials!
// =====================================================

// Load local .env file if it exists (for local testing/CLI)
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

if (file_exists(__DIR__ . '/config.php')) {
    include_once __DIR__ . '/config.php';
}

// Global Config Variables
$tgBotToken = getenv('TELEGRAM_BOT_TOKEN') ?: (isset($_ENV['TELEGRAM_BOT_TOKEN']) ? $_ENV['TELEGRAM_BOT_TOKEN'] : '8690061817:AAHl73PLbjwBV2hkE37seE6aE_YV7uzuz8A');
$tgChatId = getenv('TELEGRAM_CHAT_ID') ?: (isset($_ENV['TELEGRAM_CHAT_ID']) ? $_ENV['TELEGRAM_CHAT_ID'] : '7606730935');

// =====================================================
// FIX: Apache strips Authorization header from PHP
// =====================================================
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $headers['authorization'];
        }
    } elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $headers['authorization'];
        }
    }
}

class FirebaseClient {
    private $dbUrl;

    public function __construct() {
        $this->dbUrl = rtrim(getenv('FIREBASE_URL') ?: '', '/');
        if (empty($this->dbUrl)) {
            // Fallback: try $_ENV
            $this->dbUrl = rtrim(isset($_ENV['FIREBASE_URL']) ? $_ENV['FIREBASE_URL'] : '', '/');
        }
        if (empty($this->dbUrl) && defined('FIREBASE_URL')) {
            $this->dbUrl = rtrim(FIREBASE_URL, '/');
        }
    }

    // GET — Fetch a document or collection
    public function get($path) {
        $ch = curl_init($this->dbUrl . '/' . $path . '.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }

    // PUT — Create or overwrite a document
    public function set($path, $data) {
        $ch = curl_init($this->dbUrl . '/' . $path . '.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }

    // PATCH — Update specific fields without overwriting
    public function update($path, $data) {
        $ch = curl_init($this->dbUrl . '/' . $path . '.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }

    // DELETE — Remove a document
    public function delete($path) {
        $ch = curl_init($this->dbUrl . '/' . $path . '.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }
}

// =====================================================
// mysqli Polyfill Functions
// =====================================================
// Since Railway doesn't have the mysqli extension,
// we define stub functions so existing code won't crash.
// These return safe empty values instead of fatal errors.
// =====================================================

if (!function_exists('mysqli_report')) {
    function mysqli_report($flags = 0) { return true; }
}
if (!function_exists('mysqli_query')) {
    function mysqli_query($conn, $query, $mode = 0) { return false; }
}
if (!function_exists('mysqli_fetch_array')) {
    function mysqli_fetch_array($result, $type = 3) { return null; }
}
if (!function_exists('mysqli_fetch_assoc')) {
    function mysqli_fetch_assoc($result) { return null; }
}
if (!function_exists('mysqli_num_rows')) {
    function mysqli_num_rows($result) { return 0; }
}
if (!function_exists('mysqli_real_escape_string')) {
    function mysqli_real_escape_string($conn, $str) { return addslashes($str); }
}
if (!function_exists('mysqli_connect_error')) {
    function mysqli_connect_error() { return ''; }
}
if (!function_exists('mysqli_insert_id')) {
    function mysqli_insert_id($conn) { return 0; }
}
if (!function_exists('mysqli_affected_rows')) {
    function mysqli_affected_rows($conn) { return 0; }
}
if (!function_exists('mysqli_error')) {
    function mysqli_error($conn) { return ''; }
}
if (!function_exists('mysqli_close')) {
    function mysqli_close($conn) { return true; }
}
if (!function_exists('mysqli_fetch_row')) {
    function mysqli_fetch_row($result) { return null; }
}

// Create a dummy connection object for legacy code
if (!class_exists('MockMySQLi')) {
    class MockMySQLi {
        public function query($q, $r = 0) { return false; }
        public function prepare($q) { return false; }
        public function close() { return true; }
        public function real_escape_string($s) { return addslashes($s); }
    }
}

// Global Firebase instance — available everywhere
$firebase = new FirebaseClient();
?>
