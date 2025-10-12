<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: 重新添加 sort_order 欄位至 risk_factors 表
 *
 * 背景說明：
 * - 在 2024-09-17-110001_RemoveUnnecessaryFieldsFromRiskFactors.php 這個 migration 中，
 *   sort_order 欄位被移除了，因為當時認為此欄位不必要
 *
 * 問題描述：
 * - 在實作風險因子拖曳排序功能時，發現需要 sort_order 欄位來保存排序順序
 * - 前端已實作拖曳功能，後端 RiskFactorModel::reorderFactors() 方法也已實作
 * - 但因為欄位不存在，導致 API 呼叫失敗，錯誤訊息：
 *   "Unknown column 'risk_factors.sort_order' in 'field list'"
 *
 * 解決方案：
 * - 重新添加 sort_order 欄位到 risk_factors 表
 * - 預設值設為 0，所有現有記錄將自動設定為 0
 * - 後續可以透過拖曳排序功能來更新實際的排序值
 *
 * 相關檔案：
 * - 前端：frontend/pages/admin/risk-assessment/templates/index.vue
 * - 後端 Model：app/Models/RiskAssessment/RiskFactorModel.php (line 172)
 * - 後端 Controller：app/Controllers/Api/V1/RiskAssessment/RiskFactorController.php (line 431)
 * - API 端點：POST /api/v1/risk-assessment/templates/{templateId}/factors/reorder
 *
 * 注意事項：
 * - 此 migration 不會影響現有資料，只會添加新欄位
 * - 所有現有的風險因子記錄的 sort_order 將預設為 0
 * - 執行此 migration 後，需要手動透過拖曳排序功能來設定實際的排序值
 * - 或者可以執行 SQL 更新語句來初始化排序值（建議依據 id 順序）
 *
 * 建立日期：2025-10-11
 * 建立原因：修復風險因子拖曳排序功能的資料庫欄位缺失問題
 */
class AddSortOrderBackToRiskFactors extends Migration
{
    /**
     * 執行 Migration
     *
     * 添加 sort_order 欄位到 risk_factors 表
     */
    public function up()
    {
        log_message('info', '開始執行：重新添加 sort_order 欄位到 risk_factors 表');

        // 定義 sort_order 欄位
        $fields = [
            'sort_order' => [
                'type'       => 'INT',           // 整數型態
                'constraint' => 11,              // 最大長度 11 位數
                'default'    => 0,               // 預設值為 0
                'null'       => false,           // 不允許 NULL
                'comment'    => '排序順序 (用於拖曳排序功能)', // 欄位說明
                'after'      => 'description'    // 放在 description 欄位之後
            ],
        ];

        // 檢查欄位是否已存在（防止重複執行）
        if (!$this->db->fieldExists('sort_order', 'risk_factors')) {
            // 添加欄位到表格
            $this->forge->addColumn('risk_factors', $fields);
            log_message('info', '成功添加 sort_order 欄位到 risk_factors 表');

            // 統計受影響的記錄數量
            $count = $this->db->table('risk_factors')->countAllResults();
            log_message('info', "已為 {$count} 筆風險因子記錄設定預設排序值 0");

            log_message('info', '建議：執行此 migration 後，建議透過以下方式初始化排序值：');
            log_message('info', '1. 使用前端的拖曳排序功能手動設定順序');
            log_message('info', '2. 或執行 SQL：UPDATE risk_factors SET sort_order = id ORDER BY id');
        } else {
            log_message('warning', 'sort_order 欄位已存在於 risk_factors 表，跳過添加');
        }

        log_message('info', '完成執行：重新添加 sort_order 欄位到 risk_factors 表');
    }

    /**
     * 回滾 Migration
     *
     * 移除 sort_order 欄位（回到移除前的狀態）
     */
    public function down()
    {
        log_message('info', '開始回滾：從 risk_factors 表移除 sort_order 欄位');

        // 檢查欄位是否存在
        if ($this->db->fieldExists('sort_order', 'risk_factors')) {
            // 移除欄位
            $this->forge->dropColumn('risk_factors', 'sort_order');
            log_message('info', '成功從 risk_factors 表移除 sort_order 欄位');
        } else {
            log_message('warning', 'sort_order 欄位不存在於 risk_factors 表，無需移除');
        }

        log_message('info', '完成回滾：從 risk_factors 表移除 sort_order 欄位');
    }
}
