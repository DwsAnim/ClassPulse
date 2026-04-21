<?php
// GET /api/questions/list.php?session_id=X
// Returns all questions for a session (teacher view — includes correct answers)

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);

$sessionId = intval($_GET['session_id'] ?? 0);
if (!$sessionId) sendError('session_id required.');

$db   = getDB();
$stmt = $db->prepare(
    'SELECT q.* FROM questions q
     JOIN sessions s ON s.id = q.session_id
     WHERE q.session_id=? AND s.teacher_id=?
     ORDER BY q.sort_order ASC'
);
$stmt->bind_param('ii', $sessionId, $_SESSION['teacher_id']);
$stmt->execute();
$rows = $stmt->get_result();
$questions = [];
while ($r = $rows->fetch_assoc()) $questions[] = $r;
$stmt->close();

sendSuccess(['questions' => $questions]);
