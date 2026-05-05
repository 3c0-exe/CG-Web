@extends('layouts.app')
@section('title', 'My Students – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/professor/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/professor/subjects') }}" class="nav-item"><span class="nav-icon">📚</span><span>My Subjects</span></a>
    <a href="{{ url('/professor/live-attendance') }}" class="nav-item"><span class="nav-icon">📡</span><span>Live Attendance</span></a>
    <a href="{{ url('/professor/history') }}" class="nav-item"><span class="nav-icon">📋</span><span>Session History</span></a>
    <a href="{{ url('/professor/students') }}" class="nav-item active"><span class="nav-icon">👥</span><span>My Students</span></a>
    <a href="{{ url('/professor/rooms') }}" class="nav-item"><span class="nav-icon">🏠</span><span>Room Availability</span></a>
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
      <h1>My Students</h1>
      <p class="topbar-subtitle" id="topbarSubtitle">Select a subject to view enrolled students</p>
    </div>
  </div>

  <div class="content">

    <!-- Subject Selector -->
    <div class="section" style="margin-bottom:24px;">
      <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        <div style="flex:1; min-width:240px;">
          <label class="form-label" style="margin-bottom:6px; display:block;">Select Subject</label>
          <select class="form-select" id="subjectSelect" onchange="onSubjectChange(this.value)">
            <option value="">Choose a subject...</option>
          </select>
        </div>
        <div id="subjectMeta" style="display:none; padding:12px 20px; background:var(--gray-50); border:1px solid var(--gray-200); border-radius:8px; font-size:13px; color:var(--gray-600); white-space:nowrap;">
          <span id="subjectMetaText"></span>
        </div>
      </div>
    </div>

    <!-- Stats row (hidden until subject selected) -->
    <div id="statsRow" style="display:none; margin-bottom:24px;">
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card">
          <div class="stat-label">Enrolled Students</div>
          <div class="stat-value" id="statTotal">–</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">RFID Linked</div>
          <div class="stat-value" style="color:var(--green)" id="statRfid">–</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Avg Attendance Rate</div>
          <div class="stat-value" style="color:var(--navy-blue)" id="statAvgRate">–</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Sessions</div>
          <div class="stat-value" id="statSessions">–</div>
        </div>
      </div>
    </div>

    <!-- Students Table -->
    <div class="section" id="studentsSection" style="display:none; padding:0; overflow:hidden;">
      <div style="padding:16px 24px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--gray-200);">
        <div class="search-wrap" style="flex:1; max-width:320px;">
          <span class="search-icon">🔍</span>
          <input type="text" class="form-input" id="searchInput" placeholder="Search students..." style="padding-left:36px;" oninput="filterStudents(this.value)">
        </div>
        <div style="font-size:13px; color:var(--gray-500);" id="studentCountLabel"></div>
      </div>

      <div class="table-wrapper" style="padding:0;">
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Student ID</th>
              <th>Section</th>
              <th>RFID Status</th>
              <th>Present</th>
              <th>Late</th>
              <th>Absent</th>
              <th>Attendance Rate</th>
            </tr>
          </thead>
          <tbody id="studentsTable">
            <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--gray-400);">Select a subject above</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty state (no subject selected) -->
    <div id="emptyState" class="section" style="text-align:center; padding:64px 24px;">
      <div style="font-size:56px; margin-bottom:20px; opacity:0.25;">👥</div>
      <h2 style="font-size:18px; font-weight:600; margin-bottom:8px; color:var(--gray-700);">Select a Subject</h2>
      <p style="color:var(--gray-400); font-size:14px;">Choose one of your subjects from the dropdown above to view enrolled students and their attendance records.</p>
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

  let mySubjects = [];
  let allStudents = [];
  let sessionCount = 0;

  // ─── Init ─────────────────────────────────────────────────────────────────

  async function init() {
    try {
      // Correct route — backend scopes to the authenticated professor automatically
      const res = await axios.get('/api/professor/subjects');
      mySubjects = res.data.subjects;

      if (mySubjects.length === 0) {
        document.getElementById('emptyState').innerHTML = `
          <div style="font-size:56px; margin-bottom:20px; opacity:0.25;">📚</div>
          <h2 style="font-size:18px; font-weight:600; margin-bottom:8px; color:var(--gray-700);">No Subjects Assigned</h2>
          <p style="color:var(--gray-400); font-size:14px;">Contact your administrator to get subjects assigned to your account.</p>`;
        return;
      }

      document.getElementById('subjectSelect').innerHTML =
        '<option value="">Choose a subject...</option>' +
        mySubjects.map(s => `<option value="${s.id}">${s.name} – ${s.section?.name || ''}</option>`).join('');

      // Auto-select from URL param ?subject=X
      const urlParam = new URLSearchParams(window.location.search).get('subject');
      if (urlParam) {
        document.getElementById('subjectSelect').value = urlParam;
        onSubjectChange(urlParam);
      }
    } catch (e) {
      console.error('Init error:', e);
    }
  }

  // ─── Subject change ────────────────────────────────────────────────────────

  async function onSubjectChange(subjectId) {
    if (!subjectId) {
      document.getElementById('statsRow').style.display = 'none';
      document.getElementById('studentsSection').style.display = 'none';
      document.getElementById('emptyState').style.display = 'block';
      document.getElementById('subjectMeta').style.display = 'none';
      return;
    }

    const subject = mySubjects.find(s => s.id == subjectId);
    if (!subject) return;

    document.getElementById('subjectMetaText').textContent =
      `🏫 ${subject.section?.name || '–'}  ·  ⏱ Late: ${subject.late_threshold_minutes} min`;
    document.getElementById('subjectMeta').style.display = 'block';
    document.getElementById('topbarSubtitle').textContent = subject.name;

    document.getElementById('studentsSection').style.display = 'block';
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('statsRow').style.display = 'none';
    document.getElementById('studentsTable').innerHTML =
      '<tr><td colspan="8" style="text-align:center; padding:40px; color:var(--gray-400);">Loading students...</td></tr>';

    try {
      const res = await axios.get(`/api/professor/students?subject_id=${subjectId}`);
      allStudents = res.data.students;
      sessionCount = res.data.session_count || 0;
      renderStudents(allStudents);
      renderStats(allStudents, sessionCount);
      document.getElementById('statsRow').style.display = 'block';
    } catch (e) {
      document.getElementById('studentsTable').innerHTML =
        '<tr><td colspan="8" style="text-align:center; color:var(--red); padding:24px;">Failed to load students.</td></tr>';
    }
  }

  // ─── Render ────────────────────────────────────────────────────────────────

  function renderStats(students, sessions) {
    const total = students.length;
    const rfidLinked = students.filter(s => s.rfid_uid).length;
    const avgRate = total > 0
      ? (students.reduce((sum, s) => sum + (s.attendance_rate || 0), 0) / total).toFixed(0)
      : 0;
    document.getElementById('statTotal').textContent = total;
    document.getElementById('statRfid').textContent = rfidLinked;
    document.getElementById('statAvgRate').textContent = avgRate + '%';
    document.getElementById('statSessions').textContent = sessions;
  }

  function renderStudents(students) {
    document.getElementById('studentCountLabel').textContent = students.length + ' student' + (students.length !== 1 ? 's' : '');

    if (students.length === 0) {
      document.getElementById('studentsTable').innerHTML =
        '<tr><td colspan="8" style="text-align:center; padding:40px; color:var(--gray-400);">No students enrolled in this subject yet.</td></tr>';
      return;
    }

    document.getElementById('studentsTable').innerHTML = students.map(s => {
      const initials = (s.name || 'S').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();
      const rate = s.attendance_rate ?? 0;
      const rateColor = rate >= 75 ? 'var(--green)' : rate >= 50 ? '#D97706' : rate === 0 && sessionCount === 0 ? 'var(--gray-400)' : 'var(--red)';
      const rateLabel = sessionCount === 0 ? '–' : rate + '%';
      const rfidBadge = s.rfid_uid
        ? `<span class="badge success">✓ Linked</span>`
        : `<span class="badge neutral">Not linked</span>`;

      const barWidth = sessionCount > 0 ? Math.min(rate, 100) : 0;
      const barColor = rate >= 75 ? 'var(--green)' : rate >= 50 ? '#D97706' : 'var(--red)';

      return `
        <tr>
          <td>
            <div style="display:flex; align-items:center; gap:10px;">
              <div style="width:32px; height:32px; border-radius:50%; background:var(--navy-dark); color:var(--gold); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0;">${initials}</div>
              <div>
                <div style="font-size:13px; font-weight:600; color:var(--gray-900);">${s.name}</div>
                <div style="font-size:11px; color:var(--gray-500);">${s.email || ''}</div>
              </div>
            </div>
          </td>
          <td style="font-size:13px; font-family:monospace;">${s.student_id_number || '–'}</td>
          <td style="font-size:13px;">${s.section?.name || '–'}</td>
          <td>${rfidBadge}</td>
          <td style="font-size:13px; font-weight:600; color:var(--green);">${s.present_count ?? '–'}</td>
          <td style="font-size:13px; font-weight:600; color:#D97706;">${s.late_count ?? '–'}</td>
          <td style="font-size:13px; font-weight:600; color:var(--red);">${s.absent_count ?? '–'}</td>
          <td style="min-width:120px;">
            <div style="display:flex; align-items:center; gap:8px;">
              <div class="progress-bar" style="flex:1;">
                <div class="progress-fill" style="width:${barWidth}%; background:${barColor};"></div>
              </div>
              <span style="font-size:12px; font-weight:700; color:${rateColor}; min-width:32px; text-align:right;">${rateLabel}</span>
            </div>
          </td>
        </tr>`;
    }).join('');
  }

  function filterStudents(q) {
    const lower = q.toLowerCase();
    const filtered = q
      ? allStudents.filter(s =>
          s.name?.toLowerCase().includes(lower) ||
          s.student_id_number?.includes(q) ||
          s.email?.toLowerCase().includes(lower)
        )
      : allStudents;
    renderStudents(filtered);
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  init();
</script>
@endsection