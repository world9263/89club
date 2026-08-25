<?php
include "../../conn.php";
include "../../functions2.php";
include "../../5d_helper.php";

header('Content-Type: application/json');
echo json_encode([
    "code" => 1,
    "msg" => "Success",
    "data" => [],
    "serviceNowTime" => date('Y-m-d H:i:s'),
    "issueNumber" => strval(time())
]);
