<?php
// Landing page: if logged in redirect to dashboard, otherwise show login/register links
session_start();
if(isset($_SESSION['user_id'])){
    header('Location: index.html'); exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Task Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 text-center">
        <h1 class="mb-3">Task Management System</h1>
        <p class="lead">A simple student project to manage tasks. Login or register to continue.</p>
        <div class="d-flex justify-content-center gap-2 mt-4">
          <a href="login.php" class="btn btn-primary btn-lg">Login</a>
          <a href="register.php" class="btn btn-outline-primary btn-lg">Register</a>
        </div>
        <p class="mt-4 text-muted">Or open the <a href="index.html">Dashboard</a> (will require login for actions).</p>
      </div>
    </div>
  </div>
</body>
</html>
