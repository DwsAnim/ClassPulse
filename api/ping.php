<?php
// =============================================================
//  GET /api/ping.php
//  Quick health-check — open this in browser to verify:
//  1. PHP is running
//  2. config.php loads without errors
//  3. Database connects successfully
//  4. Session works
//  DELETE this file before going to production.
// =============================================================

require_once __DIR__ . '/config.php';

$db      = getDB();
$phpVer  = phpversion();
$dbCheck = $db->query('SELECT 1 AS ok')->fetch_assoc();

sendSuccess([
    'php_version'  => $phpVer,
    'db_connected' => $dbCheck['ok'] === '1' ? true : false,
    'session_id'   => session_id(),
    'logged_in_as' => $_SESSION['teacher_id'] ?? null,
], 'ClassPulse API is alive!');
