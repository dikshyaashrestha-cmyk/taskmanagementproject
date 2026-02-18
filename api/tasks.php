<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';
session_start();

$action = $_GET['action'] ?? 'list';

function getInput(){
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if(!$data) $data = $_POST;
    return $data;
}

try {
    if($action === 'list'){
        // If admin and no specific user requested, return all tasks
        $sessionUser = $_SESSION['user_id'] ?? null;
        $sessionRole = $_SESSION['role'] ?? null;
        if(isset($_GET['user_id'])){
            $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date IS NULL, due_date ASC, created_at DESC');
            $stmt->execute([$_GET['user_id']]);
            $tasks = $stmt->fetchAll();
        } else if($sessionUser && $sessionRole !== 'admin'){
            // non-admin: only their tasks
            $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date IS NULL, due_date ASC, created_at DESC');
            $stmt->execute([$sessionUser]);
            $tasks = $stmt->fetchAll();
        } else {
            // admin or anonymous: return all tasks
            $stmt = $pdo->query('SELECT * FROM tasks ORDER BY due_date IS NULL, due_date ASC, created_at DESC');
            $tasks = $stmt->fetchAll();
        }
        echo json_encode($tasks);
        exit;
    }

    $data = getInput();

    if($action === 'add'){
        $sessionUser = $_SESSION['user_id'] ?? null;
        if(!$sessionUser){
            http_response_code(401); echo json_encode(['error'=>'Not authenticated']); exit;
        }
        $stmt = $pdo->prepare('INSERT INTO tasks (user_id,title, description, status, priority, due_date, created_at) VALUES (?,?,?,?,?,?,NOW())');
        $stmt->execute([$sessionUser,$data['title'],$data['description'] ?? null,$data['status'] ?? 'pending',$data['priority'] ?? 'low',$data['due_date'] ?? null]);
        $id = $pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?'); $stmt->execute([$id]);
        echo json_encode($stmt->fetch());
        exit;
    }

    if($action === 'update'){
        $sessionUser = $_SESSION['user_id'] ?? null;
        if(!$sessionUser){ http_response_code(401); echo json_encode(['error'=>'Not authenticated']); exit; }
        // Only admin can change user_id; otherwise user_id stays the session user
        $sessionRole = $_SESSION['role'] ?? null;
        $userIdToSet = ($sessionRole === 'admin' && isset($data['user_id'])) ? $data['user_id'] : $sessionUser;
        $stmt = $pdo->prepare('UPDATE tasks SET user_id=?, title=?, description=?, status=?, priority=?, due_date=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$userIdToSet,$data['title'],$data['description'] ?? null,$data['status'],$data['priority'],$data['due_date'] ?? null,$data['id']]);
        echo json_encode(['success'=>true]); exit;
    }

    if($action === 'delete'){
        $sessionUser = $_SESSION['user_id'] ?? null;
        if(!$sessionUser){ http_response_code(401); echo json_encode(['error'=>'Not authenticated']); exit; }
        // Only allow delete if user owns the task or is admin
        $stmt = $pdo->prepare('SELECT user_id FROM tasks WHERE id = ?'); $stmt->execute([$data['id']]); $row = $stmt->fetch();
        $owner = $row['user_id'] ?? null;
        $sessionRole = $_SESSION['role'] ?? null;
        if($sessionRole !== 'admin' && $owner != $sessionUser){ http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id=?'); $stmt->execute([$data['id']]);
        echo json_encode(['success'=>true]); exit;
    }

    if($action === 'complete'){
        $sessionUser = $_SESSION['user_id'] ?? null;
        if(!$sessionUser){ http_response_code(401); echo json_encode(['error'=>'Not authenticated']); exit; }
        $stmt = $pdo->prepare('SELECT user_id FROM tasks WHERE id = ?'); $stmt->execute([$data['id']]); $row = $stmt->fetch();
        $owner = $row['user_id'] ?? null;
        $sessionRole = $_SESSION['role'] ?? null;
        if($sessionRole !== 'admin' && $owner != $sessionUser){ http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }
        $stmt = $pdo->prepare('UPDATE tasks SET status = "completed" WHERE id=?'); $stmt->execute([$data['id']]);
        echo json_encode(['success'=>true]); exit;
    }

    http_response_code(400);
    echo json_encode(['error'=>'Unknown action']);
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
