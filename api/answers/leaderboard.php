<?php
// GET /api/answers/leaderboard.php?session_id=X
// Returns students ranked by score.
// Score = correct answers / total questions in session * 100
// (NOT correct/answered — unanswered questions count against you)

require_once __DIR__ . '/../config.php';

$sessionId = intval($_GET['session_id'] ?? 0);
if (!$sessionId) sendError('session_id required.');

$db = getDB();

// Total questions in this session (the denominator)
$tq = $db->prepare('SELECT COUNT(*) AS n FROM questions WHERE session_id=?');
$tq->bind_param('i', $sessionId);
$tq->execute();
$totalQuestions = (int)$tq->get_result()->fetch_assoc()['n'];
$tq->close();

if ($totalQuestions === 0) sendSuccess(['leaderboard' => []]);

// Per-student: correct answers and total answered
$stmt = $db->prepare(
    'SELECT st.id, st.name,
            COALESCE(SUM(a.is_correct), 0)  AS correct,
            COUNT(a.id)                      AS answered
     FROM students st
     LEFT JOIN answers  a ON a.student_id  = st.id
     LEFT JOIN questions q ON q.id = a.question_id AND q.session_id = ?
     WHERE st.session_id = ?
     GROUP BY st.id, st.name
     ORDER BY correct DESC, answered DESC'
);
$stmt->bind_param('ii', $sessionId, $sessionId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$board = [];
$rank  = 1;
foreach ($rows as $r) {
    $correct  = (int)$r['correct'];
    $answered = (int)$r['answered'];
    // Score out of total questions (not just answered ones)
    $scorePct = round($correct / $totalQuestions * 100);
    $board[] = [
        'rank'      => $rank++,
        'name'      => $r['name'],
        'correct'   => $correct,
        'answered'  => $answered,
        'total'     => $totalQuestions,
        'score_pct' => $scorePct,
    ];
}

sendSuccess(['leaderboard' => $board]);