<?php

header('Content-Type: application/json');

include("db.php");


// ==========================================
// CHECK REQUEST METHOD
// ==========================================

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    echo json_encode(array(
        'success' => false,
        'message' => 'Invalid request method'
    ));

    exit;
}


// ==========================================
// GET VALUES
// ==========================================

$id = isset($_POST['id']) ? $_POST['id'] : '';

$status = isset($_POST['status']) ? $_POST['status'] : '';


// ==========================================
// VALIDATE
// ==========================================

if ($id == '' || $status == '') {

    echo json_encode(array(
        'success' => false,
        'message' => 'Missing report ID or status'
    ));

    exit;
}


// ==========================================
// ALLOWED STATUSES
// ==========================================

$allowedStatuses = array(
    'Reported',
    'Acknowledged',
    'Fixed'
);


if (!in_array($status, $allowedStatuses)) {

    echo json_encode(array(
        'success' => false,
        'message' => 'Invalid status'
    ));

    exit;
}


// ==========================================
// CLEAN VALUES
// ==========================================

$id = intval($id);

$status = mysql_real_escape_string($status);


// ==========================================
// UPDATE DATABASE
// ==========================================

$sql = "UPDATE reports
        SET status = '$status'
        WHERE id = $id";


$result = mysql_query($sql);


// ==========================================
// CHECK RESULT
// ==========================================

if ($result) {

    echo json_encode(array(
        'success' => true,
        'message' => 'Report status updated successfully'
    ));

} else {

    echo json_encode(array(
        'success' => false,
        'message' => mysql_error()
    ));

}

?>