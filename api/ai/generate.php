<?php
// POST /api/ai/generate.php
// 100% free — uses Groq API for all requests (text + PDF)

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('GROQ_MODEL',   'llama-3.3-70b-versatile');

// ── Prompt builder ────────────────────────────────────────────
function buildPrompt($count, $material) {
    return "Analyse the lecture material below and generate {$count} quiz questions.\n\n"
        . "Return ONLY a raw JSON array. No markdown, no backticks, no explanation.\n\n"
        . "Each object must have EXACTLY these fields:\n"
        . "{\"question\":\"...\",\"type\":\"mcq\",\"option_a\":\"...\",\"option_b\":\"...\","
        . "\"option_c\":\"...\",\"option_d\":\"...\",\"correct\":\"A\",\"timer\":30}\n\n"
        . "TYPE RULES:\n"
        . "- \"mcq\": all four options filled, correct = A/B/C/D\n"
        . "- \"true_false\": option_a=\"True\", option_b=\"False\", option_c=\"\", option_d=\"\", correct=\"A\" or \"B\"\n\n"
        . "QUALITY RULES:\n"
        . "- Each question tests a distinct concept\n"
        . "- MCQ distractors must be plausible\n"
        . "- No duplicate questions\n"
        . "- Mix MCQ and True/False naturally\n"
        . "- Timer: 15=easy, 30=medium, 60=hard\n\n"
        . "MATERIAL:\n" . $material;
}

// ── Convert any string to clean UTF-8 ────────────────────────
function toUtf8($str) {
    if (mb_check_encoding($str, 'UTF-8')) return $str;
    return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
}

// ── PDF extractor (pure PHP, no shell, no composer) ───────────
function extractPdfText($path) {
    // Try pdftotext first (available on cPanel/Linux live servers)
    if (function_exists('shell_exec')) {
        $out = @shell_exec('pdftotext -nopgbrk ' . escapeshellarg($path) . ' - 2>/dev/null');
        if ($out && strlen(trim($out)) > 30) return trim($out);
    }

    $raw  = file_get_contents($path);
    $text = '';

    preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $raw, $matches);

    foreach ($matches[1] as $blob) {
        $dec = false;
        if (function_exists('gzuncompress'))   $dec = @gzuncompress($blob);
        if (!$dec && function_exists('gzinflate')) $dec = @gzinflate(substr($blob, 2));
        if (!$dec && function_exists('gzinflate')) $dec = @gzinflate($blob);
        if (!$dec) continue;

        $dec = is_string($dec) ? $dec : '';

        preg_match_all('/BT\s*(.*?)\s*ET/s', $dec, $blocks);
        foreach ($blocks[1] as $block) {
            preg_match_all('/\[([^\]]*)\]\s*TJ/s', $block, $tjArrays);
            foreach ($tjArrays[1] as $arr) {
                preg_match_all('/\(([^)]*)\)/', $arr, $strs);
                $line = implode('', $strs[1]);
                if (trim($line) !== '') $text .= $line . "\n";
            }
            preg_match_all('/\(([^)]*)\)\s*Tj/', $block, $tj);
            foreach ($tj[1] as $s) {
                if (trim($s) !== '') $text .= $s . "\n";
            }
        }
    }

    $text = trim(preg_replace('/[ \t]{2,}/', ' ', $text));
    return strlen($text) > 30 ? $text : '';
}

// ── Check if a file is really a PDF by its magic bytes ───────
function isPdf($path) {
    $handle = fopen($path, 'rb');
    if (!$handle) return false;
    $header = fread($handle, 5);
    fclose($handle);
    return $header === '%PDF-';
}

// ── Read inputs ───────────────────────────────────────────────
$count     = max(3, min(20, intval($_POST['count'] ?? 8)));
$pasteText = trim($_POST['text'] ?? '');
$file      = $_FILES['file'] ?? null;

// A file exists but had an upload error?
if ($file && isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK && $file['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form size limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded. Please try again.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server missing temp folder.',
        UPLOAD_ERR_CANT_WRITE => 'Server failed to save the file.',
        UPLOAD_ERR_EXTENSION  => 'Upload blocked by server extension.',
    ];
    sendError($uploadErrors[$file['error']] ?? 'Upload error code ' . $file['error']);
}

