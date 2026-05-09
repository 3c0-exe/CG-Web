@extends('layouts.app')
@section('title', 'Schedules – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item {{ request()->is('admin/users') ? 'active' : '' }}"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item {{ request()->is('admin/sections') ? 'active' : '' }}"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <a href="{{ url('/admin/subjects') }}" class="nav-item {{ request()->is('admin/subjects') ? 'active' : '' }}"><span class="nav-icon">📚</span><span>Master Subjects</span></a>
    <a href="{{ url('/admin/schedules') }}" class="nav-item active"><span class="nav-icon">📅</span><span>Schedules</span></a>
    <a href="{{ url('/admin/prospectus') }}" class="nav-item {{ request()->is('admin/prospectus') ? 'active' : '' }}"><span class="nav-icon">📋</span><span>Prospectus</span></a>
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
  <div class="topbar">
    <div class="topbar-left">
      <h1>Schedules</h1>
      <p class="topbar-subtitle">Assign subjects to sections, professors, rooms, and times</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openAddSchedule()">+ Add Schedule</button>
    </div>
  </div>

  <div class="content">
    <div class="section" style="padding:16px 24px; margin-bottom:0; border-bottom:none; border-radius:8px 8px 0 0;">
      <div style="display:flex; gap:12px; align-items:center;">
        <div class="search-wrap" style="flex:1;">
          <span class="search-icon">🔍</span>
          <input type="text" class="form-input" id="searchInput" placeholder="Search by subject, section, or professor..." style="padding-left:36px;" oninput="filterSchedules(this.value)">
        </div>
      </div>
    </div>

    <div class="section" style="padding:0; overflow:hidden; border-radius:0 0 8px 8px;">
      <div class="table-wrapper" style="padding:0;">
        <table>
          <thead>
            <tr>
              <th>Subject</th>
              <th>Section</th>
              <th>Professor</th>
              <th>Schedule & Room</th>
              <th>Settings</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="schedulesTable">
            <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gray-400);">Loading schedules...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<div id="scheduleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:580px; max-height:90vh; overflow-y:auto;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;" id="modalTitle">📅 Add Schedule</h3>
    <div id="scheduleError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <input type="hidden" id="editScheduleId">
    
    <div class="form-group">
      <label class="form-label">Subject</label>
      <select class="form-select" id="schedSubject">
        <option value="">Select subject...</option>
      </select>
    </div>
    
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
      <div class="form-group">
        <label class="form-label">Section</label>
        <select class="form-select" id="schedSection">
          <option value="">Select section...</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Professor</label>
        <select class="form-select" id="schedProfessor">
          <option value="">Select professor...</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Room (Optional)</label>
      <select class="form-select" id="schedRoom">
        <option value="">Select room...</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label">Schedule Days</label>
      <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:4px;">
        <label id="dayLabelMon" style="display:flex; align-items:center; gap:6px; font-size:13px; padding:6px 12px; border:1px solid var(--gray-200); border-radius:6px; cursor:pointer;">
          <input type="checkbox" value="Mon" onchange="updateDayLabel('Mon')"> Mon
        </label>
        <label id="dayLabelTue" style="display:flex; align-items:center; gap:6px; font-size:13px; padding:6px 12px; border:1px solid var(--gray-200); border-radius:6px; cursor:pointer;">
          <input type="checkbox" value="Tue" onchange="updateDayLabel('Tue')"> Tue
        </label>
        <label id="dayLabelWed" style="display:flex; align-items:center; gap:6px; font-size:13px; padding:6px 12px; border:1px solid var(--gray-200); border-radius:6px; cursor:pointer;">
          <input type="checkbox" value="Wed" onchange="updateDayLabel('Wed')"> Wed
        </label>
        <label id="dayLabelThu" style="display:flex; align-items:center; gap:6px; font-size:13px; padding:6px 12px; border:1px solid var(--gray-200); border-radius:6px; cursor:pointer;">
          <input type="checkbox" value="Thu" onchange="updateDayLabel('Thu')"> Thu
        </label>
        <label id="dayLabelFri" style="display:flex; align-items:center; gap:6px; font-size:13px; padding:6px 12px; border:1px solid var(--gray-200); border-radius:6px; cursor:pointer;">
          <input type="checkbox" value="Fri" onchange="updateDayLabel('Fri')"> Fri
        </label>
        <label id="dayLabelSat" style="display:flex; align-items:center; gap:6px; font-size:13px; padding:6px 12px; border:1px solid var(--gray-200); border-radius:6px; cursor:pointer;">
          <input type="checkbox" value="Sat" onchange="updateDayLabel('Sat')"> Sat
        </label>
      </div>
    </div>
    
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
      <div class="form-group">
        <label class="form-label">Start Time</label>
        <input type="time" class="form-input" id="schedStartTime">
      </div>
      <div class="form-group">
        <label class="form-label">End Time</label>
        <input type="time" class="form-input" id="schedEndTime">
      </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
      <div class="form-group">
        <label class="form-label">Late Threshold (mins)</label>
        <input type="number" class="form-input" id="schedLateThreshold" value="15" min="1" max="60">
      </div>
      <div class="form-group">
        <label class="form-label">Allow Guests</label>
        <select class="form-select" id="schedAllowGuests">
          <option value="0">No</option>
          <option value="1">Yes</option>
        </select>
      </div>
    </div>

    <div style="display:flex; gap:12px; margin-top:24px;">
      <button class="btn btn-ghost" style="flex:1" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="saveSchedBtn" onclick="saveSchedule()">Save Schedule</button>
    </div>
  </div>
