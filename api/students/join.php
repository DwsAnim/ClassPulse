<?php
// POST /api/students/join.php
// Body: { room_code, name }
// Returns: { success, student: { id, name }, session: { id, title, room_code } }

require_once __DIR__ . '/../config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

$body     = json_decode(file_get_contents('php://input'), true);
$roomCode = strtoupper(trim($body['room_code'] ?? ''));
$name     = trim($body['name'] ?? '');

if (!$roomCode) sendError('Room code is required.');
if (!$name)     sendError('Your name is required.');
if (strlen($name) > 120) sendError('Name is too long.');

$db   = getDB();
$stmt = $db->prepare(
    'SELECT id, title, room_code FROM sessions WHERE room_code=? AND is_active=1 LIMIT 1'
);
$stmt->bind_param('s', $roomCode);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$session) sendError('Invalid room code or session is not active. Check with your teacher.');

// Check if name already taken in this session
$dup = $db->prepare('SELECT id FROM students WHERE session_id=? AND name=? LIMIT 1');
$dup->bind_param('is', $session['id'], $name);
$dup->execute();
$dup->store_result();
if ($dup->num_rows > 0) sendError('That name is already taken in this session. Please use a different name.');
$dup->close();

// Register student
$ins = $db->prepare('INSERT INTO students (session_id, name) VALUES (?, ?)');
$ins->bind_param('is', $session['id'], $name);
if (!$ins->execute()) sendError('Could not join session.', 500);
$studentId = $ins->insert_id;
$ins->close();

// Store in PHP session so student is "authenticated" for answer submission
$_SESSION['student_id']   = $studentId;
$_SESSION['student_name'] = $name;
$_SESSION['session_id']   = $session['id'];

sendSuccess([
    'student' => ['id' => $studentId, 'name' => $name],
    'session' => $session,
], 'Joined successfully.');
