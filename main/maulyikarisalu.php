<?php 
	session_start();
	include("conn.php");
	global $firebase;
	
	$adid = htmlspecialchars($_POST['username']);
	$psad = htmlspecialchars($_POST['password']);
	
	$admin = $firebase->get('admin_users/' . $adid);
	
	if($admin && $admin['guptapada'] == md5($psad) && $admin['sthiti'] == '1'){
		$_SESSION['unohs'] = $admin['unohs'];
		$_SESSION['nirvahaka_hesaru'] = $admin['nirvahaka_hesaru'];
		
		header("location:dashboard.php");
		exit;
	}
	else{
		header("location:index.php?err=ture");
		exit;
	}
?>
