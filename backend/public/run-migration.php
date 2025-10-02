<?php

/**
 * Migration 執行腳本
 *
 * 透過瀏覽器執行 migration，適用於無法使用命令列的環境
 *
 * 使用方式：
 * 1. 將此檔案放在 public 目錄
 * 2. 在瀏覽器訪問：http://your-domain/run-migration.php
 * 3. 執行完成後請刪除此檔案以確保安全
 */

// 安全檢查：只允許本地訪問
$allowedIPs = ['127.0.0.1', '::1', 'localhost'];
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';

if (!in_array($clientIP, $allowedIPs)) {
    die('Access denied. This script can only be run from localhost.');
}

// 載入 CodeIgniter
require __DIR__ . '/../vendor/autoload.php';

// 啟動 CodeIgniter
$app = require_once FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require FCPATH . '../vendor/codeigniter4/framework/system/bootstrap.php';

$app = Config\Services::codeigniter();
$app->initialize();

// 執行 migration
$migrate = \Config\Services::migrations();

try {
    echo "<h1>執行 Database Migration</h1>";
    echo "<pre>";

    // 顯示當前 migration 狀態
    echo "=== 當前 Migration 狀態 ===\n\n";

    $migrations = \Config\Database::connect();
    $query = $migrations->query("SELECT * FROM migrations ORDER BY id DESC LIMIT 10");
    $results = $query->getResultArray();

    if (empty($results)) {
        echo "尚無任何 migration 記錄\n\n";
    } else {
        foreach ($results as $row) {
            echo "ID: {$row['id']} | Version: {$row['version']} | Class: {$row['class']} | Group: {$row['group']} | Batch: {$row['batch']}\n";
        }
        echo "\n";
    }

    echo "=== 開始執行 Migration ===\n\n";

    // 執行所有未執行的 migrations
    if ($migrate->latest()) {
        echo "✓ Migration 執行成功！\n\n";

        // 顯示更新後的 migration 狀態
        echo "=== 更新後的 Migration 狀態 ===\n\n";
        $query = $migrations->query("SELECT * FROM migrations ORDER BY id DESC LIMIT 10");
        $results = $query->getResultArray();

        foreach ($results as $row) {
            echo "ID: {$row['id']} | Version: {$row['version']} | Class: {$row['class']} | Group: {$row['group']} | Batch: {$row['batch']}\n";
        }

        echo "\n\n=== 驗證資料表結構 ===\n\n";

        // 檢查 question_responses 表結構
        $query = $migrations->query("DESCRIBE question_responses");
        $columns = $query->getResultArray();

        echo "question_responses 表欄位：\n";
        foreach ($columns as $column) {
            if (strpos($column['Field'], 'e1_') === 0 ||
                strpos($column['Field'], 'e2_') === 0 ||
                strpos($column['Field'], 'f1_') === 0 ||
                strpos($column['Field'], 'f2_') === 0) {
                echo "  - {$column['Field']} ({$column['Type']})\n";
            }
        }

        echo "\n";
        echo "=== 完成！ ===\n";
        echo "請刪除此檔案 (run-migration.php) 以確保安全。\n";

    } else {
        echo "✗ Migration 執行失敗\n";
        echo "錯誤訊息: " . print_r($migrate->getCliMessages(), true) . "\n";
    }

    echo "</pre>";

} catch (\Throwable $e) {
    echo "</pre>";
    echo "<h2 style='color: red;'>執行失敗</h2>";
    echo "<pre>";
    echo "錯誤訊息: " . $e->getMessage() . "\n";
    echo "檔案: " . $e->getFile() . "\n";
    echo "行號: " . $e->getLine() . "\n";
    echo "\n追蹤訊息:\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
