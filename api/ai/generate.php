<?php
// POST /api/ai/generate.php
// Uses xAI Grok API (OpenAI-compatible format)
// Accepts: multipart/form-data with file= (image/PDF) OR JSON { text: "..." }
// Returns: { success, questions: [...], message }

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

// ── xAI Grok credentials ──────────────────────────────────────
define('GROK_API_URL', 'https://api.x.ai/v1/chat/completions');
define('GROK_MODEL',   'grok-3-mini');   // fast, free-tier model

// ── System prompt ─────────────────────────────────────────────
$SYSTEM_PROMPT = 'You are an expert academic quiz generator for university and secondary school lecturers. '
    . 'You generate clear, accurate, educationally sound quiz questions from lecture notes or images. '
    . 'You always return ONLY valid raw JSON — no markdown fences, no explanation, nothing else before or after the JSON.';

// ── User prompt ───────────────────────────────────────────────
$USER_TEMPLATE = <<<'PROMPT'
Analyse the lecture notes below and generate {COUNT} quiz questions.

Return ONLY a raw JSON array (no markdown, no backticks, no explanation).
Each item must have EXACTLY these fields:

{
  "question": "Question text here",
  "type": "mcq",
  "option_a": "...",
  "option_b": "...",
  "option_c": "...",
  "option_d": "...",
  "correct": "A",
  "timer": 30
}

TYPE RULES:
- "mcq": all four options filled, correct = A/B/C/D
- "true_false": option_a = "True", option_b = "False", option_c = "", option_d = "", correct = "A" (true) or "B" (false)

QUALITY RULES:
- Each question tests a specific concept from the notes
- MCQ distractors must be plausible, not obviously wrong
- No repeated questions on the same concept
- Mix MCQ and True/False naturally
- Timer: 15 for easy, 30 for medium, 60 for hard

NOTES:
PROMPT;

// ── Read inputs ───────────────────────────────────────────────
$count     = max(3, min(20, intval($_POST['count'] ?? $_GET['count'] ?? 8)));
$pasteText = trim($_POST['text'] ?? '');
$file      = $_FILES['file'] ?? null;
$hasFile   = $file && isset($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK;

// Also support JSON body (for text-only requests)
if (!$pasteText && !$hasFile) {
    $jsonBody  = json_decode(file_get_contents('php://input'), true);
    $pasteText = trim($jsonBody['text'] ?? '');
}

if (!$pasteText && !$hasFile) {
    sendError('Please provide lecture notes text or upload a file (image or PDF).');
}

// ── Build the message content ─────────────────────────────────
$userPrompt = str_replace('{COUNT}', $count, $USER_TEMPLATE);
$messages   = [
    ['role' => 'system', 'content' => $SYSTEM_PROMPT],
];

if ($hasFile) {
    // ── File handling ─────────────────────────────────────────
    $mimeType = mime_content_type($file['tmp_name']);
    $allowed  = ['image/jpeg','image/png','image/webp','image/gif','image/heic','application/pdf'];

    if (!in_array($mimeType, $allowed)) {
        sendError('Unsupported file type. Please upload JPG, PNG, WEBP, HEIC, or PDF.');
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        sendError('File too large. Maximum 10 MB.');
    }

    if ($mimeType === 'application/pdf') {
        // Try pdftotext first (available on many servers)
        $tmpPath = tempnam(sys_get_temp_dir(), 'cp_') . '.pdf';
        move_uploaded_file($file['tmp_name'], $tmpPath);
        $extracted = '';
        if (function_exists('shell_exec')) {
            $extracted = shell_exec('pdftotext -nopgbrk ' . escapeshellarg($tmpPath) . ' - 2>/dev/null');
        }
        @unlink($tmpPath);

        if ($extracted && strlen(trim($extracted)) > 30) {
            // Got text from PDF — send as text
            $messages[] = [
                'role'    => 'user',
                'content' => $userPrompt . "\n\n" . trim($extracted),
            ];
        } else {
            // No pdftotext — send as base64 image content
            // Grok vision supports PDF pages as images
            $b64 = base64_encode(file_get_contents($tmpPath ?: $file['tmp_name']));
            $messages[] = [
                'role'    => 'user',
                'content' => [
                    ['type' => 'text',      'text'      => $userPrompt],
                    ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$b64}"]],
                ],
            ];
        }
    } else {
        // Image — send as vision content (Grok supports multimodal)
        $b64 = base64_encode(file_get_contents($file['tmp_name']));
        $messages[] = [
            'role'    => 'user',
            'content' => [
                ['type' => 'text',      'text'      => $userPrompt],
                ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$b64}"]],
            ],
        ];
    }
} else {
    // Plain text notes
    $messages[] = [
        'role'    => 'user',
        'content' => $userPrompt . "\n\n" . $pasteText,
    ];
}

