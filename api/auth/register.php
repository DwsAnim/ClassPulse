<?php
// =============================================================
//  POST /api/auth/register.php
//  Body (JSON): { full_name, course, password, confirm_password }
//  Returns:     { success, message, teacher: { id, full_name, course } }
// =============================================================

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// --- Read & decode JSON body ---
$body = json_decode(file_get_contents('php://input'), true);

$fullName = trim($body['full_name']        ?? '');
$course   = trim($body['course']           ?? 'General');
$password = $body['password']              ?? '';
$confirm  = $body['confirm_password']      ?? '';

// --- Validation ---
if (!$fullName) {
    sendError('Full name is required.');
}
if (strlen($fullName) > 120) {
    sendError('Full name must be 120 characters or fewer.');
}
if (strlen($password) < 4) {
    sendError('Password must be at least 4 characters.');
}
if ($password !== $confirm) {
    sendError('Passwords do not match.');
}

// --- Check for existing teacher with same name ---
$db   = getDB();
$stmt = $db->prepare('SELECT id FROM teachers WHERE full_name = ? LIMIT 1');
$stmt->bind_param('s', $fullName);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    sendError('An account with this name already exists.');
}
$stmt->close();

// --- Hash password & insert ---
$hash = password_hash($password, PASSWORD_BCRYPT);

$insert = $db->prepare(
    'INSERT INTO teachers (full_name, course, password) VALUES (?, ?, ?)'
);
$insert->bind_param('sss', $fullName, $course, $hash);

if (!$insert->execute()) {
    sendError('Could not create account. Please try again.', 500);
}

$newId = $insert->insert_id;
$insert->close();

// --- Start PHP session so the teacher is logged in immediately ---
$_SESSION['teacher_id']   = $newId;
$_SESSION['teacher_name'] = $fullName;
$_SESSION['teacher_course'] = $course;

sendSuccess([
    'teacher' => [
        'id'        => $newId,
        'full_name' => $fullName,
        'course'    => $course,
    ]
], 'Account created successfully.');
