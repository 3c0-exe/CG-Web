@extends('layouts.app')
@section('title', 'My Subjects – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/professor/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/professor/subjects') }}" class="nav-item active"><span class="nav-icon">📚</span><span>My Subjects</span></a>
    <a href="{{ url('/professor/live-attendance') }}" class="nav-item"><span class="nav-icon">📡</span><span>Live Attendance</span></a>
    <a href="{{ url('/professor/history') }}" class="nav-item"><span class="nav-icon">📋</span><span>Session History</span></a>
    <a href="{{ url('/professor/students') }}" class="nav-item"><span class="nav-icon">👥</span><span>My Students</span></a>
    <a href="{{ url('/professor/rooms') }}" class="nav-item"><span class="nav-icon">🏠</span><span>Room Availability</span></a>
    <div class="nav-divider"></div>
    <div class="nav-section">Reports</div>
   <a href="{{ url('/professor/reports/session-overview') }}" class="nav-item"><span class="nav-icon">📈</span><span>Session Overview</span></a>
<a href="{{ url('/professor/reports/student-records') }}" class="nav-item"><span class="nav-icon">🎓</span><span>Student Records</span></a>
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
<!-- Select Section Modal -->
<div id="selectSectionModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:190; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:400px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:6px;">Select Section</h3>
    <p style="font-size:13px; color:var(--gray-500); margin-bottom:20px;" id="selectSectionSubjectName">–</p>
    
    <div id="sectionOptionsList" style="display:flex; flex-direction:column; gap:8px;">
    </div>
    
    <div style="display:flex; margin-top:20px;">
      <button class="btn btn-ghost" style="width:100%;" onclick="document.getElementById('selectSectionModal').style.display='none'">Cancel</button>
    </div>
  </div>
