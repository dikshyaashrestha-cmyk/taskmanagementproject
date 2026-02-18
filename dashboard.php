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
    <title>Task Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar shadow-sm fixed-top">
        <div class="container-fluid d-flex align-items-center justify-content-between px-3">
            <div class="d-flex align-items-center gap-3">
                <button id="toggleSidebar" class="btn btn-sm btn-outline-light d-md-none"><i class="fa fa-bars"></i></button>
                <h5 class="m-0 text-white">Task Management System</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input id="searchInput" class="form-control form-control-sm d-none d-md-block" placeholder="Search tasks...">
                <?php $userName = htmlspecialchars($_SESSION['name'] ?? ''); $userEmail = htmlspecialchars($_SESSION['email'] ?? ''); $userId = $_SESSION['user_id'] ?? null; $userRole = htmlspecialchars($_SESSION['role'] ?? ''); ?>
                <span id="greeting" class="text-white">Hello, <?php echo $userName ?: 'User'; ?></span>
                <a href="#" id="loginBtn" class="btn btn-sm btn-outline-light ms-2 d-none">Login</a>
                <a href="#" id="registerBtn" class="btn btn-sm btn-outline-light ms-2 d-none">Register</a>
                <a href="auth/logout.php" id="logoutBtn" class="btn btn-sm btn-outline-light ms-2">Logout</a>
            </div>
        </div>
    </header>

    <div class="container-fluid" style="padding-top:70px">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link active text-white" href="#"><i class="fa fa-home me-2"></i>Dashboard</a></li>
                        <li class="nav-item mt-3 px-3 text-muted">Filters</li>
                        <li class="nav-item"><a class="nav-link text-white filter" href="#" data-status="all"><i class="fa fa-list me-2"></i>All Tasks</a></li>
                        <li class="nav-item"><a class="nav-link text-white filter" href="#" data-status="pending"><i class="fa fa-clock me-2"></i>Pending</a></li>
                        <li class="nav-item"><a class="nav-link text-white filter" href="#" data-status="in_progress"><i class="fa fa-spinner me-2"></i>In Progress</a></li>
                        <li class="nav-item"><a class="nav-link text-white filter" href="#" data-status="completed"><i class="fa fa-check me-2"></i>Completed</a></li>
                        <li class="nav-item mt-3 px-3 text-muted">Priority</li>
                        <li class="nav-item"><a class="nav-link text-white filter" href="#" data-priority="high"><i class="fa fa-bolt text-danger me-2"></i>High</a></li>
                        <li class="nav-item"><a class="nav-link text-white filter" href="#" data-priority="medium"><i class="fa fa-exclamation-triangle text-warning me-2"></i>Medium</a></li>
                        <li class="nav-item"><a class="nav-link text-white filter" href="#" data-priority="low"><i class="fa fa-leaf text-success me-2"></i>Low</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Dashboard</h2>
                    <div>
                        <button id="addTaskBtn" class="btn btn-primary"><i class="fa fa-plus me-2"></i>Add Task</button>
                    </div>
                </div>

                <div id="stats" class="row gy-3 mb-4">
                    <!-- Summary cards inserted by JS -->
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tasksTable">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- tasks populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add / Edit Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="taskForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="taskId">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input id="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea id="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select id="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select id="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input id="due_date" type="date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Login / Register modals are provided via separate pages or JS modals -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Provide initial session info to client so UI can hydrate immediately
        window.__CURRENT_USER = {
            id: <?php echo $userId ? json_encode((int)$userId) : 'null'; ?>,
            name: <?php echo json_encode($userName); ?>,
            email: <?php echo json_encode($userEmail); ?>,
            role: <?php echo json_encode($userRole); ?>
        };
    </script>
    <script src="js/app.js"></script>
</body>
</html>
