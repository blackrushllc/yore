<?php
// Stop any running PHP servers first
echo "Stopping any running PHP servers...\n";
exec("ps aux | grep 'php -S' | grep -v grep", $runningServers);
if (!empty($runningServers)) {
    foreach ($runningServers as $line) {
        preg_match('/^\S+\s+(\d+)/', $line, $matches);
        if (!empty($matches[1])) {
            $pid = $matches[1];
            echo "  Killing PHP server process: $pid\n";
            exec("kill $pid");
        }
    }
    echo "  Stopped existing PHP servers.\n";
} else {
    echo "  No running PHP servers found.\n";
}

// Clear PHP sessions
echo "Clearing PHP sessions...\n";
$sessionPaths = [
    __DIR__ . '/../storage/sessions',
    sys_get_temp_dir(),
];

foreach ($sessionPaths as $path) {
    if (is_dir($path)) {
        $sessionFiles = glob($path . '/sess_*');
        if ($sessionFiles) {
            foreach ($sessionFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            echo "  Cleared " . count($sessionFiles) . " session file(s) from $path\n";
        }
    }
}

// Clear cache if it exists
$cachePath = __DIR__ . '/../storage/cache';
if (is_dir($cachePath)) {
    echo "Clearing cache...\n";
    $fileCount = 0;

    // Recursively clear all cache files
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cachePath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            unlink($file->getPathname());
            $fileCount++;
        }
    }

    if ($fileCount > 0) {
        echo "  Cleared $fileCount cache file(s) (including views)\n";
    } else {
        echo "  No cache files to clear\n";
    }
}

echo "\n";

// Accept domain as first argument, or prompt if not provided
$domain = $argv[1] ?? null;

if (!$domain) {
    // Scan the _domains folder for available domains
    $domainsPath = __DIR__ . '/../pages/_domains';

    if (!is_dir($domainsPath)) {
        die("Error: Domains directory not found at $domainsPath\n");
    }

    $domains = array_filter(scandir($domainsPath), function($item) use ($domainsPath) {
        return $item !== '.' && $item !== '..' && is_dir($domainsPath . '/' . $item);
    });

    $domains = array_values($domains); // Re-index array

    if (empty($domains)) {
        die("Error: No domains found in $domainsPath\n");
    }

    // Display available domains
    echo "\nAvailable domains:\n";
    echo str_repeat("-", 50) . "\n";
    foreach ($domains as $index => $domainName) {
        printf("%2d. %s\n", $index + 1, $domainName);
    }
    echo str_repeat("-", 50) . "\n";
    echo "Enter the number of the domain you want to serve (or 'q' to quit): ";

    $input = trim(fgets(STDIN));

    if (strtolower($input) === 'q') {
        die("Exiting...\n");
    }

    $selection = (int)$input;

    if ($selection < 1 || $selection > count($domains)) {
        die("Error: Invalid selection. Please enter a number between 1 and " . count($domains) . "\n");
    }

    $domain = $domains[$selection - 1];
}

$port = 8000;
$router = __DIR__ . '/router.php';
$webRoot = __DIR__ . '/../web';

// Pass domain to router via environment variable
putenv("YORE_DOMAIN=$domain");

echo "\nServing $domain at http://localhost:$port\n";
echo "Press Ctrl+C to stop the server\n\n";

passthru("php -S localhost:$port -t '$webRoot' '$router'", $exitCode);
