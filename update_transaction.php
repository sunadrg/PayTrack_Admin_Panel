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
    
    // Validate required fields
    $required_fields = ['original_serial', 'serial_number', 'amount', 'received_through', 'description', 'status'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Validate amount
    if (!is_numeric($input['amount']) || $input['amount'] <= 0) {
        throw new Exception('Invalid amount');
    }
    
    // Validate status
    $valid_statuses = ['pending', 'completed', 'failed'];
    if (!in_array($input['status'], $valid_statuses)) {
        throw new Exception('Invalid status');
    }
    
    // Check if the new serial number already exists (if changed)
    if ($input['original_serial'] !== $input['serial_number']) {
        $checkStmt = $conn->prepare("SELECT serial_number FROM transaction_records WHERE serial_number = ? AND serial_number != ?");
        $checkStmt->bind_param("ss", $input['serial_number'], $input['original_serial']);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            throw new Exception('Serial number already exists');
        }
        $checkStmt->close();
    }
    
    // Update the record
    $stmt = $conn->prepare("UPDATE transaction_records SET serial_number = ?, amount = ?, received_through = ?, description = ?, status = ? WHERE serial_number = ?");
    $stmt->bind_param("sdssss", 
        $input['serial_number'],
        $input['amount'],
        $input['received_through'],
        $input['description'],
        $input['status'],
        $input['original_serial']
    );
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Transaction updated successfully'
            ]);
        } else {
            throw new Exception('Record not found or no changes made');
        }
    } else {
        throw new Exception('Failed to update record: ' . $stmt->error);
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