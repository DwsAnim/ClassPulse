<?php
// GET /api/sessions/list.php
// Returns all sessions for the logged-in teacher

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);

$db   = getDB();
$stmt = $db->prepare(
    'SELECT id, title, room_code, is_active, created_at, ended_at
     FROM sessions WHERE teacher_id = ? ORDER BY created_at DESC'
);
$stmt->bind_param('i', $_SESSION['teacher_id']);
$stmt->execute();
$result   = $stmt->get_result();
$sessions = [];
while ($row = $result->fetch_assoc()) {
    // Count students per session
    $sc = $db->prepare('SELECT COUNT(*) AS cnt FROM students WHERE session_id = ?');
    $sc->bind_param('i', $row['id']);
    $sc->execute();
    $row['student_count'] = $sc->get_result()->fetch_assoc()['cnt'];
    $sc->close();
    $sessions[] = $row;
}
$stmt->close();

sendSuccess(['sessions' => $sessions]);
