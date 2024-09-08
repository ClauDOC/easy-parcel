<?php
// Logic to retrieve data from the JSON file and send it as response

// Load JSON file content
$json_data = file_get_contents(__DIR__ . '/risposta.json');
if ($json_data !== false) {
    // Decode JSON data as associative array
    $response_data = json_decode($json_data, true);

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response_data);
} else {
    // Send error response if unable to load JSON file
    http_response_code(500);
    echo json_encode(array('error' => 'Unable to load JSON data'));
}
?>