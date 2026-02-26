@extends('layouts.app')
@section('title', 'Admin Dashboard – ClassGuard')

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item active"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item"><span class="nav-icon">👥</span><span>User Management</span></a>
    <div class="nav-divider"></div>
    <div class="nav-section">System</div>
    <a href="#" class="nav-item"><span class="nav-icon">⚙️</span><span>Settings</span></a>
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
      <h1>Admin Dashboard</h1>
      <p class="topbar-subtitle" id="topbarSubtitle">System overview</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="window.location.href='{{ url('/admin/users') }}'">+ Add User</button>
      <button class="icon-btn notif-dot">🔔</button>
    </div>
  </div>

  <div class="content">
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
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
      <div class="stat-card">
        <div class="stat-header">
          <div><div class="stat-label">Pending Approvals</div><div class="stat-value" id="pendingUsers" style="color:#D97706">–</div><div class="stat-change warning">New registrations</div></div>
          <div class="stat-icon gold">⏳</div>
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
      <!-- Pending Approvals -->
      <div class="section">
        <div class="section-header">
          <h2 class="section-title">Pending Approvals</h2>
          <a href="{{ url('/admin/users') }}" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div id="pendingList">
          <div style="text-align:center; padding:32px; color:var(--gray-400);">Loading...</div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="section">
        <div class="section-header"><h2 class="section-title">Quick Actions</h2></div>
        <div style="display:flex; flex-direction:column; gap:12px;">
          <a href="{{ url('/admin/users') }}" class="btn btn-primary" style="justify-content:center;">👥 Manage Users</a>
          <button class="btn btn-ghost" style="justify-content:center;" onclick="alert('Add Subject – coming soon')">📚 Add Subject</button>
          <button class="btn btn-ghost" style="justify-content:center;" onclick="alert('Add Professor – use User Management')">👨‍🏫 Add Professor</button>
        </div>

        <div style="margin-top:24px;">
          <div class="section-header"><h2 class="section-title">System Stats</h2></div>
          <div id="systemStats" style="font-size:14px; color:var(--gray-500); line-height:2;">Loading...</div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection

@section('scripts')
<script>
  // token is declared in layouts/app.blade.php
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'admin') { localStorage.clear(); window.location.href = '/login'; }
  axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;

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
      document.getElementById('pendingUsers').textContent = stats.pending_users;
      document.getElementById('totalSubjects').textContent = stats.total_subjects;

      document.getElementById('systemStats').innerHTML = `
        <div>👥 Total users: <strong>${stats.total_students + stats.total_professors}</strong></div>
        <div>📚 Total subjects: <strong>${stats.total_subjects}</strong></div>
        <div>📡 Active sessions: <strong>${stats.active_sessions}</strong></div>
        <div>⏳ Pending approvals: <strong>${stats.pending_users}</strong></div>`;

      // Pending users
      const pendingUsers = usersRes.data.users.filter(u => u.status === 'pending').slice(0, 5);
      if (pendingUsers.length === 0) {
        document.getElementById('pendingList').innerHTML = '<div style="text-align:center; padding:24px; color:var(--gray-400);">No pending approvals.</div>';
      } else {
        document.getElementById('pendingList').innerHTML = pendingUsers.map(u => {
          const initials = (u.name || 'U').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();
          return `
            <div class="list-item" id="pending-${u.id}">
              <div class="list-left">
                <div class="user-avatar" style="background:var(--navy-blue); color:var(--white);">${initials}</div>
                <div class="item-details">
                  <h4>${u.name}</h4>
                  <p>Student · ${u.student_id_number || '–'} · ${u.section?.name || '–'}</p>
                </div>
              </div>
              <div style="display:flex; gap:6px;">
                <button class="btn btn-success btn-sm" onclick="approveUser(${u.id})">✓</button>
                <button class="btn btn-ghost btn-sm" style="color:var(--red);" onclick="rejectUser(${u.id})">✕</button>
              </div>
            </div>`;
        }).join('');
      }
    } catch (e) {
      console.error('Admin dashboard error:', e);
    }
  }

  async function approveUser(id) {
    try {
      await axios.patch(`/api/admin/users/${id}/status`, { status: 'active' });
      document.getElementById('pending-' + id)?.remove();
    } catch (e) { alert('Failed to approve user.'); }
  }

  async function rejectUser(id) {
    if (!confirm('Reject this registration?')) return;
    try {
      await axios.patch(`/api/admin/users/${id}/status`, { status: 'inactive' });
      document.getElementById('pending-' + id)?.remove();
    } catch (e) { alert('Failed to reject user.'); }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadDashboard();
</script>
@endsection
