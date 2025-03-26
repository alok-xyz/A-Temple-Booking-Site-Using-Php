<?php
require_once '../../config/config.php';

if(!isLoggedIn() || !isAdmin()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $date = $_POST['date'];
    $slots = $_POST['slots'];
    
    // Update the date
    $sql = "UPDATE available_dates SET date = ?, slots_available = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $date, $slots, $id);
    
    if($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update date']);
    }
}
?> 