@extends('layouts.app')
@section('title', 'Link RFID Card – ClassGuard')

@section('extra_styles')
<style>
  @keyframes scanPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(30,58,138,0.3); transform: scale(1); } 50% { box-shadow: 0 0 0 20px rgba(30,58,138,0); transform: scale(1.02); } }
  .scan-ring { animation: scanPulse 2s ease-in-out infinite; }
</style>
@endsection

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/student/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/student/classes') }}" class="nav-item"><span class="nav-icon">📚</span><span>My Classes</span></a>
    <a href="{{ url('/student/attendance') }}" class="nav-item"><span class="nav-icon">✓</span><span>Attendance</span></a>
    <a href="{{ url('/student/rfid-link') }}" class="nav-item active"><span class="nav-icon">🔗</span><span>Link RFID Card</span></a>
    <div class="nav-divider"></div>
    <div class="nav-section">Account</div>
    <a href="#" class="nav-item" onclick="logout()"><span class="nav-icon">🚪</span><span>Sign Out</span></a>
  </nav>
  <div class="user-section">
    <div class="user-info">
      <div class="user-avatar" id="userAvatar">JD</div>
      <div><div class="user-name" id="userName">Loading...</div><div class="user-role">Student</div></div>
    </div>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h1>Link RFID Card</h1>
      <p class="topbar-subtitle">Connect your physical RFID card to your account</p>
    </div>
  </div>

  <div class="content">
    <!-- Already linked state -->
    <div id="linkedCard" style="display:none; max-width:900px; margin-bottom:24px;">
      <div class="section" style="border-color:var(--green); background:rgba(16,185,129,0.02);">
        <div style="display:flex; align-items:center; gap:20px;">
          <div style="width:64px; height:64px; border-radius:50%; background:rgba(16,185,129,0.1); display:flex; align-items:center; justify-content:center; font-size:32px; flex-shrink:0;">✅</div>
          <div style="flex:1;">
            <h3 style="font-size:18px; font-weight:700; color:var(--gray-900); margin-bottom:4px;">RFID Card Linked</h3>
            <p style="font-size:14px; color:var(--gray-500); margin-bottom:12px;">Your card is active. Tap it on any ClassGuard reader to record attendance.</p>
            <div style="display:flex; gap:24px; font-size:13px; color:var(--gray-500);">
              <span>🔗 UID: <strong style="font-family:monospace; color:var(--gray-900)" id="linkedUid">–</strong></span>
              <span>📅 Linked: <span id="linkedDate">–</span></span>
              <span>✓ Active</span>
            </div>
          </div>
          <button class="btn btn-ghost" style="color:var(--red); border-color:var(--red);" onclick="unlinkCard()">Unlink Card</button>
        </div>
      </div>
    </div>

    <div id="linkForm" style="display:grid; grid-template-columns:1fr 1fr; gap:24px; max-width:900px;">
      <!-- Manual UID entry -->
      <div class="section">
        <div style="text-align:center; padding-bottom:24px;">
          <div style="width:120px; height:120px; border-radius:50%; border:3px solid var(--gray-200); display:inline-flex; align-items:center; justify-content:center; font-size:52px; margin-bottom:24px; background:var(--gray-50);">⌨️</div>
          <h3 style="font-size:18px; font-weight:700; color:var(--gray-900); margin-bottom:8px;">Enter UID Manually</h3>
          <p style="font-size:13px; color:var(--gray-500); margin-bottom:24px; line-height:1.6;">Visit the admin office to get your card's UID, or find it printed on the back of your RFID card.</p>
        </div>
        <div id="linkError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
        <div class="form-group">
          <label class="form-label">Card UID</label>
          <input type="text" class="form-input" id="manualUid" placeholder="e.g. A1:B2:C3:D4" style="font-family:monospace; letter-spacing:0.05em; text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
          <div class="form-hint">Format: XX:XX:XX:XX (hexadecimal)</div>
        </div>
        <button class="btn btn-primary" style="width:100%;" id="linkBtn" onclick="submitManual()">Link This Card</button>
      </div>

      <!-- How it works -->
      <div class="section">
        <h2 class="section-title" style="margin-bottom:20px;">How RFID Attendance Works</h2>
        <div style="display:flex; flex-direction:column; gap:16px;">
          <div style="display:flex; gap:16px; align-items:flex-start;">
            <div style="font-size:28px; flex-shrink:0;">🔗</div>
            <div><div style="font-size:13px; font-weight:600; color:var(--gray-900); margin-bottom:4px;">1. Link Your Card</div><div style="font-size:12px; color:var(--gray-500); line-height:1.5;">Register your RFID card once via manual entry</div></div>
          </div>
          <div style="display:flex; gap:16px; align-items:flex-start;">
            <div style="font-size:28px; flex-shrink:0;">📡</div>
            <div><div style="font-size:13px; font-weight:600; color:var(--gray-900); margin-bottom:4px;">2. Session Starts</div><div style="font-size:12px; color:var(--gray-500); line-height:1.5;">Your professor starts an attendance session in the system</div></div>
          </div>
          <div style="display:flex; gap:16px; align-items:flex-start;">
            <div style="font-size:28px; flex-shrink:0;">🤙</div>
            <div><div style="font-size:13px; font-weight:600; color:var(--gray-900); margin-bottom:4px;">3. Tap Card</div><div style="font-size:12px; color:var(--gray-500); line-height:1.5;">Tap your card on the RFID reader at the classroom</div></div>
          </div>
          <div style="display:flex; gap:16px; align-items:flex-start;">
            <div style="font-size:28px; flex-shrink:0;">✅</div>
            <div><div style="font-size:13px; font-weight:600; color:var(--gray-900); margin-bottom:4px;">4. Confirm Code</div><div style="font-size:12px; color:var(--gray-500); line-height:1.5;">Enter the class code shown on the board to confirm attendance</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection

