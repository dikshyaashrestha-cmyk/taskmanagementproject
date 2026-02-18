const apiUrl = 'api/tasks.php';
// Authentication: session-driven. We'll fetch session on load.
let currentUser = null;

const taskModalEl = document.getElementById('taskModal');
const taskModal = new bootstrap.Modal(taskModalEl);
const taskForm = document.getElementById('taskForm');
const tasksTableBody = document.querySelector('#tasksTable tbody');
const statsContainer = document.getElementById('stats');
const toggleSidebarBtn = document.getElementById('toggleSidebar');
const sidebarEl = document.getElementById('sidebar');

let tasks = [];

async function fetchTasks(){
  const res = await fetch(`${apiUrl}?action=list`);
  const data = await res.json();
  tasks = data;
  renderTasks();
  renderStats();
}

// load session info and update UI
async function loadSession(){
  try{
    // hydrate from server-provided user if available
    if(window.__CURRENT_USER && window.__CURRENT_USER.id){
      currentUser = window.__CURRENT_USER;
      const name = currentUser.name || 'User';
      const greetingEl = document.getElementById('greeting'); if(greetingEl) greetingEl.textContent = `Hello, ${name}`;
      const loginBtn = document.getElementById('loginBtn'); if(loginBtn) loginBtn.classList.add('d-none');
      const registerBtn = document.getElementById('registerBtn'); if(registerBtn) registerBtn.classList.add('d-none');
      const logoutBtn = document.getElementById('logoutBtn'); if(logoutBtn) logoutBtn.classList.remove('d-none');
      const addBtn = document.getElementById('addTaskBtn'); if(addBtn) addBtn.disabled = false;
      return;
    }
    const res = await fetch('api/session.php');
    const data = await res.json();
    if(data.logged_in){
      currentUser = data.user;
      const greetingEl = document.getElementById('greeting'); if(greetingEl) greetingEl.textContent = `Hello, ${currentUser.name}`;
      const loginBtn = document.getElementById('loginBtn'); if(loginBtn) loginBtn.classList.add('d-none');
      const registerBtn = document.getElementById('registerBtn'); if(registerBtn) registerBtn.classList.add('d-none');
        const logoutBtn = document.getElementById('logoutBtn'); if(logoutBtn) logoutBtn.classList.remove('d-none');
        // enable add button for logged in user
        const addBtn = document.getElementById('addTaskBtn'); if(addBtn) addBtn.disabled = false;
    } else {
      currentUser = null;
      const greetingEl = document.getElementById('greeting'); if(greetingEl) greetingEl.textContent = 'Hello, Guest';
      const loginBtn = document.getElementById('loginBtn'); if(loginBtn) loginBtn.classList.remove('d-none');
      const registerBtn = document.getElementById('registerBtn'); if(registerBtn) registerBtn.classList.remove('d-none');
        const logoutBtn = document.getElementById('logoutBtn'); if(logoutBtn) logoutBtn.classList.add('d-none');
        // disable add button for guests
        const addBtn = document.getElementById('addTaskBtn'); if(addBtn) addBtn.disabled = true;
    }
  }catch(e){ console.error('session load', e); }
}

// Sidebar toggle for small screens
if(toggleSidebarBtn && sidebarEl){
  toggleSidebarBtn.addEventListener('click', ()=>{
    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(sidebarEl);
    bsCollapse.toggle();
  });
}

// Filters in sidebar
function applyFilter({status=null, priority=null}){
  let filtered = tasks.slice();
  if(status && status !== 'all') filtered = filtered.filter(t=>t.status===status);
  if(priority) filtered = filtered.filter(t=>t.priority===priority);
  renderTasksFromArray(filtered);
}

document.querySelectorAll('.filter').forEach(el=>{
  el.addEventListener('click', (e)=>{
    e.preventDefault();
    // visual active state
    document.querySelectorAll('.filter').forEach(x=>x.classList.remove('active'));
    el.classList.add('active');
    const status = el.dataset.status;
    const priority = el.dataset.priority;
    applyFilter({status, priority});
  });
});

