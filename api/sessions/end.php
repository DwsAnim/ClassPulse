<?php
// POST /api/sessions/end.php
// Body: { session_id }

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

$body      = json_decode(file_get_contents('php://input'), true);
$sessionId = intval($body['session_id'] ?? 0);
if (!$sessionId) sendError('session_id is required.');

$db   = getDB();
$stmt = $db->prepare(
    'UPDATE sessions SET is_active=0, ended_at=NOW()
     WHERE id=? AND teacher_id=? AND is_active=1'
);
$stmt->bind_param('ii', $sessionId, $_SESSION['teacher_id']);
$stmt->execute();

if ($stmt->affected_rows === 0) sendError('Session not found or already ended.');
$stmt->close();

sendSuccess([], 'Session ended.');
