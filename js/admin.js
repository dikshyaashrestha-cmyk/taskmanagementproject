// ============= VARIABLES =============
const adminApiUrl = 'api/admin.php';
let currentUser = window.__CURRENT_USER;
let users = [];
let tasks = [];
let allUsers = []; // For task user dropdown

// ============= DOM ELEMENTS =============
const userModalEl = document.getElementById('userModal');
const userModal = userModalEl ? new bootstrap.Modal(userModalEl) : null;
const userForm = document.getElementById('userForm');
const usersTableBody = document.querySelector('#usersTable tbody');

const taskModalEl = document.getElementById('taskModal');
const taskModal = taskModalEl ? new bootstrap.Modal(taskModalEl) : null;
const taskForm = document.getElementById('taskForm');
const tasksTableBody = document.querySelector('#tasksTable tbody');
const statsContainer = document.getElementById('stats');

const usersSection = document.getElementById('usersSection');
const tasksSection = document.getElementById('tasksSection');
const usersTab = document.getElementById('usersTab');
const tasksTab = document.getElementById('tasksTab');

// ============= FETCH USERS =============
async function fetchUsers(){
  try {
    console.log('Fetching users...');
    const res = await fetch(adminApiUrl + '?action=list_users');
    if(!res.ok) {
      const error = await res.json();
      console.error('API error:', error);
      alert('Error: ' + (error.error || 'Unknown error'));
      return;
    }
    const data = await res.json();
    console.log('Users loaded:', data);
    users = Array.isArray(data) ? data : [];
    allUsers = users;
    renderUsers();
  } catch(error) {
    console.error('Error fetching users:', error);
    alert('Error fetching users: ' + error.message);
  }
}

// ============= FETCH TASKS =============
async function fetchTasks(){
  try {
    console.log('Fetching tasks...');
    const res = await fetch(adminApiUrl + '?action=list_tasks');
    if(!res.ok) {
      const error = await res.json();
      console.error('API error:', error);
      alert('Error: ' + (error.error || 'Unknown error'));
      return;
    }
    const data = await res.json();
    console.log('Tasks loaded:', data);
    tasks = Array.isArray(data) ? data : [];
    renderTasks();
    renderStats();
    populateUserDropdown();
  } catch(error) {
    console.error('Error fetching tasks:', error);
    alert('Error fetching tasks: ' + error.message);
  }
}

// ============= RENDER USERS =============
function renderUsers(){
  if(!usersTableBody) return;
  usersTableBody.innerHTML = '';
  users.forEach(u => {
    const tr = document.createElement('tr');
    const roleBadge = u.role === 'admin' ? '<span class="badge bg-danger">Admin</span>' : '<span class="badge bg-primary">User</span>';
    const deleteBtn = u.id != currentUser.id ? `<button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id})" title="Delete"><i class="fa fa-trash"></i></button>` : '';
    
    tr.innerHTML = `
      <td>${u.id}</td>
      <td>${u.name}</td>
      <td>${u.email}</td>
      <td>${roleBadge}</td>
      <td><small>${new Date(u.created_at).toLocaleDateString()}</small></td>
      <td>
        <button class="btn btn-sm btn-primary me-1" onclick="editUser(${u.id})" title="Edit"><i class="fa fa-edit"></i></button>
        ${deleteBtn}
      </td>
    `;
    usersTableBody.appendChild(tr);
  });
}

