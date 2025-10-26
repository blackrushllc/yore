<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = $_POST['content'];
    $filename = 'document.txt';
    $filename2 = 'document.htm';
    $backupFilename = 'document' . date('d') . '.txt'; // Get current day of the month

    // Check if the document exists
    if (file_exists($filename)) {
        // Rename the existing file to documentDD.txt
        if (!rename($filename, $backupFilename)) {
            file_put_contents($filename2, $content);
            echo 'Error: Could not rename the existing document.';
            exit;
        }
    }

    // Save the new content to document.txt
    if (file_put_contents($filename, $content) !== false) {
        echo 'Document saved successfully';
    } else {
        echo 'Error: Could not save the document.';
    }
} else {
    echo 'Invalid request';
}
