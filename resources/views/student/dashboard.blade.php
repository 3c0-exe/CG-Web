@extends('layouts.app')
@section('title', 'Dashboard – ClassGuard')

@section('content')
<aside class="sidebar">
  <div class="logo">
    <div class="logo-icon">C</div>
    <span class="logo-text">ClassGuard</span>
  </div>
  <nav>
    <a href="{{ url('/student/dashboard') }}" class="nav-item active"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/student/classes') }}" class="nav-item"><span class="nav-icon">📚</span><span>My Classes</span></a>
    <a href="{{ url('/student/attendance') }}" class="nav-item"><span class="nav-icon">✓</span><span>Attendance</span></a>
    <a href="{{ url('/student/rfid-link') }}" class="nav-item"><span class="nav-icon">🔗</span><span>Link RFID Card</span></a>
    <div class="nav-divider"></div>
    <div class="nav-section">Account</div>
    <a href="#" class="nav-item" onclick="logout()"><span class="nav-icon">🚪</span><span>Sign Out</span></a>
  </nav>
  <div class="user-section">
    <div class="user-info">
      <div class="user-avatar" id="userAvatar">JD</div>
      <div>
        <div class="user-name" id="userName">Loading...</div>
        <div class="user-role" id="userRole">Student</div>
      </div>
    </div>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h1>Dashboard</h1>
      <p class="topbar-subtitle" id="topbarSubtitle">Loading...</p>
    </div>
    <div class="topbar-right">
      <button class="icon-btn notif-dot">🔔</button>
      <button class="icon-btn">⚙️</button>
    </div>
  </div>

  <div class="content">
    <!-- RFID Alert (shown if no card linked) -->
    <div id="rfidAlert" style="display:none; background: rgba(252,211,77,0.1); border: 1px solid rgba(252,211,77,0.4); border-radius: 8px; padding: 14px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
      <div style="display:flex; align-items:center; gap:12px;">
        <span style="font-size:20px;">🔗</span>
        <div>
          <div style="font-size:14px; font-weight:600; color:#92400e;">RFID Card Not Linked</div>
          <div style="font-size:12px; color:#b45309;">Link your card to enable automatic attendance scanning</div>
        </div>
      </div>
      <a href="{{ url('/student/rfid-link') }}" style="background:var(--gold); color:var(--navy-dark); padding:8px 16px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none; white-space:nowrap;">Link Now</a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-label">Enrolled Classes</div>
            <div class="stat-value" id="classCount">–</div>
            <div class="stat-change info">This semester</div>
          </div>
          <div class="stat-icon gold">📚</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-label">Overall Attendance</div>
            <div class="stat-value" id="attendancePercent" style="color:var(--green)">–</div>
            <div class="stat-change positive" id="attendanceChange">Loading...</div>
          </div>
          <div class="stat-icon green">✅</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-label">Present</div>
            <div class="stat-value" id="presentCount">–</div>
            <div class="stat-change info">Total sessions</div>
          </div>
          <div class="stat-icon blue">📅</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-label">Absences</div>
            <div class="stat-value" id="absentCount" style="color:var(--red)">–</div>
            <div class="stat-change negative">Total absences</div>
          </div>
          <div class="stat-icon red">⚠️</div>
        </div>
      </div>
    </div>

    <div class="grid-2">
      <!-- Enrolled Classes -->
      <div class="section">
        <div class="section-header">
          <h2 class="section-title">My Classes</h2>
          <a href="{{ url('/student/classes') }}" class="btn btn-ghost btn-sm">All Classes</a>
        </div>
        <div id="classesList">
          <div style="text-align:center; padding:32px; color:var(--gray-400);">Loading classes...</div>
        </div>
      </div>

      <!-- Attendance Summary -->
      <div class="section">
        <div class="section-header">
          <h2 class="section-title">Attendance by Subject</h2>
          <a href="{{ url('/student/attendance') }}" class="btn btn-ghost btn-sm">Details</a>
        </div>
        <div id="attendanceSummary">
          <div style="text-align:center; padding:32px; color:var(--gray-400);">Loading...</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Quick Actions</h2>
      </div>
      <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <a href="{{ url('/student/classes') }}" class="btn btn-primary">📚 Join a Class</a>
        <a href="{{ url('/student/rfid-link') }}" class="btn btn-ghost">🔗 Link RFID Card</a>
        <a href="{{ url('/student/attendance') }}" class="btn btn-ghost">✓ View Attendance</a>
      </div>
    </div>
  </div>