// ============= RENDER TASKS =============
function renderTasks(){
  if(!tasksTableBody) return;
  tasksTableBody.innerHTML = '';
  tasks.forEach(t => {
    const tr = document.createElement('tr');
    const statusBadge = t.status === 'pending' ? '<span class="badge bg-warning text-dark">Pending</span>' : 
                        t.status === 'in_progress' ? '<span class="badge bg-info text-dark">In Progress</span>' : 
                        '<span class="badge bg-success">Completed</span>';
    const priorityBadge = t.priority === 'high' ? '<span class="badge badge-priority-high">High</span>' : 
                          t.priority === 'medium' ? '<span class="badge badge-priority-medium">Medium</span>' : 
                          '<span class="badge badge-priority-low">Low</span>';
    
    tr.innerHTML = `
      <td>${t.title}</td>
      <td>${t.user_name || 'Unassigned'}</td>
      <td>${statusBadge}</td>
      <td>${priorityBadge}</td>
      <td><small>${t.due_date || '-'}</small></td>
      <td>
        <button class="btn btn-sm btn-primary me-1" onclick="editTask(${t.id})" title="Edit"><i class="fa fa-edit"></i></button>
        <button class="btn btn-sm btn-danger" onclick="deleteTask(${t.id})" title="Delete"><i class="fa fa-trash"></i></button>
      </td>
    `;
    tasksTableBody.appendChild(tr);
  });
}

// ============= RENDER STATS =============
function renderStats(){
  if(!statsContainer) return;
  const total = tasks.length;
  const pending = tasks.filter(t=>t.status==='pending').length;
  const completed = tasks.filter(t=>t.status==='completed').length;
  const overdue = tasks.filter(t=>t.due_date && new Date(t.due_date) < new Date() && t.status!=='completed').length;

  statsContainer.innerHTML = `
    <div class="col-md-3">
      <div class="card text-white bg-primary">
        <div class="card-body">
          <h6>Total Tasks</h6>
          <h3>${total}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-dark bg-warning">
        <div class="card-body">
          <h6>Pending</h6>
          <h3>${pending}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-success">
        <div class="card-body">
          <h6>Completed</h6>
          <h3>${completed}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-danger">
        <div class="card-body">
          <h6>Overdue</h6>
          <h3>${overdue}</h3>
        </div>
      </div>
    </div>
  `;
}

// ============= POPULATE USER DROPDOWN =============
function populateUserDropdown(){
  const select = document.getElementById('taskUserId');
  if(!select) return;
  select.innerHTML = '<option value="">Select User</option>';
  users.forEach(u => {
    const opt = document.createElement('option');
    opt.value = u.id;
    opt.textContent = u.name + ' (' + u.email + ')';
    select.appendChild(opt);
  });
}

// ============= TAB SWITCHING =============
usersTab?.addEventListener('click', (e) => {
  e.preventDefault();
  usersSection.style.display = 'block';
  tasksSection.style.display = 'none';
  usersTab.classList.add('active');
  tasksTab.classList.remove('active');
  fetchUsers();
});

tasksTab?.addEventListener('click', (e) => {
  e.preventDefault();
  usersSection.style.display = 'none';
  tasksSection.style.display = 'block';
  tasksTab.classList.add('active');
  usersTab.classList.remove('active');
  fetchTasks();
});

// ============= ADD USER BUTTON =============
document.getElementById('addUserBtn')?.addEventListener('click', () => {
  document.querySelector('#userForm .modal-title').textContent = 'Add User';
  userForm.reset();
  document.getElementById('userId').value = '';
  document.getElementById('userPassword').required = true;
  if(userModal) userModal.show();
});

// ============= USER FORM SUBMIT =============
userForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const id = document.getElementById('userId').value;
  const action = id ? 'update_user' : 'add_user';
  
  const payload = {
    name: document.getElementById('userName').value,
    email: document.getElementById('userEmail').value,
    password: document.getElementById('userPassword').value,
    role: document.getElementById('userRole').value
  };
  if(id) payload.id = id;
  
  try {
    const res = await fetch(adminApiUrl + '?action=' + action, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if(!res.ok || data.error) {
      alert('Error: ' + (data.error || 'Unknown error'));
      return;
    }
    alert('User ' + (id ? 'updated' : 'created') + ' successfully');
    await fetchUsers();
    if(userModal) userModal.hide();
  } catch(err) {
    console.error(err);
    alert('Error saving user');
  }
});

