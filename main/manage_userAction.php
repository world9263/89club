<?php
include('conn.php');

if(isset($_POST['type'])){
	global $firebase;
	$allUsers = $firebase->get('users');
	$targetMobile = null;
	
	if ($allUsers) {
	    foreach ($allUsers as $mobile => $user) {
	        if (isset($user['id']) && $user['id'] == $_POST['id']) {
	            $targetMobile = $mobile;
	            break;
	        }
	    }
	}

	if ($targetMobile) {
		if($_POST['type']=='chk'){
			$user = $firebase->get('users/' . $targetMobile);
			$user['status'] = 0;
			$firebase->set('users/' . $targetMobile, $user);
			echo "1";
		}
		else if($_POST['type']=='unchk'){
			$user = $firebase->get('users/' . $targetMobile);
			$user['status'] = 1;
			$firebase->set('users/' . $targetMobile, $user);
			echo "1";
		}
		else if($_POST['type']=='delete'){	
			$firebase->delete('users/' . $targetMobile);
			echo "1";
		}	
	} else {
	    echo "0";
	}
}
?>