</main>
@endsection

@section('scripts')
<script>
  // Auth guard
  // token is declared in layouts/app.blade.php
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'student') {
    localStorage.clear();
    window.location.href = '/login';
  }



  // Set user info in sidebar
  document.getElementById('userName').textContent = user.name || 'Student';
  document.getElementById('userRole').textContent = 'Student · ' + (user.student_id_number || '');
  const initials = (user.name || 'S').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();
  document.getElementById('userAvatar').textContent = initials;
  document.getElementById('topbarSubtitle').textContent = new Date().toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

  // Show RFID alert if no card
  if (!user.rfid_uid) document.getElementById('rfidAlert').style.display = 'flex';

  async function loadDashboard() {
    try {
      // Load enrolled classes
      const classesRes = await axios.get('/api/enrollment/my-classes');
      const classes = classesRes.data.classes;
      document.getElementById('classCount').textContent = classes.length;

      // Render classes list
      if (classes.length === 0) {
        document.getElementById('classesList').innerHTML = '<div style="text-align:center; padding:32px; color:var(--gray-400);">No classes enrolled yet. <a href="/student/classes">Join a class →</a></div>';
      } else {
        document.getElementById('classesList').innerHTML = classes.slice(0,4).map(c => `
          <div class="list-item">
            <div class="list-left">
              <div class="item-icon" style="background:rgba(30,58,138,0.1)">📘</div>
              <div class="item-details">
                <h4>${c.subject.name}</h4>
                <p>${c.subject.code} · ${c.subject.section?.name || ''} · ${c.enrollment_type}</p>
              </div>
            </div>
            <span class="badge ${c.enrollment_type === 'guest' ? 'gold' : 'info'}">${c.enrollment_type}</span>
          </div>
        `).join('');
      }

      // Load attendance
      const attendanceRes = await axios.get('/api/attendance/my');
      const records = attendanceRes.data.attendance;
      const present = records.filter(r => r.status === 'present' || r.status === 'late').length;
      const absent = records.filter(r => r.status === 'absent').length;
      const total = records.length;
      const percentage = total > 0 ? (present / total * 100).toFixed(1) : '0.0';

      document.getElementById('presentCount').textContent = present;
      document.getElementById('absentCount').textContent = absent;
      document.getElementById('attendancePercent').textContent = percentage + '%';
      document.getElementById('attendanceChange').textContent = total + ' total sessions';

      // Attendance by subject
      const bySubject = {};
      records.forEach(r => {
        const name = r.session?.subject?.name || 'Unknown';
        if (!bySubject[name]) bySubject[name] = { present: 0, total: 0 };
        bySubject[name].total++;
        if (r.status === 'present' || r.status === 'late') bySubject[name].present++;
      });

      const summaryHtml = Object.entries(bySubject).map(([name, data]) => {
        const pct = data.total > 0 ? (data.present / data.total * 100).toFixed(0) : 0;
        const color = pct >= 75 ? 'var(--green)' : pct >= 50 ? '#D97706' : 'var(--red)';
        const fillClass = pct >= 75 ? 'green' : pct >= 50 ? 'gold' : 'red';
        return `
          <div style="margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
              <span style="font-size:13px; font-weight:500;">${name}</span>
              <span style="font-size:13px; font-weight:600; color:${color}">${pct}%</span>
            </div>
            <div class="progress-bar"><div class="progress-fill ${fillClass}" style="width:${pct}%"></div></div>
          </div>`;
      }).join('') || '<div style="color:var(--gray-400); text-align:center; padding:24px;">No attendance records yet.</div>';

      document.getElementById('attendanceSummary').innerHTML = summaryHtml;

    } catch (error) {
      console.error('Dashboard load error:', error);
    }
  }

  function logout() {
    axios.post('/api/logout').finally(() => {
      localStorage.clear();
      window.location.href = '/login';
    });
  }

  loadDashboard();
</script>
@endsection
