<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassPulse · Student</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/theme.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script src="../assets/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--bg); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px; color:var(--text); }
        .orb { position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none; }
        .orb1 { width:400px;height:400px;background:radial-gradient(circle,#6C5CE7,transparent);top:-80px;left:-80px;opacity:var(--orb1-op); }
        .orb2 { width:350px;height:350px;background:radial-gradient(circle,#FDCB6E,transparent);bottom:-60px;right:-60px;opacity:var(--orb2-op); }

        .theme-toggle { position:fixed;top:16px;right:16px;z-index:200;width:38px;height:38px;border-radius:11px;background:var(--card);border:1px solid var(--border);color:var(--text2);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.9rem; }
        .wrap { max-width:520px; width:100%; position:relative; z-index:1; }
        .header { display:flex;justify-content:space-between;align-items:center;margin-bottom:20px; }
        .logo { font-size:1.4rem;font-weight:800;background:linear-gradient(135deg,#6C5CE7,#FDCB6E);-webkit-background-clip:text;background-clip:text;color:transparent; }
        .sync-pill { display:flex;align-items:center;gap:6px;background:var(--card);border:1px solid var(--border);border-radius:40px;padding:6px 14px;font-size:.78rem;color:var(--text2); }
        .sync-dot { width:7px;height:7px;border-radius:50%;background:#444; }
        .sync-dot.on { background:#FDCB6E;animation:blink 1s ease infinite; }
        .sync-dot.ok { background:#00b894; }
        @keyframes blink { 0%,100%{opacity:1}50%{opacity:.3} }

        .card { background:var(--card);backdrop-filter:blur(24px);border:1.5px solid var(--border);border-radius:24px;padding:24px 20px;animation:slideUp .3s cubic-bezier(.2,.9,.4,1) both; }
        @keyframes slideUp { from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none} }

        .field { margin-bottom:14px; }
        .field label { display:block;font-size:.75rem;font-weight:600;color:var(--text3);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em; }
        .inp { width:100%;background:var(--input-bg);border:1.5px solid var(--input-b);border-radius:14px;padding:13px 16px;color:var(--text);font-family:inherit;font-size:.95rem;outline:none;transition:border-color .2s,box-shadow .2s; }
        .inp:focus { border-color:#6C5CE7;box-shadow:0 0 0 3px rgba(108,92,231,.2); }
        .inp::placeholder { color:var(--text3); }
        .inp.code { text-transform:uppercase;letter-spacing:5px;font-size:1.4rem;font-weight:700;text-align:center; }

        .btn-primary { width:100%;padding:14px;border:none;border-radius:14px;font-weight:700;font-size:.95rem;cursor:pointer;font-family:inherit;background:linear-gradient(135deg,#6C5CE7,#a78bfa,#FDCB6E);color:#fff;transition:opacity .15s,transform .15s;box-shadow:0 6px 20px rgba(108,92,231,.3); }
        .btn-primary:hover:not(:disabled) { opacity:.9;transform:translateY(-1px); }
        .btn-primary:disabled { opacity:.45;cursor:not-allowed; }
        .btn-ghost { background:var(--card);border:1.5px solid var(--border);border-radius:12px;padding:10px 18px;color:var(--text2);font-family:inherit;font-size:.88rem;cursor:pointer;transition:all .2s; }
        .btn-ghost:hover { background:var(--card-hover);color:var(--text); }

        .q-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:8px; }
        .q-num { font-size:.82rem;font-weight:700;color:#6C5CE7; }
        .timer-badge { background:rgba(108,92,231,.15);border:1px solid rgba(108,92,231,.25);border-radius:10px;padding:4px 12px;font-weight:700;font-size:.95rem;color:#a78bfa;min-width:54px;text-align:center; }
        .timer-badge.urgent { background:rgba(214,48,49,.15);border-color:rgba(214,48,49,.3);color:#ff7675; }
        .prog-track { height:6px;background:var(--border);border-radius:10px;overflow:hidden;margin-bottom:16px; }
        .prog-fill { height:6px;border-radius:10px;background:linear-gradient(90deg,#6C5CE7,#FDCB6E);transition:width .4s linear; }
        .q-text { font-size:1.05rem;font-weight:600;line-height:1.5;margin-bottom:4px;color:var(--text); }
        .q-type-tag { font-size:.74rem;color:var(--text3);margin-bottom:16px;display:block; }

        .option { width:100%;display:flex;align-items:center;gap:12px;background:var(--input-bg);border:2px solid var(--border);border-radius:14px;padding:13px 16px;margin-bottom:9px;color:var(--text);font-family:inherit;font-size:.92rem;font-weight:500;cursor:pointer;text-align:left;transition:all .18s; }
        .option:hover:not(:disabled) { background:rgba(108,92,231,.1);border-color:#6C5CE7; }
        .option.selected { background:rgba(108,92,231,.12);border-color:#6C5CE7; }
        .option.correct  { background:rgba(0,184,148,.12);border-color:#00b894;color:#00b894;font-weight:700; }
        .option.wrong    { background:rgba(214,48,49,.1);border-color:#d63031;color:#ff7675; }
        .option:disabled { cursor:not-allowed; }
        .opt-key { width:28px;height:28px;border-radius:8px;background:var(--border);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.78rem;flex-shrink:0; }
        .option.selected .opt-key { background:#6C5CE7;color:#fff; }
        .option.correct  .opt-key { background:#00b894;color:#fff; }
        .option.wrong    .opt-key { background:#d63031;color:#fff; }

        .feedback { text-align:center;font-weight:700;font-size:1rem;min-height:26px;margin:8px 0; }
        .confusion { display:flex;gap:8px;margin:12px 0; }
        .feel-btn { flex:1;padding:10px;border-radius:12px;background:var(--card);border:1.5px solid var(--border);color:var(--text2);font-family:inherit;font-size:.83rem;cursor:pointer;transition:all .18s; }
        .feel-btn:hover { border-color:#6C5CE7; }
        .feel-btn.active { background:rgba(108,92,231,.2);border-color:#6C5CE7;color:#a78bfa;font-weight:600; }
        .next-msg { text-align:center;color:var(--text3);font-size:.82rem;margin-top:8px; }
        .divider { height:1px;background:var(--border);margin:16px 0; }

        .big-avatar { width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,#6C5CE7,#FDCB6E);display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 14px; }
        .room-code-display { letter-spacing:5px;font-size:1.3rem;font-weight:800;color:#6C5CE7;margin:4px 0 14px; }
        .spin { display:inline-block;width:34px;height:34px;border:3px solid var(--border);border-top-color:#6C5CE7;border-radius:50%;animation:spin .8s linear infinite; }
        @keyframes spin{to{transform:rotate(360deg)}}
        .pulse-text { animation:ptxt 1.6s ease infinite; }
        @keyframes ptxt{0%,100%{opacity:1}50%{opacity:.4}}

        .big-score { font-size:3.8rem;font-weight:800;background:linear-gradient(135deg,#6C5CE7,#FDCB6E);-webkit-background-clip:text;background-clip:text;color:transparent;line-height:1.1; }
        .leader-row { display:flex;justify-content:space-between;align-items:center;background:var(--input-bg);border-radius:12px;padding:11px 14px;margin-bottom:8px;border-left:4px solid; }
        .err { color:#ff7675;font-size:.83rem;margin-top:6px; }
    </style>
</head>
<body>
<div class="orb orb1"></div>
<div class="orb orb2"></div>
<button class="theme-toggle" data-theme-btn title="Toggle theme"></button>

<div class="wrap">
    <div class="header">
        <span class="logo"><i class="fas fa-chalkboard-user me-1"></i>ClassPulse</span>
        <div class="sync-pill"><div class="sync-dot" id="syncDot"></div><span id="syncTxt">Ready</span></div>
    </div>
    <div id="view"></div>
</div>

<script>
(function(){
    'use strict';
    const _base = window.location.pathname.replace(/\/student\/.*$/,'');
    const API   = _base + '/api';
    const VIEW  = document.getElementById('view');

    let S = { student:null,session:null,questions:[],qIndex:0,answered:false,feeling:null,selectedOpt:null,timerSec:30,timerMax:30,timerTick:null,pollTick:null };

    const req=(ep,body)=>fetch(`${API}/${ep}`,{method:body?'POST':'GET',credentials:'include',headers:body?{'Content-Type':'application/json'}:{},body:body?JSON.stringify(body):undefined}).then(r=>r.json());

    function setSync(s){
        const d=document.getElementById('syncDot'),t=document.getElementById('syncTxt');
        d.className='sync-dot'+(s==='on'?' on':s==='ok'?' ok':'');
        t.textContent=s==='on'?'Syncing…':s==='ok'?'Synced':'Ready';
    }

    function showJoin(msg){
        clearInterval(S.pollTick); clearInterval(S.timerTick);
        S={student:null,session:null,questions:[],qIndex:0,answered:false,feeling:null,selectedOpt:null,timerSec:30,timerMax:30,timerTick:null,pollTick:null};
        VIEW.innerHTML=`
        <div class="card">
            <h2 style="font-size:1.4rem;font-weight:800;margin-bottom:4px;">👋 Join a session</h2>
            <p style="color:var(--text2);font-size:.88rem;margin-bottom:20px;">Enter the code your lecturer shared on the board</p>
            <div class="field"><label>Room Code</label><input id="codeInp" class="inp code" placeholder="XXXXXX" maxlength="6"></div>
            <div class="field"><label>Your Name</label><input id="nameInp" class="inp" placeholder="e.g. Adaeze Okonkwo"></div>
            ${msg?`<p class="err"><i class="fas fa-circle-exclamation me-1"></i>${msg}</p>`:''}
            <div id="joinErr" class="err"></div>
            <button class="btn-primary" id="joinBtn" style="margin-top:14px;"><i class="fas fa-arrow-right me-2"></i>Join Classroom</button>
        </div>`;
        document.getElementById('codeInp').addEventListener('input',e=>e.target.value=e.target.value.toUpperCase());
        const go=()=>doJoin(document.getElementById('codeInp').value.trim().toUpperCase(),document.getElementById('nameInp').value.trim());
        document.getElementById('joinBtn').addEventListener('click',go);
        document.getElementById('nameInp').addEventListener('keydown',e=>e.key==='Enter'&&go());
        document.getElementById('codeInp').focus();
    }

    async function doJoin(code,name){
        const errEl=document.getElementById('joinErr'),btn=document.getElementById('joinBtn');
        if(!code||code.length<4){errEl.textContent='Enter the 6-character room code.';return;}
        if(!name){errEl.textContent='Your name is required.';return;}
        btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin me-2"></i>Joining…';
        try{
            const d=await req('students/join.php',{room_code:code,name});
            if(!d.success){errEl.textContent=d.message||'Could not join.';btn.disabled=false;btn.innerHTML='<i class="fas fa-arrow-right me-2"></i>Join Classroom';return;}
            S.student=d.student; S.session=d.session;
            const qd=await req(`questions/all.php?room_code=${code}`);
            if(!qd.success||!qd.questions.length){errEl.textContent='No questions found for this session yet.';btn.disabled=false;btn.innerHTML='<i class="fas fa-arrow-right me-2"></i>Join Classroom';return;}
            S.questions=qd.questions; S.qIndex=0;
            showLobby();
        }catch(_){errEl.textContent='Cannot reach server. Is XAMPP running?';btn.disabled=false;btn.innerHTML='<i class="fas fa-arrow-right me-2"></i>Join Classroom';}
    }

    function showLobby(){
        VIEW.innerHTML=`
        <div class="card" style="text-align:center;padding:28px 20px;">
            <div class="big-avatar">🎓</div>
            <h2 style="font-size:1.3rem;font-weight:800;">${esc(S.student.name)}</h2>
            <p style="color:var(--text2);margin:4px 0 2px;">Session: <strong style="color:var(--text);">${esc(S.session.title)}</strong></p>
            <div class="room-code-display">${esc(S.session.room_code)}</div>
            <div class="divider"></div>
            <div class="spin" style="margin:10px auto;"></div>
            <p class="pulse-text" style="color:var(--text3);margin-top:12px;font-size:.88rem;">
                <i class="fas fa-hourglass-half me-1"></i>Starting quiz shortly…
            </p>
        </div>`;
        setTimeout(()=>showQuestion(0),1200);
    }

    function showQuestion(idx){
        if(idx>=S.questions.length){showEndScreen();return;}
        S.qIndex=idx; S.answered=false; S.feeling=null; S.selectedOpt=null;
        const q=S.questions[idx];
        const isTF=q.type==='true_false', isMath=q.type==='math';
        const allOpts=[{key:'A',text:q.option_a},{key:'B',text:q.option_b},{key:'C',text:q.option_c},{key:'D',text:q.option_d}];
        const opts=isTF?allOpts.slice(0,2):allOpts.filter(o=>o.text&&o.text.trim());

        VIEW.innerHTML=`
        <div class="card">
            <div class="q-header">
                <span class="q-num">Question ${idx+1} <span style="color:var(--text3);">/ ${S.questions.length}</span></span>
                <span class="timer-badge" id="timerBadge">${q.timer}s</span>
            </div>
            <div class="prog-track"><div class="prog-fill" id="timerBar" style="width:100%;"></div></div>
            <p class="q-text" id="qText">${isMath?'':esc(q.question)}</p>
            <span class="q-type-tag">${isTF?'True / False':isMath?'Math Formula':'Multiple Choice'}</span>
            <div id="optsList">
                ${opts.map(o=>`<button class="option" data-opt="${o.key}"><span class="opt-key">${o.key}</span><span>${esc(o.text)}</span></button>`).join('')}
            </div>
            <div class="feedback" id="fb"></div>
            <button class="btn-primary" id="submitBtn"><i class="fas fa-paper-plane me-2"></i>Submit Answer</button>
            <div id="afterZone" style="display:none;">
                <div class="divider"></div>
                <p style="font-size:.76rem;color:var(--text3);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em;">How did that feel?</p>
                <div class="confusion">
                    <button class="feel-btn" data-f="got_it">👍 Got it</button>
                    <button class="feel-btn" data-f="unsure">🤔 Unsure</button>
                    <button class="feel-btn" data-f="lost">😕 Lost</button>
                </div>
                <p class="next-msg" id="nextMsg"></p>
            </div>
        </div>`;

        if(isMath){try{katex.render(q.question,document.getElementById('qText'),{throwOnError:false,displayMode:true});}catch(_){document.getElementById('qText').textContent=q.question;}}
        VIEW.querySelectorAll('.option').forEach(b=>b.addEventListener('click',()=>{
            if(S.answered)return;
            VIEW.querySelectorAll('.option').forEach(x=>x.classList.remove('selected'));
            b.classList.add('selected'); S.selectedOpt=b.dataset.opt;
        }));
        VIEW.querySelectorAll('.feel-btn').forEach(b=>b.addEventListener('click',()=>{
            VIEW.querySelectorAll('.feel-btn').forEach(x=>x.classList.remove('active'));
            b.classList.add('active'); S.feeling=b.dataset.f;
        }));
        document.getElementById('submitBtn').addEventListener('click',submitAnswer);
        startTimer(parseInt(q.timer)||30);
    }

    async function submitAnswer(){
        if(S.answered)return;
        if(!S.selectedOpt){document.getElementById('fb').innerHTML='<span style="color:#ff7675;font-size:.88rem;">Select an answer first.</span>';return;}
        const btn=document.getElementById('submitBtn');
        btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin me-2"></i>Submitting…';
        const timeTaken=S.timerMax-Math.max(0,S.timerSec);
        stopTimer(); S.answered=true;
        try{
            const d=await req('answers/submit.php',{question_id:S.questions[S.qIndex].id,answer:S.selectedOpt,time_taken:timeTaken,feeling:S.feeling||null});
            VIEW.querySelectorAll('.option').forEach(b=>{b.disabled=true;if(b.dataset.opt===S.selectedOpt)b.classList.add(d.is_correct?'correct':'wrong');});
            const fb=document.getElementById('fb');
            fb.innerHTML=d.is_correct?'<span style="color:#00b894;font-size:1.05rem;">✅ Correct!</span>':'<span style="color:#ff7675;font-size:1.05rem;">❌ Wrong answer</span>';
            btn.innerHTML='<i class="fas fa-check me-2"></i>Submitted';
            document.getElementById('afterZone').style.display='block';
            startNextCountdown(2);
        }catch(_){
            document.getElementById('fb').innerHTML='<span style="color:#ff7675;font-size:.85rem;">Submit failed — check your connection.</span>';
            btn.disabled=false; btn.innerHTML='<i class="fas fa-paper-plane me-2"></i>Submit Answer'; S.answered=false;
        }
    }

    function startNextCountdown(secs){
        let left=secs;
        const tick=setInterval(()=>{
            const msgEl=document.getElementById('nextMsg');
            const isLast=S.qIndex+1>=S.questions.length;
            if(msgEl) msgEl.innerHTML=isLast?`<i class="fas fa-flag-checkered me-1"></i>Finishing in ${left}s…`:`<i class="fas fa-forward me-1"></i>Next question in ${left}s…`;
            if(left<=0){clearInterval(tick);showQuestion(S.qIndex+1);}
            left--;
        },1000);
    }

    function startTimer(sec){
        S.timerMax=sec; S.timerSec=sec; stopTimer(); renderTimer();
        S.timerTick=setInterval(()=>{S.timerSec--;renderTimer();if(S.timerSec<0){stopTimer();if(!S.answered){S.answered=true;VIEW.querySelectorAll('.option').forEach(b=>b.disabled=true);const sb=document.getElementById('submitBtn');if(sb){sb.disabled=true;sb.innerHTML='⏰ Time\'s up';}const fb=document.getElementById('fb');if(fb){fb.innerHTML='<span style="color:#FDCB6E;">⏰ Time\'s up!</span>';}const az=document.getElementById('afterZone');if(az)az.style.display='block';startNextCountdown(2);}}},1000);
    }
    function stopTimer(){clearInterval(S.timerTick);S.timerTick=null;}
    function renderTimer(){
        const v=Math.max(0,S.timerSec),pct=(v/S.timerMax*100)+'%';
        const badge=document.getElementById('timerBadge'),bar=document.getElementById('timerBar');
        if(badge){badge.textContent=v+'s';badge.className='timer-badge'+(v<=5?' urgent':'');}
        if(bar){bar.style.width=pct;bar.style.background=v<=5?'linear-gradient(90deg,#d63031,#e17055)':'linear-gradient(90deg,#6C5CE7,#FDCB6E)';}
    }

    async function showEndScreen(){
        stopTimer();
        VIEW.innerHTML=`<div class="card" style="text-align:center;"><div class="spin" style="margin:0 auto 14px;"></div><p style="color:var(--text2);">Loading results…</p></div>`;
        try{
            const d=await req(`answers/leaderboard.php?session_id=${S.session.id}`);
            const board=d.leaderboard||[],me=board.find(s=>s.name===S.student.name);
            const rank=me?me.rank:'?',pct=me?me.score_pct:0,correct=me?me.correct:0,total=me?me.total:S.questions.length;
            const colors=['#FDCB6E','#b0b7c4','#cd9f6e','#a29bfe','#74b9ff'];
            VIEW.innerHTML=`
            <div class="card" style="text-align:center;">
                <div style="font-size:2.5rem;margin-bottom:8px;">🏆</div>
                <h2 style="font-weight:800;margin-bottom:4px;">Quiz Complete!</h2>
                <p style="color:var(--text2);margin-bottom:14px;">${correct} of ${total} correct · Rank #${rank} of ${board.length}</p>
                <div class="big-score">${pct}%</div>
                <div class="divider"></div>
                <p style="font-size:.73rem;color:var(--text3);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">Leaderboard</p>
                ${board.slice(0,5).map((s,i)=>`
                <div class="leader-row" style="border-left-color:${colors[i]||'var(--border)'};">
                    <span>${i===0?'🥇':i===1?'🥈':i===2?'🥉':'#'+(i+1)} &nbsp;${esc(s.name)}${s.name===S.student.name?' <strong style="color:#FDCB6E;">← you</strong>':''}</span>
                    <span style="font-weight:700;">${s.score_pct}%</span>
                </div>`).join('')}
                <div class="divider"></div>
                <button class="btn-ghost" id="rejoinBtn" style="width:100%;"><i class="fas fa-redo me-1"></i>Join another session</button>
            </div>`;
            document.getElementById('rejoinBtn').addEventListener('click',()=>showJoin());
        }catch(_){
            VIEW.innerHTML=`<div class="card" style="text-align:center;"><div style="font-size:2.5rem;">🎉</div><h2 style="margin:12px 0;">All Done!</h2><button class="btn-ghost" style="width:100%;margin-top:16px;" onclick="location.reload()">Play again</button></div>`;
        }
    }

    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
    showJoin();
})();
</script>
</body>
</html>
