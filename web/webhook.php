<?php
// Create the webhook_data directory if it doesn't exist
$directory = __DIR__ . '/webhook_data';
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

try {
    // Check if the request method is POST
//    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//        throw new Exception('Invalid request method. Only POST requests are allowed.');
//    }

    $payload = null;

    // Check Content-Type header to determine the input format
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

//    if (stripos($contentType, 'application/json') !== false) {
//        // Handle JSON payloadll d
//        $rawInput = file_get_contents('php://input');
//        $payload = json_decode($rawInput, true);
//
//        if (json_last_error() !== JSON_ERROR_NONE) {
//            throw new Exception('Invalid JSON payload: ' . json_last_error_msg());
//        }
//    } else { //if (stripos($contentType, 'application/x-www-form-urlencoded') !== false || stripos($contentType, 'multipart/form-data') !== false) {
//        // Handle form fields
//        $payload = $_REQUEST;
//    } //else {
//        //throw new Exception('Unsupported Content-Type: ' . $contentType);
//    //}



    // Check for file attachments in the request
//    if (!empty($_FILES)) {
//        $payload['files'] = [];
//        foreach ($_FILES as $key => $file) {
//            // Ensure the file was uploaded without errors
//            if ($file['error'] === UPLOAD_ERR_OK) {
//                $payload['files'][] = [
//                    'name' => $file['name'],
//                    'type' => $file['type'],
//                    'content' => base64_encode(file_get_contents($file['tmp_name']))
//                ];
//            }
//        }
//    }

//    if (empty($payload)) {
//        //echo 'aaaaaaaaa';
//        //var_dump($_REQUEST);exit;
//        throw new Exception('No Post Data');
//    }
//var_dump($_REQUEST); exit;
// Encode the data array to JSON
    $jsonData = json_encode($_REQUEST, JSON_PRETTY_PRINT);

// Generate a filename based on the current date and time
    $filename = $directory . '/' . date('Y-m-d_H-i-s') . '.json';

// Write the JSON data to the file
    if (file_put_contents($filename, $jsonData) !== false) {
        echo json_encode(['status' => 'success', 'message' => 'OK']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error saving data']);
    }

} catch (Exception $e) {
    // Send an error response
    http_response_code(418);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

