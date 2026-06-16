<?php
// PHP Script to migrate dark classes to light/secondary classes

$directories = [
    __DIR__ . '/../',
    __DIR__ . '/../user/',
    __DIR__ . '/../admin/',
    __DIR__ . '/../includes/'
];

$replacements = [
    '/\btext-white\b/' => '', // Let it inherit default dark text from body
    '/\btable-dark\b/' => '', // Standard light table behavior
    '/\bnavbar-dark\b/' => 'navbar-light',
    '/\bbg-dark\b/' => 'bg-light',
    '/\bborder-secondary\b/' => 'border-light',
    '/\bbtn-close-white\b/' => '',
    '/\bbtn-outline-light\b/' => 'btn-outline-secondary',
    '/\btext-light\b/' => 'text-dark',
    '/\btext-white-50\b/' => 'text-muted'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) continue;
    
    $files = scandir($dir);
    foreach ($files as $file) {
        $filePath = $dir . $file;
        if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
            // Avoid modifying this migration script itself
            if (basename($filePath) === basename(__FILE__)) continue;

            $content = file_get_contents($filePath);
            $original = $content;

            foreach ($replacements as $pattern => $replacement) {
                $content = preg_replace($pattern, $replacement, $content);
            }

            if ($content !== $original) {
                file_put_contents($filePath, $content);
                echo "Updated classes in: " . realpath($filePath) . "\n";
            }
        }
    }
}

echo "HTML/PHP templates migration complete.\n";
?>
