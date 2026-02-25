@extends('layouts.app')
@section('title', 'Attendance – ClassGuard')

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/student/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/student/classes') }}" class="nav-item"><span class="nav-icon">📚</span><span>My Classes</span></a>
    <a href="{{ url('/student/attendance') }}" class="nav-item active"><span class="nav-icon">✓</span><span>Attendance</span></a>
    <a href="{{ url('/student/rfid-link') }}" class="nav-item"><span class="nav-icon">🔗</span><span>Link RFID Card</span></a>
    <div class="nav-divider"></div>
    <div class="nav-section">Account</div>
    <a href="#" class="nav-item" onclick="logout()"><span class="nav-icon">🚪</span><span>Sign Out</span></a>
  </nav>
  <div class="user-section">
    <div class="user-info">
      <div class="user-avatar" id="userAvatar">JD</div>
      <div><div class="user-name" id="userName">Loading...</div><div class="user-role">Student</div></div>
    </div>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h1>Attendance Records</h1>
      <p class="topbar-subtitle">This semester</p>
    </div>
    <div class="topbar-right">
      <select class="form-select" style="width:auto; padding:8px 32px 8px 12px; font-size:13px;" id="subjectFilter" onchange="filterRecords()">
        <option value="all">All Subjects</option>
      </select>
    </div>
  </div>

  <div class="content">
    <!-- Summary -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
      <div class="stat-card"><div class="stat-label">Sessions Attended</div><div class="stat-value" style="color:var(--green)" id="totalPresent">–</div><div class="stat-change info" id="totalSessions">Loading...</div></div>
      <div class="stat-card"><div class="stat-label">Times Late</div><div class="stat-value" style="color:#D97706" id="totalLate">–</div><div class="stat-change info">counted as present</div></div>
      <div class="stat-card"><div class="stat-label">Absences</div><div class="stat-value" style="color:var(--red)" id="totalAbsent">–</div><div class="stat-change negative">total absences</div></div>
      <div class="stat-card"><div class="stat-label">Overall Rate</div><div class="stat-value" style="color:var(--green)" id="overallRate">–</div><div class="stat-change positive">this semester</div></div>
    </div>

    <!-- Detailed Log -->
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Attendance Log</h2>
        <div style="display:flex; gap:8px;">
          <button class="btn btn-ghost btn-sm" onclick="filterByStatus('all')" id="btnAll" style="color:var(--navy-blue); border-color:var(--navy-blue);">All</button>
          <button class="btn btn-ghost btn-sm" onclick="filterByStatus('present')" id="btnPresent">Present</button>
          <button class="btn btn-ghost btn-sm" onclick="filterByStatus('absent')" id="btnAbsent">Absent</button>
          <button class="btn btn-ghost btn-sm" onclick="filterByStatus('late')" id="btnLate">Late</button>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Subject</th>
              <th>Time Scanned</th>
              <th>Type</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="logTable">
            <tr><td colspan="5" style="text-align:center; padding:32px; color:var(--gray-400);">Loading records...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
@endsection

@section('scripts')
<script>
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'student') { localStorage.clear(); window.location.href = '/login'; }
  axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;

  document.getElementById('userName').textContent = user.name || 'Student';
  document.getElementById('userAvatar').textContent = (user.name || 'S').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();

  let allRecords = [];
  let currentStatus = 'all';

  function statusBadge(s) {
    const map = { present: 'success', late: 'warning', absent: 'danger', pending: 'info' };
    return `<span class="badge ${map[s] || 'neutral'}">${s}</span>`;
  }

  function formatTime(dt) {
    if (!dt) return '–';
    return new Date(dt).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  }

  function formatDate(dt) {
    if (!dt) return '–';
    return new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function renderTable(records) {
    if (records.length === 0) {
      document.getElementById('logTable').innerHTML = '<tr><td colspan="5" style="text-align:center; padding:32px; color:var(--gray-400);">No records found.</td></tr>';
      return;
    }
    document.getElementById('logTable').innerHTML = records.map(r => `
      <tr>
        <td>${formatDate(r.rfid_scanned_at || r.created_at)}</td>
        <td>${r.session?.subject?.name || '–'}</td>
        <td>${formatTime(r.rfid_scanned_at)}</td>
        <td><span class="badge info">RFID</span></td>
        <td>${statusBadge(r.status)}</td>
      </tr>`).join('');
  }

  function filterByStatus(status) {
    currentStatus = status;
    ['all','present','absent','late'].forEach(s => {
      document.getElementById('btn' + s.charAt(0).toUpperCase() + s.slice(1)).style.color = '';
      document.getElementById('btn' + s.charAt(0).toUpperCase() + s.slice(1)).style.borderColor = '';
    });
    const active = document.getElementById('btn' + status.charAt(0).toUpperCase() + status.slice(1));
    active.style.color = 'var(--navy-blue)';
    active.style.borderColor = 'var(--navy-blue)';

    const subject = document.getElementById('subjectFilter').value;
    let filtered = allRecords;
    if (status !== 'all') filtered = filtered.filter(r => r.status === status);
    if (subject !== 'all') filtered = filtered.filter(r => r.session?.subject?.name === subject);
    renderTable(filtered);
  }

  function filterRecords() { filterByStatus(currentStatus); }

  async function loadAttendance() {
    try {
      const res = await axios.get('/api/attendance/my');
      allRecords = res.data.attendance;

      const present = allRecords.filter(r => r.status === 'present').length;
      const late = allRecords.filter(r => r.status === 'late').length;
      const absent = allRecords.filter(r => r.status === 'absent').length;
      const total = allRecords.length;
      const rate = total > 0 ? ((present + late) / total * 100).toFixed(1) : '0.0';

      document.getElementById('totalPresent').textContent = present + late;
      document.getElementById('totalSessions').textContent = `out of ${total} total`;
      document.getElementById('totalLate').textContent = late;
      document.getElementById('totalAbsent').textContent = absent;
      document.getElementById('overallRate').textContent = rate + '%';

      // Populate subject filter
      const subjects = [...new Set(allRecords.map(r => r.session?.subject?.name).filter(Boolean))];
      const filterEl = document.getElementById('subjectFilter');
      subjects.forEach(s => filterEl.innerHTML += `<option value="${s}">${s}</option>`);

      renderTable(allRecords);
    } catch (e) {
      document.getElementById('logTable').innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--red); padding:24px;">Failed to load records.</td></tr>';
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadAttendance();
</script>
@endsection
