<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banned Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 20px;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .message.info {
            padding: 15px;
            background-color: #e7f3fe;
            border-left: 5px solid #2196F3;
            color: #31708f;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<?php
include("conn.php");
global $firebase;

$allUsers = $firebase->get('users');
if (!$allUsers) $allUsers = [];

$bannedUsers = [];
foreach ($allUsers as $mobile => $user) {
    if (isset($user['status']) && $user['status'] == 0) {
        $bannedUsers[] = $user;
    }
}

if (count($bannedUsers) > 0) {
    echo "<table>";
    echo "<thead><tr><th>Mobile</th><th>User ID</th><th>Created At</th></tr></thead>";
    echo "<tbody>";
    foreach ($bannedUsers as $user) {
        $id = $user['id'] ?? '';
        $mobile = $user['mobile'] ?? '';
        $createdAt = $user['createdate'] ?? '';
        echo "<tr>
                <td>{$mobile}</td>
                <td>{$id}</td>
                <td>{$createdAt}</td>
              </tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<div class='message info'>No banned users found.</div>";
}
?>

</body>
</html>
