<?php 
// JWT Authentication Functions — Clean, no license bloat
error_reporting(0);

if (!defined("SECURITY_PASS")) { define("SECURITY_PASS", true); }

	function generate_jwt($headers, $payload, $secret = 'bdgshonuuncensored') {
		$headers_encoded = base64url_encode(json_encode($headers));
		
		$payload_encoded = base64url_encode(json_encode($payload));
		
		$signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
		$signature_encoded = base64url_encode($signature);
		
		$jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
		
		return $jwt;
	}
	
	function is_jwt_valid($jwt, $secret = 'bdgshonuuncensored') {
		
		$res = [
			'status' => '',
			'payload' => '',
		];

		$tokenParts = explode('.', $jwt);
		if (count($tokenParts) !== 3) {
			$res['status'] = 'Failed';
			return json_encode($res);
		}

		$header = base64url_decode($tokenParts[0]);
		$payload = base64url_decode($tokenParts[1]);
		$signature_provided = $tokenParts[2];

		$base64_url_header = base64url_encode($header);
		$base64_url_payload = base64url_encode($payload);
		$signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);
		$base64_url_signature = base64url_encode($signature);

		$is_signature_valid = ($base64_url_signature === $signature_provided);
		
		if (!$is_signature_valid) {
			$res['status']='Failed';
		} else {
			$res['status']='Success';
			$res['payload']=json_decode($payload, 1);
		}
		
		$allvalue = json_encode($res);
		
		return $allvalue;
	}
	
	function base64url_encode($str) {
		return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}

	function base64url_decode($str) {
		return base64_decode(strtr($str, '-_', '+/'));
	}
	
?>
