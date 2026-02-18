<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';
session_start();
if(isset($_SESSION['user_id'])){
    echo json_encode(['logged_in'=>true,'user'=>['id'=>$_SESSION['user_id'],'name'=>$_SESSION['name'],'email'=>$_SESSION['email'],'role'=>$_SESSION['role']]]);
} else {
    echo json_encode(['logged_in'=>false]);
}
