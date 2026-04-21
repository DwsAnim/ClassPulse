<?php
// POST /api/answers/submit.php
// Body: { question_id, answer, time_taken, feeling? }
// Student must have joined via students/join.php (PHP session)

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['student_id'])) sendError('Not in a session. Please join first.', 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

$body       = json_decode(file_get_contents('php://input'), true);
$questionId = intval($body['question_id'] ?? 0);
$answer     = strtoupper(trim($body['answer']    ?? ''));
$timeTaken  = intval($body['time_taken']  ?? 0);
$feeling    = $body['feeling'] ?? null; // 'got_it' | 'unsure' | 'lost'

if (!$questionId) sendError('question_id is required.');
if (!$answer)     sendError('answer is required.');

$db        = getDB();
$studentId = $_SESSION['student_id'];

// Prevent double-submit
$dup = $db->prepare('SELECT id FROM answers WHERE question_id=? AND student_id=? LIMIT 1');
$dup->bind_param('ii', $questionId, $studentId);
$dup->execute();
$dup->store_result();
if ($dup->num_rows > 0) sendError('You have already answered this question.');
$dup->close();

// Check correct answer
$qStmt = $db->prepare('SELECT correct FROM questions WHERE id=? AND is_launched=1 LIMIT 1');
$qStmt->bind_param('i', $questionId);
$qStmt->execute();
$q = $qStmt->get_result()->fetch_assoc();
$qStmt->close();
if (!$q) sendError('Question not active.');

$isCorrect = ($answer === strtoupper($q['correct'])) ? 1 : 0;

// Save answer
$ins = $db->prepare(
    'INSERT INTO answers (question_id, student_id, answer, is_correct, time_taken) VALUES (?,?,?,?,?)'
);
$ins->bind_param('iisii', $questionId, $studentId, $answer, $isCorrect, $timeTaken);
$ins->execute();
$ins->close();

// Save confusion feeling if provided
$validFeelings = ['got_it', 'unsure', 'lost'];
if ($feeling && in_array($feeling, $validFeelings)) {
    $cf = $db->prepare('INSERT INTO confusion (question_id, student_id, feeling) VALUES (?,?,?)');
    $cf->bind_param('iis', $questionId, $studentId, $feeling);
    $cf->execute();
    $cf->close();
}

sendSuccess(['is_correct' => (bool)$isCorrect], $isCorrect ? 'Correct!' : 'Wrong answer.');
