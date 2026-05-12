<?php

header('Content-Type: application/json');

// --- SECURITY HARDENING ---
// 1. Restrict CORS (Update this to your actual frontend domain)
$allowedOrigins = [
    'https://aqinode-support-bot.onrender.com',
    'https://aqinode.click',
    'http://localhost:8000', // For local testing
    'http://127.0.0.1:8000'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Content-Security-Policy: default-src \'none\';'); // Minimal backend CSP

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// 2. Basic Rate Limiting (File-based)
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitFile = __DIR__ . '/ratelimit_' . md5($ip) . '.txt';
$now = time();
$limit = 10; // 10 requests
$window = 60; // per 60 seconds

if (file_exists($rateLimitFile)) {
    $data = json_decode(file_get_contents($rateLimitFile), true);
    if ($now - $data['start'] < $window) {
        if ($data['count'] >= $limit) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests. Please wait a minute.']);
            exit;
        }
        $data['count']++;
    } else {
        $data = ['start' => $now, 'count' => 1];
    }
} else {
    $data = ['start' => $now, 'count' => 1];
}
file_put_contents($rateLimitFile, json_encode($data));
// --- END SECURITY HARDENING ---

require_once 'knowledge.php';

// Simple .env loader
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

loadEnv(__DIR__ . '/.env');

$apiKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY');

if (!$apiKey) {
    echo json_encode(['error' => 'API Key not configured']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

$context = get_relevant_context($userMessage);

/**
 * 2026 Standard System Prompt for AqiNode Support Agent
 * High-performance grounding, personality consistency, and structured output.
 */
$systemPrompt = "### IDENTITY & ROLE
You are the **AqiNode Support Assistant**, a high-intelligence digital representative of AqiNode—a visionary tech startup. Your personality is 'Tech-Native': highly efficient, slightly informal (Gen-Z influenced), professional, and deeply knowledgeable about the AqiNode ecosystem.

### OPERATIONAL DIRECTIVES
1. **GROUNDING FIRST:** Use the provided CONTEXT as your primary source of truth. 
2. **FALLBACK PROTOCOL:** If the specific answer isn't in the context, use your internal knowledge of AqiNode as an AI-first software company to provide a helpful, high-level response. Never say 'I don't know' for basic company identity questions.
3. **CONVERSATIONAL FLOW:** 
   - Acknowledge greetings (Hi, Yo, Hello) with energy.
   - For 'Who/What' questions, be punchy and value-driven.
   - For technical questions, be precise but accessible.
4. **TONE & STYLE:** 
   - Use concise, impact-heavy sentences. 
   - Avoid 'corporate speak'. Use terms like 'shipping', 'scaling', 'vibe', 'clean', 'stack'.
   - Format with markdown for readability (bolding, lists).

### BASE IDENTITY (Always True)
- **AqiNode:** A multi-product tech startup building intelligent software systems.
- **Pillars:** AI Agents, Web Development, Scalable SaaS, Cloud Infrastructure.
- **Vibe:** Building the future, shipping fast, intelligence-first.

### CONTEXT FOR THIS REQUEST
" . ($context ?: "No specific page context found. Revert to Base Identity for responses.");

$data = [
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userMessage]
    ],
    'temperature' => 0.6,
    'max_tokens' => 2048
];

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['error' => 'Groq API Error', 'details' => json_decode($response)]);
    exit;
}

$result = json_decode($response, true);
$botResponse = $result['choices'][0]['message']['content'] ?? 'Sorry, I encountered an error.';

echo json_encode(['response' => $botResponse]);
