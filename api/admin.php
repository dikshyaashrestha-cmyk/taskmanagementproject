<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';
session_start();

// Check admin privilege
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$action = $_GET['action'] ?? 'list';

function getInput(){
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if(!$data) $data = $_POST;
    return $data;
}

try {
    // ============ USERS MANAGEMENT ============
    
    if($action === 'list_users'){
        $stmt = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if($action === 'add_user'){
        $data = getInput();
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['name'], $data['email'], $password_hash, $data['role'] ?? 'user']);
        $id = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch());
        exit;
    }

    if($action === 'update_user'){
        $data = getInput();
        
        if($data['password']){
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password_hash = ?, role = ? WHERE id = ?');
            $stmt->execute([$data['name'], $data['email'], $password_hash, $data['role'], $data['id']]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
            $stmt->execute([$data['name'], $data['email'], $data['role'], $data['id']]);
        }
        
        echo json_encode(['success' => true]);
        exit;
    }

    if($action === 'delete_user'){
        $data = getInput();
        
        // Don't allow deleting self
        if($data['id'] == $_SESSION['user_id']){
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete your own account']);
            exit;
        }
        
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ============ TASKS MANAGEMENT ============

    if($action === 'list_tasks'){
        $stmt = $pdo->query('
            SELECT t.*, u.name as user_name 
            FROM tasks t 
            LEFT JOIN users u ON t.user_id = u.id 
            ORDER BY t.created_at DESC
        ');
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if($action === 'add_task'){
        $data = getInput();
        
        $stmt = $pdo->prepare('INSERT INTO tasks (user_id, title, description, status, priority, due_date, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$data['user_id'], $data['title'], $data['description'] ?? null, $data['status'] ?? 'pending', $data['priority'] ?? 'low', $data['due_date'] ?? null]);
        $id = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch());
        exit;
    }

    if($action === 'update_task'){
        $data = getInput();
        
        $stmt = $pdo->prepare('UPDATE tasks SET user_id = ?, title = ?, description = ?, status = ?, priority = ?, due_date = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$data['user_id'], $data['title'], $data['description'] ?? null, $data['status'], $data['priority'], $data['due_date'] ?? null, $data['id']]);
        
        echo json_encode(['success' => true]);
        exit;
    }

    if($action === 'delete_task'){
        $data = getInput();
        
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
