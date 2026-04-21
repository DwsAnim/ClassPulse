<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassPulse · Academic Engagement Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/theme.css">
    <script src="assets/theme.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; min-height:100vh; background:var(--bg); display:flex; align-items:center; justify-content:center; padding:20px; position:relative; overflow:hidden; color:var(--text); }
        .orb { position:fixed; border-radius:50%; filter:blur(80px); pointer-events:none; }
        .orb1 { width:500px;height:500px;background:radial-gradient(circle,#6C5CE7,transparent);top:-100px;left:-100px;opacity:var(--orb1-op);animation:drift 14s ease-in-out infinite alternate; }
        .orb2 { width:400px;height:400px;background:radial-gradient(circle,#FDCB6E,transparent);bottom:-80px;right:-80px;opacity:var(--orb2-op);animation:drift 11s ease-in-out infinite alternate-reverse; }
        @keyframes drift { 0%{transform:scale(1) translate(0,0)} 100%{transform:scale(1.15) translate(20px,30px)} }

        .container { max-width:440px; width:100%; position:relative; z-index:1; animation:entrance .7s cubic-bezier(.2,.9,.4,1) both; }
        @keyframes entrance { from{opacity:0;transform:translateY(32px) scale(.97)} to{opacity:1;transform:none} }

        /* Theme toggle */
        .theme-toggle { position:fixed; top:16px; right:16px; z-index:200; width:40px;height:40px;border-radius:12px; background:var(--card);border:1px solid var(--border);color:var(--text2);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.95rem;transition:all .2s; }
        .theme-toggle:hover { background:var(--card-hover); color:var(--text); }

        .logo-wrap { text-align:center; margin-bottom:36px; }
        .logo-icon { width:72px;height:72px;border-radius:22px;background:linear-gradient(135deg,#6C5CE7,#FDCB6E);display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:16px;box-shadow:0 12px 40px rgba(108,92,231,.4);animation:float 3s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
        .logo-name { font-size:2.6rem;font-weight:900;letter-spacing:-.03em;background:linear-gradient(135deg,#6C5CE7 0%,#a78bfa 50%,#FDCB6E 100%);-webkit-background-clip:text;background-clip:text;color:transparent; }
        .logo-sub { color:var(--text2);font-size:.9rem;margin-top:6px;font-weight:500; }

        .cards { display:flex;flex-direction:column;gap:14px; }
        .role-card { display:block;text-decoration:none;background:var(--card);backdrop-filter:blur(20px);border:1.5px solid var(--border);border-radius:24px;padding:24px 22px;transition:all .25s cubic-bezier(.2,.9,.4,1);position:relative;overflow:hidden; }
        .role-card::before { content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(108,92,231,.15),rgba(253,203,110,.08));opacity:0;transition:opacity .25s; }
        .role-card:hover { transform:translateY(-4px); border-color:rgba(108,92,231,.5); box-shadow:var(--shadow); }
        .role-card:hover::before { opacity:1; }
        .card-glow { position:absolute;top:-60px;right:-60px;width:160px;height:160px;border-radius:50%;filter:blur(50px);opacity:0;transition:opacity .3s;pointer-events:none; }
        .student-card .card-glow { background:#6C5CE7; }
        .teacher-card .card-glow { background:#FDCB6E; }
        .role-card:hover .card-glow { opacity:.6; }

        .card-inner { display:flex;align-items:center;gap:18px;position:relative;z-index:1; }
        .card-icon { width:54px;height:54px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0; }
        .student-card .card-icon { background:rgba(108,92,231,.15);color:#a78bfa; }
        .teacher-card .card-icon { background:rgba(253,203,110,.15);color:#FDCB6E; }
        .card-text h2 { font-size:1.2rem;font-weight:700;color:var(--text);margin-bottom:4px; }
        .card-text p  { font-size:.85rem;color:var(--text2);margin-bottom:10px; }
        .card-pill { display:inline-flex;align-items:center;gap:5px;font-size:.78rem;font-weight:600;padding:4px 12px;border-radius:40px; }
        .student-card .card-pill { background:rgba(108,92,231,.15);color:#a78bfa; }
        .teacher-card .card-pill { background:rgba(253,203,110,.15);color:#FDCB6E; }
        .card-arrow { margin-left:auto;color:var(--text3);font-size:1rem;transition:all .25s;flex-shrink:0; }
        .role-card:hover .card-arrow { color:#a78bfa;transform:translateX(4px); }
        .teacher-card:hover .card-arrow { color:#FDCB6E; }

        .stats { display:flex;justify-content:center;gap:28px;margin-top:28px;padding-top:20px;border-top:1px solid var(--border); }
        .stat { text-align:center; }
        .stat-num { font-size:1.2rem;font-weight:800;color:var(--text); }
        .stat-lbl { font-size:.72rem;color:var(--text3);text-transform:uppercase;letter-spacing:.06em; }
        .badge-row { display:flex;justify-content:center;gap:8px;margin-top:18px;flex-wrap:wrap; }
        .badge { display:inline-flex;align-items:center;gap:5px;background:var(--card);border:1px solid var(--border);border-radius:40px;padding:5px 12px;font-size:.75rem;color:var(--text2); }
    </style>
</head>
<body>
    <div class="orb orb1"></div>
    <div class="orb orb2"></div>
    <button class="theme-toggle" data-theme-btn title="Toggle theme"></button>

    <div class="container">
        <div class="logo-wrap">
            <div class="logo-icon">🎓</div>
            <div class="logo-name">ClassPulse</div>
            <p class="logo-sub">Real-time classroom engagement — built for Africa</p>
        </div>
        <div class="cards">
            <a href="student/student.php" class="role-card student-card">
                <div class="card-glow"></div>
                <div class="card-inner">
                    <div class="card-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="card-text">
                        <h2>I'm a Student</h2>
                        <p>Join a live quiz with your room code</p>
                        <span class="card-pill"><i class="fas fa-bolt"></i>No login required</span>
                    </div>
                    <i class="fas fa-chevron-right card-arrow"></i>
                </div>
            </a>
            <a href="teacher/auth.php" class="role-card teacher-card">
                <div class="card-glow"></div>
                <div class="card-inner">
                    <div class="card-icon"><i class="fas fa-chalkboard-user"></i></div>
                    <div class="card-text">
                        <h2>I'm a Lecturer</h2>
                        <p>Create sessions, launch quizzes &amp; view AI analytics</p>
                        <span class="card-pill"><i class="fas fa-shield-halved"></i>Secure access</span>
                    </div>
                    <i class="fas fa-chevron-right card-arrow"></i>
                </div>
            </a>
        </div>
        <div class="badge-row">
            <span class="badge"><i class="fas fa-circle" style="color:#00b894;font-size:.5rem;"></i>Real-time</span>
            <span class="badge"><i class="fas fa-mobile-alt"></i>Mobile-first</span>
            <span class="badge"><i class="fas fa-robot"></i>AI-powered</span>
            <span class="badge"><i class="fas fa-wifi"></i>Works on 2G</span>
        </div>
        <div class="stats">
            <div class="stat"><div class="stat-num">100%</div><div class="stat-lbl">Free</div></div>
            <div class="stat"><div class="stat-num">2s</div><div class="stat-lbl">Live sync</div></div>
            <div class="stat"><div class="stat-num">∞</div><div class="stat-lbl">Students</div></div>
        </div>
    </div>
</body>
</html>
