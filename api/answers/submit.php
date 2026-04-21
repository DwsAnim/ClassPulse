<?php
// POST /api/answers/submit.php
// Body: { question_id, answer, time_taken, feeling?, student_id? }
//
// student_id in the body is accepted as a fallback for when the PHP
// session cookie is lost between join and submit (shared hosting / cross-tab).

require_once __DIR__ . '/../config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

$body       = json_decode(file_get_contents('php://input'), true);
$questionId = intval($body['question_id'] ?? 0);
$answer     = strtoupper(trim($body['answer']    ?? ''));
$timeTaken  = intval($body['time_taken']  ?? 0);
$feeling    = $body['feeling']    ?? null;
$bodyStudId = intval($body['student_id'] ?? 0); // client-side fallback

if (!$questionId) sendError('question_id is required.');
if (!$answer)     sendError('answer is required.');

// Resolve student_id: prefer PHP session, fall back to body param
$studentId = (int)($_SESSION['student_id'] ?? 0);
if (!$studentId && $bodyStudId) {
    $studentId = $bodyStudId;
}
if (!$studentId) sendError('Not in a session. Please join first.', 401);

$db = getDB();

// ── Verify question belongs to an active session ───────────────
// IMPORTANT: we no longer require is_launched=1.
// Students step through ALL questions client-side, so only Q1 is
// ever marked is_launched=1 on the server. Checking is_launched
// would incorrectly reject Q2, Q3, etc.
$qStmt = $db->prepare(
    'SELECT q.id, q.correct, q.type, s.is_active
     FROM questions q
     JOIN sessions s ON s.id = q.session_id
     WHERE q.id = ? AND s.is_active = 1
     LIMIT 1'
);
$qStmt->bind_param('i', $questionId);
$qStmt->execute();
$q = $qStmt->get_result()->fetch_assoc();
$qStmt->close();

if (!$q) sendError('Question not found or session has ended.', 404);

// ── Prevent double-submit ─────────────────────────────────────
$dup = $db->prepare(
    'SELECT id FROM answers WHERE question_id=? AND student_id=? LIMIT 1'
);
$dup->bind_param('ii', $questionId, $studentId);
$dup->execute();
$dup->store_result();
$alreadyAnswered = $dup->num_rows > 0;
$dup->close();

if ($alreadyAnswered) {
    // Return the existing result rather than erroring — handles retry on network fail
    $existing = $db->prepare(
        'SELECT is_correct FROM answers WHERE question_id=? AND student_id=? LIMIT 1'
    );
    $existing->bind_param('ii', $questionId, $studentId);
    $existing->execute();
    $row = $existing->get_result()->fetch_assoc();
    $existing->close();
    sendSuccess(
        ['is_correct' => (bool)$row['is_correct'], 'duplicate' => true],
        $row['is_correct'] ? 'Correct!' : 'Wrong answer.'
    );
}

// ── Check answer ──────────────────────────────────────────────
// For True/False: correct is stored as 'A' (True) or 'B' (False)
// Student sends 'A' or 'B' — direct comparison works
$isCorrect = (strtoupper($answer) === strtoupper($q['correct'])) ? 1 : 0;

// ── Save answer ───────────────────────────────────────────────
$ins = $db->prepare(
    'INSERT INTO answers (question_id, student_id, answer, is_correct, time_taken)
     VALUES (?,?,?,?,?)'
);
$ins->bind_param('iisii', $questionId, $studentId, $answer, $isCorrect, $timeTaken);
if (!$ins->execute()) sendError('Could not save answer.', 500);
$ins->close();

// ── Save confusion feeling ────────────────────────────────────
$validFeelings = ['got_it', 'unsure', 'lost'];
if ($feeling && in_array($feeling, $validFeelings)) {
    $cf = $db->prepare(
        'INSERT INTO confusion (question_id, student_id, feeling)
         VALUES (?,?,?)
         ON DUPLICATE KEY UPDATE feeling = VALUES(feeling)'
    );
    $cf->bind_param('iis', $questionId, $studentId, $feeling);
    $cf->execute();
    $cf->close();
}

sendSuccess(
    ['is_correct' => (bool)$isCorrect],
    $isCorrect ? 'Correct!' : 'Wrong answer.'
);