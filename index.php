<?php
declare(strict_types=1);
/**
 * dev-handbook - Markdown to HTML converter
 */

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
$anchor = '';

// Extract anchor if present
$lastPart = end($parts);
if (str_starts_with($lastPart, '-')) {
    $anchor = $lastPart;
    array_pop($parts);
}

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

//die('language: ' . $language . '; defaultLanguage:' . $defaultLanguage . '; section: ' . $section . '; anchor: ' . $anchor);

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
$htmlContent = parseMarkdown($markdownContent, $defaultLanguage, $availableLanguages, $availableSections);

// Create the HTML page
$html = createHtmlPage($htmlContent, $anchor);

// Output the HTML
echo $html;

function parseMarkdown(
    string $markdown,
    string $defaultLanguage,
    array $availableLanguages,
    array $availableSections
): string {
    // Basic Markdown parsing

    // Replace headers with proper ID attributes for anchors
    $markdown = preg_replace_callback('/^# (.*?)$/m', function($matches) {
        $id = generateAnchorId($matches[1]);
        return "<h1 id=\"$id\">{$matches[1]}</h1>";
    }, $markdown);

    $markdown = preg_replace_callback('/^## (.*?)$/m', function($matches) {
        $id = generateAnchorId($matches[1]);
        return "<h2 id=\"$id\">{$matches[1]}</h2>";
    }, $markdown);

    $markdown = preg_replace_callback('/^### (.*?)$/m', function($matches) {
        $id = generateAnchorId($matches[1]);
        return "<h3 id=\"$id\">{$matches[1]}</h3>";
    }, $markdown);

    // Process tables
    $markdown = processMarkdownTables($markdown);

    // Replace links
    $markdown = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $markdown);

    // Replace bold text
    $markdown = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $markdown);

    // Replace italic text
    $markdown = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $markdown);

    // Replace code blocks
    $markdown = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $markdown);

    // Replace inline code
    $markdown = preg_replace('/`(.*?)`/', '<code>$1</code>', $markdown);

    // Process lists (including nested lists)
    $markdown = processMarkdownLists($markdown);

    // Replace paragraphs (but not inside lists or tables)
    $markdown = preg_replace('/^(?!<[a-z])(.*?)$/m', '<p>$1</p>', $markdown);

    // Replace images
    $markdown = preg_replace('/!\[(.*?)\]\((.*?)\)/', '<img src="$2" alt="$1">', $markdown);

    // Replace horizontal rules
    $markdown = preg_replace('/^---$/m', '<hr>', $markdown);

    // Replace document links to site routing links
    $markdown = processSiteLinks($markdown, $defaultLanguage, $availableLanguages, $availableSections);

    return $markdown;
}

/**
 * Process Markdown tables
 */
function processMarkdownTables(string $markdown): string
{
    // Find table blocks
    $pattern = '/^\|(.*)\|$/m';
    if (preg_match_all($pattern, $markdown, $matches, PREG_OFFSET_CAPTURE)) {
        $tableBlocks = [];
        $currentBlock = [];
        $lastOffset = -1;

        foreach ($matches[0] as $match) {
            $line = $match[0];
            $offset = $match[1];

            // Check if this line is part of the current block
            if ($lastOffset !== -1 && $offset - $lastOffset > strlen($line) + 10) {
                // This line is not part of the current block
                if (!empty($currentBlock)) {
                    $tableBlocks[] = $currentBlock;
                    $currentBlock = [];
                }
            }

            $currentBlock[] = $line;
            $lastOffset = $offset;
        }

        // Add the last block
        if (!empty($currentBlock)) {
            $tableBlocks[] = $currentBlock;
        }

        // Process each table block
        foreach ($tableBlocks as $tableBlock) {
            $tableHtml = "<table>\n";

            // Process each row
            foreach ($tableBlock as $index => $row) {
                $cells = array_map('trim', explode('|', trim($row, '|')));

                // Check if this is a header separator row
                if ($index === 1 && preg_match('/^[\s\-:]+$/', implode('', $cells))) {
                    continue;
                }

                $tableHtml .= "<tr>\n";

                // Process each cell
                foreach ($cells as $cell) {
                    // Determine if this is a header row
                    $tag = ($index === 0) ? 'th' : 'td';
                    $tableHtml .= "<$tag>$cell</$tag>\n";
                }

                $tableHtml .= "</tr>\n";
            }

            $tableHtml .= "</table>";

            // Replace the table block with the HTML
            $markdown = str_replace(implode("\n", $tableBlock), $tableHtml, $markdown);
        }
    }

    return $markdown;
}

function processMarkdownLists(string $markdown): string
{
    // Replace unordered lists
    $pattern = '/^([ ]*)-[ ]+(.*?)$/m';
    preg_match_all($pattern, $markdown, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $indentation = strlen($match[1]);
        $content = $match[2];
        $replacement = str_repeat('  ', $indentation) . "<li>$content</li>";
        $markdown = str_replace($match[0], $replacement, $markdown);
    }

    // Wrap list items in <ul> tags
    $markdown = preg_replace('/((?:<li>.*?<\/li>\n)+)/', '<ul>$1</ul>', $markdown);

    // Fix nested lists
    $markdown = preg_replace('/<\/li>\n<ul>/', '<ul>', $markdown);
    $markdown = preg_replace('/<\/ul>\n<\/li>/', '</ul></li>', $markdown);

    return $markdown;
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

function generateAnchorId(string $text): string
{
    $id = strtolower($text);
    $id = preg_replace('/[^a-z0-9]+/', '-', $id);

    // Remove leading and trailing hyphens
    $id = trim($id, '-');

    // Add a leading hyphen to match the format in URLs
    return '-' . $id;
}

function createHtmlPage(string $content, string $anchor): string
{
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Ysabeau+SC:wght@1..1000&amp;display=swap" rel="stylesheet">
    <title>dev-handbook</title>
    <style>
    body {
      background-color: #1e1e2f;
      color: #e0e0e0;
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
      color: #79b8ff;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }

    pre {
      background: #2d2d3a;
      color: #e0e0e0;
      padding: 1em;
      border-radius: 6px;
      overflow-x: auto;
      font-size: 0.95em;
    }

    code {
      font-family: 'Fira Code', monospace;
      background: #2d2d3a;
      padding: 0.2em 0.4em;
      border-radius: 4px;
      color: #ffcb6b;
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
      border-left: 4px solid #555;
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
      background-color: #2d2d3a;
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
      color: #79b8ff;
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
    тут будет
    <br> что нибудь
  </nav>
  <div class="content">
    $content
  </div>
    <script>
        document.querySelectorAll('nav a').forEach(link => {
          link.addEventListener('click', () => {
            document.querySelector('nav').classList.remove('open');
          });
        });
    </script>
</body>
</html>
HTML;

    return $html;
}
