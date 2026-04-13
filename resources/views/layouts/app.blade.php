<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'ClassGuard')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root {
      --navy-dark: #0A1628; --navy-blue: #1E3A8A; --gold: #FCD34D;
      --white: #FFFFFF; --gray-50: #F9FAFB; --gray-100: #F3F4F6;
      --gray-200: #E5E7EB; --gray-300: #D1D5DB; --gray-400: #9CA3AF;
      --gray-500: #6B7280; --gray-600: #4B5563; --gray-900: #111827;
      --green: #10B981; --red: #EF4444; --amber: #D97706;
    }
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--white); color: var(--gray-900); line-height: 1.6; }
    .sidebar { position: fixed; left: 0; top: 0; width: 240px; height: 100vh; background: var(--navy-dark); border-right: 1px solid rgba(252,211,77,0.1); padding: 24px 16px; display: flex; flex-direction: column; z-index: 100; }
    .logo { display: flex; align-items: center; gap: 12px; padding: 8px 12px; margin-bottom: 32px; }
    .logo-icon { width: 32px; height: 32px; background: var(--gold); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; color: var(--navy-dark); flex-shrink: 0; }
    .logo-text { font-size: 16px; font-weight: 600; color: var(--white); letter-spacing: -0.01em; }
    .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; margin-bottom: 4px; color: var(--gray-400); text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.15s ease; cursor: pointer; }
    .nav-item:hover { background: rgba(252,211,77,0.05); color: var(--gold); }
    .nav-item.active { background: rgba(252,211,77,0.1); color: var(--gold); }
    .nav-icon { font-size: 16px; width: 20px; text-align: center; }
    .nav-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 16px 0; }
    .nav-section { font-size: 11px; font-weight: 600; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.05em; padding: 8px 12px; margin-top: 8px; }
    .user-section { margin-top: auto; padding: 12px; border-top: 1px solid rgba(255,255,255,0.1); }
    .user-info { display: flex; align-items: center; gap: 10px; }
    .user-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--gold); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 13px; color: var(--navy-dark); flex-shrink: 0; }
    .user-name { font-size: 13px; font-weight: 600; color: var(--white); line-height: 1.2; }
    .user-role { font-size: 11px; color: var(--gray-400); }
    .main { margin-left: 240px; min-height: 100vh; }
    .topbar { background: var(--white); border-bottom: 1px solid var(--gray-200); padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
    .topbar-left h1 { font-size: 20px; font-weight: 600; color: var(--gray-900); margin-bottom: 2px; }
    .topbar-subtitle { font-size: 13px; color: var(--gray-500); }
    .topbar-right { display: flex; align-items: center; gap: 12px; }
    .icon-btn { width: 36px; height: 36px; border-radius: 6px; border: 1px solid var(--gray-200); background: var(--white); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease; font-size: 16px; color: var(--gray-600); }
    .icon-btn:hover { background: var(--gray-50); border-color: var(--gold); }
    .content { padding: 32px; max-width: 1400px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 32px; }
    .stat-card { background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; padding: 20px; transition: all 0.15s ease; }
    .stat-card:hover { border-color: var(--gold); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .stat-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .stat-icon { width: 36px; height: 36px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .stat-icon.gold { background: rgba(252,211,77,0.15); color: var(--gold); }
    .stat-icon.blue { background: rgba(30,58,138,0.1); color: var(--navy-blue); }
    .stat-icon.green { background: rgba(16,185,129,0.1); color: var(--green); }
    .stat-icon.red { background: rgba(239,68,68,0.1); color: var(--red); }
    .stat-label { font-size: 13px; color: var(--gray-500); font-weight: 500; margin-bottom: 4px; }
    .stat-value { font-size: 28px; font-weight: 700; color: var(--gray-900); line-height: 1; margin-bottom: 8px; }
    .stat-change { font-size: 12px; font-weight: 500; }
    .stat-change.positive { color: var(--green); }
    .stat-change.negative { color: var(--red); }
    .stat-change.info { color: var(--navy-blue); }
    .section { background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; padding: 24px; margin-bottom: 24px; }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .section-title { font-size: 16px; font-weight: 600; color: var(--gray-900); }
    .btn { padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.15s ease; border: none; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
    .btn-primary { background: var(--navy-blue); color: var(--white); }
    .btn-primary:hover { background: #1a337a; }
    .btn-danger { background: var(--red); color: var(--white); }
    .btn-success { background: var(--green); color: var(--white); }
    .btn-ghost { background: transparent; color: var(--gray-600); border: 1px solid var(--gray-200); }
    .btn-ghost:hover { background: var(--gray-50); }
    .btn-sm { padding: 6px 12px; font-size: 12px; }
    .badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; display: inline-flex; align-items: center; gap: 4px; }
    .badge.success { background: rgba(16,185,129,0.1); color: var(--green); }
    .badge.warning { background: rgba(252,211,77,0.15); color: var(--amber); }
    .badge.info { background: rgba(30,58,138,0.1); color: var(--navy-blue); }
    .badge.danger { background: rgba(239,68,68,0.1); color: var(--red); }
    .badge.neutral { background: var(--gray-100); color: var(--gray-600); }
    .badge.gold { background: rgba(252,211,77,0.2); color: #b45309; }
    .list-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--gray-100); }
    .list-item:last-child { border-bottom: none; }
    .list-left { display: flex; align-items: center; gap: 12px; }
    .item-icon { width: 40px; height: 40px; border-radius: 6px; background: var(--gray-50); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .item-details h4 { font-size: 14px; font-weight: 600; color: var(--gray-900); margin-bottom: 2px; }
    .item-details p { font-size: 12px; color: var(--gray-500); }
    .table-wrapper { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead th { padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--gray-200); background: var(--gray-50); }
    tbody td { padding: 14px 16px; font-size: 14px; color: var(--gray-900); border-bottom: 1px solid var(--gray-100); }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--gray-50); }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
    .form-input { width: 100%; padding: 10px 14px; border: 1px solid var(--gray-200); border-radius: 6px; font-size: 14px; color: var(--gray-900); background: var(--white); transition: border-color 0.15s ease; outline: none; }
    .form-input:focus { border-color: var(--navy-blue); box-shadow: 0 0 0 3px rgba(30,58,138,0.08); }
    .form-select { width: 100%; padding: 10px 14px; border: 1px solid var(--gray-200); border-radius: 6px; font-size: 14px; color: var(--gray-900); background: var(--white); outline: none; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239CA3AF' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px; }
    .form-hint { font-size: 12px; color: var(--gray-500); margin-top: 4px; }
    .progress-bar { height: 6px; background: var(--gray-100); border-radius: 99px; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 99px; background: var(--navy-blue); transition: width 0.3s ease; }
    .progress-fill.gold { background: var(--gold); }
    .progress-fill.green { background: var(--green); }
    .progress-fill.red { background: var(--red); }
    .search-wrap { position: relative; }
    .search-wrap input { padding-left: 36px; }
    .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: 14px; }
    .notif-dot { position: relative; }
    .notif-dot::after { content: ''; position: absolute; top: 6px; right: 6px; width: 7px; height: 7px; border-radius: 50%; background: var(--red); border: 2px solid var(--white); }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    .live-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); display: inline-block; animation: pulse 1.5s ease-in-out infinite; }
    .live-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(16,185,129,0.1); color: var(--green); font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 99px; letter-spacing: 0.03em; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media (max-width: 1024px) { .grid-2 { grid-template-columns: 1fr; } }
    @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .main { margin-left: 0; } .content { padding: 20px; } .topbar { padding: 14px 20px; } }
  </style>
  @yield('extra_styles')
