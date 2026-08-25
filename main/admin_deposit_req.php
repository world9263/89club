<?php 
include("conn.php");
global $firebase;
if(isset($_POST['app'])){
    $uid = $_POST['uid'];
    $deposit = (float)$_POST['amount'];
	$sid = $_POST['sid'];
	$ref_num = $_POST['ref_num'];

    // Find the user by id
    $allUsers = $firebase->get('users') ?: [];
    $targetMobile = null;
    $refMobile = null;
    $userObj = null;
    foreach ($allUsers as $mobile => $user) {
        if (isset($user['id']) && $user['id'] == $uid) {
            $targetMobile = $mobile;
            $userObj = $user;
            break;
        }
    }
    
    if ($targetMobile && $userObj) {
        // Update user wallet
        $userObj['motta'] = (float)($userObj['motta'] ?? 0) + $deposit;
        $firebase->set('users/' . $targetMobile, $userObj);
        
        // Update transaction status
        $tx = $firebase->get('transactions/' . $sid);
        if ($tx) {
            $tx['status'] = 1;
            $firebase->set('transactions/' . $sid, $tx);
        }
        
        echo "1~".$sid;
    } else {
        echo "2";
    }
}

if(isset($_POST['rej'])){
	$sid = $_POST['sid'];
	
    $tx = $firebase->get('transactions/' . $sid);
    if ($tx) {
        $tx['status'] = 2;
        $firebase->set('transactions/' . $sid, $tx);
        echo "1~".$sid;
    } else {
        echo "2";
    }
}
?>