</div>

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
        axios.get('/api/professor/schedules'),
        axios.get('/api/professor/session/active'),
      ]);
      
      mySubjectsData = subjectsRes.data.schedules;
      const mySubjects = mySubjectsData;
      const activeSessions = activeRes.data.sessions;

      if (mySubjects.length === 0) {
        document.getElementById('subjectsGrid').innerHTML = '<div style="text-align:center; padding:40px; color:var(--gray-400);">No subjects assigned yet. Contact your system admin.</div>';
        return;
      }

      groupedSubjects = {};
      mySubjects.forEach(s => {
          const subId = s.subject.id;
          if (!groupedSubjects[subId]) {
              groupedSubjects[subId] = {
                  subject: s.subject,
                  schedules: []
              };
          }
          groupedSubjects[subId].schedules.push(s);
      });

      const subjectList = Object.values(groupedSubjects);

      document.getElementById('subjectsGrid').innerHTML = subjectList.map((group, i) => {
        const color = colors[i % colors.length];
        const activeSchedule = group.schedules.find(s => activeSessions.find(a => a.schedule_id === s.id));
        const isActive = !!activeSchedule;
        const activeSessionInfo = activeSchedule ? activeSessions.find(a => a.schedule_id === activeSchedule.id) : null;
        
        const sectionsStr = group.schedules.map(s => s.section.name).join(', ');

        return `
          <div style="background:var(--white); border:1px solid ${isActive ? 'var(--green)' : 'var(--gray-200)'}; border-radius:8px; overflow:hidden;">
            <div style="background:${color}; padding:20px;">
              <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                  <div style="font-size:18px; font-weight:700; color:var(--white); margin:2px 0;">${group.subject?.name || 'Unknown'}</div>
                  <div style="font-size:12px; color:rgba(255,255,255,0.6);">${group.subject?.year_level?.name || ''}</div>
                </div>
                <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
                  ${isActive ? '<span class="live-badge"><span class="live-dot"></span>LIVE</span>' : ''}
                </div>
              </div>
            </div>
            <div style="padding:16px;">
              ${isActive ? `<div style="background:rgba(16,185,129,0.06); border:1px solid rgba(16,185,129,0.2); border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:13px;"><strong>Active Session</strong> running now for ${activeSchedule.section.name}</div>` : ''}
              <div style="font-size:13px; color:var(--gray-600); margin-bottom:12px;">
                <strong>Sections:</strong> ${sectionsStr}
              </div>
              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                ${isActive
                  ? `<a href="{{ url('/professor/live-attendance') }}?session=${activeSessionInfo.session_id}" class="btn btn-success btn-sm">📡 View Live</a>`
                  : `<button class="btn btn-primary btn-sm" onclick="openSelectSectionModal(${group.subject.id})">▶ Start Session</button>`
                }
                <a href="{{ url('/professor/history') }}" class="btn btn-ghost btn-sm">📋 History</a>
                <a href="{{ url('/professor/students') }}" class="btn btn-ghost btn-sm">👥 Students</a>
              </div>
            </div>
          </div>`;
      }).join('');
    } catch (e) {
      document.getElementById('subjectsGrid').innerHTML = '<div style="color:var(--red); padding:24px;">Failed to load subjects.</div>';
    }
  }

  let pendingScheduleId = null;
  let mySubjectsData = [];
  let groupedSubjects = {};

  function openSelectSectionModal(subjectId) {
      const group = groupedSubjects[subjectId];
      if(!group) return;
      
      document.getElementById('selectSectionSubjectName').textContent = group.subject.name;
      
      const optionsHtml = group.schedules.map(s => {
          return `<button class="btn btn-ghost" style="width:100%; text-align:left; border: 1px solid var(--gray-200); padding: 12px; justify-content: flex-start; margin-bottom: 4px;" onclick="startSession(${s.id})">
            <div>
              <div style="font-weight: 600; color: var(--gray-800);">${s.section.name}</div>
              <div style="font-size: 11px; color: var(--gray-500); font-weight: 400;">Schedule: ${s.schedule_days ? s.schedule_days.join(', ') : 'TBA'} ${s.schedule_start_time ? '(' + s.schedule_start_time + ')' : ''}</div>
            </div>
          </button>`;
      }).join('');
      
      document.getElementById('sectionOptionsList').innerHTML = optionsHtml;
      document.getElementById('selectSectionModal').style.display = 'flex';
  }

  async function startSession(scheduleId) {
    document.getElementById('selectSectionModal').style.display = 'none';
    pendingScheduleId = scheduleId;
    const schedule = mySubjectsData.find(s => s.id === scheduleId);
    document.getElementById('startSessionSubjectName').textContent = (schedule?.subject?.name || '–') + ' (' + (schedule?.section?.name || '-') + ')';
    document.getElementById('startSessionError').style.display = 'none';
    document.getElementById('startSessionBtn').disabled = false;
    document.getElementById('startSessionBtn').textContent = 'Start Session';

    try {
      const res = await axios.get('/api/rooms/availability');
      const rooms = res.data.rooms;
      document.getElementById('startSessionRoom').innerHTML =
        '<option value="">Select room...</option>' +
        rooms.map(r => {
          const occupied = r.status === 'occupied';
          const label = occupied
            ? `🔴 ${r.name} — ${r.session.professor} · ${r.session.time_info}`
            : `🟢 ${r.name}`;
          return `<option value="${r.id}">${label}</option>`;
        }).join('');
    } catch (e) {
      document.getElementById('startSessionRoom').innerHTML = '<option value="">Failed to load rooms</option>';
    }

    document.getElementById('startSessionModal').style.display = 'flex';
  }

  let scheduleOverride = false;

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
        schedule_id: pendingScheduleId,
        room_id: roomId,
        override: scheduleOverride,
      });
      window.location.href = '/professor/live-attendance?session=' + res.data.session.session_id;
    } catch (e) {
      const data = e.response?.data;

      // Soft warning — show with override option
      if (data?.warning) {
        errEl.style.background = 'rgba(252,211,77,0.1)';
        errEl.style.borderColor = 'rgba(252,211,77,0.4)';
        errEl.style.color = 'var(--amber)';
        errEl.innerHTML = `${data.message} <br><br>
          <button class="btn btn-primary btn-sm" onclick="proceedWithOverride()" style="margin-right:8px;">Yes, Start Anyway</button>
          <button class="btn btn-ghost btn-sm" onclick="cancelOverride()">Cancel</button>`;
        errEl.style.display = 'block';
        btn.disabled = false;
        btn.style.display = 'none';
        return;
      }

      // Hard error
      errEl.style.background = 'rgba(239,68,68,0.08)';
      errEl.style.borderColor = 'rgba(239,68,68,0.2)';
      errEl.style.color = 'var(--red)';
      errEl.textContent = '❌ ' + (data?.message || 'Failed to start session.');
      errEl.style.display = 'block';
      btn.disabled = false;
      btn.textContent = 'Start Session';
    }
  }

  function proceedWithOverride() {
    scheduleOverride = true;
    document.getElementById('startSessionBtn').style.display = '';
    document.getElementById('startSessionError').style.display = 'none';
    confirmStartSession();
  }

  function cancelOverride() {
    scheduleOverride = false;
    document.getElementById('startSessionBtn').style.display = '';
    document.getElementById('startSessionError').style.display = 'none';
    document.getElementById('startSessionBtn').disabled = false;
    document.getElementById('startSessionBtn').textContent = 'Start Session';
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadSubjects();
</script>
@endsection