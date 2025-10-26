<?php
// stop-php-server.php: Kills any running PHP built-in server processes
exec("ps aux | grep 'php -S' | grep -v grep", $output);
if (empty($output)) {
    echo "No PHP built-in server processes found.\n";
    exit(0);
}
foreach ($output as $line) {
    preg_match('/^\S+\s+(\d+)/', $line, $matches);
    if (!empty($matches[1])) {
        $pid = $matches[1];
        echo "Killing PHP server process: $pid\n";
        exec("kill $pid");
    }
}
echo "All PHP built-in server processes stopped.\n";

