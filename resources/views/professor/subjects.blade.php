@extends('layouts.app')
@section('title', 'My Subjects – ClassGuard')

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/professor/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/professor/subjects') }}" class="nav-item active"><span class="nav-icon">📚</span><span>My Subjects</span></a>
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
      <h1>My Subjects</h1>
      <p class="topbar-subtitle">Manage your subjects and start attendance sessions</p>
    </div>
  </div>

  <div class="content">
    <div id="subjectsGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:20px;">
      <div style="text-align:center; padding:40px; color:var(--gray-400);">Loading subjects...</div>
    </div>
  </div>
</main>
<!-- Start Session Modal -->
<div id="startSessionModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:400px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:6px;">▶ Start Session</h3>
    <p style="font-size:13px; color:var(--gray-500); margin-bottom:20px;" id="startSessionSubjectName">–</p>
    <div id="startSessionError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <div class="form-group">
      <label class="form-label">Room <span style="color:var(--red)">*</span></label>
      <select class="form-select" id="startSessionRoom">
        <option value="">Select room...</option>
      </select>
    </div>
    <div style="display:flex; gap:12px; margin-top:8px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('startSessionModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="startSessionBtn" onclick="confirmStartSession()">Start Session</button>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
  // token is declared in layouts/app.blade.php
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'professor') { localStorage.clear(); window.location.href = '/login'; }

  document.getElementById('userName').textContent = user.name || 'Professor';
  document.getElementById('userAvatar').textContent = (user.name || 'P').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();

  const colors = ['var(--navy-blue)','#7c3aed','#0f766e','#1d4ed8','#b45309'];

  async function loadSubjects() {
    try {
      // Look here! We are now using the correct /api/professor/ routes we made earlier
      const [subjectsRes, activeRes] = await Promise.all([
        axios.get('/api/professor/subjects'),
        axios.get('/api/professor/session/active'),
      ]);
      
      mySubjectsData = subjectsRes.data.subjects;
      const mySubjects = mySubjectsData;
      const activeSessions = activeRes.data.sessions;

      if (mySubjects.length === 0) {
        document.getElementById('subjectsGrid').innerHTML = '<div style="text-align:center; padding:40px; color:var(--gray-400);">No subjects assigned yet. Contact your system admin.</div>';
        return;
      }

      document.getElementById('subjectsGrid').innerHTML = mySubjects.map((s, i) => {
        const color = colors[i % colors.length];
        const isActive = activeSessions.find(a => a.subject_id === s.id);
        
        return `
          <div style="background:var(--white); border:1px solid ${isActive ? 'var(--green)' : 'var(--gray-200)'}; border-radius:8px; overflow:hidden;">
            <div style="background:${color}; padding:20px;">
              <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                  <div style="font-size:18px; font-weight:700; color:var(--white); margin:2px 0;">${s.name}</div>
                  <div style="font-size:12px; color:rgba(255,255,255,0.6);">${s.section?.name || ''} · ${s.year_level?.name || ''}</div>
                </div>
                <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
                  ${isActive ? '<span class="live-badge"><span class="live-dot"></span>LIVE</span>' : ''}
                </div>
              </div>
            </div>
            <div style="padding:16px;">
              ${isActive ? `<div style="background:rgba(16,185,129,0.06); border:1px solid rgba(16,185,129,0.2); border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:13px;"><strong>Active Session</strong> running now</div>` : ''}
              <div style="font-size:12px; color:var(--gray-500); margin-bottom:12px; display:flex; gap:16px;">
                <span>⏱ Late threshold: <strong>${s.late_threshold_minutes} min</strong></span>
                <span>🎫 Guests: <strong>${s.allow_guests ? 'Yes' : 'No'}</strong></span>
              </div>
              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                ${isActive
                  ? `<a href="{{ url('/professor/live-attendance') }}?session=${isActive.session_id}" class="btn btn-success btn-sm">📡 View Live</a>`
                  : `<button class="btn btn-primary btn-sm" onclick="startSession(${s.id})">▶ Start Session</button>`
                }
                <a href="{{ url('/professor/history') }}" class="btn btn-ghost btn-sm">📋 History</a>
                <a href="{{ url('/professor/students') }}?subject=${s.id}" class="btn btn-ghost btn-sm">👥 Students</a>
              </div>
            </div>
          </div>`;
      }).join('');
    } catch (e) {
      document.getElementById('subjectsGrid').innerHTML = '<div style="color:var(--red); padding:24px;">Failed to load subjects.</div>';
    }
  }

  let pendingSubjectId = null;
  let mySubjectsData = [];

  async function startSession(subjectId) {
    pendingSubjectId = subjectId;
    const subject = mySubjectsData.find(s => s.id === subjectId);
    document.getElementById('startSessionSubjectName').textContent = subject?.name || '–';
    document.getElementById('startSessionError').style.display = 'none';
    document.getElementById('startSessionBtn').disabled = false;
    document.getElementById('startSessionBtn').textContent = 'Start Session';

    try {
      const res = await axios.get('/api/rooms');
      const rooms = res.data.rooms;
      document.getElementById('startSessionRoom').innerHTML =
        '<option value="">Select room...</option>' +
        rooms.map(r => `<option value="${r.id}">${r.name}</option>`).join('');
    } catch (e) {
      document.getElementById('startSessionRoom').innerHTML = '<option value="">Failed to load rooms</option>';
    }

    document.getElementById('startSessionModal').style.display = 'flex';
  }

  async function confirmStartSession() {
    const btn = document.getElementById('startSessionBtn');
    const errEl = document.getElementById('startSessionError');
    const roomId = document.getElementById('startSessionRoom').value;

    if (!roomId) {
      errEl.textContent = '❌ Please select a room.';
      errEl.style.display = 'block';
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Starting...';
    errEl.style.display = 'none';

    try {
      const res = await axios.post('/api/professor/session/start', {
        subject_id: pendingSubjectId,
        room_id: roomId,
      });
      window.location.href = '/professor/live-attendance?session=' + res.data.session.session_id;
    } catch (e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to start session.');
      errEl.style.display = 'block';
      btn.disabled = false;
      btn.textContent = 'Start Session';
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadSubjects();
</script>
@endsection