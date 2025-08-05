<?php

$host = 'hostname';
$db   = 'database_name'; 
$user = 'username';     
$pass = 'password'; 
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;


if ($page < 1) $page = 1;
if ($limit < 1 || $limit > 50) $limit = 10;

$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM transaction_records");
$countStmt->execute();
$countStmt->bind_result($total);
$countStmt->fetch();
$countStmt->close();

$stmt = $conn->prepare("SELECT serial_number, amount, received_date, received_through, description, status FROM transaction_records ORDER BY received_date DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$stmt->bind_result($serial_number, $amount, $received_date, $received_through, $description, $status);

$transactions = [];
while ($stmt->fetch()) {
    $transactions[] = [
        'serial_number'    => $serial_number,
        'amount'           => $amount,
        'received_date'    => $received_date,
        'received_through' => $received_through,
        'description'      => $description,
        'status'           => $status
    ];
}

$totalPages = ceil($total / $limit);
$hasNextPage = $page < $totalPages;
$hasPrevPage = $page > 1;

$response = [
    'data' => $transactions,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_records' => $total,
        'limit' => $limit,
        'has_next_page' => $hasNextPage,
        'has_prev_page' => $hasPrevPage
    ]
];

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);

$stmt->close();
$conn->close();
?>