<?php
// FILE: connection.php (Fully Automatic + Redirect Fix)
error_reporting(0); // Production Mode

// 🔥 CONFIGURATION
$server_url = "https://license.investmentpro.click/server.php";
$SECRET_KEY = "JALWA_2025_SECURE_KEY_!@#"; 

// File jahan domain save hoga
$lockFile = __DIR__ . '/domain.lock';

// ====================================================
// 1. AUTOMATIC DOMAIN DETECTION (No Editing Needed)
// ====================================================
$domain = "";

if (isset($_SERVER['HTTP_HOST'])) {
    // 🌍 BROWSER MODE:
    // Jab koi site kholega, domain apne aap pakda jayega
    $domain = $_SERVER['HTTP_HOST'];
    
    // Future ke liye save kar lo (Cron ke liye)
    // Agar file nahi hai ya domain badal gaya hai, to update karo
    if (!file_exists($lockFile) || file_get_contents($lockFile) != $domain) {
        file_put_contents($lockFile, $domain);
    }
} 
elseif (file_exists($lockFile)) {
    // ⚙️ CRON MODE:
    // Browser ne jo file banayi thi, usse padho
    $domain = file_get_contents($lockFile);
} 
else {
    // 🛑 AGAR DONO FAIL HO GAYE:
    // Iska matlab site abhi tak kisi ne kholi nahi hai
    if (php_sapi_name() == "cli") {
        die("Error: Please open the website in a browser once to register the license.");
    }
}

// Safayi (www aur http hatao)
$domain = str_replace(["http://", "https://", "www."], "", $domain);

// Agar domain khali hai to aage mat badho
if (empty($domain)) { die("License Error: Domain Unknown"); }

// ====================================================
// 2. SERVER REQUEST (With Redirect & SSL Fix)
// ====================================================
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$server_url?check=$domain");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// ✅ Fix for "302 Found" & "Empty Response"
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

$response_raw = curl_exec($ch);
curl_close($ch);

// Clean JSON Response
$jsonStart = strpos($response_raw, '{');
$jsonEnd = strrpos($response_raw, '}');
if ($jsonStart !== false && $jsonEnd !== false) {
    $clean_json = substr($response_raw, $jsonStart, ($jsonEnd - $jsonStart) + 1);
    $response = json_decode($clean_json, true);
} else {
    $response = null;
}

// ====================================================
// 3. SECURITY CHECK (JWT Verification)
// ====================================================
$isActive = true;

if (isset($response['status']) && $response['status'] == 'success' && isset($response['token'])) {
    $tokenParts = explode('.', $response['token']);
    if (count($tokenParts) == 3) {
        $header = $tokenParts[0];
        $payload = $tokenParts[1];
        $sigReceived = $tokenParts[2];
        
        $sigCheck = hash_hmac('sha256', $header . "." . $payload, $SECRET_KEY, true);
        $sigCheckEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($sigCheck));

        if ($sigCheckEncoded === $sigReceived) {
            $isActive = true;
        }
    }
}

// ====================================================
// 4. FINAL ACTION
// ====================================================
if ($isActive) {
    define("SECURITY_PASS", true);
} 
else {
    // Cron job ko silent kill karo
    if (php_sapi_name() == "cli") { die(); }
    
    // Browser user ko error dikhao
    echo '<!DOCTYPE html><html><body style="background:#000;color:red;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:sans-serif;">
    <div style="text-align:center;padding:20px;border:1px solid #333;background:#111;border-radius:10px;">
        <h1 style="margin:0;">🚫 ACCESS DENIED</h1>
        <p style="color:#aaa;">License Invalid or Expired.</p>
        <code style="background:#222;padding:5px;color:#fff;">Domain: '.$domain.'</code>
    </div>
    </body></html>';
    die();
}

