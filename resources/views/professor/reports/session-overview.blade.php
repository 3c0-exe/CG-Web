@extends('layouts.app')
@section('title', 'Session Overview – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/professor/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/professor/subjects') }}" class="nav-item"><span class="nav-icon">📚</span><span>My Subjects</span></a>
    <a href="{{ url('/professor/live-attendance') }}" class="nav-item"><span class="nav-icon">📡</span><span>Live Attendance</span></a>
    <a href="{{ url('/professor/history') }}" class="nav-item"><span class="nav-icon">📋</span><span>Session History</span></a>
    <a href="{{ url('/professor/students') }}" class="nav-item"><span class="nav-icon">👥</span><span>My Students</span></a>
    <a href="{{ url('/professor/rooms') }}" class="nav-item"><span class="nav-icon">🏠</span><span>Room Availability</span></a>
    <div class="nav-divider"></div>
    <div class="nav-section">Reports</div>
    <a href="{{ url('/professor/reports/session-overview') }}" class="nav-item active"><span class="nav-icon">📈</span><span>Session Overview</span></a>
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
      <h1>Session Overview</h1>
      <p class="topbar-subtitle">How each class day went — per subject, per session</p>
    </div>
  </div>

  <div class="content">

    <!-- Subject Cards (auto-loaded) -->
    <div id="subjectCards" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:16px; margin-bottom:32px;">
      <div style="text-align:center; padding:40px; color:var(--gray-400); grid-column:1/-1;">Loading your subjects...</div>
    </div>

    <!-- Drill-down: session table for selected subject -->
    <div id="drillDown" style="display:none;">
      <div class="section">
        <div class="section-header">
          <div>
            <h2 class="section-title" id="drillTitle">Session Breakdown</h2>
            <div style="font-size:13px; color:var(--gray-500); margin-top:2px;" id="drillMeta"></div>
          </div>
          <button class="btn btn-ghost btn-sm" onclick="closeDrill()">✕ Close</button>
        </div>
        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
              <tr style="border-bottom:2px solid var(--gray-200); background:var(--gray-50);">
                <th style="text-align:left; padding:10px 16px; color:var(--gray-500); font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Date</th>
                <th style="text-align:left; padding:10px 16px; color:var(--gray-500); font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Time</th>
                <th style="text-align:center; padding:10px 16px; color:var(--green); font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Present</th>
                <th style="text-align:center; padding:10px 16px; color:var(--amber); font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Late</th>
                <th style="text-align:center; padding:10px 16px; color:var(--red); font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Absent</th>
                <th style="text-align:center; padding:10px 16px; color:var(--gray-500); font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Total</th>
                <th style="text-align:center; padding:10px 16px; color:var(--gray-500); font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Rate</th>
              </tr>
            </thead>
            <tbody id="drillRows"></tbody>
          </table>
        </div>
      </div>
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

  const colors = ['var(--navy-blue)', '#7c3aed', '#0f766e', '#1d4ed8', '#b45309'];

  async function loadOverview() {
    try {
      const subjectsRes = await axios.get('/api/professor/schedules');
      const schedules = subjectsRes.data.schedules;

      if (schedules.length === 0) {
        document.getElementById('subjectCards').innerHTML =
          '<div style="text-align:center; padding:64px; color:var(--gray-400); grid-column:1/-1;"><div style="font-size:40px; margin-bottom:12px;">📚</div>No subjects assigned yet.</div>';
        return;
      }

      const groupedSubjects = {};
      schedules.forEach(s => {
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

      document.getElementById('subjectCards').innerHTML = subjectList.map((group, i) => {
        const color = colors[i % colors.length];
        
        const sectionsHtml = group.schedules.map(s => `
          <button class="btn btn-ghost btn-sm" style="margin:4px; border:1px solid var(--gray-200);" 
            onclick="openDrill(${s.id}, '${group.subject.name.replace(/'/g,"\\\\'")}', '${s.section.name}')">
            ${s.section.name} Overview →
          </button>
        `).join('');

        return `
          <div style="background:var(--white); border:1px solid var(--gray-200); border-radius:8px; overflow:hidden;">
            <div style="background:${color}; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <div style="font-size:15px; font-weight:700; color:var(--white);">${group.subject?.name || 'Subject'}</div>
                <div style="font-size:12px; color:rgba(255,255,255,0.6); margin-top:2px;">${group.subject?.year_level?.name || ''}</div>
              </div>
            </div>
            <div style="padding:14px 20px;">
              <div style="font-size:13px; color:var(--gray-600); margin-bottom:12px; font-weight:600;">Select Section to View Report:</div>
              <div style="display:flex; flex-wrap:wrap; margin:-4px;">
                ${sectionsHtml}
              </div>
            </div>
          </div>`;
      }).join('');

    } catch (e) {
      document.getElementById('subjectCards').innerHTML =
        '<div style="color:var(--red); padding:24px; grid-column:1/-1;">Failed to load subjects.</div>';
    }
  }

  async function openDrill(subjectId, name, meta) {
    document.getElementById('drillTitle').textContent = name + ' — Session Breakdown';
    document.getElementById('drillMeta').textContent = meta;
    document.getElementById('drillRows').innerHTML =
      '<tr><td colspan="7" style="text-align:center; padding:32px; color:var(--gray-400);">Loading...</td></tr>';
    document.getElementById('drillDown').style.display = 'block';
    document.getElementById('drillDown').scrollIntoView({ behavior: 'smooth', block: 'start' });

    try {
      const res = await axios.get(`/api/professor/reports/section-attendance?schedule_id=${subjectId}`);
      const sessions = res.data.sessions;

      if (sessions.length === 0) {
        document.getElementById('drillRows').innerHTML =
          '<tr><td colspan="7" style="text-align:center; padding:32px; color:var(--gray-400);">No ended sessions yet for this subject.</td></tr>';
        return;
      }

      document.getElementById('drillRows').innerHTML = sessions.map(s => {
        const rateColor = s.rate >= 80 ? 'var(--green)' : s.rate >= 60 ? 'var(--amber)' : 'var(--red)';
        return `
          <tr style="border-bottom:1px solid var(--gray-100);">
            <td style="padding:12px 16px; font-weight:500;">${s.date}</td>
            <td style="padding:12px 16px; color:var(--gray-500); font-size:13px;">${s.started_at}${s.ended_at ? ' – ' + s.ended_at : ''}</td>
            <td style="padding:12px 16px; text-align:center; color:var(--green); font-weight:600;">${s.present_count}</td>
            <td style="padding:12px 16px; text-align:center; color:var(--amber); font-weight:600;">${s.late_count}</td>
            <td style="padding:12px 16px; text-align:center; color:var(--red); font-weight:600;">${s.absent_count}</td>
            <td style="padding:12px 16px; text-align:center; color:var(--gray-500);">${s.total}</td>
            <td style="padding:12px 16px; text-align:center;">
              <span style="background:${rateColor}20; color:${rateColor}; font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px;">${s.rate}%</span>
            </td>
          </tr>`;
      }).join('');
    } catch (e) {
      document.getElementById('drillRows').innerHTML =
        '<tr><td colspan="7" style="text-align:center; color:var(--red); padding:24px;">Failed to load sessions.</td></tr>';
    }
  }

  function closeDrill() {
    document.getElementById('drillDown').style.display = 'none';
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadOverview();
</script>
@endsection