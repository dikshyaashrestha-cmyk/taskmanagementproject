<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - Task Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar shadow-sm fixed-top">
        <div class="container-fluid d-flex align-items-center justify-content-between px-3">
            <div class="d-flex align-items-center gap-3">
                <button id="toggleSidebar" class="btn btn-sm btn-outline-light d-md-none"><i class="fa fa-bars"></i></button>
                <h5 class="m-0 text-white"><i class="fa fa-shield me-2"></i>Admin Panel</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span id="greeting" class="text-white">Hello, Admin</span>
                <a href="auth/logout.php" id="logoutBtn" class="btn btn-sm btn-outline-light ms-2">Logout</a>
            </div>
        </div>
    </header>

    <div class="container-fluid" style="padding-top:70px">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link active text-white" href="#" id="usersTab"><i class="fa fa-users me-2"></i>Users</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="#" id="tasksTab"><i class="fa fa-tasks me-2"></i>All Tasks</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="#" id="dashboardLink"><i class="fa fa-home me-2"></i>Go to Dashboard</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- USERS TAB -->
                <div id="usersSection">
                    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h2>Users Management</h2>
                        <button id="addUserBtn" class="btn btn-primary"><i class="fa fa-plus me-2"></i>Add User</button>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="usersTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Users populated by JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TASKS TAB -->
                <div id="tasksSection" style="display:none;">
                    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h2>All Tasks</h2>
                        <button id="addTaskBtn" class="btn btn-primary"><i class="fa fa-plus me-2"></i>Add Task</button>
                    </div>

                    <div id="stats" class="row gy-3 mb-4">
                        <!-- Summary cards -->
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="tasksTable">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>User</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Due Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Tasks populated by JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="userForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="userId">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input id="userName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input id="userEmail" type="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input id="userPassword" type="password" class="form-control" placeholder="Leave blank to keep current">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select id="userRole" class="form-select">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Task Modal -->
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
                        <label class="form-label">Assign to User</label>
                        <select id="taskUserId" class="form-select" required>
                            <option value="">Select User</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input id="taskTitle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea id="taskDescription" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select id="taskStatus" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select id="taskPriority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input id="taskDueDate" type="date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.__CURRENT_USER = {
            id: <?php echo $_SESSION['user_id']; ?>,
            name: <?php echo json_encode($_SESSION['name']); ?>,
            role: <?php echo json_encode($_SESSION['role']); ?>
        };
    </script>
    <script src="js/admin.js"></script>
</body>
</html>
