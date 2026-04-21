<?php
// POST /api/questions/launch.php
// Body: { question_id }  — teacher launches a specific question live

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

$body       = json_decode(file_get_contents('php://input'), true);
$questionId = intval($body['question_id'] ?? 0);
if (!$questionId) sendError('question_id is required.');

$db = getDB();

// Verify teacher owns the session this question belongs to
$chk = $db->prepare(
    'SELECT q.id, q.session_id FROM questions q
     JOIN sessions s ON s.id = q.session_id
     WHERE q.id=? AND s.teacher_id=?'
);
$chk->bind_param('ii', $questionId, $_SESSION['teacher_id']);
$chk->execute();
$row = $chk->get_result()->fetch_assoc();
$chk->close();
if (!$row) sendError('Question not found.', 404);

// Un-launch all other questions in session, launch this one
$reset = $db->prepare('UPDATE questions SET is_launched=0 WHERE session_id=?');
$reset->bind_param('i', $row['session_id']);
$reset->execute();
$reset->close();

$launch = $db->prepare('UPDATE questions SET is_launched=1, launched_at=NOW() WHERE id=?');
$launch->bind_param('i', $questionId);
$launch->execute();
$launch->close();

sendSuccess([], 'Question launched.');
