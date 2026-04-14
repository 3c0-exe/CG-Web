@extends('layouts.app')
@section('title', 'Sections – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item active"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <a href="{{ url('/admin/subjects') }}" class="nav-item"><span class="nav-icon">📚</span><span>Subjects</span></a>
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
      <h1>Sections</h1>
      <p class="topbar-subtitle">Manage year levels and sections</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-ghost" onclick="openAddRoom()">🚪 Add Room</button>
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
<div id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:440px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">✏️ Edit Section</h3>
    <div id="editError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <input type="hidden" id="editSectionId">
    <div class="form-group">
      <label class="form-label">Year Level</label>
      <select class="form-select" id="editYearLevel">
        <option value="">Select year level...</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Section Name</label>
      <input type="text" class="form-input" id="editSectionName" style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
    </div>
    <div style="display:flex; gap:12px; margin-top:8px;">
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

  function openEditSection(id, yearLevelId, name) {
    populateYearLevelDropdowns();
    document.getElementById('editSectionId').value = id;
    document.getElementById('editYearLevel').value = yearLevelId;
    document.getElementById('editSectionName').value = name;
    document.getElementById('editError').style.display = 'none';
    document.getElementById('editModal').style.display = 'flex';
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

  loadSections();
  loadRooms();
</script>
@endsection