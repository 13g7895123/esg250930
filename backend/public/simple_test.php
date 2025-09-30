<?php
// Ultra simple test to check basic PHP functionality

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$response = [
    'success' => true,
    'message' => 'Simple test working',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion()
];

echo json_encode($response);