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
    $required_fields = ['serial_number', 'amount', 'received_through', 'description', 'status'];
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
    
    // Check if serial number already exists
    $checkStmt = $conn->prepare("SELECT serial_number FROM transaction_records WHERE serial_number = ?");
    $checkStmt->bind_param("s", $input['serial_number']);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        throw new Exception('Serial number already exists');
    }
    $checkStmt->close();
    
    // Insert new record
    $stmt = $conn->prepare("INSERT INTO transaction_records (serial_number, amount, received_through, description, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsss", 
        $input['serial_number'],
        $input['amount'],
        $input['received_through'],
        $input['description'],
        $input['status']
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Transaction added successfully',
            'id' => $conn->insert_id
        ]);
    } else {
        throw new Exception('Failed to insert record: ' . $stmt->error);
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