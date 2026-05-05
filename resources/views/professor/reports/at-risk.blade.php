@extends('layouts.app')
@section('title', 'At-Risk Students – ClassGuard')

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
    <a href="{{ url('/professor/reports/session-overview') }}" class="nav-item"><span class="nav-icon">📈</span><span>Session Overview</span></a>
    <a href="{{ url('/professor/reports/student-records') }}" class="nav-item"><span class="nav-icon">🎓</span><span>Student Records</span></a>
    <a href="{{ url('/professor/reports/at-risk') }}" class="nav-item active"><span class="nav-icon">⚠️</span><span>At-Risk Students</span></a>
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
      <h1>At-Risk Students</h1>
      <p class="topbar-subtitle">Students with high absences across all your subjects</p>
    </div>
    <div class="topbar-right" style="display:flex; align-items:center; gap:10px;">
      <label style="font-size:13px; color:var(--gray-500); white-space:nowrap;">Flag after</label>
      <input type="number" id="thresholdInput" value="3" min="1" max="20"
        style="width:60px; padding:6px 10px; border:1px solid var(--gray-200); border-radius:6px; font-size:14px; text-align:center;"
        onchange="loadAtRisk()">
      <label style="font-size:13px; color:var(--gray-500); white-space:nowrap;">absences</label>
    </div>
  </div>

  <div class="content">

    <!-- Summary Cards -->
    <div class="stats-grid" id="summaryCards" style="display:none; margin-bottom:24px;">
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-label">Total Flagged</div>
            <div class="stat-value" id="sumFlagged" style="color:var(--red)">–</div>
            <div class="stat-change negative">Across all subjects</div>
          </div>
          <div class="stat-icon" style="background:rgba(239,68,68,0.1);">⚠️</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-label">Critical</div>
            <div class="stat-value" id="sumCritical" style="color:var(--red)">–</div>
            <div class="stat-change negative" id="sumCriticalSub">2× threshold</div>
          </div>
          <div class="stat-icon" style="background:rgba(239,68,68,0.1);">🔴</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-label">Warning</div>
            <div class="stat-value" id="sumWarning" style="color:var(--amber)">–</div>
            <div class="stat-change" style="color:var(--amber);">Approaching critical</div>
          </div>
          <div class="stat-icon" style="background:rgba(252,211,77,0.15);">🟡</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-label">Subjects Affected</div>
            <div class="stat-value" id="sumSubjects">–</div>
            <div class="stat-change info">With at-risk students</div>
          </div>
          <div class="stat-icon blue">📚</div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div id="loadingState" style="text-align:center; padding:64px; color:var(--gray-400);">
      <div style="font-size:13px;">Loading at-risk report...</div>
    </div>

    <!-- All Clear -->
    <div id="allClearState" style="display:none; text-align:center; padding:64px;">
      <div style="font-size:56px; margin-bottom:16px;">✅</div>
      <div style="font-size:18px; font-weight:700; color:var(--green); margin-bottom:8px;">All clear!</div>
      <div style="font-size:14px; color:var(--gray-400);">No students have reached the absence threshold across any of your subjects.</div>
    </div>

    <!-- Grouped Results -->
    <div id="resultsList" style="display:none;"></div>

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

  async function loadAtRisk() {
    const threshold = parseInt(document.getElementById('thresholdInput').value) || 3;

    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('allClearState').style.display = 'none';
    document.getElementById('resultsList').style.display = 'none';
    document.getElementById('summaryCards').style.display = 'none';

    try {
      const subjectsRes = await axios.get('/api/professor/subjects');
      const subjects = subjectsRes.data.subjects;

      if (subjects.length === 0) {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('allClearState').style.display = 'block';
        document.getElementById('allClearState').querySelector('div:nth-child(3)').textContent = 'No subjects assigned yet.';
        return;
      }

      // Load all at-risk reports in parallel
      const reports = await Promise.all(
        subjects.map(s =>
          axios.get(`/api/professor/reports/at-risk?subject_id=${s.id}&threshold=${threshold}`)
            .then(r => ({ subject: s, data: r.data }))
            .catch(() => null)
        )
      );

      const validReports = reports.filter(r => r && r.data.at_risk_count > 0);
      const totalFlagged  = validReports.reduce((sum, r) => sum + r.data.at_risk_count, 0);
      const totalCritical = validReports.reduce((sum, r) =>
        sum + r.data.students.filter(s => s.absent_count >= threshold * 2).length, 0);
      const totalWarning  = totalFlagged - totalCritical;

      document.getElementById('sumFlagged').textContent   = totalFlagged;
      document.getElementById('sumCritical').textContent  = totalCritical;
      document.getElementById('sumWarning').textContent   = totalWarning;
      document.getElementById('sumSubjects').textContent  = validReports.length;
      document.getElementById('sumCriticalSub').textContent = `≥ ${threshold * 2} absences`;
      document.getElementById('summaryCards').style.display = 'grid';
      document.getElementById('loadingState').style.display = 'none';

      if (validReports.length === 0) {
        document.getElementById('allClearState').style.display = 'block';
        return;
      }

      document.getElementById('resultsList').style.display = 'block';
      document.getElementById('resultsList').innerHTML = validReports.map((r, i) => {
        const color = colors[i % colors.length];
        const students = r.data.students;

        const studentCards = students.map((s, idx) => {
          const isCritical  = s.absent_count >= threshold * 2;
          const severity    = isCritical ? 'var(--red)' : 'var(--amber)';
          const severityBg  = isCritical ? 'rgba(239,68,68,0.05)' : 'rgba(252,211,77,0.06)';
          const borderColor = isCritical ? 'rgba(239,68,68,0.2)' : 'rgba(252,211,77,0.25)';
          const label       = isCritical ? '🔴 Critical' : '🟡 Warning';

          return `
            <div style="border:1px solid ${borderColor}; background:${severityBg}; border-radius:8px; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:8px; flex-wrap:wrap;">
              <div style="display:flex; align-items:center; gap:12px;">
                <div style="background:${severity}20; color:${severity}; font-size:12px; font-weight:800; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  ${idx + 1}
                </div>
                <div>
                  <div style="font-size:14px; font-weight:700; color:var(--gray-900);">${s.name}</div>
                  <div style="font-size:12px; color:var(--gray-500);">${s.student_id_number || 'No ID'}</div>
                </div>
              </div>
              <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                <div style="text-align:center;">
                  <div style="font-size:11px; color:var(--gray-400);">Present</div>
                  <div style="font-size:15px; font-weight:700; color:var(--green);">${s.present_count}</div>
                </div>
                <div style="text-align:center;">
                  <div style="font-size:11px; color:var(--gray-400);">Late</div>
                  <div style="font-size:15px; font-weight:700; color:var(--amber);">${s.late_count}</div>
                </div>
                <div style="text-align:center;">
                  <div style="font-size:11px; color:var(--gray-400);">Absent</div>
                  <div style="font-size:15px; font-weight:700; color:var(--red);">${s.absent_count}</div>
                </div>
                <div style="text-align:center;">
                  <div style="font-size:11px; color:var(--gray-400);">Rate</div>
                  <div style="font-size:15px; font-weight:700; color:${severity};">${s.attendance_rate}%</div>
                </div>
                <span style="background:${severity}20; color:${severity}; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; white-space:nowrap;">${label}</span>
              </div>
            </div>`;
        }).join('');

        return `
          <div style="margin-bottom:28px;">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
              <div style="width:10px; height:10px; border-radius:50%; background:${color}; flex-shrink:0;"></div>
              <div style="font-size:15px; font-weight:700; color:var(--gray-900);">${r.subject.name}</div>
              <div style="font-size:12px; color:var(--gray-500);">${r.subject.year_level?.name || ''} · ${r.subject.section?.name || ''}</div>
              <span style="background:rgba(239,68,68,0.1); color:var(--red); font-size:12px; font-weight:700; padding:2px 10px; border-radius:20px; margin-left:auto;">${students.length} flagged</span>
            </div>
            ${studentCards}
          </div>`;
      }).join('');

    } catch (e) {
      document.getElementById('loadingState').style.display = 'none';
      document.getElementById('resultsList').style.display = 'block';
      document.getElementById('resultsList').innerHTML =
        '<div style="color:var(--red); padding:24px;">Failed to load at-risk report.</div>';
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadAtRisk();
</script>
@endsection