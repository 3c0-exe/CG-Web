@extends('layouts.app')
@section('title', 'Prospectus – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item {{ request()->is('admin/users') ? 'active' : '' }}"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item {{ request()->is('admin/sections') ? 'active' : '' }}"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <a href="{{ url('/admin/subjects') }}" class="nav-item {{ request()->is('admin/subjects') ? 'active' : '' }}"><span class="nav-icon">📚</span><span>Master Subjects</span></a>
    <a href="{{ url('/admin/schedules') }}" class="nav-item {{ request()->is('admin/schedules') ? 'active' : '' }}"><span class="nav-icon">📅</span><span>Schedules</span></a>
    <a href="{{ url('/admin/prospectus') }}" class="nav-item active"><span class="nav-icon">📋</span><span>Prospectus</span></a>
    <a href="{{ url('/admin/rooms') }}" class="nav-item {{ request()->is('admin/rooms') ? 'active' : '' }}"><span class="nav-icon">🏠</span><span>Rooms</span></a>
    <a href="{{ url('/admin/devices') }}" class="nav-item {{ request()->is('admin/devices') ? 'active' : '' }}"><span class="nav-icon">📡</span><span>Devices</span></a>
    <div class="nav-divider"></div>
    <a href="#" class="nav-item" onclick="openPasswordModal()"><span class="nav-icon">🔒</span><span>Change Password</span></a>
    <a href="#" class="nav-item" onclick="logout()"><span class="nav-icon">🚪</span><span>Sign Out</span></a>
  </nav>
  <div class="user-section">
    <div class="user-info">
      <div class="user-avatar">AD</div>
      <div><div class="user-name">Admin</div><div class="user-role">System Administrator</div></div>
    </div>
  </div>
</aside>

<main class="main">
  <div class="topbar print-hide">
    <div class="topbar-left">
      <h1>Prospectus & Master Schedule</h1>
      <p class="topbar-subtitle">Overview of all assigned subjects and schedules per section</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>
  </div>

  <div class="content">
    <div id="prospectusContainer" style="display:flex; flex-direction:column; gap:32px;">
      <div style="text-align:center; padding:40px; color:var(--gray-400);">Loading prospectus data...</div>
    </div>
  </div>
</main>

<style>
  @media print {
    body { background: white; }
    .sidebar, .topbar, .print-hide { display: none !important; }
    .main { margin-left: 0 !important; padding: 0 !important; }
    .content { padding: 0 !important; max-width: 100% !important; box-shadow: none !important; }
    .yl-block { page-break-inside: avoid; margin-bottom: 20px; }
    .sec-block { page-break-inside: avoid; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 6px; font-size: 11px; }
    th { background: #f8f9fa; -webkit-print-color-adjust: exact; }
    h2, h3 { margin: 0 0 10px 0; }
  }
</style>

@endsection

@section('scripts')
<script>
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'admin') { localStorage.clear(); window.location.href = '/login'; }

  function formatTime(t) {
    if(!t) return '';
    let [h, m] = t.split(':');
    let ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${m} ${ampm}`;
  }

  async function loadProspectus() {
    try {
      const res = await axios.get('/api/admin/prospectus');
      const yearLevels = res.data.year_levels;
      
      const container = document.getElementById('prospectusContainer');
      if (!yearLevels.length) {
        container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--gray-400);">No data found.</div>';
        return;
      }

      let html = '';

      yearLevels.forEach(yl => {
        if (!yl.sections || yl.sections.length === 0) return;
        
        html += `<div class="yl-block">
          <h2 style="font-size:24px; color:var(--primary); margin-bottom:16px; border-bottom:2px solid var(--primary); padding-bottom:8px;">
            ${yl.name}
          </h2>`;
          
        yl.sections.forEach(sec => {
          html += `<div class="sec-block" style="background:var(--white); border:1px solid var(--gray-200); border-radius:8px; margin-bottom:24px; overflow:hidden;">
            <div style="background:var(--gray-50); padding:12px 16px; border-bottom:1px solid var(--gray-200);">
              <h3 style="font-size:16px; font-weight:700; color:var(--gray-900); margin:0;">Section: ${sec.name}</h3>
            </div>`;
            
          if (!sec.schedules || sec.schedules.length === 0) {
            html += `<div style="padding:16px; color:var(--gray-500); font-size:13px; text-align:center;">No subjects scheduled for this section yet.</div>`;
          } else {
            html += `
              <div class="table-wrapper" style="padding:0;">
                <table style="margin:0;">
                  <thead>
                    <tr>
                      <th>Subject Code</th>
                      <th>Subject Description</th>
                      <th>Professor</th>
                      <th>Schedule Days</th>
                      <th>Time</th>
                      <th>Room</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${sec.schedules.map(sch => {
                      const days = typeof sch.schedule_days === 'string' ? JSON.parse(sch.schedule_days) : (sch.schedule_days || []);
                      const prof = sch.professor ? `${sch.professor.title ? sch.professor.title+' ' : ''}${sch.professor.name}` : 'TBA';
                      return `
                      <tr>
                        <td style="font-family:monospace; font-weight:600;">${sch.subject?.code || 'N/A'}</td>
                        <td>${sch.subject?.name || 'Unknown'}</td>
                        <td>${prof}</td>
                        <td>${days.join(', ') || 'TBA'}</td>
                        <td>${sch.schedule_start_time ? formatTime(sch.schedule_start_time) + ' - ' + formatTime(sch.schedule_end_time) : 'TBA'}</td>
                        <td>${sch.room?.name || 'TBA'}</td>
                      </tr>`;
                    }).join('')}
                  </tbody>
                </table>
              </div>`;
          }
          html += `</div>`; // end sec-block
        });
        
        html += `</div>`; // end yl-block
      });

      container.innerHTML = html || '<div style="text-align:center; padding:40px; color:var(--gray-400);">No schedules assigned to any sections yet.</div>';
    } catch (e) {
      document.getElementById('prospectusContainer').innerHTML = 
        '<div style="text-align:center; padding:40px; color:var(--red);">Failed to load prospectus data.</div>';
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadProspectus();
</script>
@endsection
