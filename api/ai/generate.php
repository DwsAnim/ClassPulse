<?php
// POST /api/ai/generate.php
// Accepts: multipart/form-data with file= (image or PDF) OR json { text: "..." }
// Returns: { success: true, questions: [...] }

require_once __DIR__ . '/../config.php';
if (empty($_SESSION['teacher_id'])) sendError('Not authenticated.', 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed.', 405);

// ── Gemini API Key ────────────────────────────────────────────
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY);

// ── Prompt ────────────────────────────────────────────────────
$PROMPT = <<<'PROMPT'
You are a quiz generator for a classroom app. Analyse the provided note/image and generate quiz questions.

Return ONLY a valid JSON array with NO markdown, no code fences, no extra text.

Each question object must have exactly these fields:
{
  "question": "...",
  "type": "mcq" | "true_false",
  "option_a": "...",
  "option_b": "...",
  "option_c": "...",
  "option_d": "...",
  "correct": "A" | "B" | "C" | "D",
  "timer": 15 | 30 | 60
}

Rules:
- Generate 5 questions minimum, 10 maximum.
- For true_false: option_a must be "True", option_b must be "False", option_c and option_d must be empty strings, correct must be "A" or "B".
- For mcq: all four options must be filled, no empty strings.
- Vary difficulty: mix easy, medium, and hard questions.
- Cover different parts of the material, not just one topic.
- Timer should reflect difficulty: easy=15, medium=30, hard=60.
- Return ONLY the JSON array. No explanation, no markdown.
PROMPT;

// ── Build request parts ───────────────────────────────────────
$parts = [];

if (!empty($_FILES['file']['tmp_name'])) {
    $file     = $_FILES['file'];
    $mimeType = mime_content_type($file['tmp_name']);
    $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'application/pdf'];

    if (!in_array($mimeType, $allowed)) {
        sendError('Unsupported file type. Please upload a JPG, PNG, WEBP, HEIC, or PDF.');
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        sendError('File too large. Maximum size is 10 MB.');
    }

    $base64  = base64_encode(file_get_contents($file['tmp_name']));
    $parts[] = ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]];
} else {
    $body = json_decode(file_get_contents('php://input'), true);
    $text = trim($body['text'] ?? '');
    if (!$text) sendError('No file or text provided.');
    $parts[] = ['text' => "Here are the lecture notes:\n\n" . $text];
}

$parts[] = ['text' => $PROMPT];

// ── Call Gemini ───────────────────────────────────────────────
$payload = json_encode([
    'contents'         => [['parts' => $parts]],
    'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 4096],
]);

$ch = curl_init(GEMINI_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 60,
]);
$raw      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($raw === false) sendError('Failed to reach Gemini API. Check your server internet connection.', 502);

$geminiResp = json_decode($raw, true);

if ($httpCode === 429) {
    $msg  = $geminiResp['error']['message'] ?? '';
    $wait = '';
    if (preg_match('/retry in ([\d.]+)s/', $msg, $m)) {
        $wait = ' Please wait ' . ceil((float)$m[1]) . ' seconds and try again.';
    }
    sendError('Rate limit reached.' . $wait, 429);
}

if (!isset($geminiResp['candidates'])) {
    sendError('Gemini error: ' . ($geminiResp['error']['message'] ?? 'Unknown error.'), 502);
}

// ── Parse response ────────────────────────────────────────────
$text = $geminiResp['candidates'][0]['content']['parts'][0]['text'] ?? '';
if (!$text) sendError('Gemini returned an empty response. Try a clearer image or more detailed notes.', 502);

// Strip accidental markdown fences
$text = preg_replace('/```(?:json)?\s*/i', '', $text);
$text = preg_replace('/```\s*$/',          '', trim($text));

$questions = json_decode(trim($text), true);
if (!is_array($questions) || count($questions) === 0) {
    sendError('Could not parse questions from Gemini response. Please try again with a clearer image.', 502);
}

// ── Sanitize ──────────────────────────────────────────────────
$clean = [];
foreach ($questions as $q) {
    if (empty($q['question'])) continue;

    $type = in_array($q['type'] ?? '', ['mcq', 'true_false']) ? $q['type'] : 'mcq';

    if ($type === 'true_false') {
        $q['option_a'] = 'True';
        $q['option_b'] = 'False';
        $q['option_c'] = '';
        $q['option_d'] = '';
        if (!in_array($q['correct'] ?? '', ['A', 'B'])) $q['correct'] = 'A';
    } else {
        if (empty($q['option_a']) || empty($q['option_b']) || empty($q['option_c']) || empty($q['option_d'])) continue;
        if (!in_array($q['correct'] ?? '', ['A', 'B', 'C', 'D'])) $q['correct'] = 'A';
    }

    $timer   = in_array(intval($q['timer'] ?? 30), [15, 30, 60]) ? intval($q['timer']) : 30;
    $clean[] = [
        'question' => htmlspecialchars_decode(trim($q['question'])),
        'type'     => $type,
        'option_a' => trim($q['option_a'] ?? ''),
        'option_b' => trim($q['option_b'] ?? ''),
        'option_c' => trim($q['option_c'] ?? ''),
        'option_d' => trim($q['option_d'] ?? ''),
        'correct'  => strtoupper($q['correct']),
        'timer'    => $timer,
    ];
}

if (count($clean) === 0) sendError('No valid questions could be extracted. Try a higher quality image.');

sendSuccess(['questions' => $clean], count($clean) . ' question(s) generated.');