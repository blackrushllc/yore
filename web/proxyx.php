<?php
// Example usage: proxyx.php?url=ENCODED_URL
// For security, we whitelist allowed domains and only allow specific API key usage

// --- Config ---
$allowedDomains = ['hipaa.jotform.com','hipaa-api.jotform.com'];
$apiKey = '820fbf84a495cb9a28e24d97dd533a2b'; // Put your JotForm API key here

// --- Validate URL ---
if (!isset($_GET['url'])) {
    http_response_code(400);
    exit('Missing URL');
}

$targetUrl = $_GET['url'];

// Basic security: prevent SSRF by only allowing whitelisted domains
$parsedUrl = parse_url($targetUrl);
if (!in_array($parsedUrl['host'], $allowedDomains)) {
    http_response_code(403);
    exit('Forbidden domain');
}

// Optional: Inject the API key automatically
if (strpos($targetUrl, 'apikey=') === false) {
    $separator = strpos($targetUrl, '?') === false ? '?' : '&';
    $targetUrl .= $separator . 'apikey=' . urlencode($apiKey);
}

// --- Fetch the file ---
$ch = curl_init($targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
$data = curl_exec($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- Return the content ---
if ($httpCode === 200 && $data !== false) {
    header('Content-Type: application/pdf'); // force PDF content-type
    header('Content-Disposition: inline; filename="document.pdf"'); // this is the key line
    header('Content-Length: ' . strlen($data));
    echo $data;
} else {
    http_response_code($httpCode);
    exit('Failed to fetch PDF');
}
