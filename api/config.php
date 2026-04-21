<?php
// =============================================================
//  ClassPulse — Database Configuration
//  Compatible with PHP 7.4+  (XAMPP default)
// =============================================================

// ── 1. Catch ALL fatal errors and return JSON (never blank 500) ─
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'PHP Fatal Error: ' . $err['message'] .
                         ' in ' . basename($err['file']) . ' line ' . $err['line']
        ]);
    }
});

// ── 2. Session — must start before any output ──────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 3. CORS headers ────────────────────────────────────────────
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ── 4. DB credentials ──────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'joerexs1_classpulse');
define('DB_PASS', 'kinagnim2.0');        // XAMPP default: empty
define('DB_NAME', 'joerexs1_classpulse');

// ── 5. DB connection ───────────────────────────────────────────
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'message' => 'DB connection failed: ' . $conn->connect_error
        ]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// ── 6. Response helpers ────────────────────────────────────────
// NOTE: Using array_merge instead of spread operator (...$data)
// so this works on PHP 7.4 and PHP 8.x equally.
function sendSuccess($data = [], $message = 'OK') {
    echo json_encode(array_merge(
        ['success' => true, 'message' => $message],
        $data
    ));
    exit;
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}
