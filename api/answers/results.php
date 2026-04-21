<?php
// GET /api/answers/results.php?question_id=X
// Returns answer distribution for a question (teacher live chart)

require_once __DIR__ . '/../config.php';
$questionId = intval($_GET['question_id'] ?? 0);
if (!$questionId) sendError('question_id required.');

$db   = getDB();
$stmt = $db->prepare(
    "SELECT answer, COUNT(*) as count FROM answers WHERE question_id=? GROUP BY answer"
);
$stmt->bind_param('i', $questionId);
$stmt->execute();
$rows = $stmt->get_result();
$dist = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];
while ($r = $rows->fetch_assoc()) {
    if (isset($dist[$r['answer']])) $dist[$r['answer']] = (int)$r['count'];
}
$stmt->close();

// Confusion meter
$cf = $db->prepare(
    "SELECT feeling, COUNT(*) as count FROM confusion WHERE question_id=? GROUP BY feeling"
);
$cf->bind_param('i', $questionId);
$cf->execute();
$cfRows = $cf->get_result();
$confusion = ['got_it' => 0, 'unsure' => 0, 'lost' => 0];
while ($r = $cfRows->fetch_assoc()) {
    if (isset($confusion[$r['feeling']])) $confusion[$r['feeling']] = (int)$r['count'];
}
$cf->close();

sendSuccess(['distribution' => $dist, 'confusion' => $confusion]);