// ============= EDIT USER =============
window.editUser = function(id){
  const u = users.find(x => x.id == id);
  if(!u) return;
  
  document.querySelector('#userForm .modal-title').textContent = 'Edit User';
  document.getElementById('userId').value = u.id;
  document.getElementById('userName').value = u.name;
  document.getElementById('userEmail').value = u.email;
  document.getElementById('userRole').value = u.role;
  document.getElementById('userPassword').value = '';
  document.getElementById('userPassword').required = false;
  
  if(userModal) userModal.show();
}

// ============= DELETE USER =============
window.deleteUser = async function(id){
  if(!confirm('Are you sure? This user will be deleted.')) return;
  
  try {
    const res = await fetch(adminApiUrl + '?action=delete_user', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({id})
    });
    const data = await res.json();
    if(!res.ok || data.error) {
      alert('Error: ' + (data.error || 'Unknown error'));
      return;
    }
    alert('User deleted successfully');
    await fetchUsers();
  } catch(err) {
    console.error(err);
    alert('Error deleting user');
  }
}

// ============= ADD TASK BUTTON =============
document.getElementById('addTaskBtn')?.addEventListener('click', () => {
  document.querySelector('#taskForm .modal-title').textContent = 'Add Task';
  taskForm.reset();
  document.getElementById('taskId').value = '';
  if(taskModal) taskModal.show();
});

// ============= TASK FORM SUBMIT =============
taskForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const id = document.getElementById('taskId').value;
  const action = id ? 'update_task' : 'add_task';
  
  const payload = {
    user_id: document.getElementById('taskUserId').value,
    title: document.getElementById('taskTitle').value,
    description: document.getElementById('taskDescription').value,
    status: document.getElementById('taskStatus').value,
    priority: document.getElementById('taskPriority').value,
    due_date: document.getElementById('taskDueDate').value || null
  };
  if(id) payload.id = id;
  
  try {
    const res = await fetch(adminApiUrl + '?action=' + action, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if(!res.ok || data.error) {
      alert('Error: ' + (data.error || 'Unknown error'));
      return;
    }
    alert('Task ' + (id ? 'updated' : 'created') + ' successfully');
    await fetchTasks();
    if(taskModal) taskModal.hide();
  } catch(err) {
    console.error(err);
    alert('Error saving task');
  }
});

// ============= EDIT TASK =============
window.editTask = function(id){
  const t = tasks.find(x => x.id == id);
  if(!t) return;
  
  document.querySelector('#taskForm .modal-title').textContent = 'Edit Task';
  document.getElementById('taskId').value = t.id;
  document.getElementById('taskUserId').value = t.user_id || '';
  document.getElementById('taskTitle').value = t.title;
  document.getElementById('taskDescription').value = t.description || '';
  document.getElementById('taskStatus').value = t.status;
  document.getElementById('taskPriority').value = t.priority;
  document.getElementById('taskDueDate').value = t.due_date || '';
  
  if(taskModal) taskModal.show();
}

// ============= DELETE TASK =============
window.deleteTask = async function(id){
  if(!confirm('Are you sure you want to delete this task?')) return;
  
  try {
    const res = await fetch(adminApiUrl + '?action=delete_task', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({id})
    });
    const data = await res.json();
    if(!res.ok || data.error) {
      alert('Error: ' + (data.error || 'Unknown error'));
      return;
    }
    alert('Task deleted successfully');
    await fetchTasks();
  } catch(err) {
    console.error(err);
    alert('Error deleting task');
  }
}

// ============= LOGOUT =============
document.getElementById('logoutBtn')?.addEventListener('click', async (e) => {
  e.preventDefault();
  await fetch('auth/logout.php');
  window.location.href = 'index.php';
});

// ============= GO TO DASHBOARD =============
document.getElementById('dashboardLink')?.addEventListener('click', (e) => {
  e.preventDefault();
  window.location.href = 'dashboard.php';
});

// ============= INITIAL LOAD =============
console.log('Admin panel loading...');
fetchUsers();
