@extends('layouts.app')
@section('title', 'Live Attendance – ClassGuard')

@section('extra_styles')
<style>
  @keyframes slideIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
  .scan-entry { animation: slideIn 0.3s ease forwards; }
  @keyframes livePulse { 0%,100%{ opacity:1; } 50%{ opacity:0.3; } }
  .live-indicator { animation: livePulse 1.5s ease-in-out infinite; }
</style>
@endsection

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/professor/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/professor/subjects') }}" class="nav-item"><span class="nav-icon">📚</span><span>My Subjects</span></a>
    <a href="{{ url('/professor/live-attendance') }}" class="nav-item active"><span class="nav-icon">📡</span><span>Live Attendance</span></a>
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
  <div id="noSession">
    <div class="topbar">
      <div class="topbar-left"><h1>Live Attendance</h1><p class="topbar-subtitle">No active session</p></div>
      <div class="topbar-right">
        <a href="{{ url('/professor/subjects') }}" class="btn btn-primary">▶ Start a Session</a>
      </div>
    </div>
    <div class="content">
      <div class="section" style="text-align:center; padding:64px 24px;">
        <div style="font-size:64px; margin-bottom:24px; opacity:0.3">📡</div>
        <h2 style="font-size:20px; font-weight:600; margin-bottom:8px;">No Active Session</h2>
        <p style="color:var(--gray-500); margin-bottom:24px;">Go to My Subjects and start a session to see live attendance here.</p>
        <a href="{{ url('/professor/subjects') }}" class="btn btn-primary">Go to My Subjects</a>
      </div>
    </div>
  </div>

  <div id="activeSession" style="display:none;">
    <div class="topbar">
      <div class="topbar-left">
        <h1 style="display:flex; align-items:center; gap:10px;">
          Live Attendance
          <span class="live-badge"><span class="live-dot"></span>SESSION ACTIVE</span>
        </h1>
        <p class="topbar-subtitle" id="sessionInfo">Loading session...</p>
      </div>
      <div class="topbar-right">
        <button class="btn btn-danger" onclick="endSession()">⏹ End Session</button>
      </div>
    </div>

    <div class="content">
      <div style="background:var(--navy-dark); border-radius:8px; padding:16px 24px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; gap:32px;">
          <div>
            <div style="font-size:11px; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">Subject</div>
            <div style="font-size:14px; font-weight:600; color:var(--white);" id="sessionSubject">–</div>
          </div>
          <div>
            <div style="font-size:11px; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">Room</div>
            <div style="font-size:14px; font-weight:600; color:var(--white);" id="sessionRoom">–</div>
          </div>
          <div>
            <div style="font-size:11px; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">Elapsed</div>
            <div style="font-size:14px; font-weight:600; color:var(--white);" id="sessionTimer">00:00:00</div>
          </div>
        </div>
      </div>

      <div class="stats-grid" style="grid-template-columns:repeat(3,1fr); margin-bottom:24px;">
        <div class="stat-card" style="border-left:4px solid var(--green);">
          <div class="stat-label">Present</div>
          <div class="stat-value" style="color:var(--green)" id="countPresent">0</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #D97706;">
          <div class="stat-label">Late</div>
          <div class="stat-value" style="color:#D97706" id="countLate">0</div>
        </div>
        <div class="stat-card" style="border-left:4px solid var(--red);">
          <div class="stat-label">Not Yet Scanned</div>
          <div class="stat-value" style="color:var(--red)" id="countAbsent">0</div>
        </div>
      </div>

      <div class="grid-2">
        <div class="section">
          <div class="section-header">
            <h2 class="section-title">Live Scan Feed</h2>
            <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:var(--green);">
              <span class="live-indicator">●</span> Real-time
            </div>
          </div>
          <div id="scanFeed" style="display:flex; flex-direction:column; gap:8px; max-height:480px; overflow-y:auto;">
            <div style="text-align:center; padding:32px; color:var(--gray-400);">Waiting for card scans...</div>
          </div>
        </div>

        <div class="section">
          <div class="section-header">
            <h2 class="section-title">Not Yet Scanned</h2>
          </div>
          <div id="absentList" style="max-height:480px; overflow-y:auto;">
            <div style="text-align:center; padding:32px; color:var(--gray-400);">Loading...</div>
          </div>
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

  const urlParams = new URLSearchParams(window.location.search);
  const sessionId = urlParams.get('session');