</head>
<body>
  @yield('content')
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const token = localStorage.getItem('token');
    if (token) axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
  </script>
@yield('scripts')

  <!-- Change Password Modal (shared across all dashboards) -->
  <div id="changePasswordModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:300; align-items:center; justify-content:center; padding:24px;">
    <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:420px;">
      <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">🔒 Change Password</h3>
      <div id="pwError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
      <div id="pwSuccess" style="display:none; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2); color:var(--green); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
      <div class="form-group">
        <label class="form-label">Current Password</label>
        <input type="password" class="form-input" id="pwCurrent" placeholder="Enter current password">
      </div>
      <div class="form-group">
        <label class="form-label">New Password</label>
        <input type="password" class="form-input" id="pwNew" placeholder="Min. 8 characters">
      </div>
      <div class="form-group">
        <label class="form-label">Confirm New Password</label>
        <input type="password" class="form-input" id="pwConfirm" placeholder="Repeat new password">
      </div>
      <div style="display:flex; gap:12px; margin-top:8px;">
        <button class="btn btn-ghost" style="flex:1" onclick="closePasswordModal()">Cancel</button>
        <button class="btn btn-primary" style="flex:2" id="pwSaveBtn" onclick="savePassword()">Update Password</button>
      </div>
    </div>
  </div>

  <script>
    function openPasswordModal() {
      document.getElementById('changePasswordModal').style.display = 'flex';
      document.getElementById('pwCurrent').value = '';
      document.getElementById('pwNew').value = '';
      document.getElementById('pwConfirm').value = '';
      document.getElementById('pwError').style.display = 'none';
      document.getElementById('pwSuccess').style.display = 'none';
    }

    function closePasswordModal() {
      document.getElementById('changePasswordModal').style.display = 'none';
    }

    async function savePassword() {
      const btn = document.getElementById('pwSaveBtn');
      const errEl = document.getElementById('pwError');
      const successEl = document.getElementById('pwSuccess');

      const current = document.getElementById('pwCurrent').value;
      const newPw = document.getElementById('pwNew').value;
      const confirm = document.getElementById('pwConfirm').value;

      errEl.style.display = 'none';
      successEl.style.display = 'none';

      if (!current || !newPw || !confirm) {
        errEl.textContent = '❌ Please fill in all fields.';
        errEl.style.display = 'block';
        return;
      }

      if (newPw !== confirm) {
        errEl.textContent = '❌ New passwords do not match.';
        errEl.style.display = 'block';
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Updating...';

      try {
        await axios.patch('/api/me/password', {
          current_password: current,
          new_password: newPw,
          new_password_confirmation: confirm,
        });

        successEl.textContent = '✅ Password updated successfully.';
        successEl.style.display = 'block';

        document.getElementById('pwCurrent').value = '';
        document.getElementById('pwNew').value = '';
        document.getElementById('pwConfirm').value = '';

        setTimeout(() => closePasswordModal(), 1500);
      } catch (e) {
        errEl.textContent = '❌ ' + (e.response?.data?.message || 'Failed to update password.');
        errEl.style.display = 'block';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Update Password';
      }
    }
  </script>
</body>
</html>
