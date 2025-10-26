<?php
$filename = 'document.txt';

if (file_exists($filename)) {
    echo file_get_contents($filename);
} else {
    // If the file doesn't exist, create an empty document
    file_put_contents($filename, '');
    echo 'New Document';
}
