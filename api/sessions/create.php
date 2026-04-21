<?php
// POST /api/sessions/create.php
// Body: { title }
// Returns: { success, session: { id, title, room_code } }

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

$body  = json_decode(file_get_contents('php://input'), true);
$title = trim($body['title'] ?? '');
if (!$title) sendError('Session title is required.');

$db = getDB();

// Generate a unique 6-char alphanumeric room code
$attempts = 0;
do {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code  = '';
    for ($i = 0; $i < 6; $i++) $code .= $chars[random_int(0, strlen($chars) - 1)];
    $check = $db->prepare('SELECT id FROM sessions WHERE room_code = ? LIMIT 1');
    $check->bind_param('s', $code);
    $check->execute();
    $check->store_result();
    $exists = $check->num_rows > 0;
    $check->close();
    $attempts++;
} while ($exists && $attempts < 10);

// End any existing active sessions for this teacher
$end = $db->prepare('UPDATE sessions SET is_active=0, ended_at=NOW() WHERE teacher_id=? AND is_active=1');
$end->bind_param('i', $_SESSION['teacher_id']);
$end->execute();
$end->close();

// Create the new session
$stmt = $db->prepare('INSERT INTO sessions (teacher_id, title, room_code) VALUES (?, ?, ?)');
$stmt->bind_param('iss', $_SESSION['teacher_id'], $title, $code);
if (!$stmt->execute()) sendError('Could not create session: ' . $stmt->error, 500);
$sessionId = $stmt->insert_id;
$stmt->close();

sendSuccess([
    'session' => [
        'id'        => $sessionId,
        'title'     => $title,
        'room_code' => $code,
    ]
], 'Session created.');
