<?php
/*

 ▄▄▄▄    ██▓    ▄▄▄       ▄████▄   ██ ▄█▀ ██▀███   █    ██   ██████  ██░ ██
▓█████▄ ▓██▒   ▒████▄    ▒██▀ ▀█   ██▄█▒ ▓██ ▒ ██▒ ██  ▓██▒▒██    ▒ ▓██░ ██▒
▒██▒ ▄██▒██░   ▒██  ▀█▄  ▒▓§‡    ▄ ▓███▄░ ▓██ ░▄█ ▒▓██  ▒██░░ ▓██▄   ▒██▀▀██░
▒██░█▀  ▒██░   ░██▄▄▄▄██ ▒▓▓▄ ▄██▒▓██ █▄ ▒██▀▀█▄  ▓▓█  ░██░  ▒   ██▒░▓█ ░██
░▓█  ▀█▓░██████▒▓█   ▓██▒▒ ▓███▀ ░▒██▒ █▄░██▓ ▒██▒▒▒█████▓ ▒██████▒▒░▓█▒░██▓
░▒▓███▀▒░ ▒░▓  ░▒▒   ▓▒█░░ ░▒ ▒  ░▒ ▒▒ ▓▒░ ▒▓ ░▒▓░░▒▓▒ ▒ ▒ ▒ ▒▓▒ ▒ ░ ▒ ░░▒░▒
▒░▒   ░ ░ ░ ▒  ░ ▒   ▒▒ ░  ░  ▒   ░ ░▒ ▒░  ░▒ ░ ▒░░░▒░ ░ ░ ░ ░▒  ░ ░ ▒ ░▒░ ░
 ░    ░   ░ ░    ░   ▒   ░        ░ ░░ ░   ░░   ░  ░░░ ░ ░ ░  ░  ░   ░  ░░ ░
 ░          ░  ░     ░  ░░ ░      ░  ░      ░        ░           ░   ░  ░  ░
      ░                  ░
 Copyright (C) 2026, Blackrush LLC, All Rights Reserved
Created by Erik Olson, Tarpon Springs, Florida
For more information, visit BlackrushDrive.com

MIT License

Copyright (c) 2025 Erik Lee Olson for Blackrush, LLC

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

Firewall Cache Management Utility

Usage examples:
./yore firewall_cache --clear-all
./yore firewall_cache --clear-path="../pages/_domains/client.com/admin/firewalls.json"
./yore firewall_cache --stats
./yore firewall_cache --enable --cache-dir="../storage/cache/firewalls"
./yore firewall_cache --disable

*/

require_once '../app/Firewall.php';

use App\Firewall;

// Parse command line arguments
$options = getopt('', [
    'clear-all',
    'clear-path:',
    'stats',
    'enable',
    'disable',
    'cache-dir:',
    'help'
]);

if (isset($options['help']) || empty($options)) {
    echo "Firewall Cache Management Utility\n\n";
    echo "Usage: ./yore firewall_cache [options]\n\n";
    echo "Options:\n";
    echo "  --clear-all              Clear all cached firewall configurations\n";
    echo "  --clear-path=PATH        Clear cache for specific firewall config file\n";
    echo "  --stats                  Show cache statistics\n";
    echo "  --enable                 Enable caching\n";
    echo "  --disable                Disable caching\n";
    echo "  --cache-dir=DIR          Set cache directory (use with --enable)\n";
    echo "  --help                   Show this help message\n\n";
    exit(0);
}

// Handle cache directory setting
if (isset($options['cache-dir'])) {
    $cacheDir = $options['cache-dir'];
    Firewall::setCaching(true, $cacheDir);
    echo "Cache directory set to: {$cacheDir}\n";
}

// Handle enable/disable
if (isset($options['enable'])) {
    $cacheDir = $options['cache-dir'] ?? '../storage/cache/firewalls';
    Firewall::setCaching(true, $cacheDir);
    echo "Firewall caching enabled\n";
    echo "Cache directory: {$cacheDir}\n";
}

if (isset($options['disable'])) {
    Firewall::setCaching(false);
    echo "Firewall caching disabled\n";
}

// Handle cache clearing
if (isset($options['clear-all'])) {
    Firewall::clearCache();
    echo "All firewall cache cleared\n";
}

if (isset($options['clear-path'])) {
    $path = $options['clear-path'];
    Firewall::clearCache($path);
    echo "Cache cleared for: {$path}\n";
}

// Show statistics
if (isset($options['stats'])) {
    $stats = Firewall::getCacheStats();

    echo "Firewall Cache Statistics:\n";
    echo "  Enabled: " . ($stats['enabled'] ? 'Yes' : 'No') . "\n";
    echo "  Cache Directory: " . ($stats['cache_dir'] ?: 'Not set') . "\n";
    echo "  Memory Cache Entries: {$stats['memory_cache_entries']}\n";
    echo "  File Cache Entries: {$stats['file_cache_entries']}\n";
    echo "  Cache Size: " . formatBytes($stats['cache_size_bytes']) . "\n";
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}
