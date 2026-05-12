<?php

// Basic Crawler for AqiNode
// This script will:
// 1. Start from a base URL
// 2. Crawl internal links
// 3. Extract text content
// 4. Save to a JSON file for retrieval

set_time_limit(0); // Allow long execution

class Crawler {
    private $baseUrl;
    private $visited = [];
    private $queue = [];
    private $knowledge = [];

    public function __construct($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function crawl() {
        if (php_sapi_name() !== 'cli') echo "<pre>";
        $this->queue[] = $this->baseUrl;

        while (!empty($this->queue)) {
            $url = array_shift($this->queue);
            if (in_array($url, $this->visited)) continue;

            echo "Crawling: $url\n";
            $this->visited[] = $url;

            $html = @file_get_contents($url);
            if ($html === false) continue;

            $this->extractContent($url, $html);
            $this->findLinks($html);
        }

        $this->saveKnowledge();
    }

    private function extractContent($url, $html) {
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        
        // Remove scripts and styles
        foreach (['script', 'style', 'nav', 'footer', 'header'] as $tag) {
            $nodes = $dom->getElementsByTagName($tag);
            while ($nodes->length > 0) {
                $nodes->item(0)->parentNode->removeChild($nodes->item(0));
            }
        }

        $text = strip_tags($dom->saveHTML());
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (!empty($text)) {
            $this->knowledge[] = [
                'url' => $url,
                'content' => $text
            ];
        }
    }

    private function findLinks($html) {
        preg_match_all('/<a\s+[^>]*href="([^"]*)"/i', $html, $matches);
        foreach ($matches[1] as $link) {
            $link = $this->normalizeUrl($link);
            if ($link && $this->isInternal($link) && !in_array($link, $this->visited)) {
                $this->queue[] = $link;
            }
        }
        $this->queue = array_unique($this->queue);
    }

    private function normalizeUrl($url) {
        if (strpos($url, '#') !== false) $url = explode('#', $url)[0];
        if (empty($url)) return null;

        if (strpos($url, 'http') === 0) return $url;
        if (strpos($url, '//') === 0) return 'https:' . $url;
        if (strpos($url, '/') === 0) return $this->baseUrl . $url;
        
        return $this->baseUrl . '/' . $url;
    }

    private function isInternal($url) {
        return strpos($url, $this->baseUrl) === 0;
    }

    private function saveKnowledge() {
        file_put_contents('knowledge.json', json_encode($this->knowledge, JSON_PRETTY_PRINT));
        echo "Knowledge saved to knowledge.json\n";
    }
}

// Usage: php crawl.php or browse to crawl.php?token=YOUR_SECRET_TOKEN

// Simple security check for web access
if (php_sapi_name() !== 'cli') {
    $token = $_GET['token'] ?? '';
    $secret = getenv('CRAWL_TOKEN') ?: 'change-this-to-a-secure-key'; // Use env var if available
    if ($token !== $secret) {
        die('Unauthorized');
    }
}

$crawler = new Crawler('https://aqinode.click');
$crawler->crawl();

