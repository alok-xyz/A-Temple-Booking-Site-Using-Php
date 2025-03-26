<?php
require_once 'config/config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tour_id = $_POST['tour_id'];
    $date = $_POST['date'];
    
    $sql = "SELECT slots_available FROM available_dates 
            WHERE tour_id = ? AND date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $tour_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'available' => $row['slots_available'] > 0,
            'slots' => $row['slots_available']
        ]);
    } else {
        echo json_encode([
            'available' => false,
            'slots' => 0
        ]);
    }
} 