<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassPulse · Lecturer Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/theme.css">
    <script src="../assets/theme.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --p:#6C5CE7; --g:#FDCB6E; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Inter',sans-serif; min-height:100vh; background:var(--bg);
            display:flex; align-items:center; justify-content:center; padding:20px;
        }
        .orb { position:fixed; border-radius:50%; filter:blur(90px); opacity:var(--orb1-op); pointer-events:none; }
        .orb1 { width:450px;height:450px;background:radial-gradient(circle,var(--p),transparent); top:-120px;left:-120px; }
        .orb2 { width:350px;height:350px;background:radial-gradient(circle,var(--g),transparent); bottom:-80px;right:-60px; }

        .wrap { max-width:420px; width:100%; position:relative; z-index:1; animation:up .5s cubic-bezier(.2,.9,.4,1) both; }
        @keyframes up { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:none} }

        .logo-row { text-align:center; margin-bottom:28px; }
        .logo-icon { width:60px;height:60px;border-radius:18px;background:linear-gradient(135deg,var(--p),var(--g));display:inline-flex;align-items:center;justify-content:center;font-size:1.6rem;margin-bottom:12px;box-shadow:0 8px 30px rgba(108,92,231,.4); }
        .logo-name { font-size:1.8rem;font-weight:800;background:linear-gradient(135deg,#fff,#a78bfa,var(--g));-webkit-background-clip:text;background-clip:text;color:transparent; }
        .logo-sub  { color:rgba(255,255,255,.4);font-size:.85rem;margin-top:4px; }

        .card { background:var(--card);backdrop-filter:blur(24px);border:1.5px solid var(--border);border-radius:24px;padding:28px 24px; }

        /* Tabs */
        .tabs { display:flex; background:rgba(255,255,255,.05); border-radius:14px; padding:4px; margin-bottom:24px; }
        .tab { flex:1; padding:10px; border:none; border-radius:10px; font-family:inherit; font-size:.9rem; font-weight:600; cursor:pointer; color:rgba(255,255,255,.5); background:transparent; transition:all .2s; }
        .tab.active { background:linear-gradient(135deg,var(--p),#8b7ff0); color:#fff; box-shadow:0 4px 14px rgba(108,92,231,.35); }

        /* Fields */
        .field { margin-bottom:14px; }
        .field label { display:block; font-size:.78rem; font-weight:600; color:rgba(255,255,255,.45); margin-bottom:6px; text-transform:uppercase; letter-spacing:.05em; }
        .inp {
            width:100%; background:rgba(255,255,255,.06); border:1.5px solid rgba(255,255,255,.1);
            border-radius:12px; padding:13px 16px; color:#fff; font-size:.95rem; font-family:inherit; outline:none;
            transition:border-color .2s, box-shadow .2s;
        }
        .inp:focus { border-color:var(--p); box-shadow:0 0 0 3px rgba(108,92,231,.2); }
        .inp::placeholder { color:rgba(255,255,255,.25); }

        /* Buttons */
        .btn-main { width:100%;padding:14px;border:none;border-radius:12px;font-weight:700;font-size:.95rem;cursor:pointer;font-family:inherit;background:linear-gradient(135deg,var(--p),#a78bfa);color:#fff;margin-top:6px;transition:opacity .15s,transform .15s;box-shadow:0 6px 20px rgba(108,92,231,.3); }
        .btn-main:hover:not(:disabled) { opacity:.9; transform:translateY(-1px); }
        .btn-main:disabled { opacity:.5; cursor:not-allowed; }

        .err { color:#ff7675; font-size:.82rem; margin-top:6px; display:flex; align-items:center; gap:5px; }
        .back { display:block; text-align:center; margin-top:18px; color:rgba(255,255,255,.35); font-size:.85rem; text-decoration:none; transition:color .2s; }
        .back:hover { color:var(--text2); }
    </style>
</head>
<body>
<button class="theme-toggle" data-theme-btn title="Toggle theme" style="position:fixed;top:16px;right:16px;z-index:200;width:40px;height:40px;border-radius:12px;background:var(--card);border:1px solid var(--border);color:var(--text2);cursor:pointer;font-size:.95rem;"></button>
<div class="orb orb1"></div>
<div class="orb orb2"></div>

<div class="wrap">
    <div class="logo-row">
        <div class="logo-icon">🎓</div>
        <div class="logo-name">ClassPulse</div>
        <p class="logo-sub">Lecturer Portal</p>
    </div>

    <div class="card">
        <div class="tabs">
            <button class="tab active" id="loginTabBtn">Log In</button>
            <button class="tab" id="signupTabBtn">Sign Up</button>
        </div>

        <!-- Login -->
        <form id="loginForm">
            <div class="field"><label>Full Name</label><input type="text" id="loginName" class="inp" placeholder="Dr. Chukwu Emeka" autocomplete="name"></div>
            <div class="field"><label>Password</label><input type="password" id="loginPassword" class="inp" placeholder="••••••••" autocomplete="current-password"></div>
            <div id="loginError" class="err"></div>
            <button type="submit" class="btn-main" data-label='<i class="fas fa-sign-in-alt me-2"></i>Log In'>
                <i class="fas fa-sign-in-alt me-2"></i>Log In
            </button>
        </form>

        <!-- Sign Up -->
        <form id="signupForm" style="display:none;">
            <div class="field"><label>Full Name</label><input type="text" id="signupFullName" class="inp" placeholder="Dr. Chukwu Emeka" autocomplete="name"></div>
            <div class="field"><label>Course / Subject</label><input type="text" id="signupCourse" class="inp" placeholder="e.g. Physics 101"></div>
            <div class="field"><label>Password</label><input type="password" id="signupPassword" class="inp" placeholder="Min. 4 characters" autocomplete="new-password"></div>
            <div class="field"><label>Confirm Password</label><input type="password" id="signupConfirm" class="inp" placeholder="Re-enter password"></div>
            <div id="signupError" class="err"></div>
            <button type="submit" class="btn-main" data-label='<i class="fas fa-user-plus me-2"></i>Create Account'>
                <i class="fas fa-user-plus me-2"></i>Create Account
            </button>
        </form>
    </div>

    <a href="../index.php" class="back"><i class="fas fa-arrow-left me-1"></i>Back to home</a>
</div>

<script>
(function () {
    const _base = window.location.pathname.replace(/\/teacher\/.*$/, '');
    const API   = _base + '/api';

    const loginForm   = document.getElementById('loginForm');
    const signupForm  = document.getElementById('signupForm');
    const loginTab    = document.getElementById('loginTabBtn');
    const signupTab   = document.getElementById('signupTabBtn');
    const loginError  = document.getElementById('loginError');
    const signupError = document.getElementById('signupError');

    function setLoading(btn, on) {
        btn.disabled = on;
        btn.innerHTML = on ? '<i class="fas fa-spinner fa-spin me-2"></i>Please wait…' : btn.dataset.label;
    }

    async function apiPost(ep, body) {
        const r = await fetch(`${API}/${ep}`, {
            method:'POST', credentials:'include',
            headers:{'Content-Type':'application/json'}, body:JSON.stringify(body),
        });
        return r.json();
    }

    loginTab.addEventListener('click', () => {
        loginTab.classList.add('active'); signupTab.classList.remove('active');
        loginForm.style.display='block'; signupForm.style.display='none';
        loginError.innerHTML='';
    });
    signupTab.addEventListener('click', () => {
        signupTab.classList.add('active'); loginTab.classList.remove('active');
        signupForm.style.display='block'; loginForm.style.display='none';
        signupError.innerHTML='';
    });

    loginForm.addEventListener('submit', async e => {
        e.preventDefault();
        loginError.innerHTML='';
        const btn  = loginForm.querySelector('.btn-main');
        const name = document.getElementById('loginName').value.trim();
        const pass = document.getElementById('loginPassword').value;
        if (!name||!pass) { loginError.innerHTML='<i class="fas fa-circle-exclamation"></i> Please fill in both fields.'; return; }
        setLoading(btn,true);
        try {
            const d = await apiPost('auth/login.php', { full_name:name, password:pass });
            if (d.success) { sessionStorage.setItem('cp_teacher',JSON.stringify(d.teacher)); window.location.href='dashboard.php'; }
            else loginError.innerHTML=`<i class="fas fa-circle-exclamation"></i> ${d.message||'Login failed.'}`;
        } catch(_) { loginError.innerHTML='<i class="fas fa-wifi"></i> Cannot reach server. Is XAMPP running?'; }
        finally { setLoading(btn,false); }
    });

    signupForm.addEventListener('submit', async e => {
        e.preventDefault();
        signupError.innerHTML='';
        const btn  = signupForm.querySelector('.btn-main');
        const name = document.getElementById('signupFullName').value.trim();
        const course=document.getElementById('signupCourse').value.trim();
        const pass = document.getElementById('signupPassword').value;
        const conf = document.getElementById('signupConfirm').value;
        if (!name||!pass) { signupError.innerHTML='<i class="fas fa-circle-exclamation"></i> Name and password are required.'; return; }
        if (pass.length<4) { signupError.innerHTML='<i class="fas fa-circle-exclamation"></i> Password must be at least 4 characters.'; return; }
        if (pass!==conf)   { signupError.innerHTML='<i class="fas fa-circle-exclamation"></i> Passwords do not match.'; return; }
        setLoading(btn,true);
        try {
            const d = await apiPost('auth/register.php', { full_name:name, course:course||'General', password:pass, confirm_password:conf });
            if (d.success) { sessionStorage.setItem('cp_teacher',JSON.stringify(d.teacher)); window.location.href='dashboard.php'; }
            else signupError.innerHTML=`<i class="fas fa-circle-exclamation"></i> ${d.message||'Registration failed.'}`;
        } catch(_) { signupError.innerHTML='<i class="fas fa-wifi"></i> Cannot reach server. Is XAMPP running?'; }
        finally { setLoading(btn,false); }
    });

    // Auto-redirect if already logged in
    (async()=>{
        try {
            const r = await fetch(`${API}/auth/check.php`,{credentials:'include'});
            const d = await r.json();
            if (d.success) { sessionStorage.setItem('cp_teacher',JSON.stringify(d.teacher)); window.location.href='dashboard.php'; }
        } catch(_){}
    })();
})();
</script>
</body>
</html>
