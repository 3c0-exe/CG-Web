@extends('layouts.app')
@section('title', 'Room Availability – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/professor/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/professor/subjects') }}" class="nav-item"><span class="nav-icon">📚</span><span>My Subjects</span></a>
    <a href="{{ url('/professor/live-attendance') }}" class="nav-item"><span class="nav-icon">📡</span><span>Live Attendance</span></a>
    <a href="{{ url('/professor/history') }}" class="nav-item"><span class="nav-icon">📋</span><span>Session History</span></a>
    <a href="{{ url('/professor/students') }}" class="nav-item"><span class="nav-icon">👥</span><span>My Students</span></a>
    <a href="{{ url('/professor/rooms') }}" class="nav-item active"><span class="nav-icon">🏠</span><span>Room Availability</span></a>
    <div class="nav-divider"></div>
    <div class="nav-section">Reports</div>
    <a href="{{ url('/professor/reports/section-attendance') }}" class="nav-item"><span class="nav-icon">📈</span><span>Section Attendance</span></a>
    <a href="{{ url('/professor/reports/student-attendance') }}" class="nav-item"><span class="nav-icon">🎓</span><span>Student Attendance</span></a>
    <a href="{{ url('/professor/reports/at-risk') }}" class="nav-item"><span class="nav-icon">⚠️</span><span>At-Risk Students</span></a>
    <div class="nav-divider"></div>
    <div class="nav-section">Account</div>
    <a href="#" class="nav-item" onclick="openPasswordModal()"><span class="nav-icon">🔒</span><span>Change Password</span></a>
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
      <h1>Room Availability</h1>
      <p class="topbar-subtitle" id="topbarSubtitle">Checking room status...</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-ghost" onclick="loadRooms()">↻ Refresh</button>
    </div>
  </div>

  <div class="content">
    <div id="summaryRow" style="display:none; margin-bottom:24px;">
      <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
          <div class="stat-label">Total Rooms</div>
          <div class="stat-value" id="statTotal">–</div>
        </div>
        <div class="stat-card" style="border-left:4px solid var(--green);">
          <div class="stat-label">Available</div>
          <div class="stat-value" style="color:var(--green)" id="statAvailable">–</div>
        </div>
        <div class="stat-card" style="border-left:4px solid var(--red);">
          <div class="stat-label">Occupied</div>
          <div class="stat-value" style="color:var(--red)" id="statOccupied">–</div>
        </div>
      </div>
    </div>

    <div id="roomsGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:16px;">
      <div style="text-align:center; padding:40px; color:var(--gray-400);">Loading rooms...</div>
    </div>

    <div style="margin-top:16px; font-size:12px; color:var(--gray-400); text-align:right;">
      Auto-refreshes every 30 seconds · Last updated: <span id="lastUpdated">–</span>
    </div>
  </div>
</main>
@endsection

@section('scripts')
<script>
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'professor') { localStorage.clear(); window.location.href = '/login'; }

  document.getElementById('userName').textContent = user.name || 'Professor';
  document.getElementById('userAvatar').textContent = (user.name || 'P').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();

  async function loadRooms() {
    try {
      const res = await axios.get('/api/rooms/availability');
      const rooms = res.data.rooms;

      const available = rooms.filter(r => r.status === 'available').length;
      const occupied  = rooms.filter(r => r.status === 'occupied').length;

      document.getElementById('statTotal').textContent    = rooms.length;
      document.getElementById('statAvailable').textContent = available;
      document.getElementById('statOccupied').textContent  = occupied;
      document.getElementById('summaryRow').style.display  = 'block';
      document.getElementById('topbarSubtitle').textContent =
        `${available} of ${rooms.length} rooms available`;
      document.getElementById('lastUpdated').textContent =
        new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

      if (rooms.length === 0) {
        document.getElementById('roomsGrid').innerHTML =
          '<div style="text-align:center; padding:40px; color:var(--gray-400);">No rooms have been set up yet.</div>';
        return;
      }

      document.getElementById('roomsGrid').innerHTML = rooms.map(r => {
        const isOccupied = r.status === 'occupied';
        const borderColor = isOccupied ? 'var(--red)' : 'var(--green)';
        const bgAccent    = isOccupied ? 'rgba(239,68,68,0.04)' : 'rgba(16,185,129,0.04)';
        const dotColor    = isOccupied ? 'var(--red)' : 'var(--green)';
        const statusLabel = isOccupied ? 'Occupied' : 'Available';

        const sessionBlock = isOccupied ? `
          <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--gray-100); display:flex; flex-direction:column; gap:6px;">
            <div style="font-size:13px; font-weight:600; color:var(--gray-900);">${r.session.subject}</div>
            <div style="font-size:12px; color:var(--gray-500);">${r.session.section || ''}</div>
            <div style="font-size:12px; color:var(--gray-600);">👨‍🏫 ${r.session.professor || '–'}</div>
            <div style="margin-top:4px;">
              <span style="font-size:12px; font-weight:600; color:${r.session.time_type === 'overtime' ? 'var(--red)' : r.session.time_type === 'remaining' ? 'var(--navy-blue)' : 'var(--gray-600)'};">
                ⏱ ${r.session.time_info}
              </span>
            </div>
          </div>` : `
          <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--gray-100);">
            <div style="font-size:13px; color:var(--gray-400);">No active session</div>
          </div>`;

        return `
          <div style="background:var(--white); border:1px solid ${borderColor}; border-radius:8px; padding:20px; background:${bgAccent}; transition:box-shadow 0.15s ease;"
               onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'"
               onmouseout="this.style.boxShadow='none'">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
              <div style="font-size:16px; font-weight:700; color:var(--gray-900);">🚪 ${r.name}</div>
              <div style="display:flex; align-items:center; gap:6px;">
                <span style="width:8px; height:8px; border-radius:50%; background:${dotColor}; display:inline-block; ${isOccupied ? '' : 'animation:pulse 2s ease-in-out infinite;'}"></span>
                <span style="font-size:12px; font-weight:600; color:${dotColor};">${statusLabel}</span>
              </div>
            </div>
            ${sessionBlock}
          </div>`;
      }).join('');

    } catch (e) {
      document.getElementById('roomsGrid').innerHTML =
        '<div style="color:var(--red); padding:24px;">Failed to load room availability.</div>';
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadRooms();
  setInterval(loadRooms, 30000);
</script>
@endsection