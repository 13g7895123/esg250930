<?php
// Standalone templates API bypassing CodeIgniter completely

// Set proper UTF-8 and CORS headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // For now, return mock data to test the endpoint
    $templates = [
        [
            'id' => 1,
            'version_name' => 'Test Template 1',
            'description' => 'A test template for risk assessment',
            'status' => 'active',
            'created_at' => '2025-09-17 14:00:00',
            'updated_at' => '2025-09-17 14:00:00'
        ],
        [
            'id' => 2,
            'version_name' => 'Test Template 2',
            'description' => 'Another test template',
            'status' => 'draft',
            'created_at' => '2025-09-17 14:05:00',
            'updated_at' => '2025-09-17 14:05:00'
        ]
    ];

    $response = [
        'success' => true,
        'data' => $templates,
        'message' => 'Templates retrieved successfully (mock data)',
        'count' => count($templates)
    ];

    // Use JSON_UNESCAPED_UNICODE to handle UTF-8 properly
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);

    $errorResponse = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ];

    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}