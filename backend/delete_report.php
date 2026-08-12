<?php

header('Content-Type: application/json');

include("db.php");


// Check request method
if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    echo json_encode(array(
        'success' => false,
        'message' => 'Invalid request method'
    ));

    exit;
}


// Get report ID
$id = isset($_POST['id']) ? $_POST['id'] : '';


// Validate ID
if ($id == '') {

    echo json_encode(array(
        'success' => false,
        'message' => 'Missing report ID'
    ));

    exit;
}


// Convert ID to integer
$id = intval($id);


// Delete report
$sql = "DELETE FROM reports WHERE id = $id";

$result = mysql_query($sql);


// Check result
if ($result) {

    echo json_encode(array(
        'success' => true,
        'message' => 'Report deleted successfully'
    ));

} else {

    echo json_encode(array(
        'success' => false,
        'message' => mysql_error()
    ));

}

?>