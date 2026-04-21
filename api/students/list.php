<?php
// GET /api/students/list.php?room_code=XXXXXX
// Returns students in a session + answered status for current question

require_once __DIR__ . '/../config.php';
$roomCode = strtoupper(trim($_GET['room_code'] ?? ''));
if (!$roomCode) sendError('room_code is required.');

$db   = getDB();
$sess = $db->prepare('SELECT id FROM sessions WHERE room_code=? LIMIT 1');
$sess->bind_param('s', $roomCode);
$sess->execute();
$session = $sess->get_result()->fetch_assoc();
$sess->close();
if (!$session) sendError('Session not found.');

// Get currently launched question
$qStmt = $db->prepare(
    'SELECT id FROM questions WHERE session_id=? AND is_launched=1 ORDER BY launched_at DESC LIMIT 1'
);
$qStmt->bind_param('i', $session['id']);
$qStmt->execute();
$currentQ = $qStmt->get_result()->fetch_assoc();
$qStmt->close();

// Get students
$sStmt = $db->prepare('SELECT id, name FROM students WHERE session_id=? ORDER BY joined_at ASC');
$sStmt->bind_param('i', $session['id']);
$sStmt->execute();
$rows = $sStmt->get_result();
$students = [];
while ($row = $rows->fetch_assoc()) {
    $answered = false;
    if ($currentQ) {
        $aChk = $db->prepare('SELECT id FROM answers WHERE question_id=? AND student_id=? LIMIT 1');
        $aChk->bind_param('ii', $currentQ['id'], $row['id']);
        $aChk->execute();
        $aChk->store_result();
        $answered = $aChk->num_rows > 0;
        $aChk->close();
    }
    $students[] = ['id' => $row['id'], 'name' => $row['name'], 'answered' => $answered];
}
$sStmt->close();

sendSuccess(['students' => $students, 'count' => count($students)]);
