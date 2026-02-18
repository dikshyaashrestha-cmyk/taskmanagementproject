// ============= VARIABLES =============
const apiUrl = 'api/tasks.php';
let currentUser = null;
let tasks = [];
let currentFilter = { status: null, priority: null };

// ============= DOM ELEMENTS =============
const taskModalEl = document.getElementById('taskModal');
const taskModal = taskModalEl ? new bootstrap.Modal(taskModalEl) : null;
const taskForm = document.getElementById('taskForm');
const tasksTableBody = document.querySelector('#tasksTable tbody');
const statsContainer = document.getElementById('stats');

// ============= FETCH TASKS =============
async function fetchTasks(){
  try {
    console.log('Fetching tasks...');
    const res = await fetch(apiUrl + '?action=list');
    if(!res.ok) {
      console.error('API error:', res.status, res.statusText);
      return;
    }
    const data = await res.json();
    console.log('Tasks loaded:', data.length, 'tasks');
    tasks = Array.isArray(data) ? data : [];
    renderTasks();
    renderStats();
  } catch(error) {
    console.error('Error fetching tasks:', error);
  }
}

// ============= LOAD SESSION =============
async function loadSession(){
  try{
    if(window.__CURRENT_USER && window.__CURRENT_USER.id){
      currentUser = window.__CURRENT_USER;
      console.log('Current user:', currentUser);
    }
  }catch(e){ 
    console.error('Session error:', e); 
  }
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
      <div class="card text-white bg-primary stat-card" onclick="applyFilter({status: 'all', priority: null})" style="cursor: pointer;">
        <div class="card-body">
          <h6>Total</h6>
          <h3>${total}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-dark bg-warning stat-card" onclick="applyFilter({status: 'pending', priority: null})" style="cursor: pointer;">
        <div class="card-body">
          <h6>Pending</h6>
          <h3>${pending}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-success stat-card" onclick="applyFilter({status: 'completed', priority: null})" style="cursor: pointer;">
        <div class="card-body">
          <h6>Completed</h6>
          <h3>${completed}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-danger stat-card" onclick="filterOverdue()" style="cursor: pointer;">
        <div class="card-body">
          <h6>Overdue</h6>
          <h3>${overdue}</h3>
        </div>
      </div>
    </div>
  `;
}

function renderFilteredStats(filteredTasks){
  if(!statsContainer) return;
  const total = filteredTasks.length;
  const pending = filteredTasks.filter(t=>t.status==='pending').length;
  const completed = filteredTasks.filter(t=>t.status==='completed').length;
  const overdue = filteredTasks.filter(t=>t.due_date && new Date(t.due_date) < new Date() && t.status!=='completed').length;

  statsContainer.innerHTML = `
    <div class="col-md-3">
      <div class="card text-white bg-primary stat-card" onclick="applyFilter({status: 'all', priority: null})" style="cursor: pointer;">
        <div class="card-body">
          <h6>Total</h6>
          <h3>${total}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-dark bg-warning stat-card" onclick="applyFilter({status: 'pending', priority: null})" style="cursor: pointer;">
        <div class="card-body">
          <h6>Pending</h6>
          <h3>${pending}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-success stat-card" onclick="applyFilter({status: 'completed', priority: null})" style="cursor: pointer;">
        <div class="card-body">
          <h6>Completed</h6>
          <h3>${completed}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-white bg-danger stat-card" onclick="filterOverdue()" style="cursor: pointer;">
        <div class="card-body">
          <h6>Overdue</h6>
          <h3>${overdue}</h3>
        </div>
      </div>
    </div>
  `;
}

// ============= HELPER FUNCTIONS =============
function escapeHtml(text){ 
  return String(text)
    .replaceAll('&','&amp;')
    .replaceAll('<','&lt;')
    .replaceAll('>','&gt;'); 
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

// ============= RENDER TASKS =============
function getActionButtons(t){
  const buttons = [];
  
  // Check if task is completed or overdue
  const isCompleted = t.status === 'completed';
  const isOverdue = t.due_date && new Date(t.due_date) < new Date() && t.status !== 'completed';
  const isLocked = isCompleted || isOverdue;
  
  // Only show status buttons if task is NOT locked
  if(!isLocked){
    if(t.status === 'in_progress'){
      buttons.push(`<button class="btn btn-sm btn-secondary me-1" onclick="updateStatus(${t.id}, 'pending')" title="Mark Pending"><i class="fa fa-undo"></i></button>`);
      buttons.push(`<button class="btn btn-sm btn-success me-1" onclick="updateStatus(${t.id}, 'completed')" title="Mark Complete"><i class="fa fa-check"></i></button>`);
    } else { // pending
      buttons.push(`<button class="btn btn-sm btn-warning me-1" onclick="updateStatus(${t.id}, 'in_progress')" title="Mark In Progress"><i class="fa fa-play"></i></button>`);
      buttons.push(`<button class="btn btn-sm btn-success me-1" onclick="updateStatus(${t.id}, 'completed')" title="Mark Complete"><i class="fa fa-check"></i></button>`);
    }
  }
  
  // Show lock icon if task is locked
  if(isLocked){
    const lockReason = isCompleted ? 'Completed' : 'Overdue';
    buttons.push(`<span class="badge bg-secondary me-1" title="${lockReason} - Cannot change"><i class="fa fa-lock"></i></span>`);
  }
  
  // Edit & Delete
  buttons.push(`<button class="btn btn-sm btn-primary me-1" onclick="editTask(${t.id})" title="Edit"><i class="fa fa-edit"></i></button>`);
  buttons.push(`<button class="btn btn-sm btn-danger" onclick="deleteTask(${t.id})" title="Delete"><i class="fa fa-trash"></i></button>`);
  
  return buttons.join('');
}

function renderTasks(){
  if(!tasksTableBody) {
    console.error('Tasks table not found');
    return;
  }
  console.log('Rendering', tasks.length, 'tasks');
  tasksTableBody.innerHTML = '';
  tasks.forEach(t => {
    const tr = document.createElement('tr');
    const overdue = t.due_date && new Date(t.due_date) < new Date() && t.status !== 'completed';
    if(overdue) tr.classList.add('task-overdue');

    tr.innerHTML = `
      <td>${escapeHtml(t.title)}</td>
      <td>${escapeHtml(t.description || '')}</td>
      <td><small>${statusLabel(t.status)}</small></td>
      <td><small>${priorityBadge(t.priority)}</small></td>
      <td><small>${t.due_date || ''}</small></td>
      <td><small>${getActionButtons(t)}</small></td>
    `;
    tasksTableBody.appendChild(tr);
  });
}

function renderTasksFromArray(arr){
  if(!tasksTableBody) return;
  tasksTableBody.innerHTML = '';
  arr.forEach(t => {
    const tr = document.createElement('tr');
    const overdue = t.due_date && new Date(t.due_date) < new Date() && t.status !== 'completed';
    if(overdue) tr.classList.add('task-overdue');

    tr.innerHTML = `
      <td>${escapeHtml(t.title)}</td>
      <td>${escapeHtml(t.description || '')}</td>
      <td><small>${statusLabel(t.status)}</small></td>
      <td><small>${priorityBadge(t.priority)}</small></td>
      <td><small>${t.due_date || ''}</small></td>
      <td><small>${getActionButtons(t)}</small></td>
    `;
    tasksTableBody.appendChild(tr);
  });
}

// ============= FILTERS =============
function applyFilter({status=null, priority=null}){
  currentFilter = { status, priority };
  let filtered = tasks.slice();
  
  if(status && status !== 'all') {
    filtered = filtered.filter(t=>t.status===status);
  }
  if(priority) {
    filtered = filtered.filter(t=>t.priority===priority);
  }
  
  renderTasksFromArray(filtered);
  renderFilteredStats(filtered);
  
  // Update page heading
  const headingEl = document.getElementById('pageHeading');
  if(headingEl){
    if(status === 'all' || status === null){
      headingEl.textContent = 'Dashboard';
    } else if(status === 'pending'){
      headingEl.textContent = 'Pending Tasks';
    } else if(status === 'in_progress'){
      headingEl.textContent = 'In Progress Tasks';
    } else if(status === 'completed'){
      headingEl.textContent = 'Completed Tasks';
    } else if(priority === 'high'){
      headingEl.textContent = 'High Priority Tasks';
    } else if(priority === 'medium'){
      headingEl.textContent = 'Medium Priority Tasks';
    } else if(priority === 'low'){
      headingEl.textContent = 'Low Priority Tasks';
    } else {
      headingEl.textContent = 'Filtered Tasks';
    }
  }
  
  // Update sidebar active state
  document.querySelectorAll('.filter').forEach(x=>x.classList.remove('active'));
  if(status === 'all'){
    document.getElementById('dashboardLink')?.classList.add('active');
  } else {
    const filterBtn = document.querySelector(`.filter[data-status="${status}"]`) || 
                      document.querySelector(`.filter[data-priority="${priority}"]`);
    if(filterBtn) filterBtn.classList.add('active');
  }
}

window.filterOverdue = function(){
  const overdueTasks = tasks.filter(t=>t.due_date && new Date(t.due_date) < new Date() && t.status!=='completed');
  console.log('Filtering overdue tasks:', overdueTasks.length);
  renderTasksFromArray(overdueTasks);
  renderFilteredStats(overdueTasks);
  
  // Update page heading
  const headingEl = document.getElementById('pageHeading');
  if(headingEl){
    headingEl.textContent = 'Overdue Tasks';
  }
  
  // Update sidebar
  document.querySelectorAll('.filter').forEach(x=>x.classList.remove('active'));
  document.getElementById('dashboardLink')?.classList.remove('active');
}

document.querySelectorAll('.filter').forEach(el=>{
  el.addEventListener('click', (e)=>{
    e.preventDefault();
    document.querySelectorAll('.filter').forEach(x=>x.classList.remove('active'));
    el.classList.add('active');
    const status = el.dataset.status;
    const priority = el.dataset.priority;
    console.log('Filter applied:', {status, priority});
    applyFilter({status, priority});
  });
});

// ============= ADD TASK BUTTON =============
const addTaskBtn = document.getElementById('addTaskBtn');
if(addTaskBtn){
  addTaskBtn.addEventListener('click',()=>{
    const titleEl = document.querySelector('#taskForm .modal-title');
    if(titleEl) titleEl.textContent = 'Add Task';
    if(taskForm) taskForm.reset(); 
    document.getElementById('taskId').value='';
    if(taskModal) taskModal.show();
  });
}

// ============= TASK FORM SUBMIT =============
if(taskForm){
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
    const action = id ? 'update' : 'add';
    if(id) payload.id = id;
    
    try{
      console.log('Submitting task:', action, payload);
      const res = await fetch(apiUrl + '?action=' + action, { 
        method: 'POST', 
        headers:{'Content-Type':'application/json'}, 
        body: JSON.stringify(payload) 
      });
      const data = await res.json();
      if(!res.ok || data.error){
        alert('Error saving task: ' + (data.error || 'Unknown error'));
        return;
      }
      console.log('Task saved successfully');
      await fetchTasks();
      if(taskModal) taskModal.hide();
    } catch(err){
      console.error('Error:', err);
      alert('Network error saving task');
    }
  });
}

// ============= EDIT TASK =============
window.editTask = function(id){
  const t = tasks.find(x=>x.id==id); 
  if(!t) return;
  console.log('Editing task:', t);
  const titleEl = document.querySelector('#taskForm .modal-title');
  if(titleEl) titleEl.textContent = 'Edit Task';
  document.getElementById('taskId').value = t.id;
  document.getElementById('title').value = t.title;
  document.getElementById('description').value = t.description || '';
  document.getElementById('status').value = t.status;
  document.getElementById('priority').value = t.priority;
  document.getElementById('due_date').value = t.due_date || '';
  if(taskModal) taskModal.show();
}

// ============= DELETE TASK =============
window.deleteTask = async function(id){
  if(!confirm('Delete this task?')) return;
  try {
    console.log('Deleting task:', id);
    const res = await fetch(apiUrl + '?action=delete', { 
      method:'POST', 
      headers:{'Content-Type':'application/json'}, 
      body: JSON.stringify({id}) 
    });
    if(res.ok) {
      console.log('Task deleted');
      await fetchTasks();
    }
  } catch(err) {
    console.error('Delete error:', err);
    alert('Error deleting task');
  }
}

// ============= MARK COMPLETE =============
window.updateStatus = async function(id, newStatus){
  try {
    console.log('Updating task status:', id, '->', newStatus);
    const res = await fetch(apiUrl + '?action=update', { 
      method:'POST', 
      headers:{'Content-Type':'application/json'}, 
      body: JSON.stringify({
        id: id,
        title: tasks.find(t=>t.id==id)?.title || '',
        description: tasks.find(t=>t.id==id)?.description || '',
        status: newStatus,
        priority: tasks.find(t=>t.id==id)?.priority || 'low',
        due_date: tasks.find(t=>t.id==id)?.due_date || null
      })
    });
    if(res.ok) {
      console.log('Status updated');
      await fetchTasks();
    }
  } catch(err) {
    console.error('Status update error:', err);
    alert('Error updating task status');
  }
}

window.markComplete = async function(id){
  await updateStatus(id, 'completed');
}

// ============= SEARCH =============
const searchInput = document.getElementById('searchInput');
if(searchInput){
  searchInput.addEventListener('input',(e)=>{
    const q = e.target.value.toLowerCase();
    let filtered = tasks.filter(t => 
      (t.title||'').toLowerCase().includes(q) || 
      (t.description||'').toLowerCase().includes(q)
    );
    
    if(currentFilter.status && currentFilter.status !== 'all') {
      filtered = filtered.filter(t=>t.status===currentFilter.status);
    }
    if(currentFilter.priority) {
      filtered = filtered.filter(t=>t.priority===currentFilter.priority);
    }
    
    if(q){ 
      renderTasksFromArray(filtered);
      renderFilteredStats(filtered);
    } else { 
      fetchTasks();
      applyFilter(currentFilter);
    }
  });
}

// ============= DASHBOARD LINK =============
const dashboardLink = document.getElementById('dashboardLink');
if(dashboardLink){
  dashboardLink.addEventListener('click', (e)=>{
    e.preventDefault();
    document.querySelectorAll('.filter').forEach(x=>x.classList.remove('active'));
    dashboardLink.classList.add('active');
    console.log('Dashboard clicked');
    fetchTasks();
    currentFilter = { status: null, priority: null };
  });
}

// ============= LOGOUT =============
const logoutBtn = document.getElementById('logoutBtn');
if(logoutBtn){
  logoutBtn.addEventListener('click', async (e)=>{
    e.preventDefault();
    await fetch('auth/logout.php');
    window.location.href = 'index.php';
  });
}

// ============= INITIAL LOAD =============
console.log('App script starting...');
loadSession().then(()=>{
  console.log('Session loaded, fetching tasks...');
  fetchTasks();
});
