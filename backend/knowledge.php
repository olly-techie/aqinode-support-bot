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
    $words = explode(' ', strtolower(trim($query)));
    $relevant_chunks = [];
    $stop_words = ['what', 'where', 'when', 'how', 'who', 'are', 'you', 'is', 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'with', 'about'];

    foreach ($knowledge as $page) {
        $score = 0;
        $content = strtolower($page['content']);
        
        // Boost for common identity questions
        if (strpos($content, 'aqinode') !== false) {
            if (preg_match('/(who are you|what is aqinode)/i', $query)) {
                $score += 5;
            }
        }

        foreach ($words as $word) {
            $word = trim($word, '?,.!');
            if (empty($word) || in_array($word, $stop_words)) continue;
            
            if (strlen($word) > 2 && strpos($content, $word) !== false) {
                $score += 2;
                // Extra points for exact word matches (not just substrings)
                if (preg_match("/\b" . preg_quote($word, '/') . "\b/", $content)) {
                    $score += 3;
                }
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
