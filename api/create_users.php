<?php
// Creates admin and regular user with hashed passwords and assigns existing tasks with NULL user_id
require_once __DIR__ . '/db.php';

try {
    $pdo->beginTransaction();

    // Create admin if not exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['admin@example.com']);
    $admin = $stmt->fetchColumn();
    if(!$admin){
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
        $stmt->execute(['Admin','admin@example.com',$pass,'admin']);
        $admin = $pdo->lastInsertId();
    }

    // Create regular user if not exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['user@example.com']);
    $user = $stmt->fetchColumn();
    if(!$user){
        $pass = password_hash('user123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
        $stmt->execute(['Regular User','user@example.com',$pass,'user']);
        $user = $pdo->lastInsertId();
    }

    // Assign any tasks with NULL user_id to regular user
    $stmt = $pdo->prepare('UPDATE tasks SET user_id = ? WHERE user_id IS NULL');
    $stmt->execute([$user]);

    $pdo->commit();

    echo json_encode(['message'=>'users_created','admin_id'=>$admin,'user_id'=>$user]);
} catch(Exception $e){
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
