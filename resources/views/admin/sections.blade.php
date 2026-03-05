@extends('layouts.app')
@section('title', 'Sections – ClassGuard')

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item active"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <div class="nav-divider"></div>
    <div class="nav-section">System</div>
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
      <button class="btn btn-primary" onclick="openAddSection()">+ Add Section</button>
    </div>
  </div>

  <div class="content">
    <div id="sectionsGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:20px;">
      <div style="text-align:center; padding:40px; color:var(--gray-400);">Loading...</div>
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

  loadSections();
</script>
@endsection
