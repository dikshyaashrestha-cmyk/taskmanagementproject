
<?php
// Database configuration
$servername = "localhost";   // usually 'localhost'
$username = "root";          // your MySQL username
$password = "";              // your MySQL password
$dbname = "task_management"; // your database name

// Create mysqli connection (kept for compatibility)
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // If mysqli fails, we'll still try PDO below; but log if needed.
}

// Create PDO connection used by API
try {
    $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    // Return JSON error if included by API
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}

// Ready: $pdo (PDO) and $conn (mysqli) are available
?>

