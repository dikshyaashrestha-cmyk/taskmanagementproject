<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../api/db.php';
session_start();
try{
    $data = json_decode(file_get_contents('php://input'), true);
    if(!$data) $data = $_POST;
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    if(!$email || !$password){ http_response_code(400); echo json_encode(['error'=>'Missing fields']); exit; }
    $stmt = $pdo->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email = ?');
    $stmt->execute([$email]); $user = $stmt->fetch();
    if(!$user || !password_verify($password, $user['password_hash'])){ http_response_code(401); echo json_encode(['error'=>'Invalid credentials']); exit; }
    // set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    echo json_encode(['success'=>true,'user'=>['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']]]);
}catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