if (!defined("SECURITY_PASS")) { die(); }
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "clubgo_bot";
$password = "clubgo_bot"; 
$dbname = "clubgo_bot";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to handle and respond to errors
function handleError($message) {
    // Set the content type to HTML
    header('Content-Type: text/html; charset=utf-8');

    // Output the HTML for the error message
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f8d7da;
                color: #721c24;
                margin: 0;
                padding: 20px;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .error-container {
                background-color: #f5c6cb;
                border: 1px solid #f5c6cb;
                border-radius: 5px;
                padding: 20px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            .error-message {
                font-size: 18px;
                margin: 0;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            connected
        </div>
    </body>
    </html>
    ';
    exit();
}

// Get the current URL path
$request = $_SERVER['REQUEST_URI'];

// Define routes
if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($request, '/getUserBalance') !== false) {
    if (isset($_GET['userId'])) {
        $userId = $_GET['userId'];

        // Prepare SQL statement
        $stmt = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
        $stmt->bind_param("s", $userId);

        // Execute statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $balance = $row['motta'];

                // Return balance as JSON
                header('Content-Type: application/json');
                echo json_encode(["balance" => $balance]);
            } else {
                handleError("User not found");
            }
        } else {
            handleError("Failed to fetch balance");
        }

        $stmt->close();
    } else {
        handleError("Missing userId parameter");
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($request, '/bet') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['userId'];
    $betAmount = $data['betAmount'];
    $winloseAmount = $data['winloseAmount'];

    if (!$userId || $betAmount === null || $winloseAmount === null) {
        handleError('Missing parameter');
    }

    // Perform the bet logic
    $stmt = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    $stmt->bind_param("s", $userId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentBalance = $row['motta'];
            $newBalance = $currentBalance - $betAmount + $winloseAmount;

            $updateStmt = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
            $updateStmt->bind_param("ds", $newBalance, $userId);
            if ($updateStmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(["newBalance" => $newBalance]);
            } else {
                handleError('Failed to update balance');
            }

            $updateStmt->close();
        } else {
            handleError('User not found');
        }
    } else {
        handleError('Failed to fetch current balance');
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($request, '/sessionBet') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['userId'];
    $betAmount = $data['betAmount'];
    $winloseAmount = $data['winloseAmount'];
    $sessionId = $data['sessionId'];
    $type = $data['type'];

    if (!$userId || $betAmount === null || $winloseAmount === null || $sessionId === null || $type === null) {
        handleError('Missing parameter');
    }

    // Perform the session bet logic
    $stmt = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    $stmt->bind_param("s", $userId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentBalance = $row['motta'];
            $newBalance = $type == 1 ? $currentBalance - $betAmount : $currentBalance + $winloseAmount;

            $updateStmt = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
            $updateStmt->bind_param("ds", $newBalance, $userId);
            if ($updateStmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(["newBalance" => $newBalance]);
            } else {
                handleError('Failed to update balance');
            }

            $updateStmt->close();
        } else {
            handleError('User not found');
        }
    } else {
        handleError('Failed to fetch current balance');
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($request, '/cancelBet') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['userId'];
    $betAmount = $data['betAmount'];
    $round = $data['round'];

    if (!$userId || $betAmount === null || $round === null) {
        handleError('Missing parameter');
    }

    // Cancel the bet logic
    $stmt = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    $stmt->bind_param("s", $userId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentBalance = $row['motta'];
            $newBalance = $currentBalance + $betAmount; // Reverting the bet amount to the balance

            $updateStmt = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
            $updateStmt->bind_param("ds", $newBalance, $userId);
            if ($updateStmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(["newBalance" => $newBalance, "message" => "Bet canceled"]);
            } else {
                handleError('Failed to update balance');
            }

            $updateStmt->close();
        } else {
            handleError('User not found');
        }
    } else {
        handleError('Failed to fetch current balance');
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($request, '/cancelSessionBet') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['userId'];
    $betAmount = $data['betAmount'];
    $sessionId = $data['sessionId'];
    $type = $data['type'];

    if (!$userId || $betAmount === null || $sessionId === null || $type === null) {
        handleError('Missing parameter');
    }

    // Cancel the session bet logic
    $stmt = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    $stmt->bind_param("s", $userId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentBalance = $row['motta'];
            $newBalance = $currentBalance + $betAmount; // Reverting the bet amount to the balance

            $updateStmt = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
            $updateStmt->bind_param("ds", $newBalance, $userId);
            if ($updateStmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(["newBalance" => $newBalance, "message" => "Session bet canceled"]);
            } else {
                handleError('Failed to update balance');
            }

            $updateStmt->close();
        } else {
            handleError('User not found');
        }
    } else {
        handleError('Failed to fetch current balance');
    }

    $stmt->close();
} else {
    // Handle unsupported methods or endpoints
    handleError('Go To https://webghost.support and add this URL');
}

// Close the database connection
$conn->close();
?>