function renderTasks(){
  tasksTableBody.innerHTML = '';
  tasks.forEach(t => {
    const tr = document.createElement('tr');
    const overdue = t.due_date && new Date(t.due_date) < new Date() && t.status !== 'completed';
    if(overdue) tr.classList.add('task-overdue');

    tr.innerHTML = `
      <td>${escapeHtml(t.title)}</td>
      <td>${escapeHtml(t.description || '')}</td>
      <td class="small">${statusLabel(t.status)}</td>
      <td class="small">${priorityBadge(t.priority)}</td>
      <td class="small">${t.due_date || ''}</td>
        <td class="small">
          ${renderActionButtons(t)}
        </td>
    `;
    tasksTableBody.appendChild(tr);
  });
}

function renderActionButtons(t){
  const ownerId = t.user_id || null;
  const role = currentUser?.role || null;
  const userId = currentUser?.id || null;
  const canModify = (role === 'admin') || (userId && ownerId && parseInt(userId) === parseInt(ownerId));
  // if not logged in, show disabled buttons with tooltip to login
  if(!currentUser){
    return `
      <button class="btn btn-sm btn-success me-1" disabled><i class="fa fa-check"></i></button>
      <button class="btn btn-sm btn-primary me-1" disabled><i class="fa fa-edit"></i></button>
      <button class="btn btn-sm btn-danger" disabled><i class="fa fa-trash"></i></button>
    `;
  }
  if(canModify){
    return `
      <button class="btn btn-sm btn-success me-1" onclick="markComplete(${t.id})" ${t.status==='completed'? 'disabled' : ''}><i class="fa fa-check"></i></button>
      <button class="btn btn-sm btn-primary me-1" onclick="editTask(${t.id})"><i class="fa fa-edit"></i></button>
      <button class="btn btn-sm btn-danger" onclick="deleteTask(${t.id})"><i class="fa fa-trash"></i></button>
    `;
  }
  // else logged-in but not owner: allow only markComplete if not completed? keep view-only
  return `
      <button class="btn btn-sm btn-success me-1" disabled><i class="fa fa-check"></i></button>
      <button class="btn btn-sm btn-secondary me-1" disabled><i class="fa fa-eye"></i></button>
    `;
}

function renderStats(){
  const total = tasks.length;
  const pending = tasks.filter(t=>t.status==='pending').length;
  const inProgress = tasks.filter(t=>t.status==='in_progress').length;
  const completed = tasks.filter(t=>t.status==='completed').length;
  const overdue = tasks.filter(t=>t.due_date && new Date(t.due_date) < new Date() && t.status!=='completed').length;

  statsContainer.innerHTML = `
    <div class="col-md-3"><div class="card text-white bg-primary"><div class="card-body"><h6>Total</h6><h3>${total}</h3></div></div></div>
    <div class="col-md-3"><div class="card text-dark bg-warning"><div class="card-body"><h6>Pending</h6><h3>${pending}</h3></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-success"><div class="card-body"><h6>Completed</h6><h3>${completed}</h3></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-danger"><div class="card-body"><h6>Overdue</h6><h3>${overdue}</h3></div></div></div>
  `;
}

function statusLabel(s){
  if(s==='pending') return '<span class="badge bg-warning text-dark">Pending</span>';
  if(s==='in_progress') return '<span class="badge bg-info text-dark">In Progress</span>';
  return '<span class="badge bg-success">Completed</span>';
}

function priorityBadge(p){
  if(p==='high') return '<span class="badge badge-priority-high">High</span>';
  if(p==='medium') return '<span class="badge badge-priority-medium">Medium</span>';
  return '<span class="badge badge-priority-low">Low</span>';
}

function escapeHtml(text){ return String(text).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }

document.getElementById('addTaskBtn').addEventListener('click',()=>{
  document.querySelector('#taskForm .modal-title').textContent = 'Add Task';
  taskForm.reset(); document.getElementById('taskId').value='';
  taskModal.show();
});

taskForm.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const id = document.getElementById('taskId').value;
  const payload = {
    title: document.getElementById('title').value,
    description: document.getElementById('description').value,
    status: document.getElementById('status').value,
    priority: document.getElementById('priority').value,
    due_date: document.getElementById('due_date').value || null
  };
  const action = id? 'update' : 'add';
  if(id) payload.id = id;
  try{
    const res = await fetch(`${apiUrl}?action=${action}`, { method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const data = await res.json();
    if(!res.ok || data.error){
      console.error('API error', data);
        alert('Error saving task: ' + (data.error || 'Unknown error'));
      return;
    }
    await fetchTasks();
    taskModal.hide();
  } catch(err){
    console.error(err);
    alert('Network error saving task');
  }
});

