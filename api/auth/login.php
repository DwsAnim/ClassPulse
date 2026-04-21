<?php
// =============================================================
//  POST /api/auth/login.php
//  Body (JSON): { full_name, password }
//  Returns:     { success, message, teacher: { id, full_name, course } }
// =============================================================

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

$body     = json_decode(file_get_contents('php://input'), true);
$fullName = trim($body['full_name'] ?? '');
$password = $body['password']       ?? '';

if (!$fullName || !$password) {
    sendError('Please enter both name and password.');
}

// --- Fetch teacher by name ---
$db   = getDB();
$stmt = $db->prepare(
    'SELECT id, full_name, course, password FROM teachers WHERE full_name = ? LIMIT 1'
);
$stmt->bind_param('s', $fullName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendError('Invalid name or password.');   // Generic — don't reveal which field is wrong
}

$teacher = $result->fetch_assoc();
$stmt->close();

// --- Verify password against bcrypt hash ---
if (!password_verify($password, $teacher['password'])) {
    sendError('Invalid name or password.');
}

// --- Start session ---
$_SESSION['teacher_id']     = $teacher['id'];
$_SESSION['teacher_name']   = $teacher['full_name'];
$_SESSION['teacher_course'] = $teacher['course'];

sendSuccess([
    'teacher' => [
        'id'        => $teacher['id'],
        'full_name' => $teacher['full_name'],
        'course'    => $teacher['course'],
    ]
], 'Logged in successfully.');
