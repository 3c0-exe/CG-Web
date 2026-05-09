@extends('layouts.app')
@section('title', 'Devices – ClassGuard')

@section('content')
<aside class="sidebar">
<div class="logo"><img src="{{ asset('images/blue-gold-cg-bgremoved.png') }}" alt="ClassGuard" style="height:40px; width:auto;"><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/admin/dashboard') }}" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/admin/users') }}" class="nav-item {{ request()->is('admin/users') ? 'active' : '' }}"><span class="nav-icon">👥</span><span>User Management</span></a>
    <a href="{{ url('/admin/sections') }}" class="nav-item {{ request()->is('admin/sections') ? 'active' : '' }}"><span class="nav-icon">🏫</span><span>Sections</span></a>
    <a href="{{ url('/admin/subjects') }}" class="nav-item {{ request()->is('admin/subjects') ? 'active' : '' }}"><span class="nav-icon">📚</span><span>Master Subjects</span></a>
    <a href="{{ url('/admin/schedules') }}" class="nav-item {{ request()->is('admin/schedules') ? 'active' : '' }}"><span class="nav-icon">📅</span><span>Schedules</span></a>
    <a href="{{ url('/admin/prospectus') }}" class="nav-item {{ request()->is('admin/prospectus') ? 'active' : '' }}"><span class="nav-icon">📋</span><span>Prospectus</span></a>
    <a href="{{ url('/admin/rooms') }}" class="nav-item {{ request()->is('admin/rooms') ? 'active' : '' }}"><span class="nav-icon">🏠</span><span>Rooms</span></a>
    <a href="{{ url('/admin/devices') }}" class="nav-item active"><span class="nav-icon">📡</span><span>Devices</span></a>
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
      <h1>ESP32 Devices</h1>
      <p class="topbar-subtitle">Manage and authorize RFID reader hardware</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openAddDevice()">+ Register Device</button>
    </div>
  </div>

  <div class="content">
    <div class="section" style="padding:16px 24px; margin-bottom:0; border-bottom:none; border-radius:8px 8px 0 0;">
      <div style="font-size:13px; color:var(--gray-500);">
        Only <b>Active</b> devices will be allowed to submit attendance records to the server.
      </div>
    </div>

    <div class="section" style="padding:0; overflow:hidden; border-radius:0 0 8px 8px;">
      <div class="table-wrapper" style="padding:0;">
        <table>
          <thead>
            <tr>
              <th>Device ID (MAC Address)</th>
              <th>Name / Location</th>
              <th>Status</th>
              <th>Last Seen</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="devicesTable">
            <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--gray-400);">Loading devices...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<div id="deviceModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:440px;">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">📡 Register New Device</h3>
    <div id="deviceError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    
    <div class="form-group">
      <label class="form-label">Device ID (MAC Address)</label>
      <input type="text" class="form-input" id="devId" placeholder="e.g. 24:6F:28:1A:3B:5C">
      <div style="font-size:11px; color:var(--gray-400); margin-top:4px;">Must exactly match the X-Device-ID header sent by the ESP32.</div>
    </div>
    <div class="form-group">
      <label class="form-label">Name / Location</label>
      <input type="text" class="form-input" id="devName" placeholder="e.g. Room 101 Reader">
    </div>
    
    <div style="display:flex; gap:12px; margin-top:24px;">
      <button class="btn btn-ghost" style="flex:1" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="saveDevBtn" onclick="saveDevice()">Register Device</button>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'admin') { localStorage.clear(); window.location.href = '/login'; }

  let allDevices = [];

  async function loadData() {
    try {
      const res = await axios.get('/api/admin/devices');
      allDevices = res.data.devices;
      renderDevices();
    } catch (e) {
      document.getElementById('devicesTable').innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--red); padding:24px;">Failed to load data.</td></tr>';
    }
  }

  function renderDevices() {
    const tbody = document.getElementById('devicesTable');
    if (!allDevices.length) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:40px; color:var(--gray-400);">No devices registered yet.</td></tr>';
      return;
    }
    
    tbody.innerHTML = allDevices.map(d => {
      const statusBadge = d.is_active 
        ? '<span style="font-size:12px; background:rgba(34,197,94,0.1); color:#16a34a; padding:4px 8px; border-radius:12px; font-weight:600;">Active</span>'
        : '<span style="font-size:12px; background:rgba(239,68,68,0.1); color:var(--red); padding:4px 8px; border-radius:12px; font-weight:600;">Inactive</span>';
        
      const lastSeen = d.last_seen_at 
        ? new Date(d.last_seen_at).toLocaleString() 
        : '<span style="color:var(--gray-400);">Never</span>';
        
      const toggleLabel = d.is_active ? 'Deauthorize' : 'Authorize';
        
      return `
        <tr>
          <td><div style="font-family:monospace; font-weight:600; color:var(--gray-900);">${d.device_id}</div></td>
          <td>${d.name || '<span style="color:var(--gray-400);">Unnamed Device</span>'}</td>
          <td>${statusBadge}</td>
          <td style="font-size:12px;">${lastSeen}</td>
          <td>
            <div style="display:flex; gap:6px;">
              <button class="btn btn-ghost btn-sm" onclick="toggleDevice(${d.id}, ${!d.is_active})">${toggleLabel}</button>
              <button class="btn btn-ghost btn-sm" style="color:var(--red);" onclick="deleteDevice(${d.id})">🗑️</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function openAddDevice() {
    document.getElementById('devId').value = '';
    document.getElementById('devName').value = '';
    document.getElementById('deviceError').style.display = 'none';
    document.getElementById('deviceModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('deviceModal').style.display = 'none';
  }

  async function saveDevice() {
    const devId = document.getElementById('devId').value.trim();
    const name = document.getElementById('devName').value.trim();
    
    const errEl = document.getElementById('deviceError');
    if (!devId) {
      errEl.textContent = '❌ Please enter the Device ID.';
      errEl.style.display = 'block';
      return;
    }
    
    const btn = document.getElementById('saveDevBtn');
    btn.disabled = true; btn.textContent = 'Registering...';
    errEl.style.display = 'none';
    
    try {
      await axios.post('/api/admin/devices', { device_id: devId, name });
      closeModal();
      await loadData();
    } catch(e) {
      errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to register device.');
      errEl.style.display = 'block';
    } finally {
      btn.disabled = false; btn.textContent = 'Register Device';
    }
  }

  async function toggleDevice(id, makeActive) {
    try {
      await axios.patch(`/api/admin/devices/${id}/toggle`, { is_active: makeActive });
      await loadData();
    } catch(e) {
      alert(e.response?.data?.message || 'Failed to toggle device');
    }
  }

  async function deleteDevice(id) {
    if(!confirm('Are you sure you want to delete this device? It will lose access permanently unless re-registered.')) return;
    try {
      await axios.delete(`/api/admin/devices/${id}`);
      await loadData();
    } catch(e) {
      alert(e.response?.data?.message || 'Failed to delete');
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadData();
</script>
@endsection
