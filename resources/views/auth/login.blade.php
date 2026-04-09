<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – ClassGuard</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root { --navy-dark: #0A1628; --navy-blue: #1E3A8A; --gold: #FCD34D; --white: #FFFFFF; --gray-200: #E5E7EB; --gray-400: #9CA3AF; --gray-500: #6B7280; --gray-700: #374151; --gray-900: #111827; --red: #EF4444; }
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; min-height: 100vh; background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy-blue) 100%); display: flex; align-items: center; justify-content: center; padding: 24px; }
    .auth-container { width: 100%; max-width: 440px; }
    .auth-brand { text-align: center; margin-bottom: 32px; }
    .brand-logo { width: 80px; height: 80px; margin: 0 auto 12px auto; display: block; object-fit: contain; }
    .brand-name { font-size: 22px; font-weight: 600; color: var(--white); }
    .brand-tagline { font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 4px; }
    .auth-card { background: var(--white); border-radius: 12px; padding: 36px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    .auth-card h2 { font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 6px; }
    .auth-card p { font-size: 14px; color: var(--gray-500); margin-bottom: 28px; }
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 13px; font-weight: 500; color: var(--gray-700); margin-bottom: 6px; }
    .form-input { width: 100%; padding: 11px 14px; border: 1.5px solid var(--gray-200); border-radius: 7px; font-size: 14px; color: var(--gray-900); outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
    .form-input:focus { border-color: var(--navy-blue); box-shadow: 0 0 0 3px rgba(30,58,138,0.1); }
    .form-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .form-row label { font-size: 13px; font-weight: 500; color: var(--gray-700); }
    .form-row a { font-size: 12px; color: var(--navy-blue); text-decoration: none; }
    .btn-submit { width: 100%; padding: 12px; background: var(--navy-blue); color: var(--white); border: none; border-radius: 7px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.15s; margin-top: 8px; }
    .btn-submit:hover { background: var(--navy-dark); }
    .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
    .auth-footer { text-align: center; margin-top: 20px; }
    .auth-footer a { font-size: 13px; color: var(--navy-blue); text-decoration: none; }
    .error-msg { display: none; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); color: var(--red); font-size:13px; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; }
  </style>
</head>
<body>
  <div class="auth-container">
    <div class="auth-brand">
      <img src="{{ asset('images/blue-gold-cg.jpg') }}" alt="ClassGuard Logo" class="brand-logo">
      <div class="brand-name">ClassGuard</div>
      <div class="brand-tagline">RFID Attendance Management System</div>
    </div>

    <div class="auth-card">
      <h2>Welcome back</h2>
      <p>Sign in to your account to continue</p>

      <div class="error-msg" id="errorMsg"></div>

      <div class="form-group">
        <label class="form-label">Email address</label>
        <input type="email" class="form-input" placeholder="you@school.edu" id="email">
      </div>

      <div class="form-group">
        <div class="form-row">
          <label>Password</label>
          <a href="#">Forgot password?</a>
        </div>
        <input type="password" class="form-input" placeholder="••••••••" id="password">
      </div>

      <button class="btn-submit" id="loginBtn" onclick="handleLogin()">Sign In</button>

      <div class="auth-footer">
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    async function handleLogin() {
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      const errorMsg = document.getElementById('errorMsg');
      const btn = document.getElementById('loginBtn');

      if (!email || !password) {
        errorMsg.textContent = '❌ Please enter your email and password.';
        errorMsg.style.display = 'block';
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Signing in...';
      errorMsg.style.display = 'none';

      try {
        const response = await axios.post('/api/login', { email, password });
        const { token, user } = response.data;

        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));

        if (user.role === 'admin') window.location.href = '/admin/dashboard';
        else if (user.role === 'professor') window.location.href = '/professor/dashboard';
        else {
          // Should never reach here — AuthController blocks student logins at the API level.
          errorMsg.textContent = '❌ Access denied.';
          errorMsg.style.display = 'block';
          localStorage.clear();
          btn.disabled = false;
          btn.textContent = 'Sign In';
        }

      } catch (error) {
        const msg = error.response?.data?.message || 'Invalid email or password.';
        errorMsg.textContent = '❌ ' + msg;
        errorMsg.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Sign In';
      }
    }

    document.addEventListener('keydown', e => { if (e.key === 'Enter') handleLogin(); });

    // On page load: redirect already-authenticated users to their dashboard
    if (localStorage.getItem('token')) {
      const user = JSON.parse(localStorage.getItem('user') || '{}');
      if (user.role === 'admin') window.location.href = '/admin/dashboard';
      else if (user.role === 'professor') window.location.href = '/professor/dashboard';
      // No student case — students have no dashboard
    }
  </script>
</body>
</html>