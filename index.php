<?php
declare(strict_types=1);
/**
 * dev-handbook - Markdown to HTML converter
 */

require_once __DIR__ . '/vendor/Parsedown/Parsedown.php';

$availableLanguages = ['ru', 'sr'];
$availableSections = ['db', 'oop', 'php8'];
$defaultLanguage = 'en';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 0);

$requestUri = $_SERVER['REQUEST_URI'];
if (str_contains($requestUri, '?')) {
    $requestUri = substr($requestUri, 0, strpos($requestUri, '?'));
}
$requestUri = rtrim($requestUri, '/');

$parts = explode('/', trim($requestUri, '/'));
$language = $defaultLanguage;
$section = '';

// Determine language and section based on URL parts
if (count($parts) > 0) {
    // Check if the first part is a language code
    if (count($parts) >= 1 && in_array($parts[0], $availableLanguages)) {
        $language = $parts[0];
        array_shift($parts);
    }

    // Check if the next part is a section
    if (count($parts) >= 1) {
        $section = $parts[0];
    }
}

// Determine which file to load
$filePath = '';

if (empty($section)) {
    // Main page for the selected language
    if ($language === $defaultLanguage) {
        $filePath = 'README.md';
    } else {
        $filePath = "README.$language.md";
    }
} else {
    // Section page for the selected language
    if ($language === $defaultLanguage) {
        $filePath = "en.$section.md";
    } else {
        $filePath = "$language.$section.md";
    }
}

// Check if the file exists and is readable
if (!file_exists($filePath)) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1>";
    echo "<p>The requested file '$filePath' does not exist.</p>";
    exit;
}

if (!is_readable($filePath)) {
    header("HTTP/1.0 500 Internal Server Error");
    echo "<h1>500 Internal Server Error</h1>";
    echo "<p>The file '$filePath' exists but cannot be read.</p>";
    exit;
}

// Read the Markdown content
$markdownContent = file_get_contents($filePath);

// Check if the file was read successfully
if ($markdownContent === false) {
    header("HTTP/1.0 500 Internal Server Error");
    echo "<h1>500 Internal Server Error</h1>";
    echo "<p>Failed to read the file '$filePath'.</p>";
    exit;
}

// Convert Markdown to HTML
[$htmlContent, $htmlMenu] = parseMarkdown($markdownContent, $defaultLanguage, $availableLanguages, $availableSections);

// Create the HTML page
$html = createHtmlPage($htmlContent, $htmlMenu);

// Output the HTML
echo $html;

function parseMarkdown(
    string $markdown,
    string $defaultLanguage,
    array $availableLanguages,
    array $availableSections
): array {
    $parsedown = new Parsedown();
    $menu = '';

    $markdown  = $parsedown->text($markdown);
    $markdown  = processAnchors($markdown);
    $markdown  = processSiteLinks($markdown, $defaultLanguage, $availableLanguages, $availableSections);

    return [$markdown, $menu];
}

function processAnchors(string $markdown): string
{
    $pattern = '/<h([1-6])>(.*?)<\/h\1>/';

    return preg_replace_callback($pattern, function($matches) {
        $level = $matches[1];
        $text = $matches[2];
        $id = createHeadingId($text);

        return "<h{$level} id=\"{$id}\">{$text}</h{$level}>";
    }, $markdown);
}

function createHeadingId(string $text): string
{
    $text = strip_tags($text);
    $text = mb_strtolower($text, 'UTF-8');
    //$text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
    //$text = preg_replace('/[\s]+/', '-', $text);
    //$text = preg_replace('/-+/', '-', $text);

    // Заменяем пробелы на дефисы
    $text = preg_replace('/\s+/', '-', $text);

    // Заменяем множественные дефисы на одинарный
    //$text = preg_replace('/-+/', '-', $text);


    //return trim($text, '-');

    return $text;
}


