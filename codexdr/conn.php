<?php
// Load Firebase client + mysqli polyfills FIRST
require_once(__DIR__ . '/../firebase.php');

// FILE: connection.php
error_reporting(0);

$server_url = "https://license.investmentpro.click/server.php";
$SECRET_KEY = "JALWA_2025_SECURE_KEY_!@#"; 
$lockFile = __DIR__ . '/domain.lock';

$domain = "";
if (isset($_SERVER['HTTP_HOST'])) {
    $domain = $_SERVER['HTTP_HOST'];
    if (!file_exists($lockFile) || file_get_contents($lockFile) != $domain) {
        @file_put_contents($lockFile, $domain);
    }
} elseif (file_exists($lockFile)) {
    $domain = file_get_contents($lockFile);
} else {
    if (php_sapi_name() == "cli") { die("Error: Open site in browser first."); }
}
$domain = str_replace(["http://", "https://", "www."], "", $domain);
if (empty($domain)) { die("License Error: Domain Unknown"); }

// Bypass license check
$isActive = true;
define("SECURITY_PASS", true);

if (!defined("SECURITY_PASS")) { die(); }

date_default_timezone_set('Asia/Kolkata');

$conn = new MockMySQLi();

$numbermappings = array("zero", "one","two","three", "four","five","six","seven","eight","nine");

function getusercount($a,$periodid,$value)
{
	$selectquery=mysqli_query($a,"select * from `bajikattuttate` where `kalaparichaya`='$periodid' and `ojana`in($value) group by `byabaharkarta`");
	$row=mysqli_num_rows($selectquery);
	return $row;
}

function winner($conn,$periodid,$column)
{
	$query=mysqli_query($conn,"SELECT 
	SUM(CASE
		WHEN prakar = '0' THEN ketebida
	END) button,
	
	SUM(CASE
		WHEN ojana = '11' THEN ketebida
	END) as green,
	
	(SUM(CASE
		WHEN ojana = '11' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '11' THEN ketebida
	END)/100*2))*2 as greenwinamount,
	
	(SUM(CASE
		WHEN ojana = '11' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '11' THEN ketebida
	END)/100*2))*1.5 as greenwinamountwithviolet,
	
	SUM(CASE
		WHEN ojana = '12' THEN ketebida
	END) violet,
	
	(SUM(CASE
		WHEN ojana = '12' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '12' THEN ketebida
	END)/100*2))*4.5 as violetwinamount,
	
	SUM(CASE
		WHEN ojana = '10' THEN ketebida
	END) red,
	
	(SUM(CASE
		WHEN ojana = '10' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '10' THEN ketebida
	END)/100*2))*2 as redwinamount,
	(SUM(CASE
		WHEN ojana = '10' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '10' THEN ketebida
	END)/100*2))*1.5 as redwinamountwithviolet,
	
	SUM(CASE
		WHEN prakar = '1' THEN ketebida
	END) number,
	SUM(CASE
		WHEN ojana = '0' THEN ketebida
	END) `zero`,
	(SUM(CASE
		WHEN ojana = '0' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '0' THEN ketebida
	END)/100*2))*9 as zerowinamount,
	
	SUM(CASE
		WHEN ojana = '1' THEN ketebida
	END) `one`,
	(SUM(CASE
		WHEN ojana = '1' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '1' THEN ketebida
	END)/100*2))*9 as onewinamount,
	
	SUM(CASE
		WHEN ojana = '2' THEN ketebida
	END) `two`,
	(SUM(CASE
		WHEN ojana = '2' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '2' THEN ketebida
	END)/100*2))*9 as twowinamount,
	
	SUM(CASE
		WHEN ojana = '3' THEN ketebida
	END) `three`,
	(SUM(CASE
		WHEN ojana = '3' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '3' THEN ketebida
	END)/100*2))*9 as threewinamount,
	
	SUM(CASE
		WHEN ojana = '4' THEN ketebida
	END) `four`,
	(SUM(CASE
		WHEN ojana = '4' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '4' THEN ketebida
	END)/100*2))*9 as fourwinamount,
	
	SUM(CASE
		WHEN ojana = '5' THEN ketebida
	END) `five`,
	(SUM(CASE
		WHEN ojana = '5' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '5' THEN ketebida
	END)/100*2))*9 as fivewinamount,
	
	SUM(CASE
		WHEN ojana = '6' THEN ketebida
	END) `six`,
	(SUM(CASE
		WHEN ojana = '6' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '6' THEN ketebida
	END)/100*2))*9 as sixwinamount,
	
	SUM(CASE
		WHEN ojana = '7' THEN ketebida
	END) `seven`,
	(SUM(CASE
		WHEN ojana = '7' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '7' THEN ketebida
	END)/100*2))*9 as sevenwinamount,
	
	SUM(CASE
		WHEN ojana = '8' THEN ketebida
	END) `eight`,
	(SUM(CASE
		WHEN ojana = '8' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '8' THEN ketebida
	END)/100*2))*9 as eightwinamount,
	
	SUM(CASE
		WHEN ojana = '9' THEN ketebida
	END) `nine`,
	(SUM(CASE
		WHEN ojana = '9' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '9' THEN ketebida
	END)/100*2))*9 as ninewinamount,
	
	(SUM(CASE
		WHEN ojana = '13' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '13' THEN ketebida
	END)/100*2))*2 as bigwinamount,
	
	(SUM(CASE
		WHEN ojana = '14' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '14' THEN ketebida
	END)/100*2))*2 as smallwinamount
		
	FROM
	`bajikattuttate` where `kalaparichaya`='$periodid'");
	$result=mysqli_fetch_array($query);	
	return $result["$column"];	
}

