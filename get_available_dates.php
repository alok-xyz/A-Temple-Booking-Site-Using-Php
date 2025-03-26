<?php
require_once 'config/config.php';

header('Content-Type: application/json');

if(!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

try {
    $tour_id = $_POST['tour_id'];
    
    // Get available dates with slots
    $sql = "SELECT date, slots_available 
            FROM available_dates 
            WHERE tour_id = ? 
            AND date >= CURDATE() 
            AND slots_available > 0 
            ORDER BY date ASC 
            LIMIT 30";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tour_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $dates = [];
    while($row = $result->fetch_assoc()) {
        $dates[] = [
            'date' => $row['date'],
            'formatted_date' => date('D, M j, Y', strtotime($row['date'])),
            'slots' => $row['slots_available']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'dates' => $dates
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch available dates'
    ]);
}
?> 