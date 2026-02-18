<?php
header('Content-Type: application/json; charset=utf-8');

// Test database connection
require_once __DIR__ . '/api/db.php';

$response = [];

// Test 1: Database connection
try {
    $test = $pdo->query('SELECT 1');
    $response['database_connection'] = 'OK';
} catch (Exception $e) {
    $response['database_connection'] = 'FAILED: ' . $e->getMessage();
}

// Test 2: Check if tables exist
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    $response['tables_exist'] = count($tables) > 0;
    $response['table_list'] = array_column($tables, 'Tables_in_task_management');
} catch (Exception $e) {
    $response['tables_check'] = 'FAILED: ' . $e->getMessage();
}

// Test 3: Check users count
try {
    $userCount = $pdo->query('SELECT COUNT(*) as cnt FROM users')->fetch();
    $response['user_count'] = $userCount['cnt'];
} catch (Exception $e) {
    $response['user_count_error'] = $e->getMessage();
}

// Test 4: Check tasks count
try {
    $taskCount = $pdo->query('SELECT COUNT(*) as cnt FROM tasks')->fetch();
    $response['task_count'] = $taskCount['cnt'];
} catch (Exception $e) {
    $response['task_count_error'] = $e->getMessage();
}

// Test 5: List all tasks
try {
    $stmt = $pdo->query('SELECT * FROM tasks LIMIT 5');
    $tasks = $stmt->fetchAll();
    $response['sample_tasks'] = $tasks;
} catch (Exception $e) {
    $response['tasks_error'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
