<?php

// knowledge.php - Retrieval logic

function get_relevant_context($query, $knowledgeFile = 'knowledge.json') {
    if (!file_exists($knowledgeFile)) {
        return "";
    }

    $knowledge = json_decode(file_get_contents($knowledgeFile), true);
    if (!$knowledge) return "";

    // Simple keyword-based retrieval (since we can't easily use embeddings in pure PHP without external libs)
    // We'll search for query words in the content
    $words = explode(' ', strtolower($query));
    $relevant_chunks = [];

    foreach ($knowledge as $page) {
        $score = 0;
        $content = strtolower($page['content']);
        foreach ($words as $word) {
            if (strlen($word) > 3 && strpos($content, $word) !== false) {
                $score++;
            }
        }

        if ($score > 0) {
            $relevant_chunks[] = [
                'score' => $score,
                'content' => $page['content']
            ];
        }
    }

    // Sort by score
    usort($relevant_chunks, function($a, $b) {
        return $b['score'] - $a['score'];
    });

    // Limit context size
    $context = "";
    $count = 0;
    foreach ($relevant_chunks as $chunk) {
        $context .= $chunk['content'] . "\n\n";
        if (++$count >= 3) break;
    }

    return trim($context);
}
