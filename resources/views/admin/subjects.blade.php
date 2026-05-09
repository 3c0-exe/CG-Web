@extends('layouts.app')
@section('title', 'Master Subjects – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item {{ request()->is('admin/users') ? 'active' : '' }}"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item {{ request()->is('admin/sections') ? 'active' : '' }}"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <a href="{{ url('/admin/subjects') }}" class="nav-item active"><span class="nav-icon">📚</span><span>Master Subjects</span></a>
    <a href="{{ url('/admin/schedules') }}" class="nav-item {{ request()->is('admin/schedules') ? 'active' : '' }}"><span class="nav-icon">📅</span><span>Schedules</span></a>
    <a href="{{ url('/admin/prospectus') }}" class="nav-item {{ request()->is('admin/prospectus') ? 'active' : '' }}"><span class="nav-icon">📋</span><span>Prospectus</span></a>
    <a href="{{ url('/admin/rooms') }}" class="nav-item {{ request()->is('admin/rooms') ? 'active' : '' }}"><span class="nav-icon">🏠</span><span>Rooms</span></a>
    <a href="{{ url('/admin/devices') }}" class="nav-item {{ request()->is('admin/devices') ? 'active' : '' }}"><span class="nav-icon">📡</span><span>Devices</span></a>
    <div class="nav-divider"></div>
    <a href="#" class="nav-item" onclick="openPasswordModal()"><span class="nav-icon">🔒</span><span>Change Password</span></a>
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
      <h1>Master Subjects</h1>
      <p class="topbar-subtitle">Manage master subject records and course codes</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openAddSubject()">+ Add Master Subject</button>
    </div>
  </div>

  <div class="content">
    <div class="section" style="padding:16px 24px; margin-bottom:0; border-bottom:none; border-radius:8px 8px 0 0;">
      <div style="display:flex; gap:12px; align-items:center;">
        <div class="search-wrap" style="flex:1;">
          <span class="search-icon">🔍</span>
          <input type="text" class="form-input" id="searchInput" placeholder="Search by name or code..." style="padding-left:36px;" oninput="filterSubjects(this.value)">
        </div>
      </div>
    </div>

    <div class="section" style="padding:0; overflow:hidden; border-radius:0 0 8px 8px;">
      <div class="table-wrapper" style="padding:0;">
        <table>
          <thead>
            <tr>
              <th>Subject Name</th>
              <th>Course Code</th>
              <th>Year Level</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="subjectsTable">
            <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--gray-400);">Loading master subjects...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<div id="subjectModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:440px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;" id="modalTitle">📚 Add Master Subject</h3>
    <div id="subjectError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <input type="hidden" id="editSubjectId">
    
    <div class="form-group">
      <label class="form-label">Subject Name</label>
      <input type="text" class="form-input" id="subjName" placeholder="e.g. Introduction to Computing">
    </div>
    <div class="form-group">
      <label class="form-label">Course Code (Optional)</label>
      <input type="text" class="form-input" id="subjCode" placeholder="e.g. CS101">
    </div>
    <div class="form-group">
      <label class="form-label">Year Level</label>
      <select class="form-select" id="subjYearLevel">
        <option value="">Select year level...</option>
      </select>
    </div>
    
    <div style="display:flex; gap:12px; margin-top:24px;">
      <button class="btn btn-ghost" style="flex:1" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="saveSubjBtn" onclick="saveSubject()">Save Subject</button>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'admin') { localStorage.clear(); window.location.href = '/login'; }

  let allSubjects = [];
  let yearLevelsData = [];

  async function loadData() {
    try {
      const [ylRes, subjRes] = await Promise.all([
        axios.get('/api/admin/year-levels'),
        axios.get('/api/admin/subjects')
      ]);
      
      yearLevelsData = ylRes.data.year_levels;
      allSubjects = subjRes.data.subjects;
      
      populateDropdowns();
      renderSubjects(allSubjects);
    } catch (e) {
      document.getElementById('subjectsTable').innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--red); padding:24px;">Failed to load data.</td></tr>';
    }
  }

  function populateDropdowns() {
    const ylOpts = '<option value="">Select year level...</option>' + 
      yearLevelsData.map(yl => `<option value="${yl.id}">${yl.name}</option>`).join('');
    document.getElementById('subjYearLevel').innerHTML = ylOpts;
  }

  function filterSubjects(q) {
    q = q.toLowerCase();
    const filtered = allSubjects.filter(s => 
      s.name.toLowerCase().includes(q) || 
      (s.code && s.code.toLowerCase().includes(q))
    );
    renderSubjects(filtered);
  }

  function renderSubjects(list) {
    const tbody = document.getElementById('subjectsTable');
    if (!list.length) {
      tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:var(--gray-400);">No subjects found.</td></tr>';
      return;
    }
    
    tbody.innerHTML = list.map(s => {
      return `
        <tr>
          <td>
            <div style="font-weight:600; color:var(--gray-900);">${s.name}</div>
          </td>
          <td>
             <span style="font-family:monospace; background:var(--gray-100); padding:2px 6px; border-radius:4px; font-size:12px;">${s.code || 'N/A'}</span>
          </td>
          <td>
            <span style="font-size:12px; background:rgba(59,91,219,0.1); color:var(--primary); padding:4px 8px; border-radius:12px; font-weight:600;">
              ${s.year_level?.name || 'N/A'}
            </span>
          </td>
          <td>
            <div style="display:flex; gap:6px;">
              <button class="btn btn-ghost btn-sm" onclick='openEditSubject(${JSON.stringify(s).replace(/'/g, "&apos;")})'>✏️ Edit</button>
              <button class="btn btn-ghost btn-sm" style="color:var(--red);" onclick="deleteSubject(${s.id})">🗑️</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function openAddSubject() {
    document.getElementById('modalTitle').innerHTML = '📚 Add Master Subject';
    document.getElementById('editSubjectId').value = '';
    document.getElementById('subjName').value = '';
    document.getElementById('subjCode').value = '';
    document.getElementById('subjYearLevel').value = '';
    
    document.getElementById('subjectError').style.display = 'none';
    document.getElementById('subjectModal').style.display = 'flex';
  }

  function openEditSubject(s) {
    document.getElementById('modalTitle').innerHTML = '✏️ Edit Master Subject';
    document.getElementById('editSubjectId').value = s.id;
    document.getElementById('subjName').value = s.name;
    document.getElementById('subjCode').value = s.code || '';
    document.getElementById('subjYearLevel').value = s.year_level_id;
    
    document.getElementById('subjectError').style.display = 'none';
    document.getElementById('subjectModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('subjectModal').style.display = 'none';
  }

  async function saveSubject() {
    const id = document.getElementById('editSubjectId').value;
    const name = document.getElementById('subjName').value.trim();
    const code = document.getElementById('subjCode').value.trim();
    const yl = document.getElementById('subjYearLevel').value;
    
    const errEl = document.getElementById('subjectError');
    if (!name || !yl) {
      errEl.textContent = '❌ Please fill in all required fields.';
      errEl.style.display = 'block';
      return;
    }
    
    const payload = { 
      name, 
      code: code || null,
      year_level_id: yl
    };
    
    const btn = document.getElementById('saveSubjBtn');
    btn.disabled = true; btn.textContent = 'Saving...';
    errEl.style.display = 'none';
    
    try {
      if (id) await axios.patch('/api/admin/subjects/' + id, payload);
      else await axios.post('/api/admin/subjects', payload);
      
      closeModal();
      await loadData();
    } catch(e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to save subject.');
      errEl.style.display = 'block';
    } finally {
      btn.disabled = false; btn.textContent = 'Save Subject';
    }
  }

  async function deleteSubject(id) {
    if(!confirm('Delete this master subject?')) return;
    try {
      await axios.delete('/api/admin/subjects/' + id);
      await loadData();
    } catch(e) {
      alert(e.response?.data?.message || 'Failed to delete');
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadData();
</script>
@endsection