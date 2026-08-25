<?php
include "../../conn.php";
include "../../functions2.php";
include "../../k3_helper.php";

header('Content-Type: application/json');
echo json_encode([
    "code" => 1,
    "msg" => "Success",
    "data" => [],
    "serviceNowTime" => date('Y-m-d H:i:s'),
    "issueNumber" => strval(time())
]);
