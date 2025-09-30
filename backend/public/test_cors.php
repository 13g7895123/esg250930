<?php
/**
 * CORS 測試腳本
 * 用於驗證 CORS 配置是否正確工作
 */

// 設定 CORS 標頭（獨立於 CodeIgniter 框架）
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// 設定允許的來源
$allowedOrigins = [
    'http://localhost:3000',
    'http://localhost:3001', 
    'http://localhost:3101',
    'http://127.0.0.1:3000',
    'http://127.0.0.1:3001',
    'http://127.0.0.1:3101'
];

if (in_array($origin, $allowedOrigins) || 
    preg_match('/^https?:\/\/localhost(:\d+)?$/', $origin)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// 處理 OPTIONS 預檢請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 回傳測試資料
echo json_encode([
    'success' => true,
    'message' => 'CORS 配置測試成功',
    'origin' => $origin,
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s'),
    'headers' => [
        'Access-Control-Allow-Origin' => $origin,
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        'Access-Control-Allow-Credentials' => 'true'
    ]
]);
?>
