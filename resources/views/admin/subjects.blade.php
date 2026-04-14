@extends('layouts.app')
@section('title', 'Subjects – ClassGuard')

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <a href="{{ url('/admin/subjects') }}" class="nav-item active"><span class="nav-icon">📚</span><span>Subjects</span></a>
    <a href="{{ url('/admin/rooms') }}" class="nav-item"><span class="nav-icon">🏠</span><span>Room Availability</span></a>
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
      <h1>Subjects</h1>
      <p class="topbar-subtitle">Manage all subjects, professors, and class settings</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openAddSubject()">+ Add Subject</button>
    </div>
  </div>

  <div class="content">
    <div class="section" style="padding:16px 24px; margin-bottom:0; border-bottom:none; border-radius:8px 8px 0 0;">
      <div style="display:flex; gap:12px; align-items:center;">
        <div class="search-wrap" style="flex:1;">
          <span class="search-icon">🔍</span>
          <input type="text" class="form-input" id="searchInput" placeholder="Search by name or professor..." style="padding-left:36px;" oninput="filterSubjects(this.value)">
        </div>
        <select class="form-select" id="filterSection" style="width:180px;" onchange="filterSubjects(document.getElementById('searchInput').value)">
          <option value="">All Sections</option>
        </select>
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
              <th>Schedule</th>
              <th>Late Threshold</th>
              <th>Guests</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="subjectsTable">
            <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gray-400);">Loading subjects...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<div id="subjectModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;" id="modalTitle">📚 Add New Subject</h3>
    <div id="subjectError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <input type="hidden" id="editSubjectId">
    
    <div class="form-group">
      <label class="form-label">Subject Name</label>
      <input type="text" class="form-input" id="subjName" placeholder="e.g. Introduction to Computing">
    </div>
    
    <div class="form-group">
      <label class="form-label">Year Level</label>
      <select class="form-select" id="subjYearLevel" onchange="loadModalSections(this.value)">
        <option value="">Select year level...</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Section</label>
      <select class="form-select" id="subjSection">
        <option value="">Select section...</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Professor</label>
      <select class="form-select" id="subjProfessor">
        <option value="">Select professor...</option>
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
        <input type="time" class="form-input" id="subjStartTime">
      </div>
      <div class="form-group">
        <label class="form-label">End Time</label>
        <input type="time" class="form-input" id="subjEndTime">
      </div>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
      <div class="form-group">
        <label class="form-label">Late Threshold (minutes)</label>
        <input type="number" class="form-input" id="subjLateThreshold" value="15" min="1" max="60">
      </div>
      <div class="form-group">
        <label class="form-label">Allow Guest Students</label>
        <select class="form-select" id="subjAllowGuests">
          <option value="1">Yes</option>
          <option value="0">No</option>
        </select>
      </div>
    </div>
    <div style="display:flex; gap:12px; margin-top:8px;">
      <button class="btn btn-ghost" style="flex:1" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="saveSubjBtn" onclick="saveSubject()">Create Subject</button>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'admin') { localStorage.clear(); window.location.href = '/login'; }

  let allSubjects = [];
  let yearLevelsData = [];
  let professorsData = [];
  let editMode = false;

  async function loadSubjects() {
    try {
      const res = await axios.get('/api/admin/subjects');
      allSubjects = res.data.subjects;
      populateSectionFilter();
      renderTable(allSubjects);
    } catch (e) {
      document.getElementById('subjectsTable').innerHTML =
        '<tr><td colspan="6" style="text-align:center; color:var(--red); padding:24px;">Failed to load subjects.</td></tr>';
    }
  }

  function populateSectionFilter() {
    const sections = [...new Map(allSubjects.map(s => [s.section?.id, s.section])).values()].filter(Boolean);
    const select = document.getElementById('filterSection');
    select.innerHTML = '<option value="">All Sections</option>' +
      sections.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
  }

  function renderTable(subjects) {
    if (subjects.length === 0) {
      document.getElementById('subjectsTable').innerHTML =
        '<tr><td colspan="7" style="text-align:center; padding:40px; color:var(--gray-400);">No subjects found.</td></tr>';
      return;
    }
    document.getElementById('subjectsTable').innerHTML = subjects.map(s => {
      const days = Array.isArray(s.schedule_days) && s.schedule_days.length
        ? s.schedule_days.join(', ')
        : '–';
      const fmt = t => t ? t.slice(0,5) : null;
      const time = fmt(s.schedule_start_time) && fmt(s.schedule_end_time)
        ? `${fmt(s.schedule_start_time)} – ${fmt(s.schedule_end_time)}`
        : '–';
      return `
      <tr>
        <td>
          <div style="font-size:13px; font-weight:600; color:var(--gray-900);">${s.name}</div>
          <div style="font-size:11px; color:var(--gray-500);">${s.year_level?.name || '–'}</div>
        </td>
        <td style="font-size:13px;">${s.section?.name || '–'}</td>
        <td>
          <div style="font-size:13px; font-weight:500;">${s.professor?.name || '–'}</div>
          <div style="font-size:11px; color:var(--gray-500);">${s.professor?.email || ''}</div>
        </td>
        <td>
          <div style="font-size:13px;">${days}</div>
          <div style="font-size:11px; color:var(--gray-500);">${time}</div>
        </td>
        <td style="font-size:13px;">${s.late_threshold_minutes} min</td>
        <td><span class="badge ${s.allow_guests ? 'success' : 'neutral'}">${s.allow_guests ? 'Yes' : 'No'}</span></td>
        <td>
          <div style="display:flex; gap:6px;">
            <button class="btn btn-ghost btn-sm" onclick="openEditSubject(${s.id})">✏️ Edit</button>
            <button class="btn btn-ghost btn-sm" style="color:var(--red);" onclick="deleteSubject(${s.id}, '${s.name.replace(/'/g,"\\'")}')">🗑️</button>
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  function filterSubjects(q) {
    const lower = q.toLowerCase();
    const sectionFilter = document.getElementById('filterSection').value;
    let filtered = allSubjects;
    if (sectionFilter) filtered = filtered.filter(s => s.section?.id == sectionFilter);
    if (q) filtered = filtered.filter(s =>
      s.name?.toLowerCase().includes(lower) ||
      s.professor?.name?.toLowerCase().includes(lower)
    );
    renderTable(filtered);
  }

  async function loadFormData() {
    if (yearLevelsData.length && professorsData.length) return;
    const [ylRes, profRes] = await Promise.all([
      axios.get('/api/admin/year-levels'),
      axios.get('/api/admin/users?role=professor'),
    ]);
    yearLevelsData = ylRes.data.year_levels;
    professorsData = profRes.data.users;
  }

  function populateFormDropdowns(selectedYearLevelId = '', selectedSectionId = '', selectedProfessorId = '') {
    document.getElementById('subjYearLevel').innerHTML =
      '<option value="">Select year level...</option>' +
      yearLevelsData.map(yl => `<option value="${yl.id}" ${yl.id == selectedYearLevelId ? 'selected' : ''}>${yl.name}</option>`).join('');

    document.getElementById('subjProfessor').innerHTML =
      '<option value="">Select professor...</option>' +
      professorsData.map(p => `<option value="${p.id}" ${p.id == selectedProfessorId ? 'selected' : ''}>${p.name}</option>`).join('');

    if (selectedYearLevelId) {
      loadModalSections(selectedYearLevelId, selectedSectionId);
    } else {
      document.getElementById('subjSection').innerHTML = '<option value="">Select year level first...</option>';
    }
  }

  function loadModalSections(yearLevelId, selectedSectionId = '') {
    const yl = yearLevelsData.find(y => y.id == yearLevelId);
    if (!yl || !yl.sections) {
      document.getElementById('subjSection').innerHTML = '<option value="">No sections found</option>';
      return;
    }
    document.getElementById('subjSection').innerHTML =
      '<option value="">Select section...</option>' +
      yl.sections.map(s => `<option value="${s.id}" ${s.id == selectedSectionId ? 'selected' : ''}>${s.name}</option>`).join('');
  }

  function closeModal() {
    document.getElementById('subjectModal').style.display = 'none';
    editMode = false;
    document.getElementById('editSubjectId').value = '';
  }

  function updateDayLabel(day) {
    const label = document.getElementById('dayLabel' + day);
    const checked = label.querySelector('input').checked;
    label.style.borderColor = checked ? 'var(--navy-blue)' : 'var(--gray-200)';
    label.style.background = checked ? 'rgba(30,58,138,0.06)' : '';
    label.style.color = checked ? 'var(--navy-blue)' : '';
    label.style.fontWeight = checked ? '600' : '';
  }

  function getSelectedDays() {
    return ['Mon','Tue','Wed','Thu','Fri','Sat']
      .filter(d => document.querySelector(`#dayLabel${d} input`).checked);
  }

  function setSelectedDays(days) {
    ['Mon','Tue','Wed','Thu','Fri','Sat'].forEach(d => {
      const input = document.querySelector(`#dayLabel${d} input`);
      input.checked = Array.isArray(days) && days.includes(d);
      updateDayLabel(d);
    });
  }

  async function openAddSubject() {
    editMode = false;
    document.getElementById('modalTitle').textContent = '📚 Add New Subject';
    document.getElementById('saveSubjBtn').textContent = 'Create Subject';
    document.getElementById('editSubjectId').value = '';
    document.getElementById('subjName').value = '';
    document.getElementById('subjLateThreshold').value = '15';
    document.getElementById('subjAllowGuests').value = '1';
    document.getElementById('subjStartTime').value = '';
    document.getElementById('subjEndTime').value = '';
    setSelectedDays([]);
    document.getElementById('subjectError').style.display = 'none';

    try { await loadFormData(); populateFormDropdowns(); } 
    catch (e) { console.error('Failed to load form data', e); }

    document.getElementById('subjectModal').style.display = 'flex';
  }

  async function openEditSubject(subjectId) {
    editMode = true;
    const s = allSubjects.find(s => s.id === subjectId);
    if (!s) return;

    document.getElementById('modalTitle').textContent = '✏️ Edit Subject';
    document.getElementById('saveSubjBtn').textContent = 'Save Changes';
    document.getElementById('editSubjectId').value = subjectId;
    document.getElementById('subjName').value = s.name;
    document.getElementById('subjLateThreshold').value = s.late_threshold_minutes;
    document.getElementById('subjAllowGuests').value = s.allow_guests ? '1' : '0';
    document.getElementById('subjStartTime').value = s.schedule_start_time ? s.schedule_start_time.slice(0,5) : '';
    document.getElementById('subjEndTime').value = s.schedule_end_time ? s.schedule_end_time.slice(0,5) : '';
    setSelectedDays(s.schedule_days || []);
    document.getElementById('subjectError').style.display = 'none';

    try { await loadFormData(); populateFormDropdowns(s.year_level_id, s.section_id, s.professor_id); } 
    catch (e) { console.error('Failed to load form data', e); }

    document.getElementById('subjectModal').style.display = 'flex';
  }

  async function saveSubject() {
    const btn = document.getElementById('saveSubjBtn');
    const errEl = document.getElementById('subjectError');
    const name = document.getElementById('subjName').value.trim();
    const yearLevelId = document.getElementById('subjYearLevel').value;
    const sectionId = document.getElementById('subjSection').value;
    const professorId = document.getElementById('subjProfessor').value;
    const lateThreshold = document.getElementById('subjLateThreshold').value;
    const allowGuests = document.getElementById('subjAllowGuests').value;

    if (!name || !yearLevelId || !sectionId || !professorId) {
      errEl.textContent = '❌ Please fill in all required fields.';
      errEl.style.display = 'block';
      return;
    }

    const payload = {
      name,
      year_level_id: yearLevelId,
      section_id: sectionId,
      professor_id: professorId,
      late_threshold_minutes: lateThreshold,
      allow_guests: allowGuests == '1',
      schedule_days: getSelectedDays(),
      schedule_start_time: document.getElementById('subjStartTime').value || null,
      schedule_end_time: document.getElementById('subjEndTime').value || null,
    };

    btn.disabled = true;
    btn.textContent = editMode ? 'Saving...' : 'Creating...';
    errEl.style.display = 'none';

    try {
      if (editMode) {
        const subjectId = document.getElementById('editSubjectId').value;
        await axios.patch(`/api/admin/subjects/${subjectId}`, payload);
        alert('✅ Subject updated successfully!');
      } else {
        await axios.post('/api/admin/subjects', payload);
        alert('✅ Subject created successfully!');
      }
      closeModal();
      await loadSubjects();
    } catch (e) {
      const errors = e.response?.data?.errors;
      errEl.textContent = '❌ ' + (errors ? Object.values(errors)[0][0] : e.response?.data?.message || 'Failed to save subject.');
      errEl.style.display = 'block';
    } finally {
      btn.disabled = false;
      btn.textContent = editMode ? 'Save Changes' : 'Create Subject';
    }
  }

  async function deleteSubject(id, name) {
    if (!confirm(`Delete "${name}"?\n\nThis will remove the subject permanently.`)) return;
    try {
      await axios.delete(`/api/admin/subjects/${id}`);
      await loadSubjects();
    } catch (e) { alert('Failed to delete: ' + (e.response?.data?.message || 'Unknown error')); }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadSubjects();
</script>
@endsection