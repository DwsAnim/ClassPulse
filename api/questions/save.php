<?php
// POST /api/questions/save.php
// Body: { session_id, questions: [{ question, option_a..d, correct, type, timer }] }

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

$body      = json_decode(file_get_contents('php://input'), true);
$sessionId = intval($body['session_id'] ?? 0);
$questions = $body['questions']         ?? [];

if (!$sessionId) sendError('session_id is required.');
if (!is_array($questions) || count($questions) === 0) sendError('At least one question is required.');

$db = getDB();

// Verify teacher owns this session
$own = $db->prepare('SELECT id FROM sessions WHERE id=? AND teacher_id=?');
$own->bind_param('ii', $sessionId, $_SESSION['teacher_id']);
$own->execute();
$own->store_result();
if ($own->num_rows === 0) sendError('Session not found or access denied.', 404);
$own->close();

// Delete old questions cleanly (cascade handles answers/confusion)
$del = $db->prepare('DELETE FROM questions WHERE session_id=?');
$del->bind_param('i', $sessionId);
$del->execute();
$del->close();

// Insert new questions
$ins = $db->prepare(
    'INSERT INTO questions
        (session_id, question, option_a, option_b, option_c, option_d, correct, type, timer, sort_order)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

$saved = 0;
foreach ($questions as $i => $q) {
    $text    = trim($q['question']  ?? '');
    $a       = trim($q['option_a']  ?? '');
    $b       = trim($q['option_b']  ?? '');
    $c       = trim($q['option_c']  ?? '');
    $d       = trim($q['option_d']  ?? '');
    $correct = strtoupper(trim($q['correct'] ?? 'A'));
    $type    = in_array($q['type'] ?? '', ['mcq','true_false','short','math']) ? $q['type'] : 'mcq';
    $timer   = intval($q['timer'] ?? 30);
    $order   = (int)$i;

    if (!$text) continue;

    $ins->bind_param('isssssssii', $sessionId, $text, $a, $b, $c, $d, $correct, $type, $timer, $order);
    if ($ins->execute()) $saved++;
}
$ins->close();

if ($saved === 0) sendError('No valid questions were saved. Make sure each question has text.');

sendSuccess(['saved' => $saved], "$saved question(s) saved.");
