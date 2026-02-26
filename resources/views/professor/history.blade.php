@extends('layouts.app')
@section('title', 'Session History – ClassGuard')

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/professor/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/professor/subjects') }}" class="nav-item"><span class="nav-icon">📚</span><span>My Subjects</span></a>
    <a href="{{ url('/professor/live-attendance') }}" class="nav-item"><span class="nav-icon">📡</span><span>Live Attendance</span></a>
    <a href="{{ url('/professor/history') }}" class="nav-item active"><span class="nav-icon">📋</span><span>Session History</span></a>
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
      <h1>Session History</h1>
      <p class="topbar-subtitle">Past attendance sessions across all subjects</p>
    </div>
  </div>

  <div class="content">
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr); margin-bottom:24px;">
      <div class="stat-card"><div class="stat-label">Total Sessions</div><div class="stat-value" id="totalSessions">–</div><div class="stat-change info">This semester</div></div>
      <div class="stat-card"><div class="stat-label">Total Present</div><div class="stat-value" style="color:var(--green)" id="totalPresent">–</div><div class="stat-change positive">Across all sessions</div></div>
      <div class="stat-card"><div class="stat-label">Total Absent</div><div class="stat-value" style="color:var(--red)" id="totalAbsent">–</div><div class="stat-change negative">Across all sessions</div></div>
    </div>

    <div class="section">
      <div class="section-header">
        <h2 class="section-title">All Sessions</h2>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Date & Time</th>
              <th>Subject</th>
              <th>Section</th>
              <th>Present</th>
              <th>Late</th>
              <th>Absent</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="historyTable">
            <tr><td colspan="8" style="text-align:center; padding:32px; color:var(--gray-400);">Loading...</td></tr>
          </tbody>
        </table>
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
  axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;

  document.getElementById('userName').textContent = user.name || 'Professor';
  document.getElementById('userAvatar').textContent = (user.name || 'P').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();

  function formatDate(dt) {
    if (!dt) return '–';
    return new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }
  function formatTime(dt) {
    if (!dt) return '–';
    return new Date(dt).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  }

  async function loadHistory() {
    try {
      const res = await axios.get('/api/session/history');
      const sessions = res.data.sessions;

      const totalPresent = sessions.reduce((a, s) => a + s.present_count, 0);
      const totalAbsent = sessions.reduce((a, s) => a + s.absent_count, 0);
      document.getElementById('totalSessions').textContent = sessions.length;
      document.getElementById('totalPresent').textContent = totalPresent;
      document.getElementById('totalAbsent').textContent = totalAbsent;

      if (sessions.length === 0) {
        document.getElementById('historyTable').innerHTML = '<tr><td colspan="8" style="text-align:center; padding:32px; color:var(--gray-400);">No sessions yet.</td></tr>';
        return;
      }

      document.getElementById('historyTable').innerHTML = sessions.map(s => {
        const total = s.present_count + s.late_count + s.absent_count;
        const rate = total > 0 ? (((s.present_count + s.late_count) / total) * 100).toFixed(0) : 0;
        const rateColor = rate >= 75 ? 'var(--green)' : rate >= 50 ? '#D97706' : 'var(--red)';
        return `
          <tr>
            <td><strong>${formatDate(s.started_at)}</strong><br><span style="font-size:12px;color:var(--gray-500);">${formatTime(s.started_at)}${s.ended_at ? ' – ' + formatTime(s.ended_at) : ''}</span></td>
            <td>${s.subject?.name || '–'}</td>
            <td>${s.subject?.section?.name || '–'}</td>
            <td style="color:var(--green); font-weight:600">${s.present_count}</td>
            <td style="color:#D97706; font-weight:600">${s.late_count}</td>
            <td style="color:var(--red); font-weight:600">${s.absent_count}</td>
            <td><span style="color:${rateColor}; font-weight:700">${rate}%</span></td>
            <td>
              <a href="{{ url('/professor/live-attendance') }}?session=${s.session_id}" class="btn btn-ghost btn-sm">View</a>
            </td>
          </tr>`;
      }).join('');
    } catch (e) {
      document.getElementById('historyTable').innerHTML = '<tr><td colspan="8" style="text-align:center; color:var(--red); padding:24px;">Failed to load history.</td></tr>';
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadHistory();
</script>
@endsection
