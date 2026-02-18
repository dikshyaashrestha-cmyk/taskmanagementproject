<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Admin Panel Test</h1>
    
    <div class="alert alert-info">
        <h4>Session Status:</h4>
        <pre><?php 
        if(isset($_SESSION['user_id'])) {
            echo "User ID: " . $_SESSION['user_id'] . "\n";
            echo "Role: " . $_SESSION['role'] . "\n";
            echo "Name: " . $_SESSION['name'] . "\n";
            if($_SESSION['role'] === 'admin') {
                echo "\n✅ You are an ADMIN - You can access /admin.php";
            } else {
                echo "\n❌ You are not an admin - Admin panel is restricted";
            }
        } else {
            echo "❌ NOT LOGGED IN\n";
            echo "Please login first to test admin panel";
        }
        ?></pre>
    </div>

    <div class="alert alert-warning">
        <h4>Next Steps:</h4>
        <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" class="btn btn-primary">Go to Admin Panel</a>
        <?php else: ?>
            <a href="index.php" class="btn btn-primary">Login as Admin</a>
            <p class="mt-2">Use credentials: admin@example.com / admin123</p>
        <?php endif; ?>
    </div>

    <div class="alert alert-secondary">
        <h4>Check Browser Console:</h4>
        <p>Press F12 and go to Console tab to see any error messages</p>
        <button class="btn btn-info" onclick="testAPI()">Test Admin API</button>
        <pre id="apiResult"></pre>
    </div>
</div>

<script>
async function testAPI() {
    try {
        const res = await fetch('api/admin.php?action=list_users');
        const data = await res.json();
        document.getElementById('apiResult').textContent = JSON.stringify(data, null, 2);
    } catch(err) {
        document.getElementById('apiResult').textContent = 'ERROR: ' + err.message;
    }
}
</script>
</body>
</html>