</div>

<!-- Enrollment Modal -->
<div id="enrollModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:580px; max-height:90vh; overflow-y:auto;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:4px;">👥 Cross-Enrolled Students</h3>
    <p style="font-size:13px; color:var(--gray-500); margin-bottom:20px;" id="enrollModalScheduleName">–</p>

    <div id="enrollError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>

    <div style="margin-bottom:20px;">
      <div style="font-size:13px; font-weight:600; color:var(--gray-700); margin-bottom:10px;">Currently Enrolled</div>
      <div id="enrolledList" style="display:flex; flex-direction:column; gap:6px; max-height:200px; overflow-y:auto;">
        <div style="color:var(--gray-400); font-size:13px; padding:8px;">Loading...</div>
      </div>
    </div>

    <div style="border-top:1px solid var(--gray-200); padding-top:20px;">
      <div style="font-size:13px; font-weight:600; color:var(--gray-700); margin-bottom:10px;">Add Students</div>
      <div style="position:relative; margin-bottom:10px;">
        <span class="search-icon">🔍</span>
        <input type="text" class="form-input" id="enrollSearch" placeholder="Search by name or student ID..." style="padding-left:36px;" oninput="filterAvailableStudents(this.value)">
      </div>
      <div id="availableList" style="display:flex; flex-direction:column; gap:6px; max-height:200px; overflow-y:auto; margin-bottom:16px;">
        <div style="color:var(--gray-400); font-size:13px; padding:8px;">Loading...</div>
      </div>
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <span id="selectedCount" style="font-size:13px; color:var(--gray-500);">0 selected</span>
        <div style="display:flex; gap:8px;">
          <button class="btn btn-ghost" onclick="document.getElementById('enrollModal').style.display='none'">Close</button>
          <button class="btn btn-primary" id="enrollBtn" onclick="enrollSelected()">Enroll Selected</button>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'admin') { localStorage.clear(); window.location.href = '/login'; }

  let allSchedules = [];
  let subjectsData = [];
  let sectionsData = [];
  let profsData = [];
  let roomsData = [];

  async function loadData() {
    try {
      const [schedRes, subjRes, ylRes, profRes, roomRes] = await Promise.all([
        axios.get('/api/admin/schedules'),
        axios.get('/api/admin/subjects'),
        axios.get('/api/admin/year-levels'),
        axios.get('/api/admin/users?role=professor'),
        axios.get('/api/admin/rooms')
      ]);
      
      allSchedules = schedRes.data.schedules;
      subjectsData = subjRes.data.subjects;
      
      // Flatten sections
      sectionsData = [];
      ylRes.data.year_levels.forEach(yl => {
        (yl.sections || []).forEach(sec => sectionsData.push(sec));
      });
      
      profsData = profRes.data.users;
      roomsData = roomRes.data.rooms;
      
      populateDropdowns();
      renderSchedules(allSchedules);
    } catch (e) {
      document.getElementById('schedulesTable').innerHTML = '<tr><td colspan="6" style="text-align:center; color:var(--red); padding:24px;">Failed to load data.</td></tr>';
    }
  }

  function populateDropdowns() {
    document.getElementById('schedSubject').innerHTML = '<option value="">Select subject...</option>' + 
      subjectsData.map(s => `<option value="${s.id}">${s.name} ${s.code ? '('+s.code+')' : ''}</option>`).join('');
      
    document.getElementById('schedSection').innerHTML = '<option value="">Select section...</option>' + 
      sectionsData.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
      
    document.getElementById('schedProfessor').innerHTML = '<option value="">Select professor...</option>' + 
      profsData.map(p => `<option value="${p.id}">${p.title ? p.title+' ' : ''}${p.name}</option>`).join('');
      
    document.getElementById('schedRoom').innerHTML = '<option value="">Select room (optional)...</option>' + 
      roomsData.map(r => `<option value="${r.id}">${r.name}</option>`).join('');
  }

  function formatTime(t) {
    if(!t) return '';
    let [h, m] = t.split(':');
    let ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${m} ${ampm}`;
  }

  function filterSchedules(q) {
    q = q.toLowerCase();
    const filtered = allSchedules.filter(s => {
      const subj = s.subject?.name?.toLowerCase() || '';
      const sec = s.section?.name?.toLowerCase() || '';
      const prof = s.professor?.name?.toLowerCase() || '';
      return subj.includes(q) || sec.includes(q) || prof.includes(q);
    });
    renderSchedules(filtered);
  }

  function renderSchedules(list) {
    const tbody = document.getElementById('schedulesTable');
    if (!list.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gray-400);">No schedules found.</td></tr>';
      return;
    }
    
    tbody.innerHTML = list.map(s => {
      const days = typeof s.schedule_days === 'string' ? JSON.parse(s.schedule_days) : (s.schedule_days || []);
      const daysStr = days.length ? days.join(', ') : 'Not set';
      const timeStr = s.schedule_start_time ? `${formatTime(s.schedule_start_time)} - ${formatTime(s.schedule_end_time)}` : 'Not set';
      const profName = s.professor ? `${s.professor.title ? s.professor.title+' ' : ''}${s.professor.name}` : 'Not assigned';
      
      return `
        <tr>
          <td>
            <div style="font-weight:600; color:var(--gray-900);">${s.subject?.name || 'Unknown'}</div>
            <div style="font-size:12px; color:var(--gray-500);">${s.subject?.code || ''}</div>
          </td>
          <td>
            <span style="font-size:12px; background:rgba(59,91,219,0.1); color:var(--primary); padding:4px 8px; border-radius:12px; font-weight:600;">
              ${s.section?.name || 'N/A'}
            </span>
          </td>
          <td>${profName}</td>
          <td>
            <div style="font-size:13px; font-weight:500;">${daysStr}</div>
            <div style="font-size:12px; color:var(--gray-500);">${timeStr}</div>
            <div style="font-size:12px; color:var(--gray-500); margin-top:2px;">🏠 ${s.room?.name || 'No Room'}</div>
          </td>
          <td>
            <div style="font-size:12px; color:var(--gray-600);">Late: ${s.late_threshold_minutes}m</div>
            <div style="font-size:12px; color:var(--gray-600);">Guests: ${s.allow_guests ? 'Yes' : 'No'}</div>
          </td>
          <td>
            <div style="display:flex; gap:6px;">
              <button class="btn btn-ghost btn-sm" onclick='openEditSchedule(${JSON.stringify(s).replace(/'/g, "&apos;")})'>✏️</button>
              <button class="btn btn-ghost btn-sm" onclick="openEnrollments(${s.id}, '${s.subject?.name} - ${s.section?.name}')">👥</button>
              <button class="btn btn-ghost btn-sm" style="color:var(--red);" onclick="deleteSchedule(${s.id})">🗑️</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function updateDayLabel(day) {
    const cb = document.querySelector(`input[value="${day}"]`);
    const label = document.getElementById('dayLabel' + day);
    if (cb.checked) {
      label.style.background = 'rgba(59,91,219,0.1)';
      label.style.borderColor = 'var(--primary)';
      label.style.color = 'var(--primary)';
      label.style.fontWeight = '600';
    } else {
      label.style.background = 'transparent';
      label.style.borderColor = 'var(--gray-200)';
      label.style.color = 'inherit';
      label.style.fontWeight = 'normal';
    }
  }

  function openAddSchedule() {
    document.getElementById('modalTitle').innerHTML = '📅 Add Schedule';
    document.getElementById('editScheduleId').value = '';
    document.getElementById('schedSubject').value = '';
    document.getElementById('schedSection').value = '';
    document.getElementById('schedProfessor').value = '';
    document.getElementById('schedRoom').value = '';
    document.getElementById('schedStartTime').value = '';
    document.getElementById('schedEndTime').value = '';
    document.getElementById('schedLateThreshold').value = '15';
    document.getElementById('schedAllowGuests').value = '0';
    
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
      cb.checked = false;
      updateDayLabel(cb.value);
    });
    
    document.getElementById('scheduleError').style.display = 'none';
    document.getElementById('scheduleModal').style.display = 'flex';
  }

  function openEditSchedule(s) {
    document.getElementById('modalTitle').innerHTML = '✏️ Edit Schedule';
    document.getElementById('editScheduleId').value = s.id;
    document.getElementById('schedSubject').value = s.subject_id;
    document.getElementById('schedSection').value = s.section_id;
    document.getElementById('schedProfessor').value = s.professor_id;
    document.getElementById('schedRoom').value = s.room_id || '';
    
    // Format times for input
    const start = s.schedule_start_time ? s.schedule_start_time.substring(0, 5) : '';
    const end = s.schedule_end_time ? s.schedule_end_time.substring(0, 5) : '';
    
    document.getElementById('schedStartTime').value = start;
    document.getElementById('schedEndTime').value = end;
    document.getElementById('schedLateThreshold').value = s.late_threshold_minutes;
    document.getElementById('schedAllowGuests').value = s.allow_guests ? '1' : '0';
    
    const days = typeof s.schedule_days === 'string' ? JSON.parse(s.schedule_days) : (s.schedule_days || []);
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
      cb.checked = days.includes(cb.value);
      updateDayLabel(cb.value);
    });
    
    document.getElementById('scheduleError').style.display = 'none';
    document.getElementById('scheduleModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('scheduleModal').style.display = 'none';
  }

  async function saveSchedule() {
    const id = document.getElementById('editScheduleId').value;
    const errEl = document.getElementById('scheduleError');
    
    const payload = {
      subject_id: document.getElementById('schedSubject').value,
      section_id: document.getElementById('schedSection').value,
      professor_id: document.getElementById('schedProfessor').value,
      room_id: document.getElementById('schedRoom').value || null,
      late_threshold_minutes: document.getElementById('schedLateThreshold').value,
      allow_guests: document.getElementById('schedAllowGuests').value === '1',
      schedule_start_time: document.getElementById('schedStartTime').value || null,
      schedule_end_time: document.getElementById('schedEndTime').value || null,
    };
    
    const days = [];
    document.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => days.push(cb.value));
    if(days.length) payload.schedule_days = days;
    
    if (!payload.subject_id || !payload.section_id || !payload.professor_id) {
      errEl.textContent = '❌ Please fill in subject, section, and professor.';
      errEl.style.display = 'block';
      return;
    }
    
    const btn = document.getElementById('saveSchedBtn');
    btn.disabled = true; btn.textContent = 'Saving...';
    errEl.style.display = 'none';
    
    try {
      if (id) await axios.patch('/api/admin/schedules/' + id, payload);
      else await axios.post('/api/admin/schedules', payload);
      
      closeModal();
      await loadData();
    } catch(e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to save schedule.');
      errEl.style.display = 'block';
    } finally {
      btn.disabled = false; btn.textContent = 'Save Schedule';
    }
  }

  async function deleteSchedule(id) {
    if(!confirm('Delete this schedule?')) return;
    try {
      await axios.delete('/api/admin/schedules/' + id);
      await loadData();
    } catch(e) {
      alert(e.response?.data?.message || 'Failed to delete');
    }
  }

  // ─── Enrollments ──────────────────────────────────────────────────────────

  let currentEnrollSchedId = null;
  let enrollAvailableStudents = [];
  let enrollSelectedIds = new Set();

  async function openEnrollments(schedId, schedName) {
    currentEnrollSchedId = schedId;
    document.getElementById('enrollModalScheduleName').textContent = schedName;
    document.getElementById('enrollError').style.display = 'none';
    document.getElementById('enrollSearch').value = '';
    enrollSelectedIds.clear();
    updateSelectedCount();
    
    document.getElementById('enrolledList').innerHTML = '<div style="color:var(--gray-400); font-size:13px; padding:8px;">Loading...</div>';
    document.getElementById('availableList').innerHTML = '<div style="color:var(--gray-400); font-size:13px; padding:8px;">Loading...</div>';
    document.getElementById('enrollModal').style.display = 'flex';
    
    await loadEnrollments();
  }

  async function loadEnrollments() {
    try {
      const [enRes, avRes] = await Promise.all([
        axios.get(`/api/admin/schedules/${currentEnrollSchedId}/enrollments`),
        axios.get(`/api/admin/schedules/${currentEnrollSchedId}/available-students`)
      ]);
      
      const enrolled = enRes.data.students;
      enrollAvailableStudents = avRes.data.students;
      
      renderEnrolledList(enrolled);
      filterAvailableStudents('');
    } catch(e) {
      document.getElementById('enrollError').textContent = 'Failed to load students.';
      document.getElementById('enrollError').style.display = 'block';
    }
  }

  function renderEnrolledList(students) {
    const list = document.getElementById('enrolledList');
    if (!students.length) {
      list.innerHTML = '<div style="color:var(--gray-400); font-size:13px; padding:8px;">No students enrolled specifically in this schedule. Note: Section students are automatically enrolled.</div>';
      return;
    }
    
    list.innerHTML = students.map(s => `
      <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:var(--gray-50); border:1px solid var(--gray-200); border-radius:6px;">
        <div>
          <div style="font-size:13px; font-weight:600;">${s.name}</div>
          <div style="font-size:12px; color:var(--gray-500);">${s.student_id_number || 'No ID'}</div>
        </div>
        <button class="btn btn-ghost btn-sm" style="color:var(--red);" onclick="unenrollStudent(${s.id})">Remove</button>
      </div>
    `).join('');
  }

  function filterAvailableStudents(q) {
    q = q.toLowerCase();
    const filtered = enrollAvailableStudents.filter(s => 
      s.name.toLowerCase().includes(q) || 
      (s.student_id_number && s.student_id_number.toLowerCase().includes(q))
    );
    
    const list = document.getElementById('availableList');
    if (!filtered.length) {
      list.innerHTML = '<div style="color:var(--gray-400); font-size:13px; padding:8px;">No students found.</div>';
      return;
    }
    
    list.innerHTML = filtered.map(s => {
      const isSelected = enrollSelectedIds.has(s.id);
      return `
        <div style="display:flex; align-items:center; gap:12px; padding:8px 12px; border-bottom:1px solid var(--gray-100); cursor:pointer;" onclick="toggleStudentSelection(${s.id})">
          <input type="checkbox" ${isSelected ? 'checked' : ''} onclick="event.stopPropagation(); toggleStudentSelection(${s.id})">
          <div>
            <div style="font-size:13px; font-weight:600;">${s.name}</div>
            <div style="font-size:12px; color:var(--gray-500);">${s.student_id_number || 'No ID'}</div>
          </div>
        </div>
      `;
    }).join('');
  }

  function toggleStudentSelection(id) {
    if (enrollSelectedIds.has(id)) enrollSelectedIds.delete(id);
    else enrollSelectedIds.add(id);
    
    updateSelectedCount();
    filterAvailableStudents(document.getElementById('enrollSearch').value);
  }

  function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = `${enrollSelectedIds.size} selected`;
  }

  async function enrollSelected() {
    if (enrollSelectedIds.size === 0) return;
    
    const btn = document.getElementById('enrollBtn');
    btn.disabled = true; btn.textContent = 'Enrolling...';
    
    try {
      await axios.post(`/api/admin/schedules/${currentEnrollSchedId}/enroll`, {
        student_ids: Array.from(enrollSelectedIds)
      });
      enrollSelectedIds.clear();
      updateSelectedCount();
      await loadEnrollments();
      document.getElementById('enrollSearch').value = '';
    } catch(e) {
      document.getElementById('enrollError').textContent = e.response?.data?.message || 'Enrollment failed';
      document.getElementById('enrollError').style.display = 'block';
    } finally {
      btn.disabled = false; btn.textContent = 'Enroll Selected';
    }
  }

  async function unenrollStudent(studentId) {
    if(!confirm('Remove this student from this class schedule?')) return;
    try {
      await axios.delete(`/api/admin/schedules/${currentEnrollSchedId}/unenroll/${studentId}`);
      await loadEnrollments();
    } catch(e) {
      document.getElementById('enrollError').textContent = 'Failed to unenroll';
      document.getElementById('enrollError').style.display = 'block';
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadData();
</script>
@endsection
