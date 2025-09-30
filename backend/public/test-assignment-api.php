<?php
// Test script for personnel assignment API
require_once '../vendor/autoload.php';

try {
    // Test the personnel assignment API endpoint
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/api/v1/personnel/company/3');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "<h3>Personnel API Test</h3>";
    echo "<p>HTTP Code: $httpCode</p>";
    echo "<h4>Raw Response:</h4>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";

    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            echo "<h4>Parsed JSON:</h4>";
            echo "<pre>" . print_r($data, true) . "</pre>";
        } else {
            echo "<p style='color: red;'>Failed to parse JSON: " . json_last_error_msg() . "</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>