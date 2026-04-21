<?php
// GET /api/questions/current.php?room_code=XXXXXX
// Returns the currently launched question + position info for students

require_once __DIR__ . '/../config.php';

$roomCode = strtoupper(trim($_GET['room_code'] ?? ''));
if (!$roomCode) sendError('room_code is required.');

$db = getDB();

// Check session is still active
$sessStmt = $db->prepare('SELECT id, is_active FROM sessions WHERE room_code=? LIMIT 1');
$sessStmt->bind_param('s', $roomCode);
$sessStmt->execute();
$session = $sessStmt->get_result()->fetch_assoc();
$sessStmt->close();

if (!$session) sendError('Session not found.', 404);

// Return session_active flag so student knows if session ended
if (!$session['is_active']) {
    sendSuccess(['question' => null, 'session_active' => false]);
}

// Get currently launched question (correct answer NOT included)
$stmt = $db->prepare(
    'SELECT q.id, q.question, q.option_a, q.option_b, q.option_c, q.option_d,
            q.type, q.timer, q.sort_order, q.launched_at
     FROM questions q
     WHERE q.session_id=? AND q.is_launched=1
     ORDER BY q.launched_at DESC LIMIT 1'
);
$stmt->bind_param('i', $session['id']);
$stmt->execute();
$question = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Total question count
$countStmt = $db->prepare('SELECT COUNT(*) AS total FROM questions WHERE session_id=?');
$countStmt->bind_param('i', $session['id']);
$countStmt->execute();
$total = (int)$countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

sendSuccess([
    'question'       => $question,
    'session_active' => true,
    'total_questions'=> $total,
    'question_number'=> $question ? ((int)$question['sort_order'] + 1) : 0,
]);
