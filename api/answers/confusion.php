<?php
// =============================================================
//  POST /api/answers/confusion.php
//  Body (JSON): { question_id, student_id, feeling }
//  feeling: "got_it" | "unsure" | "lost"
//  Public — no teacher auth needed.
// =============================================================

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

$body       = json_decode(file_get_contents('php://input'), true);
$questionId = (int)($body['question_id'] ?? 0);
$studentId  = (int)($body['student_id']  ?? 0);
$feeling    = trim($body['feeling']      ?? '');

if (!$questionId) sendError('question_id is required.');
if (!$studentId)  sendError('student_id is required.');

$allowed = ['got_it', 'unsure', 'lost'];
if (!in_array($feeling, $allowed)) {
    sendError('feeling must be got_it, unsure, or lost.');
}

$db = getDB();

// Upsert — replace if student already submitted for this question
$stmt = $db->prepare(
    'INSERT INTO confusion (question_id, student_id, feeling)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE feeling = VALUES(feeling)'
);
$stmt->bind_param('iis', $questionId, $studentId, $feeling);

if (!$stmt->execute()) {
    // Table may not have UNIQUE key yet — fall back to insert-or-ignore
    $stmt->close();
    $ins = $db->prepare(
        'INSERT IGNORE INTO confusion (question_id, student_id, feeling) VALUES (?, ?, ?)'
    );
    $ins->bind_param('iis', $questionId, $studentId, $feeling);
    $ins->execute();
    $ins->close();
}

sendSuccess([], 'Feeling recorded.');