function processSiteLinks(
    string $markdown,
    string $defaultLanguage,
    array $availableLanguages,
    array $availableSections,
): string {
    $mdLinks = [];
    $siteLinks = [];

    $mdLinks[] = 'README.md';
    $siteLinks[] = '/';
    foreach ($availableLanguages as $language) {
        $mdLinks[] = "README.$language.md";
        $siteLinks[] = "/$language/";

        foreach ($availableSections as $section) {
            $mdLinks[] = "$language.$section.md";
            $siteLinks[] = "/$language/$section/";
        }
    }

    $markdown = str_replace($mdLinks, $siteLinks, $markdown);
    return $markdown;
}

function createHtmlPage(string $content, string $menu): string
{
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Ysabeau+SC:wght@1..1000&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/darcula.css">
    <title>dev-handbook</title>
    <style>
    body {
  background-color: #1b1b1b;
  color: #a9b7c6;
  font-family: "Ysabeau SC", serif;
  line-height: 1.6;
  margin: 0 auto;
  padding: 10px;
  box-sizing: border-box;
}

h1, h2, h3, h4 {
  color: #ffffff;
  font-weight: 600;
  border-bottom: 1px solid #444;
  padding-bottom: 0.3em;
  margin-top: 2em;
}

a {
  color: #4eade5; /* ссылки как XML-теги */
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}

pre {
  padding: 0;
  border-radius: 6px;
  overflow-x: auto;
  font-size: 0.95em;
  background: #2b2b2b;
  color: #a9b7c6;
}

code {
  font-family: 'Fira Code', monospace;
  background: #2b2b2b;
  padding: 0.2em 0.4em;
  border-radius: 4px;
  color: #ffcb6b;
}

pre code {
  display: block;
}

code p {
  padding: 0;
  margin: 0;
  display: inline-block;
}

pre p code {
  display: none;
}

p code, li code {
  font-size: 0.95em;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1.5em;
  background-color: #2d2d3a;
}

th, td {
  padding: 0.8em;
  border: 1px solid #444;
  text-align: left;
}

th {
  background-color: #3a3a4f;
  color: #f0f0f0;
  font-weight: 600;
}

blockquote {
  border-left: 4px solid #606366;
  padding-left: 1em;
  color: #aaa;
  margin: 1em 0;
}

ul, ol {
  padding-left: 1.5em;
}

img {
  max-width: 100%;
  height: auto;
}

nav {
  background-color: #2b2b2b;
  width: 250px;
  padding: 1em;
  overflow-y: auto;
  transition: transform 0.3s ease;
  position: fixed;
}

nav h2 {
  color: #ffffff;
  font-size: 1.2em;
  margin-bottom: 0.5em;
}

nav a {
  display: block;
  color: #4eade5;
  text-decoration: none;
  margin: 0.4em 0;
}
nav a:hover {
  text-decoration: underline;
}

.content {
  flex-grow: 1;
  overflow-y: auto;
  padding: 0 0 0 300px;
  max-width: 1400px;
}

.menu-toggle {
  display: none;
  position: absolute;
  top: 1em;
  left: 1em;
  background: #3a3a4f;
  color: #fff;
  border: none;
  padding: 0.5em 1em;
  border-radius: 4px;
  cursor: pointer;
  z-index: 1000;
}

.no-under-line, .no-under-line:hover {
  text-decoration: none;
}

@media (max-width: 768px) {
  nav {
    position: absolute;
    height: auto;
    transform: translateX(-100%);
    z-index: 999;
    padding-top: 40px;
  }

  nav.open {
    transform: translateX(0);
  }

  .menu-toggle {
    display: block;
  }

  .content {
    padding: 10px;
  }
}
    </style>
</head>
<body>
  <button class="menu-toggle" onclick="document.querySelector('nav').classList.toggle('open')">Menu</button>
  <nav>
    $menu
    
    <br><br>
    <a class="no-under-line" href="https://nozhove.com" target="_blank"><img src="https://nozhove.com/nozhove_pixel.png" width="40" alt="Nozhove.com"></a>
  </nav>
  <div class="content">
    $content
  </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/highlight.min.js"></script>
    <script>
        document.querySelectorAll('nav a').forEach(link => {
          link.addEventListener('click', () => {
            document.querySelector('nav').classList.remove('open');
          });
        });
    </script>
    <script>hljs.highlightAll();</script>
</body>
</html>
HTML;
    return $html;
}
