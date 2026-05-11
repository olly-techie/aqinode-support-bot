<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

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

$systemPrompt = "You are AqiNode Support Assistant. Answer ONLY using provided website context. If info is missing, say 'Not found in AqiNode docs'. Be concise and helpful. Slight Gen-Z tone but professional.

CONTEXT:
" . ($context ?: "No relevant context found.");

$data = [
    'model' => 'deepseek-r1-distill-llama-70b',
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
