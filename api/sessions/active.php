<?php
// GET /api/sessions/active.php
// Returns the teacher's currently active session (if any)

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);

$db   = getDB();
$stmt = $db->prepare(
    'SELECT id, title, room_code FROM sessions
     WHERE teacher_id=? AND is_active=1 LIMIT 1'
);
$stmt->bind_param('i', $_SESSION['teacher_id']);
$stmt->execute();
$result  = $stmt->get_result();
$session = $result->fetch_assoc();
$stmt->close();

if ($session) {
    sendSuccess(['session' => $session]);
} else {
    sendSuccess(['session' => null]);
}