$hasFile = $file && isset($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']);

if (!$pasteText && !$hasFile) {
    $json      = json_decode(file_get_contents('php://input'), true);
    $pasteText = trim($json['text'] ?? '');
    $count     = max(3, min(20, intval($json['count'] ?? $count)));
}

if (!$pasteText && !$hasFile) sendError('Please provide lecture notes text or upload a file.');

// ── Process uploaded file ─────────────────────────────────────
$noteContent = $pasteText;

if ($hasFile) {
    $tmpPath  = $file['tmp_name'];
    $fileSize = $file['size'];

    if ($fileSize > 10 * 1024 * 1024) sendError('File too large. Maximum 10 MB.');

    // Detect type by magic bytes (more reliable than mime_content_type or browser MIME)
    if (isPdf($tmpPath)) {
        // ── PDF ───────────────────────────────────────────────
        $tmp = tempnam(sys_get_temp_dir(), 'cp_') . '.pdf';
        copy($tmpPath, $tmp);
        $extracted = extractPdfText($tmp);
        @unlink($tmp);

        if (strlen(trim($extracted)) < 30) {
            sendError(
                'Could not extract text from "' . basename($file['name']) . '". ' .
                'It may be a scanned image PDF. Please paste your notes as text instead, ' .
                'or use a PDF exported from Word/Google Docs.'
            );
        }

        $extracted   = toUtf8($extracted);
        $noteContent = strlen($extracted) > 6000 ? substr($extracted, 0, 6000) : $extracted;

    } else {
        // Check image magic bytes
        $handle = fopen($tmpPath, 'rb');
        $magic  = bin2hex(fread($handle, 4));
        fclose($handle);

        $isImage = (
            substr($magic, 0, 6)  === 'ffd8ff' ||  // JPEG
            substr($magic, 0, 8)  === '89504e47' || // PNG
            substr($magic, 0, 8)  === '47494638' || // GIF
            substr($magic, 0, 8)  === '52494646'    // WEBP (RIFF)
        );

        if ($isImage) {
            sendError('Image upload is not supported yet. Please upload a PDF or paste your notes as text.');
        } else {
            sendError(
                'Unsupported file type: "' . basename($file['name']) . '". ' .
                'Please upload a PDF file, or paste your notes as text.'
            );
        }
    }
}

// ── Sanitise to UTF-8 ─────────────────────────────────────────
$noteContent = toUtf8($noteContent);

// ── Call Groq ─────────────────────────────────────────────────
$maxTokens = min(4096, ($count * 160) + 400);

$messages = [
    [
        'role'    => 'system',
        'content' => 'You are an expert academic quiz generator for university and secondary school '
                   . 'lecturers in Nigeria and Africa. You generate clear, accurate, educationally rigorous '
                   . 'quiz questions from lecture notes. '
                   . 'You ALWAYS return ONLY a raw JSON array — no markdown, no backticks, no explanation.',
    ],
    ['role' => 'user', 'content' => buildPrompt($count, $noteContent)],
];

$payload = json_encode([
    'model'       => GROQ_MODEL,
    'messages'    => $messages,
    'temperature' => 0.3,
    'max_tokens'  => $maxTokens,
    'stream'      => false,
], JSON_UNESCAPED_UNICODE);

if ($payload === false) sendError('Failed to encode request: ' . json_last_error_msg(), 500);

$ch = curl_init(GROQ_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY,
    ],
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
]);

$raw      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr || $raw === false) sendError('Could not reach Groq API: ' . $curlErr, 503);

$resp = json_decode($raw, true);

if ($httpCode !== 200) {
    if ($httpCode === 429) sendError('Rate limit reached. Please wait a moment and try again.', 429);
    if ($httpCode === 401) sendError('Invalid Groq API key. Check config.php.', 401);
    sendError('AI error: ' . ($resp['error']['message'] ?? 'HTTP ' . $httpCode . ' — ' . substr($raw, 0, 200)), 502);
}

// ── Parse + rescue partial JSON if Groq truncated ────────────
$body         = $resp['choices'][0]['message']['content'] ?? '';
$finishReason = $resp['choices'][0]['finish_reason'] ?? '';

if (!$body) sendError('AI returned empty response. Please try again.', 502);

if ($finishReason === 'length') {
    $last = strrpos($body, '},');
    $body = $last !== false
        ? substr($body, 0, $last + 1) . ']'
        : rtrim($body, " ,\n\r") . ']';
}

$body   = trim(preg_replace(['/^```(?:json)?\s*/im', '/```\s*$/m'], '', $body));
$parsed = json_decode($body, true);

if (!is_array($parsed) && preg_match('/\[[\s\S]*\]/m', $body, $m)) {
    $parsed = json_decode($m[0], true);
}
if (isset($parsed['questions']) && is_array($parsed['questions'])) {
    $parsed = $parsed['questions'];
}
if (!is_array($parsed) || count($parsed) === 0) {
    sendError('Could not parse AI response. Please try again.');
}

// ── Sanitise questions ────────────────────────────────────────
$clean = [];
foreach ($parsed as $q) {
    if (empty($q['question'])) continue;
    $type = in_array($q['type'] ?? '', ['mcq','true_false']) ? $q['type'] : 'mcq';
    $corr = strtoupper($q['correct'] ?? 'A');

    if ($type === 'true_false') {
        $clean[] = [
            'question' => trim(strip_tags($q['question'])),
            'type'     => 'true_false',
            'option_a' => 'True', 'option_b' => 'False', 'option_c' => '', 'option_d' => '',
            'correct'  => in_array($corr, ['A','B']) ? $corr : 'A',
            'timer'    => in_array(intval($q['timer'] ?? 20), [15,30,60]) ? intval($q['timer']) : 20,
        ];
    } else {
        if (empty($q['option_a']) || empty($q['option_b'])) continue;
        $clean[] = [
            'question' => trim(strip_tags($q['question'])),
            'type'     => 'mcq',
            'option_a' => trim($q['option_a']), 'option_b' => trim($q['option_b']),
            'option_c' => trim($q['option_c'] ?? ''), 'option_d' => trim($q['option_d'] ?? ''),
            'correct'  => in_array($corr, ['A','B','C','D']) ? $corr : 'A',
            'timer'    => in_array(intval($q['timer'] ?? 30), [15,30,60]) ? intval($q['timer']) : 30,
        ];
    }
}

if (count($clean) === 0) sendError('No valid questions generated. Try again with more detailed notes.');

sendSuccess(['questions' => $clean], count($clean) . ' questions generated successfully.');