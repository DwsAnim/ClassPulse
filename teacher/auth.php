<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassPulse · Lecturer Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/theme.css">
    <script src="../assets/theme.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family:'Inter',sans-serif;
            min-height:100vh;
            background:var(--bg);
            display:flex; align-items:center; justify-content:center;
            padding:20px;
            color:var(--text);
        }

        /* Background orbs */
        .orb { position:fixed; border-radius:50%; filter:blur(90px); pointer-events:none; z-index:0; }
        .orb1 { width:450px;height:450px; background:radial-gradient(circle,#6C5CE7,transparent); top:-120px;left:-120px; opacity:var(--orb1-op); }
        .orb2 { width:350px;height:350px; background:radial-gradient(circle,#FDCB6E,transparent); bottom:-80px;right:-60px; opacity:var(--orb2-op); }

        /* Theme toggle */
        .theme-btn {
            position:fixed; top:16px; right:16px; z-index:200;
            width:40px; height:40px; border-radius:12px;
            background:var(--card); border:1.5px solid var(--border);
            color:var(--text2); cursor:pointer; font-size:.95rem;
            display:flex; align-items:center; justify-content:center;
            transition:all .2s;
        }
        .theme-btn:hover { background:var(--card-hover); color:var(--text); }

        /* Main container */
        .wrap {
            max-width:420px; width:100%;
            position:relative; z-index:1;
            animation:up .5s cubic-bezier(.2,.9,.4,1) both;
        }
        @keyframes up { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:none} }

        /* Logo */
        .logo-row { text-align:center; margin-bottom:28px; }
        .logo-icon {
            width:64px; height:64px; border-radius:20px;
            background:linear-gradient(135deg,#6C5CE7,#FDCB6E);
            display:inline-flex; align-items:center; justify-content:center;
            font-size:1.8rem; margin-bottom:14px;
            box-shadow:0 10px 32px rgba(108,92,231,.4);
        }
        .logo-name {
            font-size:2rem; font-weight:800;
            background:linear-gradient(135deg,#6C5CE7,#a78bfa,#FDCB6E);
            -webkit-background-clip:text; background-clip:text; color:transparent;
        }
        .logo-sub { color:var(--text3); font-size:.88rem; margin-top:5px; font-weight:500; }

        /* Card */
        .card {
            background:var(--card);
            backdrop-filter:blur(24px);
            border:1.5px solid var(--border);
            border-radius:24px;
            padding:28px 24px;
            box-shadow:var(--shadow);
        }

        /* Tabs */
        .tabs {
            display:flex;
            background:var(--input-bg);
            border:1px solid var(--border);
            border-radius:14px;
            padding:4px;
            margin-bottom:24px;
            gap:4px;
        }
        .tab {
            flex:1; padding:10px; border:none; border-radius:10px;
            font-family:inherit; font-size:.9rem; font-weight:600;
            cursor:pointer; color:var(--text2); background:transparent;
            transition:all .2s;
        }
        .tab.active {
            background:linear-gradient(135deg,#6C5CE7,#8b7ff0);
            color:#fff;
            box-shadow:0 4px 14px rgba(108,92,231,.3);
        }
        .tab:not(.active):hover { color:var(--text); background:var(--card-hover); }

        /* Form fields */
        .field { margin-bottom:14px; }
        .field label {
            display:block;
            font-size:.75rem; font-weight:700;
            color:var(--text3);
            margin-bottom:6px;
            text-transform:uppercase; letter-spacing:.06em;
        }
        .inp {
            width:100%;
            background:var(--input-bg);
            border:1.5px solid var(--input-b);
            border-radius:12px;
            padding:13px 16px;
            color:var(--text);
            font-size:.95rem; font-family:inherit;
            outline:none;
            transition:border-color .2s, box-shadow .2s;
        }
        .inp:focus {
            border-color:#6C5CE7;
            box-shadow:0 0 0 3px rgba(108,92,231,.18);
        }
        .inp::placeholder { color:var(--text3); }

        /* Submit button */
        .btn-main {
            width:100%; padding:14px; border:none; border-radius:12px;
            font-weight:700; font-size:.95rem; cursor:pointer; font-family:inherit;
            background:linear-gradient(135deg,#6C5CE7,#a78bfa);
            color:#fff; margin-top:6px;
            transition:opacity .15s, transform .15s;
            box-shadow:0 6px 20px rgba(108,92,231,.3);
        }
        .btn-main:hover:not(:disabled) { opacity:.9; transform:translateY(-1px); }
        .btn-main:disabled { opacity:.5; cursor:not-allowed; transform:none; }

        /* Error message */
        .err {
            color:#ff7675; font-size:.82rem;
            margin-top:6px; min-height:20px;
            display:flex; align-items:center; gap:5px;
        }

        /* Back link */
        .back {
            display:block; text-align:center;
            margin-top:18px; color:var(--text3);
            font-size:.85rem; text-decoration:none;
            transition:color .2s;
        }
        .back:hover { color:var(--text2); }
    </style>
</head>
<body>

<div class="orb orb1"></div>
<div class="orb orb2"></div>
<button class="theme-btn" data-theme-btn title="Toggle theme"></button>

<div class="wrap">
    <!-- Logo -->
    <div class="logo-row">
        <div class="logo-icon">🎓</div>
        <div class="logo-name">ClassPulse</div>
        <p class="logo-sub">Lecturer Portal</p>
    </div>

    <!-- Card -->
    <div class="card">
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" id="loginTabBtn">Log In</button>
            <button class="tab" id="signupTabBtn">Sign Up</button>
        </div>

        <!-- Login form -->
        <form id="loginForm">
            <div class="field">
                <label>Full Name</label>
                <input type="text" id="loginName" class="inp" placeholder="Dr. Chukwu Emeka" autocomplete="name">
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" id="loginPassword" class="inp" placeholder="••••••••" autocomplete="current-password">
            </div>
            <div id="loginError" class="err"></div>
            <button type="submit" class="btn-main" data-label='<i class="fas fa-sign-in-alt" style="margin-right:6px;"></i>Log In'>
                <i class="fas fa-sign-in-alt" style="margin-right:6px;"></i>Log In
            </button>
        </form>

        <!-- Sign Up form -->
        <form id="signupForm" style="display:none;">
            <div class="field">
                <label>Full Name</label>
                <input type="text" id="signupFullName" class="inp" placeholder="Dr. Chukwu Emeka" autocomplete="name">
            </div>
            <div class="field">
                <label>Course / Subject</label>
                <input type="text" id="signupCourse" class="inp" placeholder="e.g. Physics 101">
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" id="signupPassword" class="inp" placeholder="Minimum 4 characters" autocomplete="new-password">
            </div>
            <div class="field">
                <label>Confirm Password</label>
                <input type="password" id="signupConfirm" class="inp" placeholder="Re-enter password">
            </div>
            <div id="signupError" class="err"></div>
            <button type="submit" class="btn-main" data-label='<i class="fas fa-user-plus" style="margin-right:6px;"></i>Create Account'>
                <i class="fas fa-user-plus" style="margin-right:6px;"></i>Create Account
            </button>
        </form>
    </div>

    <a href="../index.php" class="back">
        <i class="fas fa-arrow-left" style="margin-right:4px;"></i>Back to home
    </a>
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
        btn.innerHTML = on
            ? '<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i>Please wait…'
            : btn.dataset.label;
    }

    async function apiPost(ep, body) {
        const r = await fetch(`${API}/${ep}`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        return r.json();
    }

    // Tab switching
    loginTab.addEventListener('click', () => {
        loginTab.classList.add('active');
        signupTab.classList.remove('active');
        loginForm.style.display  = 'block';
        signupForm.style.display = 'none';
        loginError.innerHTML = '';
    });
    signupTab.addEventListener('click', () => {
        signupTab.classList.add('active');
        loginTab.classList.remove('active');
        signupForm.style.display = 'block';
        loginForm.style.display  = 'none';
        signupError.innerHTML = '';
    });

    // Login
    loginForm.addEventListener('submit', async e => {
        e.preventDefault();
        loginError.innerHTML = '';
        const btn  = loginForm.querySelector('.btn-main');
        const name = document.getElementById('loginName').value.trim();
        const pass = document.getElementById('loginPassword').value;
        if (!name || !pass) {
            loginError.innerHTML = '<i class="fas fa-circle-exclamation"></i> Please fill in both fields.';
            return;
        }
        setLoading(btn, true);
        try {
            const d = await apiPost('auth/login.php', { full_name: name, password: pass });
            if (d.success) {
                sessionStorage.setItem('cp_teacher', JSON.stringify(d.teacher));
                window.location.href = 'dashboard.php';
            } else {
                loginError.innerHTML = `<i class="fas fa-circle-exclamation"></i> ${d.message || 'Login failed.'}`;
            }
        } catch(_) {
            loginError.innerHTML = '<i class="fas fa-wifi"></i> Cannot reach server. Is XAMPP running?';
        } finally {
            setLoading(btn, false);
        }
    });

    // Sign Up
    signupForm.addEventListener('submit', async e => {
        e.preventDefault();
        signupError.innerHTML = '';
        const btn    = signupForm.querySelector('.btn-main');
        const name   = document.getElementById('signupFullName').value.trim();
        const course = document.getElementById('signupCourse').value.trim();
        const pass   = document.getElementById('signupPassword').value;
        const conf   = document.getElementById('signupConfirm').value;
        if (!name || !pass) {
            signupError.innerHTML = '<i class="fas fa-circle-exclamation"></i> Name and password are required.'; return;
        }
        if (pass.length < 4) {
            signupError.innerHTML = '<i class="fas fa-circle-exclamation"></i> Password must be at least 4 characters.'; return;
        }
        if (pass !== conf) {
            signupError.innerHTML = '<i class="fas fa-circle-exclamation"></i> Passwords do not match.'; return;
        }
        setLoading(btn, true);
        try {
            const d = await apiPost('auth/register.php', {
                full_name: name, course: course || 'General',
                password: pass, confirm_password: conf,
            });
            if (d.success) {
                sessionStorage.setItem('cp_teacher', JSON.stringify(d.teacher));
                window.location.href = 'dashboard.php';
            } else {
                signupError.innerHTML = `<i class="fas fa-circle-exclamation"></i> ${d.message || 'Registration failed.'}`;
            }
        } catch(_) {
            signupError.innerHTML = '<i class="fas fa-wifi"></i> Cannot reach server. Is XAMPP running?';
        } finally {
            setLoading(btn, false);
        }
    });

    // Auto-redirect if already logged in
    (async () => {
        try {
            const r = await fetch(`${API}/auth/check.php`, { credentials: 'include' });
            const d = await r.json();
            if (d.success) {
                sessionStorage.setItem('cp_teacher', JSON.stringify(d.teacher));
                window.location.href = 'dashboard.php';
            }
        } catch(_) {}
    })();
})();
</script>
</body>
</html>