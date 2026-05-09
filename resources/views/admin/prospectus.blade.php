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
      <h1>Prospectus</h1>
      <p class="topbar-subtitle">Assign professors to sections and view the complete schedule overview</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-ghost" onclick="window.print()">🖨️ Print</button>
    </div>
  </div>

  <div class="content">
    <!-- Year Level Tab Selector -->
    <div id="yearTabs" class="print-hide" style="display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap;"></div>

    <div id="prospectusContainer" style="display:flex; flex-direction:column; gap:24px;">
      <div style="text-align:center; padding:40px; color:var(--gray-400);">Loading prospectus data...</div>
    </div>
  </div>
</main>

<!-- Assign Professor Modal -->
<div id="assignModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:480px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:6px;">Assign Professor</h3>
    <p style="font-size:13px; color:var(--gray-500); margin-bottom:20px;" id="assignMeta">–</p>
    <div id="assignError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>

    <div class="form-group">
      <label class="form-label">Professor <span style="color:var(--red)">*</span></label>
      <select class="form-select" id="assignProfessor">
        <option value="">Select professor...</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Schedule Days</label>
      <div id="assignDays" style="display:flex; gap:6px; flex-wrap:wrap;"></div>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
      <div class="form-group">
        <label class="form-label">Start Time</label>
        <input type="time" class="form-input" id="assignStart">
      </div>
      <div class="form-group">
        <label class="form-label">End Time</label>
        <input type="time" class="form-input" id="assignEnd">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Room</label>
      <select class="form-select" id="assignRoom">
        <option value="">Select room (optional)...</option>
      </select>
    </div>

    <div style="display:flex; gap:12px; margin-top:16px;">
      <button class="btn btn-ghost" style="flex:1" onclick="closeAssignModal()">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="assignBtn" onclick="saveAssignment()">Save Assignment</button>
    </div>
  </div>
</div>

<style>
  .day-btn {
    padding: 6px 12px !important; border-radius: 6px !important; border: 1px solid var(--gray-200) !important;
    background: var(--white) !important; color: var(--gray-700) !important; font-size: 12px !important; font-weight: 600 !important;
    cursor: pointer; transition: all 0.15s ease;
  }
  .day-btn.selected { background: var(--primary) !important; color: white !important; border-color: var(--primary) !important; }
  .yl-tab {
    padding: 8px 20px !important; border-radius: 8px !important; border: 1px solid var(--gray-200) !important;
    background: var(--white) !important; color: var(--gray-600) !important; font-size: 13px !important; font-weight: 600 !important;
    cursor: pointer; transition: all 0.15s ease;
  }
  .yl-tab:hover { border-color: var(--primary) !important; color: var(--primary) !important; }
  .yl-tab.active { background: var(--primary) !important; color: white !important; border-color: var(--primary) !important; }
  .assign-btn {
    background: rgba(37,99,235,0.08); color: var(--primary); border: 1px solid rgba(37,99,235,0.2);
    padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;
    transition: all 0.15s ease;
  }
  .assign-btn:hover { background: var(--primary); color: white; }

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
    .assign-btn { display: none !important; }
    h2, h3 { margin: 0 0 10px 0; }
  }
</style>

@endsection

