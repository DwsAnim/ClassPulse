<?php
// POST /api/questions/next.php
// Called by the student AFTER answering to auto-advance the session to the next question.
// Only advances if ALL students have answered (or timer ran out — handled by client).
// For simplicity in a buildathon context, we advance immediately after any submit.
// Body: { room_code, current_question_id }

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

$body           = json_decode(file_get_contents('php://input'), true);
$roomCode       = strtoupper(trim($body['room_code']          ?? ''));
$currentQId     = intval($body['current_question_id']         ?? 0);

if (!$roomCode)   sendError('room_code is required.');
if (!$currentQId) sendError('current_question_id is required.');

$db = getDB();

// Get session
$sessStmt = $db->prepare('SELECT id, teacher_id FROM sessions WHERE room_code=? AND is_active=1 LIMIT 1');
$sessStmt->bind_param('s', $roomCode);
$sessStmt->execute();
$session = $sessStmt->get_result()->fetch_assoc();
$sessStmt->close();
if (!$session) sendError('Session not active.', 404);

// Get the next question by sort_order
$nextStmt = $db->prepare(
    'SELECT id FROM questions
     WHERE session_id=? AND sort_order > (SELECT sort_order FROM questions WHERE id=?)
     ORDER BY sort_order ASC LIMIT 1'
);
$nextStmt->bind_param('ii', $session['id'], $currentQId);
$nextStmt->execute();
$next = $nextStmt->get_result()->fetch_assoc();
$nextStmt->close();

if (!$next) {
    // No more questions — signal session end
    sendSuccess(['advanced' => false, 'session_done' => true], 'No more questions.');
}

// Launch the next question
$reset = $db->prepare('UPDATE questions SET is_launched=0 WHERE session_id=?');
$reset->bind_param('i', $session['id']);
$reset->execute();
$reset->close();

$launch = $db->prepare('UPDATE questions SET is_launched=1, launched_at=NOW() WHERE id=?');
$launch->bind_param('i', $next['id']);
$launch->execute();
$launch->close();

sendSuccess(['advanced' => true, 'session_done' => false, 'next_question_id' => $next['id']], 'Advanced.');
