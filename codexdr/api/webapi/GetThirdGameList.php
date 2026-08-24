<?php
header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");

// Get the raw POST data
$input = file_get_contents('php://input');
$payload = json_decode($input, true);

// Default response structure
$response = [
    "code" => 1, // Default error code
    "msg" => "Invalid Request",
    "data" => []
];

if (isset($payload['type'])) {
    $type = intval($payload['type']);

    // Map type values to JSON files
    $fileMap = [
        2 => './data/cq9.json',
        6 => './data/jdb.json',
        4 => './data/mg.json',
        18 => './data/jili.json',
      17 => './data/evolive.json',
      9 => './data/km.json',
      57 => './data/eg.json',
      101 => './data/idg.json',
      5 => './data/pg.json',
      41 => './data/G9.json',
       23 => './data/9g.json',
       42 => './data/sexy.json',
    ];

    if (array_key_exists($type, $fileMap)) {
        $filePath = $fileMap[$type];

        if (file_exists($filePath)) {
            $jsonData = file_get_contents($filePath);
            $response = [
                "code" => 0, // Success code
                "msg" => "Succeed",
                "msgCode" => 0,
                "serviceNowTime" => $shnunc,
                "data" => json_decode($jsonData, true, )
            ];
        } else {
            $response["msg"] = "File not found for type $type";
        }
    } else {
        $response["msg"] = "Invalid type provided";
    }
} else {
    $response["msg"] = "Type not specified in request";
}



// Additional Information
$response["code"] = 0;
$response["msg"] = "Succeed";
$response["msgCode"] = 0;
$response["serviceNowTime"] = $shnunc;
echo json_encode($response, JSON_UNESCAPED_SLASHES);
