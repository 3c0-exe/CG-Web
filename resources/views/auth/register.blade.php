<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account – ClassGuard</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root { --navy-dark: #0A1628; --navy-blue: #1E3A8A; --gold: #FCD34D; --white: #FFFFFF; --gray-100: #F3F4F6; --gray-200: #E5E7EB; --gray-400: #9CA3AF; --gray-500: #6B7280; --gray-700: #374151; --gray-900: #111827; --red: #EF4444; --green: #10B981; }
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; min-height: 100vh; background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy-blue) 100%); display: flex; align-items: flex-start; justify-content: center; padding: 32px 24px; }
    .auth-container { width: 100%; max-width: 500px; }
    .auth-brand { text-align: center; margin-bottom: 28px; }
    .brand-icon { width: 48px; height: 48px; background: var(--gold); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; color: var(--navy-dark); margin-bottom: 10px; }
    .brand-name { font-size: 20px; font-weight: 600; color: var(--white); }
    .auth-card { background: var(--white); border-radius: 12px; padding: 36px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    .auth-card h2 { font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 6px; }
    .auth-card > p { font-size: 14px; color: var(--gray-500); margin-bottom: 28px; }
    .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 13px; font-weight: 500; color: var(--gray-700); margin-bottom: 6px; }
    .form-label span { color: var(--red); }
    .form-input, .form-select { width: 100%; padding: 10px 14px; border: 1.5px solid var(--gray-200); border-radius: 7px; font-size: 14px; color: var(--gray-900); outline: none; transition: border-color 0.15s, box-shadow 0.15s; background: var(--white); }
    .form-input:focus, .form-select:focus { border-color: var(--navy-blue); box-shadow: 0 0 0 3px rgba(30,58,138,0.1); }
    .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239CA3AF' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px; }
    .form-hint { font-size: 11px; color: var(--gray-400); margin-top: 4px; }
    .form-error { font-size: 11px; color: var(--red); margin-top: 4px; display: none; }
    .section-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-400); margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--gray-100); }
    .password-strength { margin-top: 6px; }
    .strength-bar { height: 4px; border-radius: 99px; background: var(--gray-100); overflow: hidden; margin-bottom: 4px; }
    .strength-fill { height: 100%; border-radius: 99px; width: 0; transition: width 0.3s, background 0.3s; }
    .strength-text { font-size: 11px; color: var(--gray-400); }
    .terms-check { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px; }
    .terms-check input { margin-top: 2px; flex-shrink: 0; }
    .terms-check label { font-size: 13px; color: var(--gray-500); }
    .terms-check a { color: var(--navy-blue); text-decoration: none; }
    .btn-submit { width: 100%; padding: 12px; background: var(--navy-blue); color: var(--white); border: none; border-radius: 7px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.15s; }
    .btn-submit:hover { background: var(--navy-dark); }
    .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
    .btn-back { width: 100%; padding: 12px; background: var(--gray-200); color: var(--gray-700); border: none; border-radius: 7px; font-size: 15px; font-weight: 600; cursor: pointer; }
    .auth-footer { text-align: center; margin-top: 20px; }
    .auth-footer a { font-size: 13px; color: var(--navy-blue); text-decoration: none; }
    .step-indicator { display: flex; align-items: center; gap: 8px; margin-bottom: 28px; }
    .step { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; color: var(--gray-400); }
    .step.active { color: var(--navy-blue); }
    .step.done { color: var(--green); }
    .step-num { width: 22px; height: 22px; border-radius: 50%; border: 1.5px solid currentColor; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }
    .step-divider { flex: 1; height: 1px; background: var(--gray-200); }
    .error-banner { display: none; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); color: var(--red); font-size: 13px; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; }
  </style>
