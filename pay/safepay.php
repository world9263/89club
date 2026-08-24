<?php 
    header('Content-type: text/plain; charset=utf-8');
    include ("../serive/samparka.php");
?>

<?php 
if(isset($_GET['amount'])){
    $ramt = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['amount']));
} else{
    $ramt = 0;
}

// Format the amount to ensure two decimal places
$dot_pos = strpos($ramt, '.');
if ($dot_pos === false) {
    $ramt = $ramt . '.00';
} else {
    $after_dot = substr($ramt, $dot_pos + 1);
    $after_dot_length = strlen($after_dot);
    if ($after_dot_length > 2) {
        $after_dot = substr($after_dot, 0, 2);
        $ramt = substr($ramt, 0, $dot_pos + 1) . $after_dot;
    } elseif ($after_dot_length < 2) {
        $zeros_to_add = 2 - $after_dot_length;
        $ramt = $ramt . str_repeat('0', $zeros_to_add);
    }
}

$date = date("Ymd");
$time = time();
$serial = $date . $time . rand(100000, 999900);

$tyid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['tyid']));
$uid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['uid']));
$sign = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['sign']));
$urlInfo = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['urlInfo']));

// Check if `uid` exists in the `demo` table
$demoQuery = "SELECT 1 FROM demo WHERE balakedara = '$uid'";
$demoResult = $conn->query($demoQuery);

if ($demoResult->num_rows > 0) {
    // If uid exists in demo table, insert into `thevani` table and update `motta`
    $createdate = date("Y-m-d H:i:s");
    
    // Insert into `thevani`
    $insertQuery = "
        INSERT INTO `thevani` (`balakedara`, `motta`, `dharavahi`, `mula`, `ullekha`, `duravani`, `ekikrtapavati`, `dinankavannuracisi`, `madari`, `pavatiaidi`, `sthiti`) 
        VALUES ('$uid', '$ramt', '$serial', 'IndianPay', 'N/A', 'N/A', 'N/A', '$createdate', '1005', '2', '1')
    ";
    $conn->query($insertQuery);

    // Update `motta` field in `shonu_kaichila`
    $updateQuery = "
        UPDATE `shonu_kaichila`
        SET `motta` = `motta` + $ramt
        WHERE `balakedara` = '$uid'
    ";
    $conn->query($updateQuery);

    // Redirect to recharge history
    header('Location: https://89club-production.up.railway.app/#/main');
    exit;
}

// If not found in `demo` table, proceed with the rest of the existing logic

$res = [
    'code' => 405,
    'message' => 'Illegal access!',
];
if (isset($_GET['tyid']) && isset($_GET['amount']) && isset($_GET['uid']) && isset($_GET['sign']) && isset($_GET['urlInfo'])) {
    $userId = $uid;
    $userPhoto = '1';

    $numquery = "SELECT mobile, codechorkamukala
        FROM shonu_subjects
        WHERE id = ".$userId;
    $numresult = $conn->query($numquery);
    $numarr = mysqli_fetch_array($numresult);

    $userName = '91'.$numarr['mobile'];
    $nickName = $numarr['codechorkamukala'];

    $creaquery = "SELECT createdate
        FROM shonu_subjects
        WHERE id = ".$userId;
    $crearesult = $conn->query($creaquery);
    $creaarr = mysqli_fetch_array($crearesult);

    $knbdstr = '{"userId":'.$userId.',"userPhoto":"'.$userPhoto.'","userName":'.$userName.',"nickName":"'.$nickName.'","createdate":"'.$creaarr['createdate'].'"}';
    $shonusign = strtoupper(hash('sha256', $knbdstr));

    $urlarr = explode (",", $urlInfo);
    $theirurl = $urlarr[0];
    $myurl = 'https://89club-production.up.railway.app';

    if($shonusign == $sign && $theirurl == $myurl){

        $orderid = $serial;
        $amount = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['amount']));
        $name = 'TestName';
        $email = 'info@91clubgo.site';
        $mobile = $numarr['mobile'];
        $remark = 'remark';
        $type = 2;
        $notify_url = "https://89club-production.up.railway.app/pay/spwebhook.php";

        if (!$ramt || !$serial) {
            die("Error: Amount or order ID not provided.");
        }
$apiUrl = "https://api.pnsafepay.com/gateway.aspx";
$merchantNumber = "1084227"; 
$secretKey = "cece4f8da6616231e8c5464319306b7e"; 
$data = [
    "currency" => "INR",  
    "mer_no" => $merchantNumber,   
    "method" => "trade.create",  
    "order_amount" => $amount,  
    "order_no" => $orderid,   
    "payemail" => "testuser@email.com",  
    "payname" => "Test User",  
    "payphone" => "9876543210",  
    "paytypecode" => "11003", 
    "returnurl" => $notify_url, 
];
$dataForSignature = array_filter($data, function($value) {
    return $value !== null && $value !== ''; 
});
ksort($dataForSignature);
$queryString = urldecode(http_build_query($dataForSignature));

$signatureString = $queryString . $secretKey;


$data['sign'] = md5($signatureString);

$jsonData = json_encode($data);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
$response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "Error: " . curl_error($ch);
        } else {
            // Decode the JSON response
            $responseData = json_decode($response, true);

            // Check if the response contains the payment URL
            if ($responseData && isset($responseData['order_data'])) {
                $amt = $amount;
                $srl = $serial;
                $source = 'SG-pay';
                $ref_num = $un['order_id'];					
                $emailQ = mysqli_query($conn , "SELECT mobile FROM `shonu_subjects` WHERE `id` = '".$uid."'");
                $emailA = mysqli_fetch_array($emailQ);
                $email = $emailA['mobile'];
                $upi = 'IndianPayUPI';
                $createdate = date("Y-m-d H:i:s");

                // Insert payment record into `thevani`
                $deposit1 = mysqli_query($conn, "INSERT INTO `thevani`(`balakedara`, `motta`, `dharavahi`, `mula`, `ullekha`, `duravani`, `ekikrtapavati`, `dinankavannuracisi`, `madari`, `pavatiaidi`, `sthiti`) 
                VALUES('$uid', '$amount', '$srl', '$source','$ref_num', '$email', '$upi', '$createdate', '1005', '2', '0')");
                
                header('Location: ' . $responseData['order_data']);
                exit;
            } else {
                // If there's an error, display an appropriate message
                echo "Error: Unable to process payment.";
                var_dump($response);
            }
        }

        curl_close($ch);

    } else {
        $res['code'] = 10000;
        $res['success'] = 'false';
        $res['message'] = 'Sorry, The system is busy, please try again later!';

        header('Content-Type: text/html; charset=utf-8');
        http_response_code(200);
        echo json_encode($res);
    }
}
	else {
		header('Content-Type: application/json; charset=utf-8');
		http_response_code(200);
		echo json_encode($res);	
	}
?>
