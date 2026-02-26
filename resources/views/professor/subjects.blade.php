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

<!-- Settings Modal -->
<div id="settingsModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:460px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;" id="settingsTitle">Subject Settings</h3>
    <div class="form-group">
      <label class="form-label">Late Threshold (minutes after start)</label>
      <input type="number" class="form-input" id="lateThreshold" value="15" min="1" max="60">
    </div>
    <div class="form-group">
      <label class="form-label">Allow Guest Students</label>
      <select class="form-select" id="allowGuests">
        <option value="1">Yes – allow students from other sections</option>
        <option value="0">No – only enrolled students</option>
      </select>
    </div>
    <div style="display:flex; gap:12px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('settingsModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" onclick="saveSettings()">Save Settings</button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  // token is declared in layouts/app.blade.php
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'professor') { localStorage.clear(); window.location.href = '/login'; }
  axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;

  document.getElementById('userName').textContent = user.name || 'Professor';
  document.getElementById('userAvatar').textContent = (user.name || 'P').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();

  const colors = ['var(--navy-blue)','#7c3aed','#0f766e','#1d4ed8','#b45309'];
  let currentSubjectId = null;

  async function loadSubjects() {
    try {
      const [subjectsRes, activeRes] = await Promise.all([
        axios.get('/api/admin/subjects'),
        axios.get('/api/session/active'),
      ]);
      const mySubjects = subjectsRes.data.subjects.filter(s => s.professor_id === user.id);
      const activeSessions = activeRes.data.sessions;

      if (mySubjects.length === 0) {
        document.getElementById('subjectsGrid').innerHTML = '<div style="text-align:center; padding:40px; color:var(--gray-400);">No subjects assigned yet. Contact admin.</div>';
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
                  <div style="font-size:11px; font-weight:600; color:rgba(255,255,255,0.5); letter-spacing:0.05em;">${s.code}</div>
                  <div style="font-size:18px; font-weight:700; color:var(--white); margin:2px 0;">${s.name}</div>
                  <div style="font-size:12px; color:rgba(255,255,255,0.6);">${s.section?.name || ''} · ${s.year_level?.name || ''}</div>
                </div>
                <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
                  ${isActive ? '<span class="live-badge"><span class="live-dot"></span>LIVE</span>' : ''}
                  <div style="background:var(--gold); color:var(--navy-dark); padding:6px 12px; border-radius:6px; font-size:14px; font-weight:800; letter-spacing:0.1em;">${s.class_code}</div>
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
                <button class="btn btn-ghost btn-sm" onclick="openSettings(${s.id}, '${s.name}', ${s.late_threshold_minutes}, ${s.allow_guests ? 1 : 0})">⚙️ Settings</button>
              </div>
            </div>
          </div>`;
      }).join('');
    } catch (e) {
      document.getElementById('subjectsGrid').innerHTML = '<div style="color:var(--red); padding:24px;">Failed to load subjects.</div>';
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

  function openSettings(id, name, threshold, guests) {
    currentSubjectId = id;
    document.getElementById('settingsTitle').textContent = name + ' – Settings';
    document.getElementById('lateThreshold').value = threshold;
    document.getElementById('allowGuests').value = guests;
    document.getElementById('settingsModal').style.display = 'flex';
  }

  function saveSettings() {
    document.getElementById('settingsModal').style.display = 'none';
    alert('Settings saved! (API integration for subject update coming soon)');
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadSubjects();
</script>
@endsection