// ── Call Grok API ─────────────────────────────────────────────
$payload = json_encode([
    'model'       => GROK_MODEL,
    'messages'    => $messages,
    'temperature' => 0.3,    // lower = more factual, consistent
    'max_tokens'  => 4096,
    'stream'      => false,
]);

$ch = curl_init(GROK_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROK_API_KEY,
    ],
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$raw      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// ── Handle errors ─────────────────────────────────────────────
if ($curlErr) {
    sendError('Could not reach Grok API: ' . $curlErr, 503);
}

$resp = json_decode($raw, true);

if ($httpCode !== 200) {
    $errMsg = $resp['error']['message'] ?? ('Grok API error (HTTP ' . $httpCode . ')');
    // Rate limit
    if ($httpCode === 429) {
        sendError('Rate limit reached. Please wait a moment and try again.', 429);
    }
    // Auth error
    if ($httpCode === 401) {
        sendError('Invalid Grok API key. Please check your configuration.', 401);
    }
    sendError('Grok error: ' . $errMsg, 502);
}

// ── Extract generated text ────────────────────────────────────
$generatedText = $resp['choices'][0]['message']['content'] ?? '';
if (!$generatedText) {
    sendError('Grok returned an empty response. Please try again.', 502);
}

// Strip any accidental markdown fences
$generatedText = preg_replace('/^```(?:json)?\s*/im', '', $generatedText);
$generatedText = preg_replace('/```\s*$/m', '', $generatedText);
$generatedText = trim($generatedText);

// ── Parse JSON ────────────────────────────────────────────────
// Handle both array [...] and object {"questions":[...]}
$parsed = json_decode($generatedText, true);
if (!$parsed) {
    // Try extracting JSON array if there's extra text
    if (preg_match('/\[[\s\S]*\]/m', $generatedText, $m)) {
        $parsed = json_decode($m[0], true);
    }
}

// Unwrap {"questions": [...]} if needed
if (isset($parsed['questions']) && is_array($parsed['questions'])) {
    $parsed = $parsed['questions'];
}

if (!is_array($parsed) || count($parsed) === 0) {
    sendError('Could not parse questions from AI response. Please try again with clearer notes.', 502);
}

// ── Sanitise & validate each question ────────────────────────
$clean = [];
foreach ($parsed as $q) {
    if (empty($q['question'])) continue;

    $type = in_array($q['type'] ?? '', ['mcq','true_false','math']) ? $q['type'] : 'mcq';

    if ($type === 'true_false') {
        // Enforce True/False structure
        $q['option_a'] = 'True';
        $q['option_b'] = 'False';
        $q['option_c'] = '';
        $q['option_d'] = '';
        $corr = strtoupper($q['correct'] ?? 'A');
        if (!in_array($corr, ['A','B'])) $corr = 'A';
        $q['correct'] = $corr;
    } else {
        // MCQ — skip if options missing
        if (empty($q['option_a']) || empty($q['option_b'])) continue;
        $corr = strtoupper($q['correct'] ?? 'A');
        if (!in_array($corr, ['A','B','C','D'])) $corr = 'A';
        $q['correct'] = $corr;
    }

    $timer = intval($q['timer'] ?? 30);
    if (!in_array($timer, [15, 30, 60])) $timer = 30;

    $clean[] = [
        'question' => trim(strip_tags($q['question'])),
        'type'     => $type,
        'option_a' => trim($q['option_a'] ?? ''),
        'option_b' => trim($q['option_b'] ?? ''),
        'option_c' => trim($q['option_c'] ?? ''),
        'option_d' => trim($q['option_d'] ?? ''),
        'correct'  => $q['correct'],
        'timer'    => $timer,
    ];
}

if (count($clean) === 0) {
    sendError('No valid questions were extracted. Try again with more detailed notes.');
}

sendSuccess(
    ['questions' => $clean],
    count($clean) . ' question(s) generated successfully.'
);