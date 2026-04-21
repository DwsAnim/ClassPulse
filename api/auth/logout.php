<?php
// =============================================================
//  POST /api/auth/logout.php
//  Destroys the PHP session.
//  Returns: { success, message }
// =============================================================

require_once __DIR__ . '/../config.php';

session_unset();
session_destroy();

sendSuccess([], 'Logged out.');