</head>
<body>
  <div class="auth-container">
    <div class="auth-brand">
      <div class="brand-icon">C</div>
      <div class="brand-name">ClassGuard</div>
    </div>

    <div class="auth-card">
      <h2>Create Student Account</h2>
      <p>Register to track your attendance across all subjects</p>

      <div class="error-banner" id="errorBanner"></div>

      <div class="step-indicator">
        <div class="step active" id="step1-indicator"><div class="step-num">1</div><span>Personal Info</span></div>
        <div class="step-divider"></div>
        <div class="step" id="step2-indicator"><div class="step-num">2</div><span>Academic Details</span></div>
        <div class="step-divider"></div>
        <div class="step" id="step3-indicator"><div class="step-num">3</div><span>Security</span></div>
      </div>

      <!-- Step 1 -->
      <div id="step1">
        <div class="section-label">Personal Information</div>
        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">First Name <span>*</span></label>
            <input type="text" class="form-input" id="firstName" placeholder="Juan">
          </div>
          <div class="form-group">
            <label class="form-label">Last Name <span>*</span></label>
            <input type="text" class="form-input" id="lastName" placeholder="Dela Cruz">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address <span>*</span></label>
          <input type="email" class="form-input" id="email" placeholder="juan@school.edu">
          <div class="form-hint">Use your school-issued email address</div>
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="tel" class="form-input" id="phone" placeholder="09XX-XXX-XXXX">
          <div class="form-hint">Used for SMS attendance notifications</div>
        </div>
        <button class="btn-submit" onclick="nextStep(2)">Continue →</button>
      </div>

      <!-- Step 2 -->
      <div id="step2" style="display:none">
        <div class="section-label">Academic Details</div>
        <div class="form-group">
          <label class="form-label">Student ID Number <span>*</span></label>
          <input type="text" class="form-input" id="studentId" placeholder="2024-00001">
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">Year Level <span>*</span></label>
            <select class="form-select" id="yearLevel" onchange="loadSections()">
              <option value="">Select year</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Section <span>*</span></label>
            <select class="form-select" id="section">
              <option value="">Select section</option>
            </select>
          </div>
        </div>
        <div style="display:flex; gap:12px;">
          <button class="btn-back" style="flex:1" onclick="nextStep(1)">← Back</button>
          <button class="btn-submit" style="flex:2" onclick="nextStep(3)">Continue →</button>
        </div>
      </div>

      <!-- Step 3 -->
      <div id="step3" style="display:none">
        <div class="section-label">Set Password</div>
        <div class="form-group">
          <label class="form-label">Password <span>*</span></label>
          <input type="password" class="form-input" id="password" placeholder="Min. 8 characters" oninput="checkPw()">
          <div class="password-strength">
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <div class="strength-text" id="strengthText">Enter a password</div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password <span>*</span></label>
          <input type="password" class="form-input" id="passwordConfirm" placeholder="Re-enter password">
        </div>
        <div class="terms-check">
          <input type="checkbox" id="terms">
          <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
        </div>
        <div style="display:flex; gap:12px;">
          <button class="btn-back" style="flex:1" onclick="nextStep(2)">← Back</button>
          <button class="btn-submit" style="flex:2" id="submitBtn" onclick="submitForm()">Create Account</button>
        </div>
      </div>

      <div class="auth-footer">
        <a href="{{ url('/login') }}">Already have an account? Sign in</a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    // Load year levels and sections from API
    async function loadYearLevels() {
      try {
        const res = await axios.get('/api/admin/year-levels');
        const select = document.getElementById('yearLevel');
        res.data.year_levels.forEach(yl => {
          select.innerHTML += `<option value="${yl.id}">${yl.name}</option>`;
        });
      } catch (e) {
        // fallback static options if not logged in
        ['1st Year','2nd Year','3rd Year','4th Year'].forEach((y, i) => {
          document.getElementById('yearLevel').innerHTML += `<option value="${i+1}">${y}</option>`;
        });
      }
    }

    async function loadSections() {
      const yearLevelId = document.getElementById('yearLevel').value;
      const sectionSelect = document.getElementById('section');
      sectionSelect.innerHTML = '<option value="">Select section</option>';
      if (!yearLevelId) return;
      try {
        const res = await axios.get('/api/admin/year-levels');
        const yl = res.data.year_levels.find(y => y.id == yearLevelId);
        if (yl && yl.sections) {
          yl.sections.forEach(s => {
            sectionSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
          });
        }
      } catch (e) {}
    }

    function nextStep(n) {
      document.querySelectorAll('[id^="step"]').forEach(el => {
        if (!el.id.includes('indicator')) el.style.display = 'none';
      });
      document.getElementById('step' + n).style.display = 'block';
      ['step1-indicator','step2-indicator','step3-indicator'].forEach((id, i) => {
        const el = document.getElementById(id);
        el.className = 'step' + (i + 1 < n ? ' done' : i + 1 === n ? ' active' : '');
        el.querySelector('.step-num').textContent = i + 1 < n ? '✓' : i + 1;
      });
    }

    function checkPw() {
      const pw = document.getElementById('password').value;
      const fill = document.getElementById('strengthFill');
      const text = document.getElementById('strengthText');
      let score = 0;
      if (pw.length >= 8) score++;
      if (/[A-Z]/.test(pw)) score++;
      if (/[0-9]/.test(pw)) score++;
      if (/[^A-Za-z0-9]/.test(pw)) score++;
      const colors = ['#EF4444','#F97316','#FCD34D','#10B981'];
      const labels = ['Too weak','Fair','Good','Strong'];
      fill.style.width = (score * 25) + '%';
      fill.style.background = colors[score - 1] || '#E5E7EB';
      text.textContent = score ? labels[score - 1] : 'Enter a password';
    }

    async function submitForm() {
      const errorBanner = document.getElementById('errorBanner');
      const submitBtn = document.getElementById('submitBtn');

      if (!document.getElementById('terms').checked) {
        errorBanner.textContent = '❌ Please agree to the Terms of Service.';
        errorBanner.style.display = 'block';
        return;
      }

      const password = document.getElementById('password').value;
      const passwordConfirm = document.getElementById('passwordConfirm').value;

      if (password !== passwordConfirm) {
        errorBanner.textContent = '❌ Passwords do not match.';
        errorBanner.style.display = 'block';
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Creating Account...';
      errorBanner.style.display = 'none';

      const firstName = document.getElementById('firstName').value;
      const lastName = document.getElementById('lastName').value;

      try {
        await axios.post('/api/register', {
          name: firstName + ' ' + lastName,
          email: document.getElementById('email').value,
          phone: document.getElementById('phone').value,
          student_id_number: document.getElementById('studentId').value,
          year_level_id: document.getElementById('yearLevel').value,
          section_id: document.getElementById('section').value,
          password: password,
          password_confirmation: passwordConfirm,
        });

        alert('Account created! Pending admin approval. You will be redirected to login.');
        window.location.href = '/login';

      } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors) {
          const firstError = Object.values(errors)[0][0];
          errorBanner.textContent = '❌ ' + firstError;
        } else {
          errorBanner.textContent = '❌ ' + (error.response?.data?.message || 'Registration failed.');
        }
        errorBanner.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Account';
      }
    }

    loadYearLevels();
  </script>
</body>
</html>
