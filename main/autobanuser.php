<?php
include ("conn.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="images/favicon.png" />
    <title>Duplicate IP Checker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #9481ff;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .content {
            padding: 20px;
            font-size: 16px;
            line-height: 1.6;
        }
        .content pre {
            background: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            overflow-x: auto;
        }
        .footer {
            background-color: #9481ff;
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Duplicate IP Checker</h1>
        </div>
        <div class="content">
            <?php
            global $firebase;
            $allUsers = $firebase->get('users');
            if (!$allUsers) $allUsers = [];
            
            $ipCounts = [];
            foreach ($allUsers as $mobile => $user) {
                if (!empty($user['ishonup'])) {
                    $ip = $user['ishonup'];
                    if (!isset($ipCounts[$ip])) {
                        $ipCounts[$ip] = [];
                    }
                    $ipCounts[$ip][] = ['mobile' => $mobile, 'id' => $user['id'] ?? ''];
                }
            }

            $bannedAny = false;
            foreach ($ipCounts as $ip => $users) {
                if (count($users) > 1) {
                    $bannedAny = true;
                    echo "<pre>Duplicate IP: $ip\n";
                    echo "User IDs: ";
                    foreach ($users as $u) {
                        echo $u['id'] . " ";
                        $userObj = $firebase->get('users/' . $u['mobile']);
                        $userObj['status'] = 0;
                        $firebase->set('users/' . $u['mobile'], $userObj);
                    }
                    echo "</pre>";
                }
            }

            if ($bannedAny) {
                echo "<p>Users automatic banned with duplicate IPs.</p>";
            } else {
                echo "<p>No duplicate IPs found.</p>";
            }
            ?>
        </div>
        <div class="footer">
            &copy; 91 𝐂𝐋𝐔𝐁 Duplicate IP Checker
        </div>
    </div>
</body>
</html>
