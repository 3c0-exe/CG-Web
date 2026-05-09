@extends('layouts.app')
@section('title', 'Admin Dashboard – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item active"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <a href="{{ url('/admin/subjects') }}" class="nav-item"><span class="nav-icon">📚</span><span>Master Subjects</span></a>
    <a href="{{ url('/admin/schedules') }}" class="nav-item"><span class="nav-icon">📅</span><span>Schedules</span></a>
    <a href="{{ url('/admin/prospectus') }}" class="nav-item"><span class="nav-icon">📋</span><span>Prospectus</span></a>
    <a href="{{ url('/admin/rooms') }}" class="nav-item"><span class="nav-icon">🏠</span><span>Rooms</span></a>
    <a href="{{ url('/admin/devices') }}" class="nav-item"><span class="nav-icon">📡</span><span>Devices</span></a>
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
      <h1>Admin Dashboard</h1>
      <p class="topbar-subtitle" id="topbarSubtitle">System overview</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="window.location.href='{{ url('/admin/users') }}'">+ Add User</button>
    </div>
  </div>

  <div class="content">
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
      <div class="stat-card">
        <div class="stat-header">
          <div><div class="stat-label">Total Students</div><div class="stat-value" id="totalStudents">–</div><div class="stat-change info">Registered</div></div>
          <div class="stat-icon gold">👥</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div><div class="stat-label">Professors</div><div class="stat-value" id="totalProfessors">–</div><div class="stat-change info">Active faculty</div></div>
          <div class="stat-icon blue">👨‍🏫</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div><div class="stat-label">Active Sessions</div><div class="stat-value" id="activeSessions" style="color:var(--green)">–</div><div class="stat-change positive">Live right now</div></div>
          <div class="stat-icon green">📡</div>
        </div>
      </div>
    </div>

    <div class="stats-grid" style="grid-template-columns:repeat(2,1fr);">
      <div class="stat-card">
        <div class="stat-header">
          <div><div class="stat-label">Total Subjects</div><div class="stat-value" id="totalSubjects">–</div><div class="stat-change info">This semester</div></div>
          <div class="stat-icon blue">📚</div>
        </div>
      </div>
    </div>

    <div class="grid-2">
      <!-- Recent Users -->
      <div class="section">
        <div class="section-header">
          <h2 class="section-title">Recent Students</h2>
          <a href="{{ url('/admin/users') }}" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div id="recentStudentsList">
          <div style="text-align:center; padding:32px; color:var(--gray-400);">Loading...</div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="section">
        <div class="section-header"><h2 class="section-title">Quick Actions</h2></div>
        <div style="display:flex; flex-direction:column; gap:12px;">
          <a href="{{ url('/admin/users') }}" class="btn btn-primary" style="justify-content:center;">👥 Manage Users</a>
          <a href="{{ url('/admin/subjects') }}" class="btn btn-ghost" style="justify-content:center;">📚 Manage Subjects</a>
          <button class="btn btn-ghost" style="justify-content:center;" onclick="openAddSubject()">➕ Add Subject</button>
          <button class="btn btn-ghost" style="justify-content:center;" onclick="window.location.href='{{ url('/admin/users') }}'">👨‍🏫 Add Professor</button>
        </div>

        <div style="margin-top:24px;">
          <div class="section-header"><h2 class="section-title">System Stats</h2></div>
          <div id="systemStats" style="font-size:14px; color:var(--gray-500); line-height:2;">Loading...</div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Add Subject Modal -->
<div id="subjectModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">📚 Add New Subject</h3>
    <div id="subjectError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <div class="form-group">
      <label class="form-label">Subject Name</label>
      <input type="text" class="form-input" id="subjName" placeholder="e.g. Introduction to Computing">
    </div>
    <div class="form-group">
      <label class="form-label">Subject Code</label>
      <input type="text" class="form-input" id="subjCode" placeholder="e.g. ITC001">
    </div>
    <div class="form-group">
      <label class="form-label">Year Level</label>
      <select class="form-select" id="subjYearLevel">
        <option value="">Select year level...</option>
      </select>
    </div>
    <div style="font-size:12px; color:var(--gray-500); margin-top:16px;">
      Note: Sections, Professors, and Times are assigned under the <b>Schedules</b> tab after creating this Master Subject.
    </div>
    <div style="display:flex; gap:12px; margin-top:8px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('subjectModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="saveSubjBtn" onclick="saveSubject()">Create Subject</button>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
  // token is declared in layouts/app.blade.php
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'admin') { localStorage.clear(); window.location.href = '/login'; }

  document.getElementById('topbarSubtitle').textContent = 'System overview · ' + new Date().toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

  async function loadDashboard() {
    try {
      const [statsRes, usersRes] = await Promise.all([
        axios.get('/api/admin/stats'),
        axios.get('/api/admin/users?role=student'),
      ]);

      const stats = statsRes.data.stats;
      document.getElementById('totalStudents').textContent = stats.total_students;
      document.getElementById('totalProfessors').textContent = stats.total_professors;
      document.getElementById('activeSessions').textContent = stats.active_sessions;
      document.getElementById('totalSubjects').textContent = stats.total_subjects;

      document.getElementById('systemStats').innerHTML = `
        <div>👥 Total users: <strong>${stats.total_students + stats.total_professors}</strong></div>
        <div>📚 Total subjects: <strong>${stats.total_subjects}</strong></div>
        <div>📡 Active sessions: <strong>${stats.active_sessions}</strong></div>`;

      // Show the 5 most recently added students
      const recentStudents = usersRes.data.users.slice(0, 5);
      if (recentStudents.length === 0) {
        document.getElementById('recentStudentsList').innerHTML = '<div style="text-align:center; padding:24px; color:var(--gray-400);">No students imported yet.</div>';
      } else {
        document.getElementById('recentStudentsList').innerHTML = recentStudents.map(u => {
          const initials = (u.name || 'U').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();
          return `
            <div class="list-item">
              <div class="list-left">
                <div class="user-avatar" style="background:var(--navy-blue); color:var(--white);">${initials}</div>
                <div class="item-details">
                  <h4>${u.name}</h4>
                  <p>Student · ${u.student_id_number || '–'} · ${u.section?.name || '–'}</p>
                </div>
              </div>
            </div>`;
        }).join('');
      }
    } catch (e) {
      console.error('Admin dashboard error:', e);
    }
  }

  let yearLevelsData = [];
  let professorsData = [];

  async function openAddSubject() {
    document.getElementById('subjectModal').style.display = 'flex';
    document.getElementById('subjectError').style.display = 'none';
    document.getElementById('subjName').value = '';
    document.getElementById('subjCode').value = '';
    try {
      const ylRes = await axios.get('/api/admin/year-levels');
      yearLevelsData = ylRes.data.year_levels;
      document.getElementById('subjYearLevel').innerHTML =
        '<option value="">Select year level...</option>' +
        yearLevelsData.map(yl => `<option value="${yl.id}">${yl.name}</option>`).join('');
    } catch (e) { console.error('Failed to load form data', e); }
  }

  async function saveSubject() {
    const btn = document.getElementById('saveSubjBtn');
    const errEl = document.getElementById('subjectError');
    const name = document.getElementById('subjName').value.trim();
    const code = document.getElementById('subjCode').value.trim();
    const yearLevelId = document.getElementById('subjYearLevel').value;

    if (!name || !code || !yearLevelId) {
      errEl.textContent = '❌ Please fill in all required fields.';
      errEl.style.display = 'block';
      return;
    }

    btn.disabled = true; btn.textContent = 'Creating...'; errEl.style.display = 'none';
    try {
      await axios.post('/api/admin/subjects', {
        name, code,
        year_level_id: yearLevelId,
      });
      document.getElementById('subjectModal').style.display = 'none';
      loadDashboard();
      alert('✅ Subject created successfully!');
    } catch (e) {
      const errors = e.response?.data?.errors;
      errEl.textContent = '❌ ' + (errors ? Object.values(errors)[0][0] : e.response?.data?.message || 'Failed to create subject.');
      errEl.style.display = 'block';
    } finally { btn.disabled = false; btn.textContent = 'Create Subject'; }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadDashboard();
</script>
@endsection