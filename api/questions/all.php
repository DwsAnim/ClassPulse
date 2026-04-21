<?php
// GET /api/questions/all.php?room_code=XXXXXX
// Public endpoint — returns ALL questions for a session (no correct answers).
// Each student loads this on join and steps through locally.

require_once __DIR__ . '/../config.php';

$roomCode = strtoupper(trim($_GET['room_code'] ?? ''));
if (!$roomCode) sendError('room_code is required.');

$db = getDB();

$sess = $db->prepare('SELECT id, title, is_active FROM sessions WHERE room_code=? LIMIT 1');
$sess->bind_param('s', $roomCode);
$sess->execute();
$session = $sess->get_result()->fetch_assoc();
$sess->close();

if (!$session) sendError('Session not found.', 404);
if (!$session['is_active']) sendError('Session has ended.', 410);

$stmt = $db->prepare(
    'SELECT id, question, option_a, option_b, option_c, option_d,
            type, timer, sort_order
     FROM questions
     WHERE session_id = ?
     ORDER BY sort_order ASC'
);
$stmt->bind_param('i', $session['id']);
$stmt->execute();
$rows = $stmt->get_result();
$questions = [];
while ($r = $rows->fetch_assoc()) {
    // Never send correct answer to students
    $questions[] = [
        'id'       => (int)$r['id'],
        'question' => $r['question'],
        'option_a' => $r['option_a'],
        'option_b' => $r['option_b'],
        'option_c' => $r['option_c'],
        'option_d' => $r['option_d'],
        'type'     => $r['type'],
        'timer'    => (int)$r['timer'],
        'sort_order'=> (int)$r['sort_order'],
    ];
}
$stmt->close();

sendSuccess([
    'questions'       => $questions,
    'total'           => count($questions),
    'session_title'   => $session['title'],
]);
