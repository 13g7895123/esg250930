<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 處理OPTIONS預檢請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 設定錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 資料庫連接配置
$host = '127.0.0.1';
$port = 9318;
$username = 'esg_user';
$password = 'esg_pass';
$database = 'esg_db';

try {
    // 建立PDO連接並設定UTF-8編碼
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );

    // 從URL中獲取assessmentId
    $uri = $_SERVER['REQUEST_URI'];
    preg_match('/assessment\/(\d+)\/structure/', $uri, $matches);
    $assessmentId = isset($matches[1]) ? (int)$matches[1] : null;

    if (!$assessmentId) {
        throw new Exception('Assessment ID is required');
    }

    // 獲取評估記錄
    $stmt = $pdo->prepare("SELECT * FROM company_assessments WHERE id = ?");
    $stmt->execute([$assessmentId]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assessment) {
        throw new Exception('Assessment not found');
    }

    // 獲取分類
    $stmt = $pdo->prepare("SELECT * FROM question_categories WHERE assessment_id = ? ORDER BY sort_order ASC, category_name ASC");
    $stmt->execute([$assessmentId]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 獲取主題
    $stmt = $pdo->prepare("SELECT * FROM question_topics WHERE assessment_id = ? ORDER BY sort_order ASC, topic_name ASC");
    $stmt->execute([$assessmentId]);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 獲取因子 - 分別獲取避免複雜JOIN
    $stmt = $pdo->prepare("SELECT * FROM question_factors WHERE assessment_id = ? ORDER BY sort_order ASC, factor_name ASC");
    $stmt->execute([$assessmentId]);
    $factors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 為每個因子添加相關資訊
    foreach ($factors as &$factor) {
        // 獲取主題資訊
        if ($factor['topic_id']) {
            $stmt = $pdo->prepare("SELECT topic_name, topic_code FROM question_topics WHERE id = ?");
            $stmt->execute([$factor['topic_id']]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);
            $factor['topic_name'] = $topic['topic_name'] ?? null;
            $factor['topic_code'] = $topic['topic_code'] ?? null;
        } else {
            $factor['topic_name'] = null;
            $factor['topic_code'] = null;
        }

        // 獲取分類資訊（直接或透過主題）
        $categoryId = $factor['category_id'];
        if (!$categoryId && $factor['topic_id']) {
            // 透過主題獲取分類ID
            $stmt = $pdo->prepare("SELECT category_id FROM question_topics WHERE id = ?");
            $stmt->execute([$factor['topic_id']]);
            $topicData = $stmt->fetch(PDO::FETCH_ASSOC);
            $categoryId = $topicData['category_id'] ?? null;
        }

        if ($categoryId) {
            $stmt = $pdo->prepare("SELECT category_name, category_code FROM question_categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            $factor['category_name'] = $category['category_name'] ?? null;
            $factor['category_code'] = $category['category_code'] ?? null;
        } else {
            $factor['category_name'] = null;
            $factor['category_code'] = null;
        }

        // 計算內容數量
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM question_contents WHERE factor_id = ?");
        $stmt->execute([$factor['id']]);
        $factor['content_count'] = (int)$stmt->fetchColumn();
    }

    // 構建結構數據
    $structure = [
        'assessment' => $assessment,
        'categories' => $categories,
        'topics' => $topics,
        'factors' => $factors,
        'contents_count' => 0,
        'responses_count' => 0
    ];

    $response = [
        'success' => true,
        'data' => [
            'structure' => $structure,
            'stats' => []
        ]
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>