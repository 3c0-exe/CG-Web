@extends('layouts.app')
@section('title', 'Sections – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item {{ request()->is('admin/users') ? 'active' : '' }}"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item active"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <a href="{{ url('/admin/subjects') }}" class="nav-item {{ request()->is('admin/subjects') ? 'active' : '' }}"><span class="nav-icon">📚</span><span>Master Subjects</span></a>
    <a href="{{ url('/admin/schedules') }}" class="nav-item {{ request()->is('admin/schedules') ? 'active' : '' }}"><span class="nav-icon">📅</span><span>Schedules</span></a>
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
      <h1>Sections</h1>
      <p class="topbar-subtitle">Manage year levels and sections</p>
    </div>
    <div class="topbar-right" style="display:flex; gap:8px;">
      <button class="btn btn-ghost" onclick="exportSetup()">📤 Export Setup</button>
      <button class="btn btn-ghost" onclick="openImportModal()">📥 Import Setup</button>
      <button class="btn btn-primary" onclick="openAddSection()">+ Add Section</button>
    </div>
  </div>

  <div class="content">
    <div id="sectionsGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:20px;">
      <div style="text-align:center; padding:40px; color:var(--gray-400);">Loading...</div>
    </div>

    <div class="section" style="margin-top:32px;">
      <div class="section-header">
        <h2 class="section-title">🚪 Rooms</h2>
        <button class="btn btn-primary btn-sm" onclick="openAddRoom()">+ Add Room</button>
      </div>
      <div class="table-wrapper" style="padding:0;">
        <table>
          <thead>
            <tr>
              <th>Room Name</th>
              <th style="width:140px;">Actions</th>
            </tr>
          </thead>
          <tbody id="roomsTable">
            <tr><td colspan="2" style="text-align:center; padding:32px; color:var(--gray-400);">Loading rooms...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- Add Section Modal -->
<div id="addModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:440px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">🏫 Add New Section</h3>
    <div id="addError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <div class="form-group">
      <label class="form-label">Year Level</label>
      <select class="form-select" id="addYearLevel">
        <option value="">Select year level...</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Section Name</label>
      <input type="text" class="form-input" id="addSectionName" placeholder="e.g. 101C" style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
      <div class="form-hint">Use format: 101A, 201B, 301C, etc.</div>
    </div>
    <div style="display:flex; gap:12px; margin-top:8px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="addBtn" onclick="saveSection()">Add Section</button>
    </div>
  </div>
</div>

<!-- Edit Section Modal -->
<div id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px; overflow-y:auto;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:820px; margin:auto;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">✏️ Edit Section</h3>
    <div id="editError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <input type="hidden" id="editSectionId">

    <!-- Section name fields -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:4px;">
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Year Level</label>
        <select class="form-select" id="editYearLevel">
          <option value="">Select year level...</option>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Section Name</label>
        <input type="text" class="form-input" id="editSectionName" style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
      </div>
    </div>

    <!-- Student panels -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:24px;">

      <!-- Enrolled Students -->
      <div>
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
          <div>
            <div style="font-size:13px; font-weight:700; color:var(--gray-900);">✅ Enrolled Students</div>
            <div id="enrolledSubtitle" style="font-size:11px; color:var(--gray-400); margin-top:1px;"></div>
          </div>
          <span id="enrolledCount" style="background:var(--primary,#3b5bdb); color:#fff; font-size:11px; font-weight:700; padding:2px 9px; border-radius:20px;">0</span>
        </div>
        <input type="text" id="enrolledSearch" placeholder="🔍 Search enrolled…" oninput="filterStudentLists()"
          style="width:100%; box-sizing:border-box; padding:7px 10px; border:1px solid var(--gray-200); border-radius:6px; font-size:12px; margin-bottom:8px; outline:none;">
        <div id="enrolledList" style="border:1px solid var(--gray-200); border-radius:8px; max-height:260px; overflow-y:auto; background:var(--gray-50);">
          <div style="padding:32px; text-align:center; color:var(--gray-400); font-size:13px;">Loading…</div>
        </div>
      </div>

      <!-- Unenrolled Students -->
      <div>
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
          <div>
            <div style="font-size:13px; font-weight:700; color:var(--gray-900);">👤 Unenrolled Students</div>
            <div id="unenrolledSubtitle" style="font-size:11px; color:var(--gray-400); margin-top:1px;">Students from other sections</div>
          </div>
          <span id="unenrolledCount" style="background:var(--gray-300,#dee2e6); color:var(--gray-700,#495057); font-size:11px; font-weight:700; padding:2px 9px; border-radius:20px;">0</span>
        </div>
        <input type="text" id="unenrolledSearch" placeholder="🔍 Search students…" oninput="filterStudentLists()"
          style="width:100%; box-sizing:border-box; padding:7px 10px; border:1px solid var(--gray-200); border-radius:6px; font-size:12px; margin-bottom:8px; outline:none;">
        <div id="unenrolledList" style="border:1px solid var(--gray-200); border-radius:8px; max-height:260px; overflow-y:auto; background:var(--gray-50);">
          <div style="padding:32px; text-align:center; color:var(--gray-400); font-size:13px;">Loading…</div>
        </div>
      </div>
    </div>

    <div id="enrollMsg" style="display:none; font-size:12px; padding:8px 12px; border-radius:6px; margin-top:12px;"></div>

    <div style="display:flex; gap:12px; margin-top:20px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="editBtn" onclick="updateSection()">Save Changes</button>
    </div>
  </div>
</div>

<!-- Add Room Modal -->
<div id="addRoomModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:400px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">🚪 Add Room</h3>
    <div id="addRoomError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <div class="form-group">
      <label class="form-label">Room Name</label>
      <input type="text" class="form-input" id="addRoomName" placeholder="e.g. Room 101">
    </div>
    <div style="display:flex; gap:12px; margin-top:8px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('addRoomModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="addRoomBtn" onclick="saveRoom()">Add Room</button>
    </div>
  </div>
</div>

<!-- Edit Room Modal -->
<div id="editRoomModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:400px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">✏️ Edit Room</h3>
    <div id="editRoomError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <input type="hidden" id="editRoomId">
    <div class="form-group">
      <label class="form-label">Room Name</label>
      <input type="text" class="form-input" id="editRoomName">
    </div>
    <div style="display:flex; gap:12px; margin-top:8px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('editRoomModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="editRoomBtn" onclick="updateRoom()">Save Changes</button>
    </div>
  </div>
</div>

<!-- Import Setup Modal -->
<div id="importModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:400px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">📥 Import Setup Data</h3>
    <p style="font-size:13px; color:var(--gray-500); margin-bottom:16px;">Upload a previously exported ClassGuard setup JSON file to restore Year Levels, Sections, Subjects, Schedules, and Users.</p>
    
    <div id="importMsg" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    
    <div class="form-group">
      <input type="file" id="importFile" accept=".json" class="form-input" style="padding:10px;">
    </div>
    
    <div style="display:flex; gap:12px; margin-top:24px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('importModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="importBtn" onclick="importSetup()">Import Data</button>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'admin') { localStorage.clear(); window.location.href = '/login'; }

  let yearLevels = [];

  async function loadSections() {
    try {
      const res = await axios.get('/api/admin/year-levels');
      yearLevels = res.data.year_levels;
      renderGrid();
    } catch (e) {
      document.getElementById('sectionsGrid').innerHTML = '<div style="color:var(--red); padding:24px;">Failed to load sections.</div>';
    }
  }

  function renderGrid() {
    if (yearLevels.length === 0) {
      document.getElementById('sectionsGrid').innerHTML = '<div style="text-align:center; padding:40px; color:var(--gray-400);">No data found.</div>';
      return;
    }
    document.getElementById('sectionsGrid').innerHTML = yearLevels.map(yl => `
      <div class="section">
        <div class="section-header">
          <h2 class="section-title">📚 ${yl.name}</h2>
          <span class="badge info">${yl.sections?.length || 0} sections</span>
        </div>
        <div style="display:flex; flex-direction:column; gap:8px;">
          ${(yl.sections && yl.sections.length > 0)
            ? yl.sections.map(s => `
              <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--gray-50); border-radius:6px; border:1px solid var(--gray-200);">
                <div style="display:flex; align-items:center; gap:10px;">
                  <span style="font-size:16px;">🏫</span>
                  <span style="font-size:14px; font-weight:600; color:var(--gray-900);">${s.name}</span>
                </div>
                <div style="display:flex; gap:6px;">
                  <button class="btn btn-ghost btn-sm" onclick="openEditSection(${s.id}, ${yl.id}, '${s.name}')">✏️ Edit</button>
                  <button class="btn btn-ghost btn-sm" style="color:var(--red);" onclick="deleteSection(${s.id}, '${s.name}')">🗑️</button>
                </div>
              </div>`).join('')
            : '<div style="padding:16px; color:var(--gray-400); font-size:13px; text-align:center;">No sections yet.</div>'
          }
          <button class="btn btn-ghost btn-sm" style="margin-top:4px; justify-content:center;" onclick="openAddSectionForYear(${yl.id})">+ Add Section</button>
        </div>
      </div>
    `).join('');
  }

  function populateYearLevelDropdowns() {
    const options = '<option value="">Select year level...</option>' +
      yearLevels.map(yl => `<option value="${yl.id}">${yl.name}</option>`).join('');
    document.getElementById('addYearLevel').innerHTML = options;
    document.getElementById('editYearLevel').innerHTML = options;
  }

  function openAddSection() {
    populateYearLevelDropdowns();
    document.getElementById('addYearLevel').value = '';
    document.getElementById('addSectionName').value = '';
    document.getElementById('addError').style.display = 'none';
    document.getElementById('addModal').style.display = 'flex';
  }

  function openAddSectionForYear(yearLevelId) {
    populateYearLevelDropdowns();
    document.getElementById('addYearLevel').value = yearLevelId;
    document.getElementById('addSectionName').value = '';
    document.getElementById('addError').style.display = 'none';
    document.getElementById('addModal').style.display = 'flex';
  }

  async function saveSection() {
    const btn = document.getElementById('addBtn');
    const errEl = document.getElementById('addError');
    const yearLevelId = document.getElementById('addYearLevel').value;
    const name = document.getElementById('addSectionName').value.trim();
    if (!yearLevelId || !name) {
      errEl.textContent = '❌ Please fill in all fields.';
      errEl.style.display = 'block';
      return;
    }
    btn.disabled = true; btn.textContent = 'Adding...'; errEl.style.display = 'none';
    try {
      await axios.post('/api/admin/sections', { year_level_id: yearLevelId, name });
      document.getElementById('addModal').style.display = 'none';
      await loadSections();
    } catch (e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to add section.');
      errEl.style.display = 'block';
    } finally { btn.disabled = false; btn.textContent = 'Add Section'; }
  }

  // ─── Student enrollment state ────────────────────────────────────────────
  let _editSectionName = '';
  let _allStudents     = [];   // [{id, name, student_id, section_id, section_name}]
  let _enrolledIds     = new Set();

  async function openEditSection(id, yearLevelId, name) {
    _editSectionName = name;
    populateYearLevelDropdowns();
    document.getElementById('editSectionId').value = id;
    document.getElementById('editYearLevel').value = yearLevelId;
    document.getElementById('editSectionName').value = name;
    document.getElementById('editError').style.display = 'none';
    document.getElementById('enrollMsg').style.display = 'none';
    document.getElementById('enrolledSubtitle').textContent = 'Section ' + name;
    document.getElementById('editModal').style.display = 'flex';

    // Reset lists while loading
    document.getElementById('enrolledList').innerHTML   = '<div style="padding:32px; text-align:center; color:var(--gray-400); font-size:13px;">Loading…</div>';
    document.getElementById('unenrolledList').innerHTML = '<div style="padding:32px; text-align:center; color:var(--gray-400); font-size:13px;">Loading…</div>';
    document.getElementById('enrolledSearch').value   = '';
    document.getElementById('unenrolledSearch').value = '';

    try {
      // Fetch all students + the section's current students in parallel
      const [allRes, secRes] = await Promise.all([
        axios.get('/api/admin/users?role=student'),
        axios.get(`/api/admin/sections/${id}/students`)
      ]);

      // Normalise — adjust field names if your API differs
      _allStudents = (allRes.data.users || []).map(s => ({
        id:           s.id,
        name:         s.name || (s.first_name + ' ' + s.last_name),
        student_id:   s.student_id_number || s.student_id || s.school_id || '',
        section_id:   s.section_id || null,
        section_name: s.section_name || s.section?.name || null,
        sections:     s.sections || [],
      }));

      const enrolled = secRes.data.students || secRes.data || [];
      _enrolledIds = new Set(enrolled.map(s => s.id));

      renderStudentLists();
    } catch (e) {
      const msg = '⚠️ Could not load students: ' + (e.response?.data?.message || e.message);
      document.getElementById('enrolledList').innerHTML   = `<div style="padding:20px; text-align:center; color:var(--red); font-size:12px;">${msg}</div>`;
      document.getElementById('unenrolledList').innerHTML = `<div style="padding:20px; text-align:center; color:var(--red); font-size:12px;">${msg}</div>`;
    }
  }

  function filterStudentLists() { renderStudentLists(); }

  function renderStudentLists() {
    const eq = document.getElementById('enrolledSearch').value.toLowerCase();
    const uq = document.getElementById('unenrolledSearch').value.toLowerCase();
    const sectionId = document.getElementById('editSectionId').value;

    const enrolled   = _allStudents.filter(s =>  _enrolledIds.has(s.id));
    const unenrolled = _allStudents.filter(s => !_enrolledIds.has(s.id));

    document.getElementById('enrolledCount').textContent   = enrolled.length;
    document.getElementById('unenrolledCount').textContent = unenrolled.length;

    // Enrolled list
    const efil = enrolled.filter(s => !eq || s.name.toLowerCase().includes(eq) || s.student_id.toLowerCase().includes(eq));
    document.getElementById('enrolledList').innerHTML = efil.length
      ? efil.map(s => studentRow(s, true, sectionId)).join('')
      : '<div style="padding:24px; text-align:center; color:var(--gray-400); font-size:13px;">No enrolled students.</div>';

    // Unenrolled list
    const ufil = unenrolled.filter(s => !uq || s.name.toLowerCase().includes(uq) || s.student_id.toLowerCase().includes(uq));
    document.getElementById('unenrolledList').innerHTML = ufil.length
      ? ufil.map(s => studentRow(s, false, sectionId)).join('')
      : '<div style="padding:24px; text-align:center; color:var(--gray-400); font-size:13px;">No unenrolled students.</div>';
  }

  function studentRow(s, isEnrolled, sectionId) {
    const sectionLabels = !isEnrolled
      ? [
          ...(s.section_name ? [s.section_name] : []),
          ...(s.sections || []).map(sec => sec.name).filter(n => n && n !== s.section_name)
        ]
      : [];
    const badge = sectionLabels.length
      ? sectionLabels.map(n =>
          `<span style="font-size:10px; padding:2px 7px; border-radius:10px; background:rgba(59,91,219,0.08); color:var(--primary,#3b5bdb); font-weight:600; white-space:nowrap; margin-right:2px;">${n}</span>`
        ).join('')
      : '';
    const btn = isEnrolled
      ? `<button onclick="unenrollStudent(${s.id})" title="Remove from section"
           style="padding:4px 10px; font-size:11px; border:1px solid rgba(239,68,68,0.4); background:rgba(239,68,68,0.06); color:var(--red,#dc2626); border-radius:5px; cursor:pointer; white-space:nowrap;">
           ✕ Remove
         </button>`
      : `<button onclick="enrollStudent(${s.id}, '${sectionId}')" title="Enroll in this section"
           style="padding:4px 10px; font-size:11px; border:1px solid rgba(59,91,219,0.35); background:rgba(59,91,219,0.07); color:var(--primary,#3b5bdb); border-radius:5px; cursor:pointer; white-space:nowrap;">
           + Enroll
         </button>`;

    return `
      <div style="display:flex; align-items:center; gap:8px; padding:9px 12px; border-bottom:1px solid var(--gray-100); background:var(--white);">
        <div style="width:30px; height:30px; border-radius:50%; background:var(--primary,#3b5bdb); color:#fff; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          ${s.name.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase()}
        </div>
        <div style="flex:1; min-width:0;">
          <div style="font-size:13px; font-weight:600; color:var(--gray-900); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${s.name}</div>
          <div style="font-size:11px; color:var(--gray-400);">${s.student_id || 'No ID'}</div>
        </div>
        ${badge}
        ${btn}
      </div>`;
  }

  async function enrollStudent(studentId, sectionId) {
    const msgEl = document.getElementById('enrollMsg');
    try {
      await axios.post(`/api/admin/sections/${sectionId}/students`, { student_id: studentId });
      _enrolledIds.add(studentId);
      renderStudentLists();
      showEnrollMsg('✅ Student enrolled successfully.', 'success');
    } catch (e) {
      showEnrollMsg('❌ ' + (e.response?.data?.message || 'Failed to enroll student.'), 'error');
    }
  }

  async function unenrollStudent(studentId) {
    const sectionId = document.getElementById('editSectionId').value;
    const msgEl = document.getElementById('enrollMsg');
    try {
      await axios.delete(`/api/admin/sections/${sectionId}/students/${studentId}`);
      _enrolledIds.delete(studentId);
      renderStudentLists();
      showEnrollMsg('✅ Student removed from section.', 'success');
    } catch (e) {
      showEnrollMsg('❌ ' + (e.response?.data?.message || 'Failed to remove student.'), 'error');
    }
  }

  function showEnrollMsg(text, type) {
    const el = document.getElementById('enrollMsg');
    el.textContent = text;
    el.style.display = 'block';
    el.style.background = type === 'success' ? 'rgba(34,197,94,0.08)' : 'rgba(239,68,68,0.08)';
    el.style.border     = type === 'success' ? '1px solid rgba(34,197,94,0.25)' : '1px solid rgba(239,68,68,0.2)';
    el.style.color      = type === 'success' ? '#16a34a' : 'var(--red)';
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.style.display = 'none'; }, 3500);
  }

  async function updateSection() {
    const btn = document.getElementById('editBtn');
    const errEl = document.getElementById('editError');
    const id = document.getElementById('editSectionId').value;
    const yearLevelId = document.getElementById('editYearLevel').value;
    const name = document.getElementById('editSectionName').value.trim();
    if (!yearLevelId || !name) {
      errEl.textContent = '❌ Please fill in all fields.';
      errEl.style.display = 'block';
      return;
    }
    btn.disabled = true; btn.textContent = 'Saving...'; errEl.style.display = 'none';
    try {
      await axios.patch(`/api/admin/sections/${id}`, { year_level_id: yearLevelId, name });
      document.getElementById('editModal').style.display = 'none';
      await loadSections();
    } catch (e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to update section.');
      errEl.style.display = 'block';
    } finally { btn.disabled = false; btn.textContent = 'Save Changes'; }
  }

  async function deleteSection(id, name) {
    if (!confirm(`Delete section "${name}"? This cannot be undone.`)) return;
    try {
      await axios.delete(`/api/admin/sections/${id}`);
      await loadSections();
    } catch (e) {
      alert('Failed to delete: ' + (e.response?.data?.message || 'Unknown error'));
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  // ─── Rooms ────────────────────────────────────────────────────────────────

  let allRooms = [];

  async function loadRooms() {
    try {
      const res = await axios.get('/api/admin/rooms');
      allRooms = res.data.rooms;
      renderRooms();
    } catch (e) {
      document.getElementById('roomsTable').innerHTML =
        '<tr><td colspan="2" style="text-align:center; color:var(--red); padding:24px;">Failed to load rooms.</td></tr>';
    }
  }

  function renderRooms() {
    if (allRooms.length === 0) {
      document.getElementById('roomsTable').innerHTML =
        '<tr><td colspan="2" style="text-align:center; padding:32px; color:var(--gray-400);">No rooms yet.</td></tr>';
      return;
    }
    document.getElementById('roomsTable').innerHTML = allRooms.map(r => `
      <tr>
        <td style="font-size:14px; font-weight:500;">🚪 ${r.name}</td>
        <td>
          <div style="display:flex; gap:6px;">
            <button class="btn btn-ghost btn-sm" onclick="openEditRoom(${r.id}, '${r.name.replace(/'/g,"\\'")}')">✏️ Edit</button>
            <button class="btn btn-ghost btn-sm" style="color:var(--red);" onclick="deleteRoom(${r.id}, '${r.name.replace(/'/g,"\\'")}')">🗑️</button>
          </div>
        </td>
      </tr>`).join('');
  }

  function openAddRoom() {
    document.getElementById('addRoomName').value = '';
    document.getElementById('addRoomError').style.display = 'none';
    document.getElementById('addRoomModal').style.display = 'flex';
  }

  async function saveRoom() {
    const btn = document.getElementById('addRoomBtn');
    const errEl = document.getElementById('addRoomError');
    const name = document.getElementById('addRoomName').value.trim();
    if (!name) {
      errEl.textContent = '❌ Please enter a room name.';
      errEl.style.display = 'block';
      return;
    }
    btn.disabled = true; btn.textContent = 'Adding...'; errEl.style.display = 'none';
    try {
      await axios.post('/api/admin/rooms', { name });
      document.getElementById('addRoomModal').style.display = 'none';
      await loadRooms();
    } catch (e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to add room.');
      errEl.style.display = 'block';
    } finally { btn.disabled = false; btn.textContent = 'Add Room'; }
  }

  function openEditRoom(id, name) {
    document.getElementById('editRoomId').value = id;
    document.getElementById('editRoomName').value = name;
    document.getElementById('editRoomError').style.display = 'none';
    document.getElementById('editRoomModal').style.display = 'flex';
  }

  async function updateRoom() {
    const btn = document.getElementById('editRoomBtn');
    const errEl = document.getElementById('editRoomError');
    const id = document.getElementById('editRoomId').value;
    const name = document.getElementById('editRoomName').value.trim();
    if (!name) {
      errEl.textContent = '❌ Please enter a room name.';
      errEl.style.display = 'block';
      return;
    }
    btn.disabled = true; btn.textContent = 'Saving...'; errEl.style.display = 'none';
    try {
      await axios.patch(`/api/admin/rooms/${id}`, { name });
      document.getElementById('editRoomModal').style.display = 'none';
      await loadRooms();
    } catch (e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to update room.');
      errEl.style.display = 'block';
    } finally { btn.disabled = false; btn.textContent = 'Save Changes'; }
  }

  async function deleteRoom(id, name) {
    if (!confirm(`Delete room "${name}"? This cannot be undone.`)) return;
    try {
      await axios.delete(`/api/admin/rooms/${id}`);
      await loadRooms();
    } catch (e) {
      alert('Failed to delete: ' + (e.response?.data?.message || 'Unknown error'));
    }
  }

  // ─── Export / Import ──────────────────────────────────────────────────────

  async function exportSetup() {
    try {
      const res = await axios.get('/api/admin/export/setup');
      const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(res.data, null, 2));
      const downloadAnchorNode = document.createElement('a');
      downloadAnchorNode.setAttribute("href",     dataStr);
      downloadAnchorNode.setAttribute("download", "classguard-setup-export.json");
      document.body.appendChild(downloadAnchorNode);
      downloadAnchorNode.click();
      downloadAnchorNode.remove();
    } catch (e) {
      alert("Failed to export setup data.");
    }
  }

  function openImportModal() {
    document.getElementById('importFile').value = '';
    document.getElementById('importMsg').style.display = 'none';
    document.getElementById('importModal').style.display = 'flex';
  }

  async function importSetup() {
    const fileInput = document.getElementById('importFile');
    const msgEl = document.getElementById('importMsg');
    const btn = document.getElementById('importBtn');

    if (!fileInput.files || fileInput.files.length === 0) {
      msgEl.textContent = '❌ Please select a JSON file first.';
      msgEl.style.display = 'block';
      msgEl.style.background = 'rgba(239,68,68,0.08)';
      msgEl.style.borderColor = 'rgba(239,68,68,0.2)';
      msgEl.style.color = 'var(--red)';
      return;
    }

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);

    btn.disabled = true;
    btn.textContent = 'Importing...';
    msgEl.style.display = 'none';

    try {
      const res = await axios.post('/api/admin/import/setup', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      msgEl.textContent = '✅ ' + res.data.message;
      msgEl.style.display = 'block';
      msgEl.style.background = 'rgba(34,197,94,0.08)';
      msgEl.style.borderColor = 'rgba(34,197,94,0.25)';
      msgEl.style.color = '#16a34a';
      
      setTimeout(() => {
        document.getElementById('importModal').style.display = 'none';
        loadSections();
        loadRooms();
      }, 2000);
      
    } catch (e) {
      msgEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to import data.');
      msgEl.style.display = 'block';
      msgEl.style.background = 'rgba(239,68,68,0.08)';
      msgEl.style.borderColor = 'rgba(239,68,68,0.2)';
      msgEl.style.color = 'var(--red)';
    } finally {
      btn.disabled = false;
      btn.textContent = 'Import Data';
    }
  }

  loadSections();
  loadRooms();
</script>
@endsection