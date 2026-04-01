@extends('layouts.app')
@section('title', 'Professor Dashboard – ClassGuard')

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/professor/dashboard') }}" class="nav-item active"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/professor/subjects') }}" class="nav-item"><span class="nav-icon">📚</span><span>My Subjects</span></a>
    <a href="{{ url('/professor/live-attendance') }}" class="nav-item"><span class="nav-icon">📡</span><span>Live Attendance</span></a>
    <a href="{{ url('/professor/history') }}" class="nav-item"><span class="nav-icon">📋</span><span>Session History</span></a>
    <a href="{{ url('/professor/students') }}" class="nav-item"><span class="nav-icon">👥</span><span>My Students</span></a>
    <div class="nav-divider"></div>
    <div class="nav-section">Account</div>
    <a href="#" class="nav-item" onclick="logout()"><span class="nav-icon">🚪</span><span>Sign Out</span></a>
  </nav>
  <div class="user-section">
    <div class="user-info">
      <div class="user-avatar" id="userAvatar">PS</div>
      <div><div class="user-name" id="userName">Loading...</div><div class="user-role">Professor</div></div>
    </div>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h1>Dashboard</h1>
      <p class="topbar-subtitle" id="topbarSubtitle">Loading...</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="window.location.href='{{ url('/professor/live-attendance') }}'">▶ Start Session</button>
    </div>
  </div>

  <div class="content">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-header">
          <div><div class="stat-label">My Subjects</div><div class="stat-value" id="subjectCount">–</div><div class="stat-change info">This semester</div></div>
          <div class="stat-icon gold">📚</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div><div class="stat-label">Active Sessions</div><div class="stat-value" id="activeCount" style="color:var(--green)">–</div><div class="stat-change positive">Live right now</div></div>
          <div class="stat-icon green">📡</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div><div class="stat-label">Total Sessions</div><div class="stat-value" id="historyCount">–</div><div class="stat-change info">All time</div></div>
          <div class="stat-icon blue">📋</div>
        </div>
      </div>
    </div>

    <div class="grid-2">
      <!-- My Subjects -->
      <div class="section">
        <div class="section-header">
          <h2 class="section-title">My Subjects</h2>
          <a href="{{ url('/professor/subjects') }}" class="btn btn-ghost btn-sm">Manage</a>
        </div>
        <div id="subjectsList">
          <div style="text-align:center; padding:32px; color:var(--gray-400);">Loading subjects...</div>
        </div>
      </div>

      <!-- Active Sessions -->
      <div class="section">
        <div class="section-header">
          <h2 class="section-title">Active Sessions</h2>
          <span class="live-badge"><span class="live-dot"></span>LIVE</span>
        </div>
        <div id="activeSessions">
          <div style="text-align:center; padding:32px; color:var(--gray-400);">No active sessions</div>
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
  if (!token || user.role !== 'professor') { localStorage.clear(); window.location.href = '/login'; }

  document.getElementById('userName').textContent = user.name || 'Professor';
  document.getElementById('userAvatar').textContent = (user.name || 'P').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();
  document.getElementById('topbarSubtitle').textContent = new Date().toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' }) + ' · Welcome back, ' + (user.name || 'Professor');

  async function loadDashboard() {
    try {
      const [subjectsRes, activeRes, historyRes] = await Promise.all([
        axios.get('/api/admin/subjects'),
        axios.get('/api/session/active'),
        axios.get('/api/session/history'),
      ]);

      const mySubjects = subjectsRes.data.subjects.filter(s => s.professor_id === user.id);
      const activeSessions = activeRes.data.sessions;
      const history = historyRes.data.sessions;

      document.getElementById('subjectCount').textContent = mySubjects.length;
      document.getElementById('activeCount').textContent = activeSessions.length;
      document.getElementById('historyCount').textContent = history.length;

      if (mySubjects.length === 0) {
        document.getElementById('subjectsList').innerHTML = '<div style="text-align:center; padding:32px; color:var(--gray-400);">No subjects assigned yet.</div>';
      } else {
        document.getElementById('subjectsList').innerHTML = mySubjects.map(s => {
          const isActive = activeSessions.find(a => a.subject_id === s.id);
          return `
            <div style="border:1px solid ${isActive ? 'var(--green)' : 'var(--gray-200)'}; border-radius:8px; padding:16px; display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; background:${isActive ? 'rgba(16,185,129,0.02)' : 'transparent'};">
              <div>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:2px;">
                  <div style="font-size:14px; font-weight:600; color:var(--gray-900);">${s.name}</div>
                  ${isActive ? '<span class="live-badge"><span class="live-dot"></span>LIVE</span>' : ''}
                </div>
                <div style="font-size:12px; color:var(--gray-500);">${s.code} · ${s.section?.name || ''}</div>
                <div style="margin-top:6px; font-size:12px; color:var(--gray-500);">Class Code: <strong>${s.class_code}</strong></div>
              </div>
              <div style="display:flex; gap:8px;">
                ${isActive
                  ? `<a href="{{ url('/professor/live-attendance') }}?session=${isActive.session_id}" class="btn btn-success btn-sm">View Live</a>`
                  : `<button class="btn btn-primary btn-sm" onclick="startSession(${s.id})">▶ Start</button>`
                }
                <a href="{{ url('/professor/history') }}" class="btn btn-ghost btn-sm">History</a>
              </div>
            </div>`;
        }).join('');
      }

      if (activeSessions.length === 0) {
        document.getElementById('activeSessions').innerHTML = '<div style="text-align:center; padding:32px; color:var(--gray-400);">No active sessions right now.</div>';
      } else {
        document.getElementById('activeSessions').innerHTML = activeSessions.map(s => `
          <div class="list-item">
            <div class="list-left">
              <div class="item-icon" style="background:rgba(16,185,129,0.1)">📡</div>
              <div class="item-details">
                <h4>${s.subject?.name || 'Unknown Subject'}</h4>
                <p>Started ${new Date(s.started_at).toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'})}</p>
              </div>
            </div>
            <a href="{{ url('/professor/live-attendance') }}?session=${s.session_id}" class="btn btn-success btn-sm">View</a>
          </div>`).join('');
      }

    } catch (e) {
      console.error('Dashboard error:', e);
    }
  }

  async function startSession(subjectId) {
    try {
      const res = await axios.post('/api/session/start', { subject_id: subjectId });
      window.location.href = '/professor/live-attendance?session=' + res.data.session.session_id;
    } catch (e) {
      alert(e.response?.data?.message || 'Failed to start session.');
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadDashboard();
</script>
@endsection