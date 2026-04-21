<?php
// GET /api/answers/leaderboard.php?session_id=X
// Returns ranked students by score for the session

require_once __DIR__ . '/../config.php';
$sessionId = intval($_GET['session_id'] ?? 0);
if (!$sessionId) sendError('session_id required.');

$db   = getDB();
$stmt = $db->prepare(
    'SELECT st.name,
            COUNT(CASE WHEN a.is_correct=1 THEN 1 END) AS correct,
            COUNT(a.id) AS total,
            ROUND(COUNT(CASE WHEN a.is_correct=1 THEN 1 END) / NULLIF(COUNT(a.id),0) * 100) AS score_pct
     FROM students st
     LEFT JOIN answers a ON a.student_id = st.id
     LEFT JOIN questions q ON q.id = a.question_id
     WHERE st.session_id=?
     GROUP BY st.id, st.name
     ORDER BY correct DESC, total ASC'
);
$stmt->bind_param('i', $sessionId);
$stmt->execute();
$rows = $stmt->get_result();
$board = [];
$rank  = 1;
while ($r = $rows->fetch_assoc()) {
    $r['rank'] = $rank++;
    $board[]   = $r;
}
$stmt->close();

sendSuccess(['leaderboard' => $board]);
