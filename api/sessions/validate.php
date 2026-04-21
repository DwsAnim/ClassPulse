<?php
// =============================================================
//  GET /api/sessions/validate.php?room=XXXXXX
//  Public endpoint — no auth needed.
//  Used by the student join page to check if a room code is valid
//  before letting the student in.
//  Returns: { success, session: { id, title, room_code } }
// =============================================================

require_once __DIR__ . '/../config.php';

$roomCode = strtoupper(trim($_GET['room'] ?? ''));

if (!$roomCode) {
    sendError('Room code is required.');
}

$db   = getDB();
$stmt = $db->prepare(
    'SELECT id, title, room_code FROM sessions WHERE room_code = ? AND is_active = 1 LIMIT 1'
);
$stmt->bind_param('s', $roomCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendError('Invalid room code or session has ended.', 404);
}

$session = $result->fetch_assoc();
$stmt->close();

sendSuccess([
    'session' => [
        'id'        => (int)$session['id'],
        'title'     => $session['title'],
        'room_code' => $session['room_code'],
    ]
]);
