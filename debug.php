<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Debug - Task Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Debug Information</h1>
    <div class="alert alert-info">
        <h4>Session Data:</h4>
        <pre><?php echo json_encode($_SESSION, JSON_PRETTY_PRINT); ?></pre>
    </div>

    <div class="alert alert-warning">
        <h4>API Test - Fetching Tasks:</h4>
        <button class="btn btn-primary" onclick="testAPI()">Test API</button>
        <pre id="apiResult">Waiting...</pre>
    </div>

    <div class="alert alert-success">
        <h4>DOM Elements:</h4>
        <pre id="domResult">Checking...</pre>
    </div>
</div>

<script>
    console.log('Debug page loaded');

    async function testAPI() {
        try {
            const res = await fetch('api/tasks.php?action=list');
            const data = await res.json();
            document.getElementById('apiResult').textContent = JSON.stringify(data, null, 2);
            console.log('API result:', data);
        } catch(err) {
            document.getElementById('apiResult').textContent = 'ERROR: ' + err.message;
            console.error('API error:', err);
        }
    }

    // Check DOM elements
    const elements = {
        'taskModal': document.getElementById('taskModal'),
        'taskForm': document.getElementById('taskForm'),
        'tasksTable': document.getElementById('tasksTable'),
        'stats': document.getElementById('stats'),
        'toggleSidebar': document.getElementById('toggleSidebar'),
        'sidebar': document.getElementById('sidebar'),
        'addTaskBtn': document.getElementById('addTaskBtn'),
        'dashboardLink': document.getElementById('dashboardLink'),
        'searchInput': document.getElementById('searchInput'),
    };

    const domStatus = {};
    for (let key in elements) {
        domStatus[key] = elements[key] ? 'FOUND' : 'MISSING';
    }
    document.getElementById('domResult').textContent = JSON.stringify(domStatus, null, 2);
    console.log('DOM status:', domStatus);
</script>
</body>
</html>