@section('scripts')
<script>
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'admin') { localStorage.clear(); window.location.href = '/login'; }

  let allYearLevels = [];
  let allProfessors = [];
  let allRooms = [];
  let activeYL = null;
  let editingScheduleId = null;
  const ALL_DAYS = ['Mon','Tue','Wed','Thu','Fri','Sat'];

  function formatTime(t) {
    if(!t) return '';
    let [h, m] = t.split(':');
    let ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${m} ${ampm}`;
  }

  async function loadProspectus() {
    try {
      const [prosRes, usersRes, roomsRes] = await Promise.all([
        axios.get('/api/admin/prospectus'),
        axios.get('/api/admin/users'),
        axios.get('/api/rooms'),
      ]);

      allYearLevels = prosRes.data.year_levels;
      allProfessors = usersRes.data.users.filter(u => u.role === 'professor');
      allRooms = roomsRes.data.rooms || [];

      renderTabs();
      if (allYearLevels.length > 0) {
        selectYL(allYearLevels[0].id);
      } else {
        document.getElementById('prospectusContainer').innerHTML =
          '<div style="text-align:center; padding:40px; color:var(--gray-400);">No year levels found. Add sections first.</div>';
      }
    } catch (e) {
      document.getElementById('prospectusContainer').innerHTML =
        '<div style="text-align:center; padding:40px; color:var(--red);">Failed to load prospectus data.</div>';
    }
  }

  function renderTabs() {
    const base = 'display:inline-block; padding:8px 20px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; margin-right:8px; margin-bottom:8px;';
    const inactive = base + 'background:#f3f4f6; color:#4b5563; border:1px solid #e5e7eb;';
    const active = base + 'background:var(--navy-blue); color:#fff; border:1px solid var(--navy-blue);';

    document.getElementById('yearTabs').innerHTML = allYearLevels.map(yl =>
      `<div style="${activeYL === yl.id ? active : inactive}" onclick="selectYL(${yl.id})">${yl.name}</div>`
    ).join('') + `<div style="${activeYL === 'all' ? active : inactive}" onclick="selectYL('all')">All Year Levels</div>`;
  }

  function selectYL(ylId) {
    activeYL = ylId;
    renderTabs();
    renderProspectus();
  }

  function renderProspectus() {
    const container = document.getElementById('prospectusContainer');
    const levels = activeYL === 'all' ? allYearLevels : allYearLevels.filter(yl => yl.id === activeYL);

    if (!levels.length) {
      container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--gray-400);">No data for this year level.</div>';
      return;
    }

    let html = '';
    levels.forEach(yl => {
      if (!yl.sections || yl.sections.length === 0) return;

      html += `<div class="yl-block">
        <h2 style="font-size:22px; color:var(--primary); margin-bottom:16px; border-bottom:2px solid var(--primary); padding-bottom:8px;">
          📘 ${yl.name}
        </h2>`;

      yl.sections.forEach(sec => {
        html += `<div class="sec-block" style="background:var(--white); border:1px solid var(--gray-200); border-radius:8px; margin-bottom:20px; overflow:hidden;">
          <div style="background:var(--gray-50); padding:12px 16px; border-bottom:1px solid var(--gray-200); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:15px; font-weight:700; color:var(--gray-900); margin:0;">📂 ${sec.name}</h3>
            <span style="font-size:12px; color:var(--gray-500);">${(sec.schedules || []).length} subject(s) assigned</span>
          </div>`;

        if (!sec.schedules || sec.schedules.length === 0) {
          html += `<div style="padding:20px; color:var(--gray-400); font-size:13px; text-align:center;">No subjects scheduled for this section yet. <a href="/admin/schedules" style="color:var(--primary); text-decoration:underline;">Add via Schedules →</a></div>`;
        } else {
          html += `
            <div class="table-wrapper" style="padding:0;">
              <table style="margin:0;">
                <thead>
                  <tr>
                    <th>Subject</th>
                    <th>Professor</th>
                    <th>Days</th>
                    <th>Time</th>
                    <th>Room</th>
                    <th class="print-hide" style="width:80px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  ${sec.schedules.map(sch => {
                    const days = typeof sch.schedule_days === 'string' ? JSON.parse(sch.schedule_days) : (sch.schedule_days || []);
                    const prof = sch.professor ? sch.professor.name : null;
                    const profDisplay = prof
                      ? `<span style="color:var(--gray-900); font-weight:600;">${prof}</span>`
                      : `<span style="color:var(--amber); font-weight:600;">⚠ Unassigned</span>`;
                    const timeStr = sch.schedule_start_time ? formatTime(sch.schedule_start_time) + ' – ' + formatTime(sch.schedule_end_time) : '<span style="color:var(--gray-400);">TBA</span>';
                    const roomStr = sch.room?.name || '<span style="color:var(--gray-400);">TBA</span>';

                    return `
                    <tr>
                      <td style="font-weight:600;">${sch.subject?.name || 'Unknown'}</td>
                      <td>${profDisplay}</td>
                      <td>${days.length ? days.join(', ') : '<span style="color:var(--gray-400);">TBA</span>'}</td>
                      <td>${timeStr}</td>
                      <td>${roomStr}</td>
                      <td class="print-hide"><button class="assign-btn" onclick="openAssignModal(${sch.id})">✏️ Edit</button></td>
                    </tr>`;
                  }).join('')}
                </tbody>
              </table>
            </div>`;
        }
        html += `</div>`;
      });
      html += `</div>`;
    });

    container.innerHTML = html || '<div style="text-align:center; padding:40px; color:var(--gray-400);">No schedules assigned to any sections yet.</div>';
  }

  // ─── Assign/Edit Modal ──────────────────────────────────────────────────
  function openAssignModal(scheduleId) {
    editingScheduleId = scheduleId;

    // Find the schedule across all year levels
    let schedule = null;
    allYearLevels.forEach(yl => {
      yl.sections.forEach(sec => {
        const found = (sec.schedules || []).find(s => s.id === scheduleId);
        if (found) { schedule = found; schedule._section = sec.name; schedule._yl = yl.name; }
      });
    });
    if (!schedule) return;

    document.getElementById('assignMeta').textContent =
      `${schedule.subject?.name || 'Subject'} → ${schedule._section} (${schedule._yl})`;
    document.getElementById('assignError').style.display = 'none';

    // Professor select
    document.getElementById('assignProfessor').innerHTML =
      '<option value="">Select professor...</option>' +
      allProfessors.map(p => `<option value="${p.id}" ${schedule.professor_id == p.id ? 'selected' : ''}>${p.name}</option>`).join('');

    // Days
    const currentDays = typeof schedule.schedule_days === 'string' ? JSON.parse(schedule.schedule_days) : (schedule.schedule_days || []);
    document.getElementById('assignDays').innerHTML = ALL_DAYS.map(d => {
      const sel = currentDays.includes(d);
      const style = sel
        ? 'display:inline-block; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; background:var(--navy-blue); color:#fff; border:1px solid var(--navy-blue);'
        : 'display:inline-block; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; background:#fff; color:#4b5563; border:1px solid #e5e7eb;';
      return `<div style="${style}" onclick="toggleDay(this)" data-day="${d}">${d}</div>`;
    }).join('');

    // Time
    document.getElementById('assignStart').value = schedule.schedule_start_time || '';
    document.getElementById('assignEnd').value = schedule.schedule_end_time || '';

    // Room
    document.getElementById('assignRoom').innerHTML =
      '<option value="">No room assigned</option>' +
      allRooms.map(r => `<option value="${r.id}" ${schedule.room_id == r.id ? 'selected' : ''}>${r.name}</option>`).join('');

    document.getElementById('assignBtn').disabled = false;
    document.getElementById('assignBtn').textContent = 'Save Assignment';
    document.getElementById('assignModal').style.display = 'flex';
  }

  function closeAssignModal() {
    document.getElementById('assignModal').style.display = 'none';
  }

  async function saveAssignment() {
    const btn = document.getElementById('assignBtn');
    const errEl = document.getElementById('assignError');
    btn.disabled = true; btn.textContent = 'Saving...';
    errEl.style.display = 'none';

    const selectedDays = [...document.querySelectorAll('#assignDays .day-btn.selected')].map(b => b.dataset.day);
    const professorId = document.getElementById('assignProfessor').value || null;
    const startTime = document.getElementById('assignStart').value || null;
    const endTime = document.getElementById('assignEnd').value || null;
    const roomId = document.getElementById('assignRoom').value || null;

    try {
      await axios.patch(`/api/admin/schedules/${editingScheduleId}`, {
        professor_id: professorId,
        schedule_days: selectedDays,
        schedule_start_time: startTime,
        schedule_end_time: endTime,
        room_id: roomId,
      });

      closeAssignModal();
      // Reload
      const res = await axios.get('/api/admin/prospectus');
      allYearLevels = res.data.year_levels;
      renderProspectus();
    } catch (e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to save.');
      errEl.style.display = 'block';
      btn.disabled = false; btn.textContent = 'Save Assignment';
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadProspectus();
</script>
@endsection