@section('scripts')
<script>
  const token = localStorage.getItem('token');
  let user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'student') { localStorage.clear(); window.location.href = '/login'; }
  axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;

  document.getElementById('userName').textContent = user.name || 'Student';
  document.getElementById('userAvatar').textContent = (user.name || 'S').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();

  function checkLinkedStatus() {
    if (user.rfid_uid) {
      document.getElementById('linkedCard').style.display = 'block';
      document.getElementById('linkForm').style.display = 'none';
      document.getElementById('linkedUid').textContent = user.rfid_uid;
      document.getElementById('linkedDate').textContent = user.rfid_linked_at ? new Date(user.rfid_linked_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '–';
    }
  }

  async function submitManual() {
    const uid = document.getElementById('manualUid').value.trim();
    const btn = document.getElementById('linkBtn');
    const errEl = document.getElementById('linkError');
    if (!uid) { errEl.textContent = '❌ Please enter the card UID'; errEl.style.display = 'block'; return; }
    btn.disabled = true; btn.textContent = 'Linking...'; errEl.style.display = 'none';
    try {
      const res = await axios.post('/api/rfid/link', { uid });
      user = res.data.user;
      localStorage.setItem('user', JSON.stringify(user));
      document.getElementById('linkedCard').style.display = 'block';
      document.getElementById('linkForm').style.display = 'none';
      document.getElementById('linkedUid').textContent = user.rfid_uid;
      document.getElementById('linkedDate').textContent = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch (error) {
      errEl.textContent = '❌ ' + (error.response?.data?.message || 'Failed to link card.');
      errEl.style.display = 'block';
    } finally { btn.disabled = false; btn.textContent = 'Link This Card'; }
  }

  async function unlinkCard() {
    if (!confirm('Unlink this card? You will need to re-link it to use RFID attendance.')) return;
    try {
      await axios.post('/api/rfid/unlink');
      user.rfid_uid = null;
      user.rfid_linked_at = null;
      localStorage.setItem('user', JSON.stringify(user));
      document.getElementById('linkedCard').style.display = 'none';
      document.getElementById('linkForm').style.display = 'grid';
      document.getElementById('manualUid').value = '';
    } catch (error) {
      alert('Failed to unlink card: ' + (error.response?.data?.message || 'Unknown error'));
    }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  checkLinkedStatus();
</script>
@endsection
