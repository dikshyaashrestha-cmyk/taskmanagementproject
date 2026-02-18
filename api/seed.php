<?php
// Simple seeder: inserts sample tasks if table is empty.
require_once __DIR__ . '/db.php';

try {
    // seed users first
    $userCount = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if($userCount == 0){
        $ustmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
        $uadminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $uuserPass = password_hash('user123', PASSWORD_DEFAULT);
        $ustmt->execute(['Admin','admin@example.com',$uadminPass,'admin']);
        $adminId = $pdo->lastInsertId();
        $ustmt->execute(['Regular User','user@example.com',$uuserPass,'user']);
        $userId = $pdo->lastInsertId();
    } else {
        // fetch existing users for IDs
        $row = $pdo->query("SELECT id, role FROM users ORDER BY id ASC")->fetchAll();
        $adminId = $row[0]['id'] ?? null;
        $userId = $row[1]['id'] ?? $row[0]['id'] ?? null;
    }

    // seed tasks if none exist
    $count = $pdo->query('SELECT COUNT(*) AS c FROM tasks')->fetchColumn();
    if($count > 0){
        echo json_encode(['message'=>'Already seeded','tasks'=>$count,'users'=>$userCount]);
        exit;
    }

    $tasks = [
        ['user_id'=>$adminId,'title'=>'Finish project proposal','description'=>'Write and submit project proposal to supervisor.','status'=>'pending','priority'=>'high','due_date'=>date('Y-m-d', strtotime('+3 days'))],
        ['user_id'=>$userId,'title'=>'Prepare presentation','description'=>'Create slides for mid-term presentation.','status'=>'in_progress','priority'=>'medium','due_date'=>date('Y-m-d', strtotime('+7 days'))],
        ['user_id'=>$userId,'title'=>'Refactor codebase','description'=>'Clean up CSS and JS, add comments.','status'=>'pending','priority'=>'low','due_date'=>null],
        ['user_id'=>$adminId,'title'=>'Submit assignment','description'=>'Submit lab assignment on portal.','status'=>'completed','priority'=>'low','due_date'=>date('Y-m-d', strtotime('-2 days'))],
    ];

    $stmt = $pdo->prepare('INSERT INTO tasks (user_id,title,description,status,priority,due_date,created_at) VALUES (?,?,?,?,?,?,NOW())');
    foreach($tasks as $t){
        $stmt->execute([$t['user_id'],$t['title'],$t['description'],$t['status'],$t['priority'],$t['due_date']]);
    }

    echo json_encode(['message'=>'Seeded','inserted'=>count($tasks),'admin_id'=>$adminId,'user_id'=>$userId]);
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
