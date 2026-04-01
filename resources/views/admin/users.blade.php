@extends('layouts.app')
@section('title', 'User Management – ClassGuard')

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item active"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <a href="{{ url('/admin/subjects') }}" class="nav-item"><span class="nav-icon">📚</span><span>Subjects</span></a>
    <div class="nav-divider"></div>
    <a href="#" class="nav-item" onclick="logout()"><span class="nav-icon">🚪</span><span>Sign Out</span></a>
  </nav>
  <div class="user-section">
    <div class="user-info">
      <div class="user-avatar">AD</div>
      <div><div class="user-name">Admin</div><div class="user-role">System Administrator</div></div>
    </div>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h1>User Management</h1>
      <p class="topbar-subtitle">Manage students, professors, and admins</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openAddProfessor()">+ Add Professor</button>
    </div>
  </div>

  <div class="content">
    <div class="section" style="padding:0; overflow:hidden;">
      <!-- Tabs -->
      <div style="display:flex; border-bottom:1px solid var(--gray-200);">
        <button class="tab-btn" id="tab-all" onclick="setTab('all')" style="padding:16px 24px; border:none; background:none; font-size:14px; font-weight:500; cursor:pointer; border-bottom:2px solid var(--navy-blue); color:var(--navy-blue);">All Users</button>
        <button class="tab-btn" id="tab-pending" onclick="setTab('pending')" style="padding:16px 24px; border:none; background:none; font-size:14px; font-weight:500; cursor:pointer; border-bottom:2px solid transparent; color:var(--gray-500);">Pending</button>
        <button class="tab-btn" id="tab-students" onclick="setTab('students')" style="padding:16px 24px; border:none; background:none; font-size:14px; font-weight:500; cursor:pointer; border-bottom:2px solid transparent; color:var(--gray-500);">Students</button>
        <button class="tab-btn" id="tab-professors" onclick="setTab('professors')" style="padding:16px 24px; border:none; background:none; font-size:14px; font-weight:500; cursor:pointer; border-bottom:2px solid transparent; color:var(--gray-500);">Professors</button>
      </div>

      <!-- Search -->
      <div style="padding:16px 24px; display:flex; gap:12px; align-items:center; background:var(--gray-50); border-bottom:1px solid var(--gray-200);">
        <div class="search-wrap" style="flex:1;">
          <span class="search-icon">🔍</span>
          <input type="text" class="form-input" placeholder="Search by name, email, or student ID..." style="padding-left:36px;" oninput="filterSearch(this.value)">
        </div>
      </div>

      <!-- Table -->
      <div class="table-wrapper" style="padding:0;">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Role</th>
              <th>Student ID</th>
              <th>Section</th>
              <th>RFID</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <tr><td colspan="7" style="text-align:center; padding:32px; color:var(--gray-400);">Loading users...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- Add Professor Modal -->