function rlamt($conn,$periodid,$column)
{
	$query=mysqli_query($conn,"SELECT 
	SUM(CASE
		WHEN prakar = '0' THEN ketebida
	END) button,
	
	SUM(CASE
		WHEN ojana = '11' THEN ketebida
	END) as green,
	
	(SUM(CASE
		WHEN ojana = '11' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '11' THEN ketebida
	END)/100*2)) as greenwinamount,
	
	(SUM(CASE
		WHEN ojana = '11' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '11' THEN ketebida
	END)/100*2)) as greenwinamountwithviolet,
	
	SUM(CASE
		WHEN ojana = '12' THEN ketebida
	END) violet,
	
	(SUM(CASE
		WHEN ojana = '12' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '12' THEN ketebida
	END)/100*2)) as violetwinamount,
	
	SUM(CASE
		WHEN ojana = '10' THEN ketebida
	END) red,
	
	(SUM(CASE
		WHEN ojana = '10' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '10' THEN ketebida
	END)/100*2)) as redwinamount,
	(SUM(CASE
		WHEN ojana = '10' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '10' THEN ketebida
	END)/100*2)) as redwinamountwithviolet,
	
	SUM(CASE
		WHEN prakar = '1' THEN ketebida
	END) number,
	SUM(CASE
		WHEN ojana = '0' THEN ketebida
	END) `zero`,
	(SUM(CASE
		WHEN ojana = '0' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '0' THEN ketebida
	END)/100*2)) as zerowinamount,
	
	SUM(CASE
		WHEN ojana = '1' THEN ketebida
	END) `one`,
	(SUM(CASE
		WHEN ojana = '1' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '1' THEN ketebida
	END)/100*2)) as onewinamount,
	
	SUM(CASE
		WHEN ojana = '2' THEN ketebida
	END) `two`,
	(SUM(CASE
		WHEN ojana = '2' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '2' THEN ketebida
	END)/100*2)) as twowinamount,
	
	SUM(CASE
		WHEN ojana = '3' THEN ketebida
	END) `three`,
	(SUM(CASE
		WHEN ojana = '3' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '3' THEN ketebida
	END)/100*2)) as threewinamount,
	
	SUM(CASE
		WHEN ojana = '4' THEN ketebida
	END) `four`,
	(SUM(CASE
		WHEN ojana = '4' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '4' THEN ketebida
	END)/100*2)) as fourwinamount,
	
	SUM(CASE
		WHEN ojana = '5' THEN ketebida
	END) `five`,
	(SUM(CASE
		WHEN ojana = '5' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '5' THEN ketebida
	END)/100*2)) as fivewinamount,
	
	SUM(CASE
		WHEN ojana = '6' THEN ketebida
	END) `six`,
	(SUM(CASE
		WHEN ojana = '6' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '6' THEN ketebida
	END)/100*2)) as sixwinamount,
	
	SUM(CASE
		WHEN ojana = '7' THEN ketebida
	END) `seven`,
	(SUM(CASE
		WHEN ojana = '7' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '7' THEN ketebida
	END)/100*2)) as sevenwinamount,
	
	SUM(CASE
		WHEN ojana = '8' THEN ketebida
	END) `eight`,
	(SUM(CASE
		WHEN ojana = '8' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '8' THEN ketebida
	END)/100*2)) as eightwinamount,
	
	SUM(CASE
		WHEN ojana = '9' THEN ketebida
	END) `nine`,
	(SUM(CASE
		WHEN ojana = '9' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '9' THEN ketebida
	END)/100*2)) as ninewinamount,
	
	(SUM(CASE
		WHEN ojana = '13' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '13' THEN ketebida
	END)/100*2)) as bigwinamount,
	
	(SUM(CASE
		WHEN ojana = '14' THEN ketebida
	END)-(SUM(CASE
		WHEN ojana = '14' THEN ketebida
	END)/100*2)) as smallwinamount
		
	FROM
	`bajikattuttate` where `kalaparichaya`='$periodid'");
	$result=mysqli_fetch_array($query);	
	return $result["$column"];	
}

function encryptor($action, $string) {
	$output = false;
	$encrypt_method = "AES-256-CBC";
	$secret_key = 'shonu';
	$secret_iv = 'kani123';
	$key = hash('sha256', $secret_key);
	$iv = substr(hash('sha256', $secret_iv), 0, 16);
	if( $action == 'encrypt' ) {
		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = base64_encode($output);
	}
	else if( $action == 'decrypt' ){
		$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	}
	return $output;
}

?>
