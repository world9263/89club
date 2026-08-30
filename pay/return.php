<?php
include("../serive/samparka.php");
global $firebase;

// Retrieve Order ID from GET parameters or fallback to browser cookie
$order_id = $_GET['order_id'] ?? $_GET['order_sn'] ?? $_GET['mchOrderNo'] ?? $_COOKIE['last_initiated_order'] ?? null;

if ($order_id) {
    $order_id = trim($order_id);
    
    // Fetch deposit details from Firebase
    $deposit = $firebase->get('deposits/' . $order_id);
    
    if ($deposit && isset($deposit['status']) && $deposit['status'] === 'initiated') {
        // Upgrade status to "request on gateway" so it appears on the admin panel
        $firebase->update('deposits/' . $order_id, [
            'status' => 'request on gateway',
            'returnedAt' => date('Y-m-d H:i:s')
        ]);
    }
}

// Redirect player back to their wallet recharge history page in the game
header("Location: https://" . $_SERVER['HTTP_HOST'] . "/#/wallet/RechargeHistory");
exit;
?>