<div id="professorModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:460px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">Add Professor</h3>
    <div id="profError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <div class="form-group">
      <label class="form-label">Full Name</label>
      <input type="text" class="form-input" id="profName" placeholder="Prof. Juan Santos">
    </div>
    <div class="form-group">
      <label class="form-label">Email</label>
      <input type="email" class="form-input" id="profEmail" placeholder="prof@school.edu">
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <input type="password" class="form-input" id="profPassword" placeholder="Min. 8 characters">
    </div>
    <div style="display:flex; gap:12px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('professorModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="saveProfBtn" onclick="saveProfessor()">Add Professor</button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  // token is declared in layouts/app.blade.php
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'admin') { localStorage.clear(); window.location.href = '/login'; }

  let allUsers = [];
  let currentTab = 'all';

  function statusBadge(s) {
    if (s === 'active') return '<span class="badge success">Active</span>';
    if (s === 'pending') return '<span class="badge warning">Pending</span>';
    return '<span class="badge danger">Inactive</span>';
  }
  function roleBadge(r) {
    if (r === 'professor') return '<span class="badge info">Professor</span>';
    if (r === 'admin') return '<span class="badge gold">Admin</span>';
    return '<span class="badge neutral">Student</span>';
  }

  function renderTable(users) {
    if (users.length === 0) {
      document.getElementById('tableBody').innerHTML = '<tr><td colspan="7" style="text-align:center; padding:32px; color:var(--gray-400);">No users found.</td></tr>';
      return;
    }
    document.getElementById('tableBody').innerHTML = users.map(u => {
      const initials = (u.name || 'U').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();
      return `
        <tr>
          <td>
            <div style="display:flex; align-items:center; gap:10px;">
              <div style="width:32px; height:32px; border-radius:50%; background:var(--navy-dark); color:var(--gold); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0;">${initials}</div>
              <div>
                <div style="font-size:13px; font-weight:600;">${u.name}</div>
                <div style="font-size:11px; color:var(--gray-500);">${u.email}</div>
              </div>
            </div>
          </td>
          <td>${roleBadge(u.role)}</td>
          <td style="font-size:13px; font-family:monospace;">${u.student_id_number || '–'}</td>
          <td style="font-size:13px;">${u.section?.name || '–'}</td>
          <td style="font-size:12px; font-family:monospace; color:${u.rfid_uid ? 'var(--green)' : 'var(--gray-400)'};">${u.rfid_uid || '–'}</td>
          <td>${statusBadge(u.status)}</td>
          <td>
            <div style="display:flex; gap:4px;">
              ${u.status === 'pending' ? `<button class="btn btn-success btn-sm" onclick="updateStatus(${u.id}, 'active')">✓ Approve</button>` : ''}
              ${u.status === 'active' && u.role !== 'admin' ? `<button class="btn btn-ghost btn-sm" style="color:var(--red);" onclick="updateStatus(${u.id}, 'inactive')">Suspend</button>` : ''}
              ${u.status === 'inactive' ? `<button class="btn btn-ghost btn-sm" onclick="updateStatus(${u.id}, 'active')">Activate</button>` : ''}
            </div>
          </td>
        </tr>`;
    }).join('');
  }

  async function loadUsers() {
    try {
      const res = await axios.get('/api/admin/users');
      allUsers = res.data.users;
      renderFiltered();
    } catch (e) {
      document.getElementById('tableBody').innerHTML = '<tr><td colspan="7" style="text-align:center; color:var(--red); padding:24px;">Failed to load users.</td></tr>';
    }
  }

  function renderFiltered() {
    let filtered = allUsers;
    if (currentTab === 'pending') filtered = allUsers.filter(u => u.status === 'pending');
    else if (currentTab === 'students') filtered = allUsers.filter(u => u.role === 'student');
    else if (currentTab === 'professors') filtered = allUsers.filter(u => u.role === 'professor');
    renderTable(filtered);
  }

  function setTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.style.borderBottom = '2px solid transparent';
      btn.style.color = 'var(--gray-500)';
    });
    document.getElementById('tab-' + tab).style.borderBottom = '2px solid var(--navy-blue)';
    document.getElementById('tab-' + tab).style.color = 'var(--navy-blue)';
    renderFiltered();
  }

  function filterSearch(q) {
    const lower = q.toLowerCase();
    let filtered = allUsers;
    if (currentTab === 'pending') filtered = filtered.filter(u => u.status === 'pending');
    else if (currentTab === 'students') filtered = filtered.filter(u => u.role === 'student');
    else if (currentTab === 'professors') filtered = filtered.filter(u => u.role === 'professor');
    if (q) filtered = filtered.filter(u => u.name?.toLowerCase().includes(lower) || u.email?.toLowerCase().includes(lower) || u.student_id_number?.includes(q));
    renderTable(filtered);
  }

  async function updateStatus(id, status) {
    try {
      await axios.patch(`/api/admin/users/${id}/status`, { status });
      loadUsers();
    } catch (e) { alert('Failed to update status.'); }
  }

  function openAddProfessor() {
    document.getElementById('professorModal').style.display = 'flex';
    document.getElementById('profError').style.display = 'none';
    document.getElementById('profName').value = '';
    document.getElementById('profEmail').value = '';
    document.getElementById('profPassword').value = '';
  }

  async function saveProfessor() {
    const btn = document.getElementById('saveProfBtn');
    const errEl = document.getElementById('profError');
    btn.disabled = true; btn.textContent = 'Saving...'; errEl.style.display = 'none';
    try {
      await axios.post('/api/admin/users/professor', {
        name: document.getElementById('profName').value,
        email: document.getElementById('profEmail').value,
        password: document.getElementById('profPassword').value,
      });
      document.getElementById('professorModal').style.display = 'none';
      loadUsers();
    } catch (e) {
      const errors = e.response?.data?.errors;
      errEl.textContent = '❌ ' + (errors ? Object.values(errors)[0][0] : e.response?.data?.message || 'Failed.');
      errEl.style.display = 'block';
    } finally { btn.disabled = false; btn.textContent = 'Add Professor'; }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadUsers();
</script>
@endsection