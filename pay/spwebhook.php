<?php
include ("../serive/samparka.php");
$secretKey = "cece4f8da6616231e8c5464319306b7e"; 


$rawData = file_get_contents("php://input");


$data = json_decode($rawData, true);

// वर्तमान समय प्राप्त करें
$current_time = date("Y-m-d H:i:s");



// समय को JSON डेटा के बाहर प्रीफिक्स के रूप में जोड़ें
$finalOutput = $current_time . $data;

// डेटा को `sp.txt` में सेव करें
$file = 'sp.txt';
file_put_contents($file, $finalOutput);


// सिग्नेचर के लिए आवश्यक पैरामीटर को छांटें (null और sign वाले पैरामीटर को हटा दें)
$dataForSignature = array_filter($data, function($key) {
    return $key !== 'sign' && $key !== null; // सिग्नेचर और null वैल्यू वाले पैरामीटर को हटा दें
}, ARRAY_FILTER_USE_KEY);

// पैरामीटर को ASCII क्रम में छांटें
ksort($dataForSignature);

// Query String बनाएं
$queryString = urldecode(http_build_query($dataForSignature));

// सिग्नेचर स्ट्रिंग तैयार करें (key को अंत में जोड़ें)
$signatureString = $queryString . $secretKey;

// MD5 सिग्नेचर बनाएं
$generatedSign = md5($signatureString);

// प्राप्त सिग्नेचर के साथ तुलना करें
if ($generatedSign === $data['sign']) {
   
// Order processing logic
$mchOrderNo = $data['order_no']; // Order ID received in the callback

// Check if the order exists and is not already processed (status = 0)
$checkamt = mysqli_query($conn, "SELECT motta, balakedara FROM thevani WHERE dharavahi = '".$mchOrderNo."' AND sthiti = '0'");

if (!$checkamt) {
    logError("Database query error: " . mysqli_error($conn));
    echo json_encode([
        "message" => "fail(database error)",
        "status" => false,
    ]);
    exit;
}

$checkamtrow = mysqli_num_rows($checkamt);

if ($checkamtrow >= 1) {
    $checkamtar = mysqli_fetch_array($checkamt);
    $motta = $checkamtar['motta'];
    $shonuid = $checkamtar['balakedara'];

    // Update the user's balance
    $nabikarana = "UPDATE shonu_kaichila
                   SET motta = ROUND(motta + '".$motta."', 2)
                   WHERE balakedara = '".$shonuid."'";
    
    if (!$conn->query($nabikarana)) {
        logError("Database update error: " . mysqli_error($conn));
        echo json_encode([
            "message" => "fail(update error)",
            "status" => false,
        ]);
        exit;
    }

    // Update the order status to processed (status = 1)
    $sql2 = mysqli_query($conn, "UPDATE thevani SET sthiti = '1' WHERE dharavahi = '".$mchOrderNo."'");

    if (!$sql2) {
        logError("Database update error: " . mysqli_error($conn));
        echo json_encode([
            "message" => "fail(update error)",
            "status" => false,
        ]);
        exit;
    }
    echo "ok"; 
}
} else {
    // सिग्नेचर अमान्य है
    echo "Invalid signature"; // यह संकेत देता है कि सिग्नेचर अमान्य है
}
?>