console.log('sessionId from URL:', sessionId);
  let sessionData = null;
  let timerInterval = null;
  let pollInterval = null;

  function statusColor(s) {
    if (s === 'present') return { bg: 'rgba(16,185,129,0.08)', border: 'rgba(16,185,129,0.25)', badge: 'success', icon: '✅', label: 'Present' };
    if (s === 'late') return { bg: 'rgba(252,211,77,0.08)', border: 'rgba(252,211,77,0.35)', badge: 'warning', icon: '⏰', label: 'Late' };
    return { bg: 'rgba(229,231,235,0.5)', border: 'rgba(209,213,219,0.5)', badge: 'neutral', icon: '❌', label: 'Absent' };
  }

  function startTimer(startedAt) {
    timerInterval = setInterval(() => {
      const elapsed = Math.floor((Date.now() - new Date(startedAt).getTime()) / 1000);
      const h = String(Math.floor(elapsed / 3600)).padStart(2, '0');
      const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
      const s = String(elapsed % 60).padStart(2, '0');
      document.getElementById('sessionTimer').textContent = `${h}:${m}:${s}`;
    }, 1000);
  }

  async function loadLiveFeed() {
    if (!sessionId) return;
    try {
      // Updated to the secure professor route
      const res = await axios.get(`/api/professor/attendance/live/${sessionId}`);
      console.log('live feed response:', res.data);
      const { session, records } = res.data;
      sessionData = session;

      console.log('showing active session');
      document.getElementById('activeSession').style.display = 'block';
      document.getElementById('noSession').style.display = 'none';
      document.getElementById('sessionSubject').textContent = session.subject?.name || '–';
      document.getElementById('sessionRoom').textContent = session.room?.name || '–';
      document.getElementById('sessionInfo').textContent = `${session.subject?.name || ''} · ${session.subject?.section?.name || ''} · ${session.room?.name || 'No room'} · Started ${new Date(session.started_at).toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' })}`;

      if (!timerInterval) startTimer(session.started_at);

      const present = records.filter(r => r.status === 'present').length;
      const late = records.filter(r => r.status === 'late').length;
      
      document.getElementById('countPresent').textContent = present;
      document.getElementById('countLate').textContent = late;
      document.getElementById('countAbsent').textContent = '–'; // Kept as dash until total class size is calculated

      if (records.length === 0) {
        document.getElementById('scanFeed').innerHTML = '<div style="text-align:center; padding:32px; color:var(--gray-400);">Waiting for card scans...</div>';
      } else {
        document.getElementById('scanFeed').innerHTML = records.map(r => {
          const c = statusColor(r.status);
          const initials = (r.student?.name || 'S').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();
          return `
            <div class="scan-entry" style="background:${c.bg}; border:1px solid ${c.border}; border-radius:8px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
              <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:50%; background:var(--navy-dark); color:var(--gold); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0;">${initials}</div>
                <div>
                  <div style="font-size:13px; font-weight:600; color:var(--gray-900);">${r.student?.name || '–'}</div>
                  <div style="font-size:11px; color:var(--gray-500);">${r.student?.student_id_number || ''} · Scanned ${r.rfid_scanned_at ? new Date(r.rfid_scanned_at).toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'}) : '–'}</div>
                </div>
              </div>
              <span class="badge ${c.badge}">${c.icon} ${c.label}</span>
            </div>`;
        }).join('');
      }
    } catch (e) {
      document.getElementById('noSession').style.display = 'block';
      document.getElementById('activeSession').style.display = 'none';
    }
  }

  async function endSession() {
    if (!sessionId || !confirm('End this session? All unscanned students will be marked absent.')) return;
    try {
      // Updated to the secure professor route
      await axios.post(`/api/professor/session/end/${sessionId}`);
      clearInterval(timerInterval);
      clearInterval(pollInterval);
      window.location.href = '/professor/history';
    } catch (e) {
      alert(e.response?.data?.message || 'Failed to end session.');
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  if (sessionId) {
    loadLiveFeed();
    pollInterval = setInterval(loadLiveFeed, 3000);
  } else {
    // Updated to the secure professor route
    axios.get('/api/professor/session/active').then(res => {
      if (res.data.sessions.length > 0) {
        window.location.href = '/professor/live-attendance?session=' + res.data.sessions[0].session_id;
      }
    }).catch(() => {});
  }
</script>
@endsection