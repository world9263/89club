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
include("serive/samparka.php");


$samasye = "SELECT atadaaidi
	  FROM gelluonduhogu_trx10
	  ORDER BY kramasankhye DESC LIMIT 1";
	$samasyephalitansa = $conn->query($samasye);
	$samasyesreni = mysqli_fetch_array($samasyephalitansa);
	
	if($samasyesreni['atadaaidi'] != null){
		$gadhipathuli = "SELECT ojana, ketebida
		  FROM bajikattuttate_trx10
		  WHERE kalaparichaya = ".$samasyesreni['atadaaidi']."
		  ORDER BY parichaya DESC LIMIT 1";
		$gadhipathuliphala = $conn->query($gadhipathuli);
		$gadhipathulidhadi = mysqli_num_rows($gadhipathuliphala);

// Check if we fetched the `atadaaidi` value (kalaparichaya)
if ($samasyesreni && !empty($samasyesreni['atadaaidi'])) {
    $kalaparichaya = $samasyesreni['atadaaidi']; // Store the kalaparichaya value

    // Fetch the last block from the `gellaluhogiondu_trx` table
    $lastBlockQuery = "SELECT `bh` FROM `gellaluhogiondu_trx10` ORDER BY `shonu` DESC LIMIT 1";
    $lastBlockResult = $conn->query($lastBlockQuery);
    $lastBlockRow = $lastBlockResult->fetch_assoc();
    $lastBlock = !empty($lastBlockRow['bh']) ? $lastBlockRow['bh'] : 0; // Default to 0 if no block is found

    // Increment the block to fetch the next data
    $apiUrl = "https://liteqr.live/api.php?typeid=16";

// Initialize cURL
$ch = curl_init();

// cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout after 30 seconds

// Execute cURL request
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
} 
    $data = json_decode($response, true);

   
        $gameslist = $data['data']['data']['gameslist'];

  
        $latestGame = $gameslist[0] ?? null;

       
            $blockID = $latestGame['blockID'] ?? 'N/A';
            $issueNumber = $latestGame['issueNumber'] ?? 'N/A';
            $block = $latestGame['blockNumber'] ?? 'N/A';

            echo "Latest Result:\n";
            echo "Block ID: $blockID\n";
            echo "Issue Number: $issueNumber\n";
            echo "Block Number: $block\n";

    // API URL with the next block number (fetching from the new endpoint)
    $apiUrl = "https://apilist.tronscanapi.com/api/block?number=" . $block;

    // Fetch the data from the API
    $apiData = @file_get_contents($apiUrl);

    // Check if the API request was successful
    if ($apiData !== FALSE) {
        // Decode the JSON response
        $apiResponse = json_decode($apiData, true);

        // Check if `data` is available in the API response
        if (!empty($apiResponse['data'])) {
            $block = $block; // Block number
            $hash = $blockID;   // Block hash

            // Fetch the last numeric character from the hash
            $kadimesucyanka = null;
            for ($i = strlen($hash) - 1; $i >= 0; $i--) {
                if (is_numeric($hash[$i])) {
                    $kadimesucyanka = (int)$hash[$i];
                    break;
                }
            }

            // Default to 0 if no number is found in the hash
            if ($kadimesucyanka === null) {
                $kadimesucyanka = 0;
            }

            // Determine `banna` based on `kadimesucyanka`
            if ($kadimesucyanka == 0) {
                $banna = 'red,violet';
            } elseif ($kadimesucyanka == 5) {
                $banna = 'green,violet';
            } elseif (in_array($kadimesucyanka, [1, 3, 7, 9])) {
                $banna = 'green';
            } elseif (in_array($kadimesucyanka, [2, 4, 6, 8])) {
                $banna = 'red';
            } else {
                $banna = 'unknown';
            }

            // Current date and time
            $dinanka = date('Y-m-d H:i:s');
            $yadrcchikasanke[] = $kadimesucyanka;
			$yadrcchikasankhye = (int)implode('', $yadrcchikasanke);
            // Insert data into the `gellaluhogiondu_trx` table
            $tathya = mysqli_query(
                $conn,
                "INSERT INTO `gellaluhogiondu_trx10` 
                (`kalaparichaya`, `bele`, `phalitansa`, `banna`, `bh`, `hash`, `phalitansadaprakara`, `dinankavannuracisi`) 
                VALUES 
                ('" . $issueNumber . "', 
                 '" . $kadimesucyanka . "', 
                 '" . $kadimesucyanka . "', 
                 '" . $banna . "', 
                 '" . $block . "', 
                 '" . $hash . "', 
                 'uncensored', 
                 '" . $dinanka . "')"
            );
          
          

            if($kadimesucyanka == 0){
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '10' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '12'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '12' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '0'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '0' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '14' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
}
if($kadimesucyanka == 1){
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '11' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
          
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '1'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '1' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '14' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
}
if($kadimesucyanka == 2){
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '10' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
          
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '2'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '2' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '14' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
}
if($kadimesucyanka == 3){
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '11' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
          
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '3'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '3' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '14' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
}
if($kadimesucyanka == 4){
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '10' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
          
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '4'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '4' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '14' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
}
if($kadimesucyanka == 5){
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '11' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '12'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '12' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '5'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '5' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '13' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
}
if($kadimesucyanka == 6){
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '10' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
          
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '6'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '6' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '13' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
}
if($kadimesucyanka == 7){
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '11' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
          
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '7'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '7' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '13' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
}
if($kadimesucyanka == 8){
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '10' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
          
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '8'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '8' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '13' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
}
if($kadimesucyanka == 9){
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '11' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
          
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '9'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '9' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
  
  $nabikarana = "UPDATE bajikattuttate_trx10 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
  $conn->query($nabikarana);
  $nabikarana = "UPDATE shonu_kaichila
  INNER JOIN (
    SELECT byabaharkarta, SUM(sesabida) AS total_paid
    FROM bajikattuttate_trx10
    WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
    AND ojana = '13' 
    AND phalaphala ='gagner'
    GROUP BY byabaharkarta
  )  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
  SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
  ";
  $conn->query($nabikarana);
}
$nabikarana_dui = "UPDATE bajikattuttate_trx10 set ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."'";
$conn->query($nabikarana_dui);

                echo "Data inserted successfully!";
            } else {
                echo "Error inserting data: " . $conn->error;
            }
        } else {
            echo "No valid data found in API response.";
        }
    } else {
        echo "Error fetching data from API.";
    }
} else {
    echo "Error: No valid `atadaaidi` value found from the `gelluonduhogu` table.";
}
?>
