@extends('layouts.app')
@section('title', 'User Management – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item active"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <a href="{{ url('/admin/subjects') }}" class="nav-item"><span class="nav-icon">📚</span><span>Master Subjects</span></a>
    <a href="{{ url('/admin/schedules') }}" class="nav-item"><span class="nav-icon">📅</span><span>Schedules</span></a>
    <a href="{{ url('/admin/prospectus') }}" class="nav-item"><span class="nav-icon">📋</span><span>Prospectus</span></a>
    <a href="{{ url('/admin/rooms') }}" class="nav-item"><span class="nav-icon">🏠</span><span>Rooms</span></a>
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
      <h1>User Management</h1>
      <p class="topbar-subtitle">Manage students, professors, and admins</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openAddProfessor()">+ Add Professor</button>
    </div>
  </div>

  <div class="content">

    <div class="section" style="padding: 24px; margin-bottom: 20px; overflow: hidden; border: 1px solid var(--gray-200); border-radius: 8px;">
      <h3 style="font-size:16px; font-weight:600; margin-bottom:8px;">Bulk Import Students (CSV)</h3>
      <p style="font-size:13px; color:var(--gray-500); margin-bottom:16px;">
        Upload a CSV file with the following column headers: <strong>name, email, student_id_number, rfid_uid</strong>. Section assignment is done separately after import.
      </p>
      
      <form id="csvUploadForm" style="display:flex; gap:12px; align-items:center;">
        <input type="file" id="csvFile" accept=".csv, .txt" required style="font-size:13px; padding:8px; border: 1px solid var(--gray-200); border-radius: 6px; flex: 1; max-width: 400px;">
        <button type="submit" id="uploadBtn" class="btn btn-primary">Upload & Import</button>
      </form>

      <div id="uploadStatus" style="display:none; margin-top:16px; padding:12px 16px; border-radius:6px; font-size:13px; font-weight: 500;"></div>
    </div>

    <div class="section" style="padding:0; overflow:hidden;">
      <div style="display:flex; border-bottom:1px solid var(--gray-200);">
        <button class="tab-btn" id="tab-all" onclick="setTab('all')" style="padding:16px 24px; border:none; background:none; font-size:14px; font-weight:500; cursor:pointer; border-bottom:2px solid var(--navy-blue); color:var(--navy-blue);">All Users</button>
        <button class="tab-btn" id="tab-students" onclick="setTab('students')" style="padding:16px 24px; border:none; background:none; font-size:14px; font-weight:500; cursor:pointer; border-bottom:2px solid transparent; color:var(--gray-500);">Students</button>
        <button class="tab-btn" id="tab-professors" onclick="setTab('professors')" style="padding:16px 24px; border:none; background:none; font-size:14px; font-weight:500; cursor:pointer; border-bottom:2px solid transparent; color:var(--gray-500);">Professors</button>
      </div>

      <div style="padding:16px 24px; display:flex; gap:12px; align-items:center; background:var(--gray-50); border-bottom:1px solid var(--gray-200);">
        <div class="search-wrap" style="flex:1;">
          <span class="search-icon">🔍</span>
          <input type="text" class="form-input" placeholder="Search by name, email, or student ID..." style="padding-left:36px;" oninput="filterSearch(this.value)">
        </div>
      </div>

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

<div id="professorModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:460px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">Add Professor</h3>
    <div id="profError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
<div class="form-group">
      <label class="form-label">Title</label>
      <select class="form-input" id="profTitle">
        <option value="">Select title...</option>
        <option value="Prof.">Inst.</option>
        <option value="Prof.">Prof.</option>
        <option value="Ms.">Ms.</option>
        <option value="Mrs.">Mrs.</option>
        <option value="Mr.">Mr.</option>
        <option value="Dr.">Dr.</option>
        <option value="Engr.">Engr.</option>
        <option value="Atty.">Atty.</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Full Name</label>
      <input type="text" class="form-input" id="profName" placeholder="Juan Santos">
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

<div id="editUserModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:460px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">Edit User Details</h3>
    <div id="editError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    
    <input type="hidden" id="editUserId">
    
<div class="form-group" id="editTitleGroup" style="display:none;">
      <label class="form-label">Title</label>
      <select class="form-input" id="editTitle">
        <option value="">Select title...</option>
        <option value="Prof.">Prof.</option>
        <option value="Ms.">Ms.</option>
        <option value="Mrs.">Mrs.</option>
        <option value="Mr.">Mr.</option>
        <option value="Dr.">Dr.</option>
        <option value="Engr.">Engr.</option>
        <option value="Atty.">Atty.</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Full Name</label>
      <input type="text" class="form-input" id="editName">
    </div>
    <div class="form-group">
      <label class="form-label">Student ID (Optional)</label>
      <input type="text" class="form-input" id="editStudentId">
    </div>
    <div class="form-group">
      <label class="form-label">RFID UID (Editable)</label>
      <div style="display:flex; gap:8px; align-items:center;">
        <input type="text" class="form-input" id="editRfid" placeholder="e.g., A1:B2:C3:D4" style="flex:1;">
        <button type="button" class="btn btn-ghost" id="scanBtn" onclick="startEnrollScan()" style="white-space:nowrap; font-size:12px;">📡 Scan Card</button>
      </div>
      <p id="scanStatus" style="font-size: 11px; color: var(--gray-500); margin-top: 4px;">Update this if the student gets a new ID card.</p>
    </div>

    <div class="form-group" id="editIrregularGroup" style="display:none;">
      <label class="form-label">Student Type</label>
      <label style="display:flex; align-items:center; gap:10px; cursor:pointer; padding:10px 14px; border:1px solid var(--gray-200); border-radius:8px;">
        <input type="checkbox" id="editIsIrregular" style="width:16px; height:16px; cursor:pointer; accent-color:var(--navy-blue);">
        <div>
          <div style="font-size:13px; font-weight:500;">Mark as Irregular Student</div>
          <div style="font-size:11px; color:var(--gray-500);">Can be assigned to multiple sections.</div>
        </div>
      </label>
    </div>
    
    <div style="display:flex; gap:12px; margin-top: 24px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('editUserModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="saveEditBtn" onclick="saveUserEdit()">Save Changes</button>
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

  function sectionDisplay(u) {
    if (u.role !== 'student') return '–';
    if (u.is_irregular) {
      const chips = u.sections && u.sections.length
        ? u.sections.map(s => `<span class="badge info" style="margin-right:2px;">${s.name}</span>`).join('')
        : '<span class="badge warning">⚠ No Section</span>';
      return chips + ' <span class="badge gold" style="font-size:10px;">Irregular</span>';
    }
    return u.section ? u.section.name : '<span class="badge warning">⚠ No Section</span>';
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
              <div class="user-avatar" style="background:var(--navy-blue); color:var(--white); font-size:11px;">${initials}</div>
<div>
                <div style="font-size:13px; font-weight:600;">${u.role === 'professor' && u.title ? u.title + ' ' + u.name : u.name}</div>
                <div style="font-size:11px; color:var(--gray-500);">${u.email}</div>
              </div>
            </div>
          </td>
          <td>${roleBadge(u.role)}</td>
          <td style="font-size:13px; font-family:monospace;">${u.student_id_number || '–'}</td>
          <td style="font-size:13px;">${sectionDisplay(u)}</td>
          <td>
            ${u.rfid_uid
              ? `<span class="badge success">✓ Linked</span>`
              : `<span class="badge neutral">Not linked</span>`}
          </td>
          <td>${statusBadge(u.status)}</td>
          <td>
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
              <button class="btn btn-ghost btn-sm" onclick="openEditModal(${u.id})">✏️ Edit</button>
              ${u.role === 'student' ? `<button class="btn btn-ghost btn-sm" onclick="openAssignSection(${u.id})">🏫 Section</button>` : ''}
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
    if (currentTab === 'students') filtered = allUsers.filter(u => u.role === 'student');
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
    if (currentTab === 'students') filtered = filtered.filter(u => u.role === 'student');
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
    document.getElementById('profTitle').value = '';
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
        title: document.getElementById('profTitle').value,
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

  function openEditModal(userId) {
    const u = allUsers.find(u => u.id === userId);
    if (!u) return;

    document.getElementById('editUserId').value = u.id;
    document.getElementById('editName').value = u.name || '';
    document.getElementById('editStudentId').value = u.student_id_number || '';
    document.getElementById('editRfid').value = u.rfid_uid || '';

    const titleGroup = document.getElementById('editTitleGroup');
    if (u.role === 'professor') {
      titleGroup.style.display = 'block';
      document.getElementById('editTitle').value = u.title || '';
    } else {
      titleGroup.style.display = 'none';
      document.getElementById('editTitle').value = '';
    }

    const irregularGroup = document.getElementById('editIrregularGroup');
    if (u.role === 'student') {
      irregularGroup.style.display = 'block';
      document.getElementById('editIsIrregular').checked = !!u.is_irregular;
    } else {
      irregularGroup.style.display = 'none';
      document.getElementById('editIsIrregular').checked = false;
    }

    document.getElementById('editError').style.display = 'none';
    document.getElementById('editUserModal').style.display = 'flex';
  }

  async function saveUserEdit() {
    const btn = document.getElementById('saveEditBtn');
    const errEl = document.getElementById('editError');
    const id = document.getElementById('editUserId').value;
    
    btn.disabled = true; btn.textContent = 'Saving...'; errEl.style.display = 'none';
    
    try {
      const payload = {
        title: document.getElementById('editTitle').value,
        name: document.getElementById('editName').value,
        student_id_number: document.getElementById('editStudentId').value,
        rfid_uid: document.getElementById('editRfid').value,
      };
      if (document.getElementById('editIrregularGroup').style.display !== 'none') {
        payload.is_irregular = document.getElementById('editIsIrregular').checked;
      }
      await axios.patch(`/api/admin/users/${id}`, payload);
      
      document.getElementById('editUserModal').style.display = 'none';
      loadUsers();
    } catch (e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to update user.');
      errEl.style.display = 'block';
    } finally { 
      btn.disabled = false; btn.textContent = 'Save Changes'; 
    }
  }

  // Handle CSV Bulk Upload via Axios
  document.getElementById('csvUploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('csvFile');
    const uploadBtn = document.getElementById('uploadBtn');
    const statusDiv = document.getElementById('uploadStatus');
    
    if (fileInput.files.length === 0) return;

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);

    uploadBtn.disabled = true;
    uploadBtn.innerText = 'Importing...';
    statusDiv.style.display = 'none';

    try {
      const response = await axios.post('/api/admin/users/import-students', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      statusDiv.style.display = 'block';
      statusDiv.style.background = 'rgba(16, 185, 129, 0.1)';
      statusDiv.style.color = '#065f46';
      statusDiv.style.border = '1px solid rgba(16, 185, 129, 0.3)';
      statusDiv.innerText = '✅ ' + response.data.message;
      
      fileInput.value = '';
      loadUsers();
      
    } catch (error) {
      statusDiv.style.display = 'block';
      statusDiv.style.background = 'rgba(239, 68, 68, 0.1)';
      statusDiv.style.color = '#991b1b';
      statusDiv.style.border = '1px solid rgba(239, 68, 68, 0.3)';
      statusDiv.innerText = '❌ ' + (error.response?.data?.message || 'Upload failed. Please check your CSV file format.');
    } finally {
      uploadBtn.disabled = false;
      uploadBtn.innerText = 'Upload & Import';
    }
  });

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  // ─── Assign Section ───────────────────────────────────────────────────────

  let yearLevelsData = [];

  async function openAssignSection(userId) {
    const u = allUsers.find(u => u.id === userId);
    if (!u) return;

    document.getElementById('assignUserId').value = userId;
    document.getElementById('assignStudentName').textContent = u.name;
    document.getElementById('assignError').style.display = 'none';

    try {
      if (!yearLevelsData.length) {
        const res = await axios.get('/api/admin/year-levels');
        yearLevelsData = res.data.year_levels;
      }
    } catch (e) { }

    const ylOptions = '<option value="">Select year level...</option>' +
      yearLevelsData.map(yl => `<option value="${yl.id}">${yl.name}</option>`).join('');

    if (u.is_irregular) {
      document.getElementById('assignRegularUI').style.display = 'none';
      document.getElementById('assignIrregularUI').style.display = 'block';
      document.getElementById('assignIrregularYearLevel').innerHTML = ylOptions;
      document.getElementById('assignIrregularSectionSelect').innerHTML = '<option value="">Select section...</option>';
      renderAssignedSections(userId, u.sections || []);
    } else {
      document.getElementById('assignRegularUI').style.display = 'block';
      document.getElementById('assignIrregularUI').style.display = 'none';
      document.getElementById('assignYearLevel').innerHTML = ylOptions;
      if (u.year_level_id) {
        document.getElementById('assignYearLevel').value = u.year_level_id;
        loadAssignSections(u.year_level_id, u.section_id);
      } else {
        document.getElementById('assignSectionSelect').innerHTML = '<option value="">Select section...</option>';
      }
    }

    document.getElementById('assignSectionModal').style.display = 'flex';
  }

  function loadAssignSections(yearLevelId, selectedSectionId = null) {
    const yl = yearLevelsData.find(y => y.id == yearLevelId);
    if (!yl || !yl.sections) {
      document.getElementById('assignSectionSelect').innerHTML = '<option value="">No sections found</option>';
      return;
    }
    document.getElementById('assignSectionSelect').innerHTML =
      '<option value="">Select section...</option>' +
      yl.sections.map(s => `<option value="${s.id}" ${s.id == selectedSectionId ? 'selected' : ''}>${s.name}</option>`).join('');
  }

  function loadIrregularSections(yearLevelId) {
    const yl = yearLevelsData.find(y => y.id == yearLevelId);
    if (!yl || !yl.sections) {
      document.getElementById('assignIrregularSectionSelect').innerHTML = '<option value="">No sections found</option>';
      return;
    }
    document.getElementById('assignIrregularSectionSelect').innerHTML =
      '<option value="">Select section...</option>' +
      yl.sections.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
  }

  function renderAssignedSections(userId, sections) {
    const container = document.getElementById('assignedSectionsList');
    if (!sections.length) {
      container.innerHTML = '<span style="font-size:13px; color:var(--gray-400);">No sections assigned yet.</span>';
      return;
    }
    container.innerHTML = sections.map(s =>
      `<span style="display:inline-flex; align-items:center; gap:6px; background:var(--navy-blue); color:white; font-size:12px; padding:4px 10px; border-radius:20px;">
        ${s.name}
        <button onclick="removeIrregularSection(${userId}, ${s.id})" style="background:none; border:none; color:white; cursor:pointer; font-size:16px; line-height:1; padding:0; opacity:0.8;">×</button>
      </span>`
    ).join('');
  }

  async function saveAssignSection() {
    const btn = document.getElementById('assignSaveBtn');
    const errEl = document.getElementById('assignError');
    const userId = document.getElementById('assignUserId').value;
    const yearLevelId = document.getElementById('assignYearLevel').value;
    const sectionId = document.getElementById('assignSectionSelect').value;

    if (!yearLevelId || !sectionId) {
      errEl.textContent = '❌ Please select both a year level and section.';
      errEl.style.display = 'block';
      return;
    }

    btn.disabled = true; btn.textContent = 'Saving...'; errEl.style.display = 'none';

    try {
      await axios.patch(`/api/admin/users/${userId}/assign-section`, {
        year_level_id: yearLevelId,
        section_id: sectionId,
      });
      document.getElementById('assignSectionModal').style.display = 'none';
      loadUsers();
    } catch (e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to assign section.');
      errEl.style.display = 'block';
    } finally {
      btn.disabled = false; btn.textContent = 'Save';
    }
  }

  async function addIrregularSection() {
    const btn = document.getElementById('addIrregularSectionBtn');
    const errEl = document.getElementById('assignError');
    const userId = document.getElementById('assignUserId').value;
    const yearLevelId = document.getElementById('assignIrregularYearLevel').value;
    const sectionId = document.getElementById('assignIrregularSectionSelect').value;

    if (!yearLevelId || !sectionId) {
      errEl.textContent = '❌ Please select both a year level and section.';
      errEl.style.display = 'block';
      return;
    }

    btn.disabled = true; btn.textContent = 'Adding...'; errEl.style.display = 'none';

    try {
      await axios.post(`/api/admin/users/${userId}/add-section`, {
        year_level_id: yearLevelId,
        section_id: sectionId,
      });
      await loadUsers();
      const updated = allUsers.find(u => u.id == userId);
      if (updated) renderAssignedSections(userId, updated.sections || []);
      document.getElementById('assignIrregularYearLevel').value = '';
      document.getElementById('assignIrregularSectionSelect').innerHTML = '<option value="">Select section...</option>';
    } catch (e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to add section.');
      errEl.style.display = 'block';
    } finally {
      btn.disabled = false; btn.textContent = 'Add Section';
    }
  }

  async function removeIrregularSection(userId, sectionId) {
    const errEl = document.getElementById('assignError');
    errEl.style.display = 'none';
    try {
      await axios.delete(`/api/admin/users/${userId}/remove-section/${sectionId}`);
      await loadUsers();
      const updated = allUsers.find(u => u.id == userId);
      if (updated) renderAssignedSections(userId, updated.sections || []);
    } catch (e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to remove section.');
      errEl.style.display = 'block';
    }
  }

  let enrollPollInterval = null;

  async function startEnrollScan() {
    const btn = document.getElementById('scanBtn');
    const status = document.getElementById('scanStatus');

    btn.disabled = true;
    btn.textContent = '⏳ Waiting...';
    status.textContent = 'Tap the RFID card on the reader now...';
    status.style.color = 'var(--navy-blue)';

    try {
      await axios.post('/api/admin/rfid/enroll/start');
    } catch (e) {
      status.textContent = 'Failed to start scan mode.';
      status.style.color = 'var(--red)';
      btn.disabled = false;
      btn.textContent = '📡 Scan Card';
      return;
    }

    enrollPollInterval = setInterval(async () => {
      try {
        const res = await axios.get('/api/admin/rfid/enroll/pending');
        if (res.data.uid) {
          clearInterval(enrollPollInterval);
          document.getElementById('editRfid').value = res.data.uid;
          status.textContent = '✅ Card scanned: ' + res.data.uid;
          status.style.color = 'green';
          btn.disabled = false;
          btn.textContent = '📡 Scan Card';
        }
      } catch (e) { }
    }, 1000);

    // Auto-cancel after 30s
    setTimeout(async () => {
      if (!enrollPollInterval) return;
      clearInterval(enrollPollInterval);
      await axios.delete('/api/admin/rfid/enroll/cancel');
      status.textContent = 'Scan timed out. Try again.';
      status.style.color = 'var(--red)';
      btn.disabled = false;
      btn.textContent = '📡 Scan Card';
    }, 30000);
  }

  // Cancel enroll if modal is closed mid-scan
  document.getElementById('editUserModal').querySelector('.btn-ghost').addEventListener('click', async () => {
    if (enrollPollInterval) {
      clearInterval(enrollPollInterval);
      enrollPollInterval = null;
      await axios.delete('/api/admin/rfid/enroll/cancel').catch(() => {});
    }
  });

  loadUsers();
</script>

<!-- Assign Section Modal -->
<div id="assignSectionModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:440px; max-height:90vh; overflow-y:auto;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:4px;">🏫 Assign Section</h3>
    <p style="font-size:13px; color:var(--gray-500); margin-bottom:20px;" id="assignStudentName">–</p>
    <div id="assignError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <input type="hidden" id="assignUserId">

    <!-- Regular student UI (one section) -->
    <div id="assignRegularUI">
      <div class="form-group">
        <label class="form-label">Year Level</label>
        <select class="form-select" id="assignYearLevel" onchange="loadAssignSections(this.value)">
          <option value="">Select year level...</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Section</label>
        <select class="form-select" id="assignSectionSelect">
          <option value="">Select section...</option>
        </select>
      </div>
      <div style="display:flex; gap:12px; margin-top:8px;">
        <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('assignSectionModal').style.display='none'">Cancel</button>
        <button class="btn btn-primary" style="flex:2" id="assignSaveBtn" onclick="saveAssignSection()">Save</button>
      </div>
    </div>

    <!-- Irregular student UI (multiple sections) -->
    <div id="assignIrregularUI" style="display:none;">
      <div style="margin-bottom:16px;">
        <p style="font-size:12px; font-weight:600; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Assigned Sections</p>
        <div id="assignedSectionsList" style="display:flex; flex-wrap:wrap; gap:8px; min-height:36px;"></div>
      </div>
      <div style="border-top:1px solid var(--gray-200); padding-top:16px; margin-top:4px;">
        <p style="font-size:12px; font-weight:600; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:12px;">Add Section</p>
        <div class="form-group">
          <label class="form-label">Year Level</label>
          <select class="form-select" id="assignIrregularYearLevel" onchange="loadIrregularSections(this.value)">
            <option value="">Select year level...</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Section</label>
          <select class="form-select" id="assignIrregularSectionSelect">
            <option value="">Select section...</option>
          </select>
        </div>
        <button class="btn btn-primary" style="width:100%;" id="addIrregularSectionBtn" onclick="addIrregularSection()">Add Section</button>
      </div>
      <div style="margin-top:16px;">
        <button class="btn btn-ghost" style="width:100%;" onclick="document.getElementById('assignSectionModal').style.display='none'">Done</button>
      </div>
    </div>
  </div>
</div>

@endsection