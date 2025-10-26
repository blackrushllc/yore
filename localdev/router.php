<?php
// Set unlimited execution time for local development
set_time_limit(0);

// Show all errors for local dev
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error) {
        echo "<pre>SHUTDOWN ERROR:\n";
        print_r($error);
        echo "</pre>";
    }
});

// router.php: Mimics Apache rewrite rules for Yore (local dev only)
$webRoot = __DIR__ . '/../web';
$requestedPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestedFile = $webRoot . $requestedPath;

// Shim domain for local dev
$domain = getenv('YORE_DOMAIN');
if ($domain) {
    $_SERVER['HTTP_HOST'] = $domain;
    $_SERVER['SERVER_NAME'] = $domain;
    $_SERVER['YORE_LOCAL_DEV'] = 'true'; // Used to determine cookie domain should be localhost
}

// Serve the file if it exists and is not a directory
if (php_sapi_name() === 'cli-server') {
    if (is_file($requestedFile)) {
        return false;
    }
    if (is_dir($requestedFile)) {
        return false;
    }
}

// Mimic Apache's RewriteRule ^(.*)$ /index.php/$1 [NC,L]
$_SERVER['PATH_INFO'] = $requestedPath;
$_SERVER['ORIG_PATH_INFO'] = $requestedPath;
require $webRoot . '/index.php';
