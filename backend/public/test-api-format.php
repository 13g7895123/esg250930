<?php
// 測試前後端資料格式轉換
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 獲取 JSON 輸入
$input = json_decode(file_get_contents('php://input'), true);

// 如果沒有輸入，使用測試資料
if (empty($input)) {
    $input = [
        'category_id' => '13',      // 模擬字串
        'topic_id' => '',           // 模擬空字串
        'risk_factor_id' => '25',   // 模擬字串
        'description' => '測試描述'
    ];
}

// 驗證規則（模擬 CodeIgniter 驗證）
$validationResults = [];

// 檢查 category_id
if (isset($input['category_id'])) {
    $value = $input['category_id'];
    if ($value === '' || $value === null) {
        $validationResults['category_id'] = [
            'value' => $value,
            'type' => gettype($value),
            'is_valid' => true,
            'message' => '✅ 空值允許'
        ];
    } elseif (is_int($value) || (is_string($value) && ctype_digit($value))) {
        $validationResults['category_id'] = [
            'value' => $value,
            'type' => gettype($value),
            'is_valid' => true,
            'message' => '✅ 整數或數字字串'
        ];
    } else {
        $validationResults['category_id'] = [
            'value' => $value,
            'type' => gettype($value),
            'is_valid' => false,
            'message' => '❌ 不是有效的整數'
        ];
    }
}

// 檢查 topic_id
if (isset($input['topic_id'])) {
    $value = $input['topic_id'];
    if ($value === '' || $value === null) {
        $validationResults['topic_id'] = [
            'value' => $value,
            'type' => gettype($value),
            'is_valid' => $value === null,  // 空字串不允許
            'message' => $value === null ? '✅ null 值允許' : '❌ 空字串不允許，應為 null'
        ];
    } elseif (is_int($value) || (is_string($value) && ctype_digit($value))) {
        $validationResults['topic_id'] = [
            'value' => $value,
            'type' => gettype($value),
            'is_valid' => true,
            'message' => '✅ 整數或數字字串'
        ];
    } else {
        $validationResults['topic_id'] = [
            'value' => $value,
            'type' => gettype($value),
            'is_valid' => false,
            'message' => '❌ 不是有效的整數'
        ];
    }
}

// 檢查 risk_factor_id
if (isset($input['risk_factor_id'])) {
    $value = $input['risk_factor_id'];
    if ($value === '' || $value === null) {
        $validationResults['risk_factor_id'] = [
            'value' => $value,
            'type' => gettype($value),
            'is_valid' => $value === null,  // 空字串不允許
            'message' => $value === null ? '✅ null 值允許' : '❌ 空字串不允許，應為 null'
        ];
    } elseif (is_int($value) || (is_string($value) && ctype_digit($value))) {
        $validationResults['risk_factor_id'] = [
            'value' => $value,
            'type' => gettype($value),
            'is_valid' => true,
            'message' => '✅ 整數或數字字串'
        ];
    } else {
        $validationResults['risk_factor_id'] = [
            'value' => $value,
            'type' => gettype($value),
            'is_valid' => false,
            'message' => '❌ 不是有效的整數'
        ];
    }
}

// 整體驗證結果
$allValid = true;
foreach ($validationResults as $result) {
    if (!$result['is_valid']) {
        $allValid = false;
        break;
    }
}

// 返回結果
echo json_encode([
    'success' => $allValid,
    'message' => $allValid ? '✅ 所有欄位格式正確' : '❌ 有欄位格式錯誤',
    'received_data' => $input,
    'validation_results' => $validationResults,
    'test_cases' => [
        '空字串轉 null' => [
            'input' => ['topic_id' => ''],
            'expected' => ['topic_id' => null],
            'result' => '空字串應轉換為 null'
        ],
        '數字字串轉整數' => [
            'input' => ['category_id' => '13'],
            'expected' => ['category_id' => 13],
            'result' => '字串 "13" 應轉換為整數 13'
        ]
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);