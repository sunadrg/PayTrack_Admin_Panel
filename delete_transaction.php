<?php
header('Content-Type: application/json');

// Database configuration
$host = 'hostname';
$db   = 'database_name'; 
$user = 'username';     
$pass = 'password'; 

try {
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate serial number
    if (empty($input['serial_number'])) {
        throw new Exception('Serial number is required');
    }
    
    // Check if record exists
    $checkStmt = $conn->prepare("SELECT serial_number FROM transaction_records WHERE serial_number = ?");
    $checkStmt->bind_param("s", $input['serial_number']);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows === 0) {
        throw new Exception('Record not found');
    }
    $checkStmt->close();
    
    // Delete the record
    $stmt = $conn->prepare("DELETE FROM transaction_records WHERE serial_number = ?");
    $stmt->bind_param("s", $input['serial_number']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
        } else {
            throw new Exception('Failed to delete record');
        }
    } else {
        throw new Exception('Failed to delete record: ' . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?> 