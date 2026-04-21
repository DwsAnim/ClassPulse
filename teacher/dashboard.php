<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassPulse · Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/theme.css">
    <script src="../assets/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        :root { --p:#6C5CE7; --g:#FDCB6E; --green:#00b894; --red:#d63031; --warn:#e17055; }
        *{ margin:0;padding:0;box-sizing:border-box; }
        body{ font-family:'Inter',sans-serif;background:var(--bg);min-height:100vh;color:var(--text);padding:16px 12px 100px; }

        /* Orbs */
        .orb{position:fixed;border-radius:50%;filter:blur(100px);opacity:var(--orb1-op);pointer-events:none;z-index:0;}
        .orb1{width:500px;height:500px;background:radial-gradient(circle,var(--p),transparent);top:-150px;left:-100px;}
        .orb2{width:400px;height:400px;background:radial-gradient(circle,var(--g),transparent);bottom:-100px;right:-80px;}

        /* Layout */
        .shell{max-width:860px;margin:0 auto;position:relative;z-index:1;}

        /* Header */
        .topbar{display:flex;align-items:center;justify-content:space-between;padding:12px 0 20px;}
        .logo{font-size:1.3rem;font-weight:800;background:linear-gradient(135deg,#fff,#a78bfa,var(--g));-webkit-background-clip:text;background-clip:text;color:transparent;}
        .teacher-pill{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:40px;padding:7px 14px;font-size:.82rem;color:rgba(255,255,255,.6);}
        .top-right{display:flex;align-items:center;gap:10px;}
        .sync-pill{display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:40px;padding:6px 12px;font-size:.78rem;color:rgba(255,255,255,.5);}
        .sync-dot{width:7px;height:7px;border-radius:50%;background:#444;transition:background .3s;}
        .sync-dot.live{background:var(--green);animation:blink 1.2s ease infinite;}
        .sync-dot.pulse{background:var(--g);animation:blink .7s ease infinite;}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
        .icon-btn{width:38px;height:38px;border-radius:12px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.6);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;}
        .icon-btn:hover{background:rgba(255,255,255,.12);color:#fff;}

        /* Live banner */
        .live-banner{background:linear-gradient(135deg,rgba(108,92,231,.2),rgba(253,203,110,.1));border:1.5px solid rgba(108,92,231,.35);border-radius:20px;padding:18px 20px;margin-bottom:20px;display:none;animation:slideDown .3s ease;}
        @keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}
        .live-dot{width:10px;height:10px;border-radius:50%;background:#ff6b6b;animation:blink .8s ease infinite;display:inline-block;margin-right:6px;}
        .code-box{font-size:1.6rem;font-weight:800;letter-spacing:5px;color:var(--p);background:rgba(108,92,231,.12);border:2px dashed rgba(108,92,231,.4);border-radius:12px;padding:8px 18px;display:inline-block;}
        .copy-btn{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:10px;color:var(--text2);padding:6px 14px;font-size:.8rem;cursor:pointer;margin-left:10px;transition:all .2s;}
        .copy-btn:hover{background:rgba(255,255,255,.14);color:#fff;}
        #qrBox img{border-radius:8px;}

        /* Card */
        .card{background:var(--card);backdrop-filter:blur(20px);border:1.5px solid var(--card-b);border-radius:20px;padding:22px 20px;margin-bottom:16px;animation:fadeUp .3s ease both;}
        @keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
        .card-title{font-size:1rem;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px;}

        /* Stat grid */
        .stat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px;}
        .stat-box{background:var(--input-bg);border:1px solid var(--border);border-radius:14px;padding:14px 10px;text-align:center;}
        .stat-val{font-size:1.5rem;font-weight:800;background:linear-gradient(135deg,#fff,#a78bfa);-webkit-background-clip:text;background-clip:text;color:transparent;}
        .stat-lbl{font-size:.72rem;color:var(--text3);margin-top:2px;text-transform:uppercase;letter-spacing:.05em;}

        /* Session list */
        .session-row{background:var(--input-bg);border:1px solid var(--border);border-radius:14px;padding:14px 16px;margin-bottom:10px;transition:border-color .2s;}
        .session-row.active-row{border-color:rgba(108,92,231,.5);background:rgba(108,92,231,.08);}
        .badge-live{background:rgba(0,184,148,.2);color:var(--green);border:1px solid rgba(0,184,148,.3);border-radius:30px;padding:3px 10px;font-size:.72rem;font-weight:700;}
        .badge-ended{background:rgba(255,255,255,.07);color:var(--text3);border:1px solid rgba(255,255,255,.1);border-radius:30px;padding:3px 10px;font-size:.72rem;}

        /* Buttons */
        .btn-primary{background:linear-gradient(135deg,var(--p),#a78bfa);border:none;color:#fff;font-family:inherit;font-weight:700;padding:13px 20px;border-radius:14px;cursor:pointer;font-size:.9rem;width:100%;transition:opacity .15s,transform .15s;box-shadow:0 6px 20px rgba(108,92,231,.3);}
        .btn-primary:hover:not(:disabled){opacity:.9;transform:translateY(-1px);}
        .btn-primary:disabled{opacity:.45;cursor:not-allowed;}
        .btn-ghost{background:rgba(255,255,255,.07);border:1.5px solid rgba(255,255,255,.12);border-radius:12px;padding:11px 18px;color:var(--text2);font-family:inherit;font-size:.88rem;cursor:pointer;transition:all .2s;font-weight:500;}
        .btn-ghost:hover{background:rgba(255,255,255,.12);color:#fff;}
        .btn-danger{background:rgba(214,48,49,.15);border:1.5px solid rgba(214,48,49,.3);border-radius:12px;padding:11px 18px;color:#ff7675;font-family:inherit;font-size:.88rem;cursor:pointer;transition:all .2s;}
        .btn-danger:hover{background:rgba(214,48,49,.25);}
        .btn-row{display:flex;gap:10px;flex-wrap:wrap;}

        /* Form inputs */
        .field{margin-bottom:14px;}
        .field label{display:block;font-size:.75rem;font-weight:600;color:var(--text3);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;}
        .inp{width:100%;background:var(--input-bg);border:1.5px solid var(--input-b);border-radius:12px;padding:12px 16px;color:var(--text);font-family:inherit;font-size:.92rem;outline:none;transition:border-color .2s,box-shadow .2s;}
        .inp:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(108,92,231,.2);}
        .inp::placeholder{color:rgba(255,255,255,.22);}
        select.inp{cursor:pointer;}

        /* Question builder */
        .q-block{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:14px;padding:16px;margin-bottom:12px;}
        .q-block-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
        .q-num-label{font-size:.82rem;font-weight:700;color:var(--p);}
        .del-btn{width:30px;height:30px;border-radius:8px;background:rgba(214,48,49,.12);border:1px solid rgba(214,48,49,.2);color:#ff7675;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.8rem;}
        .opt-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;}
        .hint-box{background:rgba(108,92,231,.1);border:1px solid rgba(108,92,231,.2);border-radius:10px;padding:10px 14px;font-size:.78rem;color:rgba(255,255,255,.5);margin-bottom:10px;}
        .row2{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
        .err-msg{color:#ff7675;font-size:.82rem;margin:6px 0;}

        /* Live monitor */
        .progress-track{height:7px;background:rgba(255,255,255,.08);border-radius:10px;overflow:hidden;margin:8px 0 14px;}
        .progress-fill{height:7px;border-radius:10px;background:linear-gradient(90deg,var(--p),var(--g));transition:width .4s;}
        .student-row{display:flex;justify-content:space-between;align-items:center;padding:9px 12px;border-bottom:1px solid rgba(255,255,255,.05);font-size:.85rem;}
        .student-row:last-child{border-bottom:none;}
        .dot-ans{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px;}
        .dot-ans.done{background:var(--green);}
        .dot-ans.wait{background:var(--g);}
        .cf-row{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
        .cf-label{font-size:.82rem;color:rgba(255,255,255,.5);width:60px;flex-shrink:0;}
        .cf-track{flex:1;height:7px;background:rgba(255,255,255,.08);border-radius:10px;overflow:hidden;}
        .cf-fill{height:7px;border-radius:10px;transition:width .4s;}
        .cf-num{font-size:.8rem;color:rgba(255,255,255,.5);width:24px;text-align:right;flex-shrink:0;}
        .current-q-box{background:rgba(108,92,231,.1);border:1px solid rgba(108,92,231,.25);border-radius:12px;padding:14px 16px;}
        .autopilot-badge{background:rgba(0,184,148,.15);color:var(--green);border:1px solid rgba(0,184,148,.25);border-radius:30px;padding:3px 12px;font-size:.72rem;font-weight:700;}

        /* Analytics */
        .analytics-stat{background:var(--input-bg);border:1px solid var(--border);border-radius:12px;padding:12px;text-align:center;}
        .analytics-stat .val{font-size:1.3rem;font-weight:800;}
        .analytics-stat .lbl{font-size:.7rem;color:var(--text3);text-transform:uppercase;letter-spacing:.05em;margin-top:2px;}
        .chart-wrap{background:rgba(0,0,0,.2);border-radius:14px;padding:14px;margin-bottom:14px;}
        .student-card{display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,.04);border-radius:12px;padding:12px 14px;margin-bottom:8px;border-left:3px solid;}
        .q-analysis{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:14px;margin-bottom:10px;}
        .diff-badge{border-radius:30px;padding:2px 10px;font-size:.72rem;font-weight:700;}
        .diff-easy{background:rgba(0,184,148,.15);color:var(--green);}
        .diff-medium{background:rgba(253,203,110,.15);color:var(--g);}
        .diff-hard{background:rgba(214,48,49,.15);color:#ff7675;}
        details summary{cursor:pointer;font-weight:600;font-size:.88rem;color:var(--text2);padding:8px 0;list-style:none;}
        details summary::-webkit-details-marker{display:none;}
        details summary::before{content:'▶ ';font-size:.7rem;color:var(--p);}
        details[open] summary::before{content:'▼ ';}

        /* Bottom nav */
        .bottom-nav{position:fixed;bottom:0;left:0;right:0;background:var(--nav-bg);backdrop-filter:blur(20px);border-top:1px solid var(--nav-b);display:flex;justify-content:space-around;padding:10px 0 14px;z-index:100;}
        .nav-item{display:flex;flex-direction:column;align-items:center;gap:4px;cursor:pointer;padding:4px 20px;border-radius:12px;transition:all .2s;border:none;background:transparent;color:var(--text3);font-family:inherit;}
        .nav-item:hover{color:var(--text2);}
        .nav-item.active{color:var(--p);}
        .nav-item i{font-size:1.1rem;}
        .nav-item span{font-size:.67rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;}
    </style>
</head>
<body>
<div class="orb orb1"></div>
<div class="orb orb2"></div>

<div class="shell">
    <!-- Top bar -->
    <div class="topbar">
        <div class="logo"><i class="fas fa-chalkboard-user me-1"></i>ClassPulse</div>
        <div class="top-right">
            <div class="sync-pill"><div class="sync-dot" id="syncDot"></div><span id="syncTxt">Ready</span></div>
            <span id="teacherPill" class="teacher-pill"><i class="fas fa-user-tie"></i><span id="tName">…</span></span>
            <button class="icon-btn" data-theme-btn title="Toggle theme"></button>
            <button class="icon-btn" id="logoutBtn" title="Log out"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    </div>

    <!-- Live session banner -->
    <div class="live-banner" id="liveBanner">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="font-size:.75rem;color:rgba(255,255,255,.5);margin-bottom:4px;">
                    <span class="live-dot"></span>LIVE SESSION
                </div>
                <div id="bannerTitle" style="font-weight:800;font-size:1.05rem;margin-bottom:8px;"></div>
                <div id="bannerCode" class="code-box"></div>
                <button class="copy-btn" id="copyBtn"><i class="fas fa-copy me-1"></i>Copy</button>
            </div>
            <div id="qrBox" style="width:90px;height:90px;"></div>
        </div>
    </div>

    <!-- Main view -->
    <div id="mainView"></div>
</div>

<!-- Bottom nav -->
<nav class="bottom-nav">
    <button class="nav-item active" data-view="overview"><i class="fas fa-house"></i><span>Overview</span></button>
    <button class="nav-item" data-view="creator"><i class="fas fa-circle-plus"></i><span>Create</span></button>
    <button class="nav-item" data-view="live"><i class="fas fa-signal"></i><span>Live</span></button>
    <button class="nav-item" data-view="analytics"><i class="fas fa-chart-bar"></i><span>Analytics</span></button>
</nav>

<script>
(function(){
'use strict';
const _base = window.location.pathname.replace(/\/teacher\/.*$/,'');
const API   = _base + '/api';

let teacher = null, activeSession = null, charts = {}, pollTimer = null, currentView = 'overview';
let draftQuestions = [];
const VIEW = document.getElementById('mainView');

// ── Sync indicator ─────────────────────────────────────────────
function setSync(s){ // 'pulse'|'live'|'off'
    const d=document.getElementById('syncDot'), t=document.getElementById('syncTxt');
    d.className='sync-dot'+(s==='live'?' live':s==='pulse'?' pulse':'');
    t.textContent=s==='live'?'Synced':s==='pulse'?'Updating…':'Ready';
}

// ── API ────────────────────────────────────────────────────────
const post=(ep,b)=>fetch(`${API}/${ep}`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(b)}).then(r=>r.json());
const get=(ep)=>fetch(`${API}/${ep}`,{credentials:'include'}).then(r=>r.json());

// ── Auth ───────────────────────────────────────────────────────
async function init(){
    try{
        const d=await get('auth/check.php');
        if(!d.success){location.href='auth.php';return;}
        teacher=d.teacher; sessionStorage.setItem('cp_teacher',JSON.stringify(teacher));
    }catch(_){
        teacher=JSON.parse(sessionStorage.getItem('cp_teacher')||'null');
        if(!teacher){location.href='auth.php';return;}
    }
    document.getElementById('tName').textContent=`${teacher.full_name} · ${teacher.course||'General'}`;
    await refreshActive();
    loadView('overview');
    startPoll();
}

async function refreshActive(){
    try{ const d=await get('sessions/active.php'); activeSession=d.success?d.session:null; }
    catch(_){ activeSession=null; }
    renderBanner();
}

function renderBanner(){
    const b=document.getElementById('liveBanner');
    if(activeSession){
        b.style.display='block';
        document.getElementById('bannerTitle').textContent=activeSession.title;
        document.getElementById('bannerCode').textContent=activeSession.room_code;
        const qrBox=document.getElementById('qrBox');
        qrBox.innerHTML='';
        try{ new QRCode(qrBox,{text:`${location.origin}${_base}/student/student.php`,width:88,height:88}); }catch(_){}
    }else{ b.style.display='none'; }
}

// ── Polling ────────────────────────────────────────────────────
function startPoll(){
    clearInterval(pollTimer);
    pollTimer=setInterval(async()=>{
        if(currentView==='live'&&activeSession){ setSync('pulse'); await updateLiveData(); setSync('live'); }
    },3000);
}

// ── View router ────────────────────────────────────────────────
function loadView(v){
    currentView=v;
    Object.values(charts).forEach(c=>c&&c.destroy()); charts={};
    document.querySelectorAll('.nav-item').forEach(b=>b.classList.toggle('active',b.dataset.view===v));
    switch(v){
        case 'overview':  renderOverview(); break;
        case 'creator':   renderCreator();  break;
        case 'live':      renderLive();     break;
        case 'analytics': renderAnalytics();break;
    }
}

// ── OVERVIEW ───────────────────────────────────────────────────
async function renderOverview(){
    VIEW.innerHTML=loading();
    const d=await get('sessions/list.php');
    const sessions=d.sessions||[];
    const totalStudents=sessions.reduce((s,x)=>s+(+x.student_count||0),0);
    const totalSessions=sessions.length;
    const liveSessions=sessions.filter(s=>s.is_active).length;

    VIEW.innerHTML=`
    <div class="card">
        <div class="card-title"><i class="fas fa-layer-group" style="color:var(--p)"></i>Dashboard</div>
        <div class="stat-grid">
            <div class="stat-box"><div class="stat-val">${totalSessions}</div><div class="stat-lbl">Sessions</div></div>
            <div class="stat-box"><div class="stat-val">${liveSessions}</div><div class="stat-lbl">Live Now</div></div>
            <div class="stat-box"><div class="stat-val">${totalStudents}</div><div class="stat-lbl">Students</div></div>
        </div>
        ${activeSession?`<button class="btn-primary" data-view="live" style="margin-bottom:14px;"><i class="fas fa-signal me-2"></i>Go to Live Session</button>`:''}
        <div class="card-title" style="margin-bottom:10px;"><i class="fas fa-clock-rotate-left" style="color:var(--g)"></i>Recent Sessions</div>
        ${sessions.length===0
            ?'<p style="color:rgba(255,255,255,.35);font-size:.9rem;text-align:center;padding:20px;">No sessions yet. Hit Create to start!</p>'
            :sessions.map(s=>`
            <div class="session-row ${s.is_active?'active-row':''}">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                    <span style="font-weight:600;font-size:.9rem;">${esc(s.title)}</span>
                    ${s.is_active?`<span class="badge-live">🔴 Live</span>`:`<span class="badge-ended">Ended</span>`}
                </div>
                <div style="font-size:.77rem;color:var(--text3);">
                    Code: <strong style="color:var(--text2);letter-spacing:2px;">${s.room_code}</strong>
                    &nbsp;·&nbsp;${s.student_count} student${s.student_count!=1?'s':''}
                    &nbsp;·&nbsp;${fmtDate(s.created_at)}
                </div>
                ${!s.is_active?`<button class="btn-ghost" style="margin-top:10px;padding:7px 14px;font-size:.78rem;" onclick="pickAnalytics(${s.id})"><i class="fas fa-chart-bar me-1"></i>View Analytics</button>`:''}
            </div>`).join('')}
    </div>`;
}
window.pickAnalytics=id=>{ window._analyticsId=id; loadView('analytics'); };

// ── CREATOR ────────────────────────────────────────────────────
function renderCreator(){
    draftQuestions=[];
    VIEW.innerHTML=`
    <div class="card">
        <div class="card-title"><i class="fas fa-circle-plus" style="color:var(--p)"></i>New Session</div>
        <div class="field"><label>Session Title</label><input id="titleInp" class="inp" placeholder="e.g. Biology 201 — Cell Division"></div>
        <div class="card-title" style="margin-bottom:10px;"><i class="fas fa-list-check" style="color:var(--g)"></i>Questions</div>
        <div id="qContainer"></div>
        <button class="btn-ghost" id="addQBtn" style="width:100%;margin-bottom:14px;"><i class="fas fa-plus me-2"></i>Add Question</button>
        <div id="createErr" class="err-msg"></div>
        <button class="btn-primary" id="launchBtn"><i class="fas fa-rocket me-2"></i>Save & Launch Session</button>
    </div>`;
    document.getElementById('addQBtn').addEventListener('click', addQ);
    document.getElementById('launchBtn').addEventListener('click', doLaunch);
    addQ();
}

function addQ(){
    const idx=draftQuestions.length;
    draftQuestions.push({type:'mcq',timer:'30',correct:'A',option_a:'True',option_b:'False',option_c:'',option_d:''});
    const c=document.getElementById('qContainer');
    const div=document.createElement('div');
    div.className='q-block'; div.id=`qb${idx}`;
    div.innerHTML=`
    <div class="q-block-header">
        <span class="q-num-label">Q${idx+1}</span>
        <button class="del-btn" onclick="delQ(${idx})"><i class="fas fa-trash"></i></button>
    </div>
    <div class="field"><label>Question</label><input class="inp" placeholder="Type your question…" data-f="question" data-i="${idx}"></div>
    <div class="field"><label>Type</label>
        <select class="inp q-type" data-f="type" data-i="${idx}">
            <option value="mcq">Multiple Choice (MCQ)</option>
            <option value="true_false">True / False</option>
            <option value="math">Math / Formula</option>
        </select>
    </div>
    <div class="q-mcq-section">
        <div class="opt-grid">
            <div class="field" style="margin:0"><label>Option A</label><input class="inp" placeholder="Option A" data-f="option_a" data-i="${idx}"></div>
            <div class="field" style="margin:0"><label>Option B</label><input class="inp" placeholder="Option B" data-f="option_b" data-i="${idx}"></div>
            <div class="field" style="margin:0"><label>Option C</label><input class="inp" placeholder="Option C" data-f="option_c" data-i="${idx}"></div>
            <div class="field" style="margin:0"><label>Option D</label><input class="inp" placeholder="Option D" data-f="option_d" data-i="${idx}"></div>
        </div>
        <div class="row2">
            <div class="field" style="margin:0"><label>Correct Answer</label>
                <select class="inp q-correct" data-f="correct" data-i="${idx}">
                    <option value="A">A is correct</option><option value="B">B is correct</option>
                    <option value="C">C is correct</option><option value="D">D is correct</option>
                </select>
            </div>
            <div class="field" style="margin:0"><label>Timer</label>
                <select class="inp" data-f="timer" data-i="${idx}">
                    <option value="15">15 seconds</option><option value="30" selected>30 seconds</option><option value="60">60 seconds</option>
                </select>
            </div>
        </div>
    </div>
    <div class="q-tf-section" style="display:none;">
        <div class="row2" style="margin-bottom:10px;">
            <input class="inp" value="True" readonly data-f="option_a" data-i="${idx}">
            <input class="inp" value="False" readonly data-f="option_b" data-i="${idx}">
        </div>
        <div class="row2">
            <div class="field" style="margin:0"><label>Correct</label>
                <select class="inp q-tf-correct" data-f="correct" data-i="${idx}">
                    <option value="A">True is correct</option><option value="B">False is correct</option>
                </select>
            </div>
            <div class="field" style="margin:0"><label>Timer</label>
                <select class="inp" data-f="timer" data-i="${idx}">
                    <option value="15">15 seconds</option><option value="30" selected>30 seconds</option><option value="60">60 seconds</option>
                </select>
            </div>
        </div>
    </div>
    <div class="q-math-hint" style="display:none;">
        <div class="hint-box"><i class="fas fa-info-circle me-1"></i>For Math questions, type your formula in Option A using KaTeX: e.g. <code>x = \\frac{-b \\pm \\sqrt{b^2-4ac}}{2a}</code></div>
    </div>`;
    c.appendChild(div);

    // Type change logic
    div.querySelector('.q-type').addEventListener('change', e=>{
        const t=e.target.value;
        draftQuestions[idx].type=t;
        div.querySelector('.q-mcq-section').style.display=(t==='mcq'||t==='math')?'':'none';
        div.querySelector('.q-tf-section').style.display=t==='true_false'?'':'none';
        div.querySelector('.q-math-hint').style.display=t==='math'?'':'none';
        if(t==='true_false'){ draftQuestions[idx].option_a='True';draftQuestions[idx].option_b='False';draftQuestions[idx].option_c='';draftQuestions[idx].option_d=''; }
    });

    // Sync all inputs
    div.querySelectorAll('[data-f]').forEach(el=>{
        const sync=()=>{ draftQuestions[parseInt(el.dataset.i)][el.dataset.f]=el.value; };
        el.addEventListener('input',sync); el.addEventListener('change',sync);
    });
}
window.delQ=idx=>{ const el=document.getElementById(`qb${idx}`); if(el)el.remove(); draftQuestions[idx]=null; };

async function doLaunch(){
    const title=document.getElementById('titleInp').value.trim();
    const errEl=document.getElementById('createErr');
    const btn=document.getElementById('launchBtn');
    errEl.textContent='';
    if(!title){errEl.textContent='Session title is required.';return;}
    const qs=draftQuestions.filter(q=>q&&q.question&&q.question.trim());
    if(!qs.length){errEl.textContent='Add at least one question.';return;}
    btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin me-2"></i>Creating…';
    try{
        const s=await post('sessions/create.php',{title});
        if(!s.success){errEl.textContent=s.message;btn.disabled=false;btn.innerHTML='<i class="fas fa-rocket me-2"></i>Save & Launch Session';return;}
        const sv=await post('questions/save.php',{session_id:s.session.id,questions:qs.map(q=>({
            question:q.question||'',option_a:q.option_a||'',option_b:q.option_b||'',
            option_c:q.option_c||'',option_d:q.option_d||'',correct:q.correct||'A',
            type:q.type||'mcq',timer:parseInt(q.timer||30)
        }))});
        if(!sv.success){errEl.textContent=sv.message;btn.disabled=false;btn.innerHTML='<i class="fas fa-rocket me-2"></i>Save & Launch Session';return;}
        // Launch Q1
        const ql=await get(`questions/list.php?session_id=${s.session.id}`);
        if(ql.questions&&ql.questions.length) await post('questions/launch.php',{question_id:ql.questions[0].id});
        await refreshActive(); loadView('live');
    }catch(err){
        errEl.textContent='Server error: '+(err.message||'Is XAMPP running?');
        btn.disabled=false; btn.innerHTML='<i class="fas fa-rocket me-2"></i>Save & Launch Session';
    }
}

// ── LIVE ───────────────────────────────────────────────────────
async function renderLive(){
    if(!activeSession){
        VIEW.innerHTML=`<div class="card" style="text-align:center;padding:40px 20px;">
            <i class="fas fa-signal" style="font-size:2.5rem;color:rgba(255,255,255,.2);margin-bottom:16px;"></i>
            <p style="color:rgba(255,255,255,.5);margin-bottom:16px;">No active session running.</p>
            <button class="btn-primary" style="max-width:200px;margin:0 auto;" data-view="creator">Create Session</button>
        </div>`;
        return;
    }
    VIEW.innerHTML=`
    <div class="card">
        <div class="card-title"><i class="fas fa-signal" style="color:var(--green)"></i>Live Monitor
            <span class="autopilot-badge" style="margin-left:auto;">🤖 Auto-pilot</span>
        </div>

        <!-- Student count + progress -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="font-size:.85rem;color:rgba(255,255,255,.5);">Students answered</span>
            <span id="progLabel" style="font-size:.85rem;font-weight:700;">0/0</span>
        </div>
        <div class="progress-track"><div class="progress-fill" id="progFill" style="width:0%"></div></div>

        <!-- Live bar chart -->
        <div class="chart-wrap"><canvas id="liveChart" height="140"></canvas></div>

        <!-- Current question -->
        <div class="card-title"><i class="fas fa-circle-question" style="color:var(--p)"></i>Current Question</div>
        <div class="current-q-box" id="curQBox">
            <p style="color:var(--text3);font-size:.85rem;">Loading…</p>
        </div>

        <!-- Confusion meter -->
        <div class="card-title" style="margin-top:16px;"><i class="fas fa-fire" style="color:var(--warn)"></i>Confusion Meter</div>
        <div class="cf-row"><span class="cf-label">Got it 👍</span><div class="cf-track"><div class="cf-fill" id="cfGot" style="width:0%;background:var(--green);"></div></div><span class="cf-num" id="cfGotN">0</span></div>
        <div class="cf-row"><span class="cf-label">Unsure 🤔</span><div class="cf-track"><div class="cf-fill" id="cfUnsure" style="width:0%;background:var(--g);"></div></div><span class="cf-num" id="cfUnsureN">0</span></div>
        <div class="cf-row"><span class="cf-label">Lost 😕</span><div class="cf-track"><div class="cf-fill" id="cfLost" style="width:0%;background:var(--red);"></div></div><span class="cf-num" id="cfLostN">0</span></div>

        <!-- Student list -->
        <div class="card-title" style="margin-top:16px;"><i class="fas fa-users" style="color:var(--p)"></i>Students <span id="stuCount" style="font-weight:400;font-size:.8rem;color:var(--text3);">(0)</span></div>
        <div id="stuList" style="max-height:220px;overflow-y:auto;border-radius:12px;background:rgba(0,0,0,.15);"></div>

        <!-- Actions -->
        <div class="btn-row" style="margin-top:16px;">
            <button class="btn-danger flex" id="endBtn" style="flex:1;"><i class="fas fa-stop me-2"></i>End Session</button>
            <button class="btn-ghost" data-view="analytics" style="flex:1;"><i class="fas fa-chart-bar me-2"></i>Analytics</button>
        </div>
    </div>`;

    setTimeout(initLiveChart,40);
    await updateLiveData();
    document.getElementById('endBtn').addEventListener('click',endSession);
}

function initLiveChart(){
    const ctx=document.getElementById('liveChart')?.getContext('2d');
    if(!ctx)return;
    Object.values(charts).forEach(c=>c&&c.destroy()); charts={};
    charts.live=new Chart(ctx,{
        type:'bar',
        data:{labels:['A','B','C','D'],datasets:[{label:'Responses',data:[0,0,0,0],
            backgroundColor:['rgba(108,92,231,.7)','rgba(253,203,110,.7)','rgba(0,184,148,.7)','rgba(225,112,85,.7)'],
            borderRadius:8,borderSkipped:false}]},
        options:{plugins:{legend:{display:false}},scales:{
            x:{grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'rgba(255,255,255,.5)'}},
            y:{beginAtZero:true,ticks:{stepSize:1,color:'rgba(255,255,255,.5)'},grid:{color:'rgba(255,255,255,.05)'}}
        }}
    });
}

async function updateLiveData(){
    if(!activeSession)return;
    const [sData,qData]=await Promise.all([
        get(`students/list.php?room_code=${activeSession.room_code}`),
        get(`questions/list.php?session_id=${activeSession.id}`)
    ]);

    // Students
    if(sData.success){
        const sts=sData.students, ans=sts.filter(s=>s.answered).length, tot=sts.length;
        const el=id=>document.getElementById(id);
        if(el('progLabel')) el('progLabel').textContent=`${ans}/${tot}`;
        if(el('progFill'))  el('progFill').style.width=tot?(ans/tot*100)+'%':'0%';
        if(el('stuCount'))  el('stuCount').textContent=`(${tot})`;
        if(el('stuList'))   el('stuList').innerHTML=sts.length===0
            ?'<p style="text-align:center;color:rgba(255,255,255,.3);padding:16px;font-size:.85rem;">No students joined yet</p>'
            :sts.map(s=>`<div class="student-row">
                <span><span class="dot-ans ${s.answered?'done':'wait'}"></span>${esc(s.name)}</span>
                <span style="font-size:.78rem;color:${s.answered?'var(--green)':'rgba(255,255,255,.35)'};">${s.answered?'✓ Done':'⏳ Waiting'}</span>
            </div>`).join('');
    }

    // Current launched question
    const allQs=qData.questions||[];
    const launched=allQs.find(q=>parseInt(q.is_launched)===1);
    const lIdx=launched?allQs.indexOf(launched)+1:0;
    const el=id=>document.getElementById(id);

    if(el('curQBox')){
        el('curQBox').innerHTML=launched
            ?`<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
                <span style="font-weight:600;font-size:.9rem;line-height:1.4;">${esc(launched.question)}</span>
                <span style="flex-shrink:0;background:rgba(0,184,148,.2);color:var(--green);border-radius:30px;padding:2px 10px;font-size:.72rem;font-weight:700;">Q${lIdx}/${allQs.length}</span>
              </div>
              <div style="margin-top:6px;font-size:.75rem;color:var(--text3);">${launched.type.toUpperCase()} · ${launched.timer}s</div>`
            :'<p style="color:rgba(255,255,255,.35);font-size:.85rem;">No question active yet</p>';
    }

    // Answer distribution + confusion
    if(launched&&charts.live){
        const rData=await get(`answers/results.php?question_id=${launched.id}`);
        if(rData.success){
            const d=rData.distribution;
            charts.live.data.datasets[0].data=[d.A,d.B,d.C,d.D];
            charts.live.update();
            const cf=rData.confusion, tot=(cf.got_it+cf.unsure+cf.lost)||1;
            const pct=v=>Math.round(v/tot*100)+'%';
            if(el('cfGot'))    {el('cfGot').style.width=pct(cf.got_it);   el('cfGotN').textContent=cf.got_it;}
            if(el('cfUnsure')) {el('cfUnsure').style.width=pct(cf.unsure);el('cfUnsureN').textContent=cf.unsure;}
            if(el('cfLost'))   {el('cfLost').style.width=pct(cf.lost);    el('cfLostN').textContent=cf.lost;}
        }
    }
}

async function endSession(){
    if(!confirm('End this session? Students will see the results screen.'))return;
    const d=await post('sessions/end.php',{session_id:activeSession.id});
    if(d.success){ activeSession=null; renderBanner(); loadView('overview'); }
    else alert(d.message);
}

// ── ANALYTICS ──────────────────────────────────────────────────
async function renderAnalytics(){
    VIEW.innerHTML=loading('Loading analytics report…');
    let sessionId=window._analyticsId||(activeSession?activeSession.id:null);
    window._analyticsId=null;
    if(!sessionId){
        const ls=await get('sessions/list.php');
        const ended=(ls.sessions||[]).find(s=>!s.is_active);
        if(!ended){
            VIEW.innerHTML=`<div class="card" style="text-align:center;padding:40px 20px;">
                <div style="font-size:3rem;margin-bottom:12px;">📋</div>
                <p style="color:var(--text2);font-weight:600;margin-bottom:6px;">No completed sessions yet</p>
                <p style="color:var(--text3);font-size:.88rem;">End a live session to generate an analytics report.</p>
                <button class="btn-primary" style="max-width:200px;margin:20px auto 0;" data-view="creator">Create Session</button>
            </div>`;
            return;
        }
        sessionId=ended.id;
    }
    const d=await get(`analytics/report.php?session_id=${sessionId}`);
    if(!d.success){VIEW.innerHTML=`<div class="card"><p style="color:#ff7675;">${d.message}</p></div>`;return;}

    const {session,overview,students,questions,confusion,struggling,reteach_list}=d;

    // Also load session list for switcher
    const ls=await get('sessions/list.php');
    const allSessions=(ls.sessions||[]).filter(s=>!s.is_active);

    const tierColor=t=>t==='excelling'?'var(--green)':t==='average'?'var(--g)':'var(--red)';
    const tierIcon =t=>t==='excelling'?'🟢':t==='average'?'🟡':'🔴';
    const tierLabel=t=>t==='excelling'?'Excelling':t==='average'?'Average':'Needs Help';
    const diffClass=t=>t==='easy'?'diff-easy':t==='medium'?'diff-medium':'diff-hard';
    const diffIcon =t=>t==='easy'?'✅':t==='medium'?'⚠️':'🔴';

    // Confusion totals
    const cfTotal = (confusion.got_it||0)+(confusion.unsure||0)+(confusion.lost||0);
    const cfPct   = v => cfTotal>0 ? Math.round(v/cfTotal*100) : 0;

    // Class health score: weighted avg of score + confusion sentiment
    const confusionScore = cfTotal>0 ? Math.round(((confusion.got_it*100)+(confusion.unsure*50)+(confusion.lost*0))/cfTotal) : null;

    // Grade label
    const gradeLabel = s => s>=85?'A':s>=70?'B':s>=55?'C':s>=40?'D':'F';
    const gradeColor = s => s>=85?'var(--green)':s>=70?'#74b9ff':s>=55?'var(--g)':s>=40?'var(--warn)':'var(--red)';

    VIEW.innerHTML=`
    <!-- Session selector (if multiple sessions) -->
    ${allSessions.length>1?`
    <div class="card" style="padding:14px 18px;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span style="font-size:.8rem;color:var(--text3);white-space:nowrap;">Viewing report for:</span>
            <select class="inp" id="sessionSwitcher" style="flex:1;min-width:180px;padding:8px 12px;font-size:.85rem;">
                ${allSessions.map(s=>`<option value="${s.id}" ${s.id==sessionId?'selected':''}>${esc(s.title)} — ${fmtDate(s.created_at)}</option>`).join('')}
            </select>
        </div>
    </div>`:''}

    <!-- Header -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
            <div>
                <div class="card-title" style="margin-bottom:4px;"><i class="fas fa-chart-bar" style="color:var(--p)"></i>${esc(session.title)}</div>
                <p style="font-size:.8rem;color:var(--text3);">
                    ${fmtDate(session.created_at)} &nbsp;·&nbsp;
                    Room: <strong style="letter-spacing:2px;color:var(--text2);">${session.room_code}</strong>
                    ${session.ended_at?` &nbsp;·&nbsp; Ended ${fmtDate(session.ended_at)}`:''}
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="text-align:center;background:var(--input-bg);border:1.5px solid var(--border);border-radius:14px;padding:10px 18px;">
                    <div style="font-size:1.8rem;font-weight:900;color:${gradeColor(overview.avg_score)};">${gradeLabel(overview.avg_score)}</div>
                    <div style="font-size:.68rem;color:var(--text3);text-transform:uppercase;letter-spacing:.05em;">Class Grade</div>
                </div>
            </div>
        </div>

        <!-- 6 key stats -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:8px;">
            <div class="analytics-stat">
                <div class="val">${overview.total_students}</div>
                <div class="lbl">Students</div>
            </div>
            <div class="analytics-stat">
                <div class="val">${overview.total_questions}</div>
                <div class="lbl">Questions</div>
            </div>
            <div class="analytics-stat">
                <div class="val" style="color:${gradeColor(overview.avg_score)};">${overview.avg_score}%</div>
                <div class="lbl">Avg Score</div>
            </div>
            <div class="analytics-stat">
                <div class="val" style="color:var(--green);">${overview.top_score}%</div>
                <div class="lbl">Top Score</div>
            </div>
            <div class="analytics-stat">
                <div class="val" style="color:var(--red);">${overview.bottom_score}%</div>
                <div class="lbl">Lowest</div>
            </div>
            <div class="analytics-stat">
                <div class="val">${overview.completed_students}/${overview.total_students}</div>
                <div class="lbl">Completed</div>
            </div>
        </div>

        <!-- Performance tiers summary -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:8px;">
            ${['excelling','average','needs_attention'].map(t=>{
                const cnt=students.filter(s=>s.tier===t).length;
                const pct=overview.total_students>0?Math.round(cnt/overview.total_students*100):0;
                return `<div style="background:var(--input-bg);border:1px solid var(--border);border-radius:12px;padding:10px;text-align:center;">
                    <div style="font-size:1.3rem;font-weight:800;color:${tierColor(t)};">${cnt}</div>
                    <div style="font-size:.7rem;color:var(--text3);margin-top:2px;">${tierLabel(t)}</div>
                    <div style="font-size:.68rem;color:var(--text3);">${pct}% of class</div>
                </div>`;
            }).join('')}
        </div>
    </div>

    <!-- Score distribution chart -->
    <div class="card">
        <div class="card-title"><i class="fas fa-bar-chart" style="color:var(--p)"></i>Score Distribution</div>
        <div class="chart-wrap"><canvas id="distChart" height="120"></canvas></div>
    </div>

    <!-- Confusion summary — fixed and rich -->
    <div class="card">
        <div class="card-title"><i class="fas fa-fire" style="color:var(--warn)"></i>Confusion Meter
            <span style="margin-left:auto;font-size:.75rem;color:var(--text3);font-weight:400;">${cfTotal} responses total</span>
        </div>
        ${cfTotal===0?`
        <p style="color:var(--text3);font-size:.88rem;text-align:center;padding:12px;">
            No confusion data yet — students tap the feeling buttons after each answer.
        </p>`:`
        <!-- Got it -->
        <div style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:.85rem;font-weight:600;">👍 Got it</span>
                <span style="font-size:.85rem;font-weight:700;color:var(--green);">${confusion.got_it} &nbsp;<span style="color:var(--text3);font-weight:400;">(${cfPct(confusion.got_it)}%)</span></span>
            </div>
            <div style="height:10px;background:var(--input-bg);border-radius:10px;overflow:hidden;">
                <div style="height:10px;width:${cfPct(confusion.got_it)}%;background:var(--green);border-radius:10px;transition:width .5s;"></div>
            </div>
        </div>
        <!-- Unsure -->
        <div style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:.85rem;font-weight:600;">🤔 Unsure</span>
                <span style="font-size:.85rem;font-weight:700;color:var(--g);">${confusion.unsure} &nbsp;<span style="color:var(--text3);font-weight:400;">(${cfPct(confusion.unsure)}%)</span></span>
            </div>
            <div style="height:10px;background:var(--input-bg);border-radius:10px;overflow:hidden;">
                <div style="height:10px;width:${cfPct(confusion.unsure)}%;background:var(--g);border-radius:10px;transition:width .5s;"></div>
            </div>
        </div>
        <!-- Lost -->
        <div style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:.85rem;font-weight:600;">😕 Lost</span>
                <span style="font-size:.85rem;font-weight:700;color:var(--red);">${confusion.lost} &nbsp;<span style="color:var(--text3);font-weight:400;">(${cfPct(confusion.lost)}%)</span></span>
            </div>
            <div style="height:10px;background:var(--input-bg);border-radius:10px;overflow:hidden;">
                <div style="height:10px;width:${cfPct(confusion.lost)}%;background:var(--red);border-radius:10px;transition:width .5s;"></div>
            </div>
        </div>
        <!-- Insight -->
        <div style="background:var(--input-bg);border:1px solid var(--border);border-radius:12px;padding:12px 14px;margin-top:4px;">
            <span style="font-size:.82rem;color:var(--text2);">
                ${confusion.lost>confusion.got_it
                    ? '⚠️ <strong>High confusion detected.</strong> More than half the class felt lost — consider revising this topic.'
                    : confusion.unsure>confusion.got_it
                    ? '📌 <strong>Mixed understanding.</strong> Many students were unsure — a quick recap may help.'
                    : '✅ <strong>Good comprehension.</strong> Most students felt confident after answering.'}
            </span>
        </div>`}
    </div>

    <!-- Re-teach priority -->
    ${reteach_list.length?`
    <div class="card">
        <div class="card-title"><i class="fas fa-rotate-left" style="color:var(--warn)"></i>Re-teach Priority
            <span style="margin-left:auto;font-size:.75rem;color:var(--text3);font-weight:400;">${reteach_list.length} topic${reteach_list.length>1?'s':''} flagged</span>
        </div>
        <p style="font-size:.82rem;color:var(--text3);margin-bottom:12px;">These questions had less than 40% correct — revisit before the next class.</p>
        ${reteach_list.map((q,i)=>`
        <div class="q-analysis" style="border-left:3px solid var(--red);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                <span style="font-size:.88rem;font-weight:600;">#${i+1} ${esc(q.question)}</span>
                <span style="flex-shrink:0;background:rgba(214,48,49,.15);color:#ff7675;border-radius:30px;padding:3px 10px;font-size:.72rem;font-weight:700;">
                    ${q.correct_pct}% correct
                </span>
            </div>
        </div>`).join('')}
    </div>`:''}

    <!-- Per-question breakdown (full) -->
    <div class="card">
        <div class="card-title"><i class="fas fa-list-check" style="color:var(--p)"></i>Question Breakdown</div>
        ${questions.map((q,i)=>{
            const barW = v => q.total_answers>0?Math.round(v/q.total_answers*100):0;
            return `
            <div class="q-analysis" style="border-left:3px solid ${q.difficulty==='easy'?'var(--green)':q.difficulty==='medium'?'var(--g)':'var(--red)'};">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:8px;">
                    <span style="font-size:.88rem;font-weight:700;">Q${i+1}. ${esc(q.question)}</span>
                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                        <span class="diff-badge ${diffClass(q.difficulty)}">${diffIcon(q.difficulty)} ${q.difficulty}</span>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:6px;margin-bottom:8px;font-size:.78rem;">
                    <span style="color:var(--text3);">✅ Correct: <strong style="color:var(--text);">${q.correct_count}/${q.total_answers}</strong> (${q.correct_pct}%)</span>
                    <span style="color:var(--text3);">⏱ Avg time: <strong style="color:var(--text);">${q.avg_time}s</strong></span>
                    <span style="color:var(--text3);">Type: <strong style="color:var(--text);">${q.type==='true_false'?'True/False':q.type==='math'?'Math':'MCQ'}</strong></span>
                    ${q.most_missed?`<span style="color:var(--text3);">Most missed: <strong style="color:#ff7675;">Option ${q.most_missed}</strong></span>`:'<span></span>'}
                </div>
                <!-- Answer distribution mini bars -->
                <div style="display:flex;flex-direction:column;gap:4px;">
                    ${['A','B','C','D'].filter(k=>q.distribution[k]!=null&&(q.type!=='true_false'||k==='A'||k==='B')).map(k=>`
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="width:20px;font-size:.75rem;font-weight:700;color:${k===q.correct?'var(--green)':'var(--text3)'};">${k}</span>
                        <div style="flex:1;height:7px;background:var(--input-bg);border-radius:6px;overflow:hidden;">
                            <div style="height:7px;width:${barW(q.distribution[k]||0)}%;background:${k===q.correct?'var(--green)':q.most_missed===k?'var(--red)':'rgba(108,92,231,.4)'};border-radius:6px;transition:width .4s;"></div>
                        </div>
                        <span style="width:28px;font-size:.73rem;color:var(--text3);text-align:right;">${q.distribution[k]||0}</span>
                    </div>`).join('')}
                </div>
            </div>`;
        }).join('')}
    </div>

    <!-- All students ranking -->
    <div class="card">
        <div class="card-title"><i class="fas fa-trophy" style="color:var(--g)"></i>Student Rankings</div>
        ${students.length===0?`<p style="color:var(--text3);text-align:center;padding:16px;">No students data.</p>`:''}
        ${students.map((s,i)=>`
        <div class="student-card" style="border-left-color:${tierColor(s.tier)};">
            <div style="display:flex;align-items:center;gap:10px;flex:1;">
                <div style="width:30px;height:30px;border-radius:50%;background:${i===0?'rgba(253,203,110,.2)':i===1?'rgba(176,183,196,.15)':i===2?'rgba(205,159,110,.15)':'var(--input-bg)'};display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;color:${i===0?'var(--g)':i===1?'#b0b7c4':i===2?'#cd9f6e':'var(--text3)'};">
                    ${i===0?'🥇':i===1?'🥈':i===2?'🥉':'#'+(i+1)}
                </div>
                <div>
                    <div style="font-weight:700;font-size:.9rem;">${esc(s.name)}</div>
                    <div style="font-size:.74rem;color:var(--text3);">${tierIcon(s.tier)} ${tierLabel(s.tier)} &nbsp;·&nbsp; ${s.correct}/${s.total_q} correct &nbsp;·&nbsp; avg ${s.avg_time}s</div>
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <div style="font-size:1.3rem;font-weight:900;color:${gradeColor(s.pct)};">${s.pct}%</div>
                <div style="font-size:.7rem;color:${gradeColor(s.pct)};font-weight:700;">${gradeLabel(s.pct)}</div>
            </div>
        </div>`).join('')}
    </div>`;

    // Wire session switcher
    setTimeout(()=>{
        const sw=document.getElementById('sessionSwitcher');
        if(sw) sw.addEventListener('change',()=>{ window._analyticsId=parseInt(sw.value); renderAnalytics(); });

        // Score distribution chart
        const ctx=document.getElementById('distChart')?.getContext('2d');
        if(!ctx)return;
        const bkts=overview.score_distribution;
        const bgColors=Object.keys(bkts).map(k=>{
            const mid=parseInt(k.split('-')[0]);
            return mid<=40?'rgba(214,48,49,.7)':mid<=60?'rgba(225,112,85,.7)':mid<=70?'rgba(253,203,110,.7)':mid<=85?'rgba(116,185,255,.7)':'rgba(0,184,148,.7)';
        });
        charts.dist=new Chart(ctx,{type:'bar',
            data:{labels:Object.keys(bkts),datasets:[{label:'Students',data:Object.values(bkts),
                backgroundColor:bgColors,borderRadius:8,borderSkipped:false}]},
            options:{plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>`${ctx.parsed.y} student${ctx.parsed.y!==1?'s':''}`}}},
                scales:{
                    x:{grid:{color:'var(--chart-grid)'},ticks:{color:'var(--chart-tick)'}},
                    y:{beginAtZero:true,ticks:{stepSize:1,color:'var(--chart-tick)'},grid:{color:'var(--chart-grid)'}}
                }}
        });
    },60);
}

// ── Helpers ────────────────────────────────────────────────────
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtDate(s){ return s?new Date(s).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}):''; }
function loading(msg=''){
    return `<div class="card" style="text-align:center;padding:40px 20px;">
        <div style="width:36px;height:36px;border:3px solid rgba(108,92,231,.3);border-top-color:var(--p);border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 14px;"></div>
        <style>@keyframes spin{to{transform:rotate(360deg)}}</style>
        <p style="color:var(--text3);font-size:.88rem;">${msg||'Loading…'}</p>
    </div>`;
}

// ── Event delegation ───────────────────────────────────────────
document.addEventListener('click', async e=>{
    const vb=e.target.closest('[data-view]');
    if(vb){ loadView(vb.dataset.view); return; }
    if(e.target.closest('#logoutBtn')){
        try{ await post('auth/logout.php',{}); }catch(_){}
        sessionStorage.removeItem('cp_teacher'); location.href='auth.php';
    }
    if(e.target.closest('#copyBtn')&&activeSession){
        navigator.clipboard?.writeText(activeSession.room_code).then(()=>{
            const btn=document.getElementById('copyBtn');
            btn.innerHTML='<i class="fas fa-check me-1"></i>Copied!';
            setTimeout(()=>btn.innerHTML='<i class="fas fa-copy me-1"></i>Copy',1500);
        });
    }
});

init();
})();
</script>
</body>
</html>