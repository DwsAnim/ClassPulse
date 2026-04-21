<?php
// GET /api/analytics/report.php?session_id=N
require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);

$sessionId = (int)($_GET['session_id'] ?? 0);
if (!$sessionId) sendError('session_id is required.');
$db = getDB();

// ── Verify ownership ──────────────────────────────────────────
$s = $db->prepare('SELECT id,title,room_code,created_at,ended_at FROM sessions WHERE id=? AND teacher_id=?');
$s->bind_param('ii', $sessionId, $_SESSION['teacher_id']);
$s->execute();
$session = $s->get_result()->fetch_assoc();
$s->close();
if (!$session) sendError('Session not found.', 404);

// ── Total questions ───────────────────────────────────────────
$r = $db->prepare('SELECT COUNT(*) AS n FROM questions WHERE session_id=?');
$r->bind_param('i', $sessionId);
$r->execute();
$totalQuestions = (int)$r->get_result()->fetch_assoc()['n'];
$r->close();

// ── Total students ────────────────────────────────────────────
$r = $db->prepare('SELECT COUNT(*) AS n FROM students WHERE session_id=?');
$r->bind_param('i', $sessionId);
$r->execute();
$totalStudents = (int)$r->get_result()->fetch_assoc()['n'];
$r->close();

// ── Per-student breakdown ─────────────────────────────────────
$r = $db->prepare("
    SELECT st.id, st.name,
           COUNT(a.id)             AS answered,
           COALESCE(SUM(a.is_correct),0) AS correct,
           COALESCE(ROUND(AVG(a.time_taken),1),0) AS avg_time
    FROM students st
    LEFT JOIN answers a  ON a.student_id  = st.id
    LEFT JOIN questions q ON q.id = a.question_id AND q.session_id = ?
    WHERE st.session_id = ?
    GROUP BY st.id, st.name
    ORDER BY correct DESC, answered DESC
");
$r->bind_param('ii', $sessionId, $sessionId);
$r->execute();
$rows = $r->get_result()->fetch_all(MYSQLI_ASSOC); // fetch ALL before closing
$r->close();

$students = [];
foreach ($rows as $row) {
    $answered = (int)$row['answered'];
    $correct  = (int)$row['correct'];
    $pct      = $totalQuestions > 0 ? round($correct / $totalQuestions * 100) : 0;
    $tier     = $pct >= 70 ? 'excelling' : ($pct >= 40 ? 'average' : 'needs_attention');
    $students[] = [
        'id'       => (int)$row['id'],
        'name'     => $row['name'],
        'answered' => $answered,
        'correct'  => $correct,
        'total_q'  => $totalQuestions,
        'pct'      => $pct,
        'avg_time' => (float)$row['avg_time'],
        'tier'     => $tier,
    ];
}

// ── Class averages ────────────────────────────────────────────
$scores      = array_column($students, 'pct');
$avgScore    = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
$topScore    = count($scores) > 0 ? max($scores) : 0;
$bottomScore = count($scores) > 0 ? min($scores) : 0;
$completedCount = count(array_filter($students, fn($s) => $s['answered'] > 0));
$completionRate = $totalStudents > 0 ? round($completedCount / $totalStudents * 100) : 0;

$buckets = ['0-40'=>0,'41-60'=>0,'61-70'=>0,'71-85'=>0,'86-100'=>0];
foreach ($students as $s) {
    if      ($s['pct'] <= 40) $buckets['0-40']++;
    elseif  ($s['pct'] <= 60) $buckets['41-60']++;
    elseif  ($s['pct'] <= 70) $buckets['61-70']++;
    elseif  ($s['pct'] <= 85) $buckets['71-85']++;
    else                      $buckets['86-100']++;
}

// ── Per-question analysis ─────────────────────────────────────
$r = $db->prepare("
    SELECT q.id, q.question, q.correct AS correct_option, q.sort_order, q.type,
           COUNT(a.id)                    AS total_answers,
           COALESCE(SUM(a.is_correct),0)  AS correct_count,
           COALESCE(SUM(a.answer='A'),0)  AS count_a,
           COALESCE(SUM(a.answer='B'),0)  AS count_b,
           COALESCE(SUM(a.answer='C'),0)  AS count_c,
           COALESCE(SUM(a.answer='D'),0)  AS count_d,
           COALESCE(ROUND(AVG(a.time_taken),1),0) AS avg_time
    FROM questions q
    LEFT JOIN answers a ON a.question_id = q.id
    WHERE q.session_id = ?
    GROUP BY q.id, q.question, q.correct, q.sort_order, q.type
    ORDER BY q.sort_order ASC
");
$r->bind_param('i', $sessionId);
$r->execute();
$qRows = $r->get_result()->fetch_all(MYSQLI_ASSOC); // fetch ALL before closing
$r->close();

$questions = [];
foreach ($qRows as $row) {
    $total   = (int)$row['total_answers'];
    $correct = (int)$row['correct_count'];
    $pct     = $total > 0 ? round($correct / $total * 100) : 0;
    $diff    = $pct >= 70 ? 'easy' : ($pct >= 40 ? 'medium' : 'hard');
    $opts    = ['A'=>(int)$row['count_a'],'B'=>(int)$row['count_b'],'C'=>(int)$row['count_c'],'D'=>(int)$row['count_d']];
    $correctK= strtoupper($row['correct_option']);
    $wrongOnly= array_filter($opts, fn($k) => $k !== $correctK, ARRAY_FILTER_USE_KEY);
    arsort($wrongOnly);
    $mostMissed = array_key_first($wrongOnly) ?? null;
    $questions[] = [
        'id'           => (int)$row['id'],
        'question'     => $row['question'],
        'type'         => $row['type'],
        'correct'      => $correctK,
        'total_answers'=> $total,
        'correct_count'=> $correct,
        'correct_pct'  => $pct,
        'difficulty'   => $diff,
        'avg_time'     => (float)$row['avg_time'],
        'distribution' => $opts,
        'most_missed'  => $mostMissed,
    ];
}

// ── Confusion meter ───────────────────────────────────────────
$r = $db->prepare("
    SELECT feeling, COUNT(*) AS n
    FROM confusion c
    JOIN questions q ON q.id = c.question_id
    WHERE q.session_id = ?
    GROUP BY feeling
");
$r->bind_param('i', $sessionId);
$r->execute();
$cfRows = $r->get_result()->fetch_all(MYSQLI_ASSOC);
$r->close();

$confusion = ['got_it'=>0,'unsure'=>0,'lost'=>0];
foreach ($cfRows as $row) {
    if (isset($confusion[$row['feeling']])) $confusion[$row['feeling']] = (int)$row['n'];
}

// ── Derived ───────────────────────────────────────────────────
$struggling = array_values(array_filter($students, fn($s) => $s['tier'] === 'needs_attention'));
$hardest    = array_values(array_filter($questions, fn($q) => $q['difficulty'] === 'hard'));
usort($hardest, fn($a,$b) => $a['correct_pct'] <=> $b['correct_pct']);

sendSuccess([
    'session' => [
        'id'        => (int)$session['id'],
        'title'     => $session['title'],
        'room_code' => $session['room_code'],
        'created_at'=> $session['created_at'],
        'ended_at'  => $session['ended_at'],
    ],
    'overview' => [
        'total_students'     => $totalStudents,
        'total_questions'    => $totalQuestions,
        'avg_score'          => $avgScore,
        'top_score'          => $topScore,
        'bottom_score'       => $bottomScore,
        'completion_rate'    => $completionRate,
        'completed_students' => $completedCount,
        'score_distribution' => $buckets,
    ],
    'students'     => $students,
    'questions'    => $questions,
    'confusion'    => $confusion,
    'struggling'   => $struggling,
    'reteach_list' => array_map(fn($q) => [
        'question'    => $q['question'],
        'correct_pct' => $q['correct_pct'],
        'difficulty'  => $q['difficulty'],
    ], $hardest),
]);
