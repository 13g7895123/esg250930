<?php
/**
 * 修復 company_assessments 表中 name 欄位為空的記錄
 * 從 risk_assessment_templates 表查詢對應的範本名稱來填充
 *
 * 使用方式：在瀏覽器訪問此文件
 * 例如：http://localhost/backend/public/fix-assessment-name.php
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-10-13
 */

// 允許跨域訪問（方便測試）
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// 載入 CodeIgniter
require_once __DIR__ . '/../vendor/autoload.php';

// 設定環境
$_SERVER['CI_ENVIRONMENT'] = 'development';

// 載入 CodeIgniter 應用程式
$app = require_once __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

// 建立應用程式實例
$app = \Config\Services::codeigniter();
$app->initialize();

try {
    // 連接資料庫
    $db = \Config\Database::connect();

    $results = [];
    $results['timestamp'] = date('Y-m-d H:i:s');

    // 步驟 1: 檢查 id=31 的當前狀態
    $results['step1'] = '檢查 id=31 當前狀態';
    $query = $db->query("
        SELECT id, company_id, template_id, template_version, name, assessment_year
        FROM company_assessments
        WHERE id = 31
    ");
    $record = $query->getRowArray();
    $results['current_record'] = $record;

    if (!$record) {
        $results['status'] = 'error';
        $results['message'] = 'id=31 的記錄不存在';
        echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 步驟 2: 查詢對應的範本名稱
    $results['step2'] = '查詢對應的範本名稱';
    $query = $db->query("
        SELECT ca.id, ca.template_id, ca.name as current_name, rat.version_name as template_name
        FROM company_assessments ca
        LEFT JOIN risk_assessment_templates rat ON ca.template_id = rat.id
        WHERE ca.id = 31
    ");
    $templateInfo = $query->getRowArray();
    $results['template_info'] = $templateInfo;

    if (!$templateInfo['template_name']) {
        $results['status'] = 'error';
        $results['message'] = '找不到對應的範本（template_id=' . $record['template_id'] . '）';
        echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 步驟 3: 檢查是否需要更新
    if (!empty($record['name']) && $record['name'] !== '') {
        $results['status'] = 'skip';
        $results['message'] = 'id=31 的 name 欄位已有值，無需更新';
        $results['current_name'] = $record['name'];
        echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 步驟 4: 執行更新
    $results['step3'] = '執行更新';
    $updateQuery = "
        UPDATE company_assessments ca
        JOIN risk_assessment_templates rat ON ca.template_id = rat.id
        SET ca.name = rat.version_name
        WHERE ca.id = 31 AND (ca.name IS NULL OR ca.name = '')
    ";

    $db->query($updateQuery);
    $affectedRows = $db->affectedRows();

    $results['affected_rows'] = $affectedRows;

    // 步驟 5: 驗證結果
    $results['step4'] = '驗證更新結果';
    $query = $db->query("
        SELECT id, company_id, template_id, template_version, name, assessment_year
        FROM company_assessments
        WHERE id = 31
    ");
    $updatedRecord = $query->getRowArray();
    $results['updated_record'] = $updatedRecord;

    // 步驟 6: 檢查其他需要修復的記錄
    $results['step5'] = '檢查其他需要修復的記錄';
    $query = $db->query("
        SELECT ca.id, ca.template_id, ca.name, rat.version_name
        FROM company_assessments ca
        LEFT JOIN risk_assessment_templates rat ON ca.template_id = rat.id
        WHERE ca.name IS NULL OR ca.name = ''
    ");
    $otherRecords = $query->getResultArray();
    $results['other_empty_records'] = $otherRecords;
    $results['other_empty_count'] = count($otherRecords);

    if ($affectedRows > 0) {
        $results['status'] = 'success';
        $results['message'] = "成功修復 id=31 的 name 欄位，值為：{$updatedRecord['name']}";
    } else {
        $results['status'] = 'no_change';
        $results['message'] = '未執行更新（可能已經有值或範本不存在）';
    }

    // 提供批量修復的 SQL（如果需要）
    if ($results['other_empty_count'] > 0) {
        $results['batch_fix_sql'] = "UPDATE company_assessments ca
JOIN risk_assessment_templates rat ON ca.template_id = rat.id
SET ca.name = rat.version_name
WHERE ca.name IS NULL OR ca.name = '';";
        $results['batch_fix_note'] = "發現還有 {$results['other_empty_count']} 筆記錄的 name 欄位為空，可執行上述 SQL 批量修復";
    }

} catch (\Exception $e) {
    $results['status'] = 'error';
    $results['message'] = '執行失敗：' . $e->getMessage();
    $results['trace'] = $e->getTraceAsString();
}

// 輸出結果
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
