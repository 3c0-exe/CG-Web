@extends('layouts.app')
@section('title', 'My Classes – ClassGuard')

@section('content')
<aside class="sidebar">
  <div class="logo"><div class="logo-icon">C</div><span class="logo-text">ClassGuard</span></div>
  <nav>
    <a href="{{ url('/student/dashboard') }}" class="nav-item"><span class="nav-icon">📊</span><span>Dashboard</span></a>
    <a href="{{ url('/student/classes') }}" class="nav-item active"><span class="nav-icon">📚</span><span>My Classes</span></a>
    <a href="{{ url('/student/attendance') }}" class="nav-item"><span class="nav-icon">✓</span><span>Attendance</span></a>
    <a href="{{ url('/student/rfid-link') }}" class="nav-item"><span class="nav-icon">🔗</span><span>Link RFID Card</span></a>
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
      <h1>My Classes</h1>
      <p class="topbar-subtitle" id="classSubtitle">Loading...</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openJoinModal()">+ Join a Class</button>
    </div>
  </div>

  <div class="content">
    <div id="classGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:20px; margin-bottom:32px;">
      <div style="text-align:center; padding:40px; color:var(--gray-400);">Loading classes...</div>
    </div>
  </div>
</main>

<!-- Join Class Modal -->
<div id="joinModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; padding:24px;" onclick="closeModal(event)">
  <div style="background:var(--white); border-radius:12px; padding:32px; width:100%; max-width:400px; box-shadow:0 20px 60px rgba(0,0,0,0.3);" onclick="event.stopPropagation()">
    <h3 style="font-size:18px; font-weight:700; margin-bottom:8px;">Join a Class</h3>
    <p style="font-size:14px; color:var(--gray-500); margin-bottom:24px;">Enter the 6-character class code provided by your professor</p>
    <div id="joinError" style="display:none; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); color:var(--red); font-size:13px; padding:10px 14px; border-radius:6px; margin-bottom:16px;"></div>
    <div style="margin-bottom:20px;">
      <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Class Code</label>
      <input type="text" id="classCode" maxlength="6" placeholder="e.g. ITC001" style="width:100%; padding:14px; border:1.5px solid var(--gray-200); border-radius:7px; font-size:20px; font-weight:700; letter-spacing:0.2em; text-align:center; text-transform:uppercase; outline:none;" oninput="this.value=this.value.toUpperCase()">
    </div>
    <div style="display:flex; gap:12px;">
      <button class="btn btn-ghost" style="flex:1" onclick="document.getElementById('joinModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" style="flex:2" id="joinBtn" onclick="joinClass()">Join Class</button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  // token is declared in layouts/app.blade.php
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!token || user.role !== 'student') { localStorage.clear(); window.location.href = '/login'; }
  axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;

  document.getElementById('userName').textContent = user.name || 'Student';
  const initials = (user.name || 'S').split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase();
  document.getElementById('userAvatar').textContent = initials;

  const colors = ['var(--navy-blue)','#1d4ed8','#7c3aed','#0f766e','#b45309','#dc2626'];

  async function loadClasses() {
    try {
      const res = await axios.get('/api/enrollment/my-classes');
      const classes = res.data.classes;
      document.getElementById('classSubtitle').textContent = `${classes.length} enrolled · This semester`;

      if (classes.length === 0) {
        document.getElementById('classGrid').innerHTML = `
          <div onclick="openJoinModal()" style="border:2px dashed var(--gray-200); border-radius:8px; padding:40px 24px; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer;">
            <div style="font-size:36px; margin-bottom:12px; opacity:0.4">+</div>
            <div style="font-size:14px; font-weight:600; color:var(--gray-500); margin-bottom:4px;">Join a Class</div>
            <div style="font-size:12px; color:var(--gray-400);">Enter class code from your professor</div>
          </div>`;
        return;
      }

      const html = classes.map((c, i) => {
        const color = colors[i % colors.length];
        const isGuest = c.enrollment_type === 'guest';
        return `
          <div style="background:var(--white); border:1px solid var(--gray-200); border-radius:8px; overflow:hidden;">
            <div style="background:${color}; padding:20px; position:relative;">
              <div style="font-size:11px; font-weight:600; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">${c.subject.code}</div>
              <div style="font-size:18px; font-weight:700; color:var(--white); margin-bottom:4px;">${c.subject.name}</div>
              <div style="font-size:13px; color:rgba(255,255,255,0.6);">${c.subject.professor?.name || 'Professor'}</div>
              <div style="position:absolute; top:16px; right:16px;"><span class="badge ${isGuest ? 'gold' : 'success'}">${isGuest ? 'Guest' : 'Regular'}</span></div>
            </div>
            <div style="padding:16px;">
              ${isGuest ? `<div style="background:rgba(252,211,77,0.1); border:1px solid rgba(252,211,77,0.3); border-radius:6px; padding:10px; margin-bottom:12px; font-size:12px; color:#92400e;">🎫 You are attending as a guest</div>` : ''}
              <div style="border-top:1px solid var(--gray-100); padding-top:14px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:12px; color:var(--gray-500);">Class code: <strong>${c.subject.class_code}</strong></span>
                <a href="{{ url('/student/attendance') }}" class="btn btn-ghost btn-sm">View Details</a>
              </div>
            </div>
          </div>`;
      }).join('');

      const addCard = `
        <div onclick="openJoinModal()" style="border:2px dashed var(--gray-200); border-radius:8px; padding:40px 24px; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer;" onmouseover="this.style.borderColor='var(--navy-blue)'" onmouseout="this.style.borderColor='var(--gray-200)'">
          <div style="font-size:36px; margin-bottom:12px; opacity:0.4">+</div>
          <div style="font-size:14px; font-weight:600; color:var(--gray-500); margin-bottom:4px;">Join a Class</div>
          <div style="font-size:12px; color:var(--gray-400);">Enter class code from your professor</div>
        </div>`;

      document.getElementById('classGrid').innerHTML = html + addCard;
    } catch (e) {
      document.getElementById('classGrid').innerHTML = '<div style="color:var(--red); padding:24px;">Failed to load classes.</div>';
    }
  }

  function openJoinModal() { document.getElementById('joinModal').style.display = 'flex'; document.getElementById('classCode').value = ''; document.getElementById('joinError').style.display = 'none'; }
  function closeModal(e) { if (e.target === document.getElementById('joinModal')) document.getElementById('joinModal').style.display = 'none'; }

  async function joinClass() {
    const code = document.getElementById('classCode').value;
    const btn = document.getElementById('joinBtn');
    const errEl = document.getElementById('joinError');
    if (code.length < 2) { errEl.textContent = 'Please enter a valid class code'; errEl.style.display = 'block'; return; }
    btn.disabled = true; btn.textContent = 'Joining...'; errEl.style.display = 'none';
    try {
      await axios.post('/api/enrollment/join', { class_code: code });
      document.getElementById('joinModal').style.display = 'none';
      loadClasses();
    } catch (error) {
      errEl.textContent = '❌ ' + (error.response?.data?.message || 'Failed to join class.');
      errEl.style.display = 'block';
    } finally { btn.disabled = false; btn.textContent = 'Join Class'; }
  }

  function logout() { axios.post('/api/logout').finally(() => { localStorage.clear(); window.location.href = '/login'; }); }

  loadClasses();
</script>
@endsection
