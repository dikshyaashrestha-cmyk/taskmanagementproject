<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../api/db.php';

try{
    $data = json_decode(file_get_contents('php://input'), true);
    if(!$data) $data = $_POST;
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    if(!$name || !$email || !$password){ http_response_code(400); echo json_encode(['error'=>'Missing fields']); exit; }
    // check exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?'); $stmt->execute([$email]);
    if($stmt->fetchColumn()){ http_response_code(409); echo json_encode(['error'=>'Email exists']); exit; }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
    $stmt->execute([$name,$email,$hash,'user']);
    echo json_encode(['success'=>true,'user_id'=>$pdo->lastInsertId()]);
}catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