window.editTask = function(id){
  const t = tasks.find(x=>x.id==id); if(!t) return;
  document.querySelector('#taskForm .modal-title').textContent = 'Edit Task';
  document.getElementById('taskId').value = t.id;
  document.getElementById('title').value = t.title;
  document.getElementById('description').value = t.description;
  document.getElementById('status').value = t.status;
  document.getElementById('priority').value = t.priority;
  document.getElementById('due_date').value = t.due_date || '';
  taskModal.show();
}

window.deleteTask = async function(id){
  if(!confirm('Delete this task?')) return;
  await fetch(`${apiUrl}?action=delete`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}) });
  await fetchTasks();
}

window.markComplete = async function(id){
  await fetch(`${apiUrl}?action=complete`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}) });
  await fetchTasks();
}

document.getElementById('searchInput').addEventListener('input',(e)=>{
  const q = e.target.value.toLowerCase();
  const filtered = tasks.filter(t => (t.title||'').toLowerCase().includes(q) || (t.description||'').toLowerCase().includes(q));
  if(q){ renderTasksFromArray(filtered); } else { fetchTasks(); }
});

function renderTasksFromArray(arr){
  tasksTableBody.innerHTML = '';
  arr.forEach(t=>{
    const tr = document.createElement('tr');
    const overdue = t.due_date && new Date(t.due_date) < new Date() && t.status !== 'completed';
    if(overdue) tr.classList.add('task-overdue');
    tr.innerHTML = `
      <td>${escapeHtml(t.title)}</td>
      <td>${escapeHtml(t.description || '')}</td>
      <td class="small">${statusLabel(t.status)}</td>
      <td class="small">${priorityBadge(t.priority)}</td>
      <td class="small">${t.due_date || ''}</td>
      <td class="small">
        <button class="btn btn-sm btn-success me-1" onclick="markComplete(${t.id})" ${t.status==='completed'? 'disabled' : ''}><i class="fa fa-check"></i></button>
        <button class="btn btn-sm btn-primary me-1" onclick="editTask(${t.id})"><i class="fa fa-edit"></i></button>
        <button class="btn btn-sm btn-danger" onclick="deleteTask(${t.id})"><i class="fa fa-trash"></i></button>
      </td>
    `;
    tasksTableBody.appendChild(tr);
  });
}

// initial load
loadSession().then(()=>fetchTasks());

// Login/register/logout handlers
const loginModalEl = document.getElementById('loginModal');
const registerModalEl = document.getElementById('registerModal');
const loginModal = loginModalEl ? new bootstrap.Modal(loginModalEl) : null;
const registerModal = registerModalEl ? new bootstrap.Modal(registerModalEl) : null;
const loginBtnEl = document.getElementById('loginBtn');
const registerBtnEl = document.getElementById('registerBtn');
const logoutBtnEl = document.getElementById('logoutBtn');
if(loginBtnEl && loginModal){ loginBtnEl.addEventListener('click', ()=> loginModal.show()); }
if(registerBtnEl && registerModal){ registerBtnEl.addEventListener('click', ()=> registerModal.show()); }
if(logoutBtnEl){ logoutBtnEl.addEventListener('click', async (e)=>{
  e.preventDefault();
  await fetch('auth/logout.php');
  await loadSession();
  fetchTasks();
});

const loginFormEl = document.getElementById('loginForm');
if(loginFormEl){
  loginFormEl.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const email = document.getElementById('loginEmail').value;
  const password = document.getElementById('loginPassword').value;
  const res = await fetch('auth/login.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({email,password}) });
  const data = await res.json();
  if(!res.ok || data.error){ alert(data.error || 'Login failed'); return; }
  await loadSession(); loginModal.hide(); fetchTasks();
  });
}

const registerFormEl = document.getElementById('registerForm');
if(registerFormEl){
  registerFormEl.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const name = document.getElementById('regName').value;
    const email = document.getElementById('regEmail').value;
    const password = document.getElementById('regPassword').value;
    const res = await fetch('auth/register.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({name,email,password}) });
    const data = await res.json();
    if(!res.ok || data.error){ alert(data.error || 'Register failed'); return; }
    alert('Registration successful — please login'); if(registerModal) registerModal.hide();
  });
}
