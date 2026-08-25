<?php
session_start();
include("conn.php");

if ($_SESSION['unohs'] == null) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $firebase;
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

    if (empty($id) || empty($remarks)) {
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
        exit;
    }

    $ticket = $firebase->get('support_tickets/' . $id);
    if ($ticket) {
        $ticket['remarks'] = $remarks;
        $ticket['status'] = 4;
        $firebase->set('support_tickets/' . $id, $ticket);
        echo json_encode(["success" => true, "status" => "success", "message" => "Remarks updated successfully"]);
    } else {
        echo json_encode(["success" => false, "status" => "error", "message" => "Failed to update remarks"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

?>
