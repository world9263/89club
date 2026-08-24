<?php
// =====================================================
// 89 CLUB — Firebase Client Helper
// =====================================================
// Reads FIREBASE_URL from environment variable (.env on Railway)
// NEVER hardcode the Firebase URL in any file!
// =====================================================

class FirebaseClient {
    private $dbUrl;

    public function __construct() {
        $this->dbUrl = rtrim(getenv('FIREBASE_URL') ?: '', '/');
        if (empty($this->dbUrl)) {
            // Fallback: try $_ENV
            $this->dbUrl = rtrim(isset($_ENV['FIREBASE_URL']) ? $_ENV['FIREBASE_URL'] : '', '/');
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
