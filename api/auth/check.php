<?php
// =============================================================
//  GET /api/auth/check.php
//  Returns the currently logged-in teacher from session,
//  or 401 if not authenticated.
//  The frontend calls this on every dashboard load.
// =============================================================

require_once __DIR__ . '/../config.php';


if (empty($_SESSION['teacher_id'])) {
    sendError('Not authenticated.', 401);
}

sendSuccess([
    'teacher' => [
        'id'        => $_SESSION['teacher_id'],
        'full_name' => $_SESSION['teacher_name'],
        'course'    => $_SESSION['teacher_course'],
    ]
]);
