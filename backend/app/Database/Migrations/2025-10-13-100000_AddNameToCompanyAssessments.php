<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 在 company_assessments 表新增 name 欄位
 *
 * 新增題項名稱欄位，用於儲存評估項目的自訂名稱
 * 新增時預設使用範本版本名稱作為初始值
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-10-13
 */
class AddNameToCompanyAssessments extends Migration
{
    /**
     * 執行 Migration - 新增 name 欄位到 company_assessments 表
     */
    public function up()
    {
        // 定義 name 欄位
        $fields = [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '題項名稱，預設從範本版本名稱複製，可獨立修改',
                'after'      => 'template_version'  // 放在 template_version 之後
            ]
        ];

        // 新增欄位到 company_assessments 表
        $this->forge->addColumn('company_assessments', $fields);

        // 新增索引以提升查詢效能
        $this->db->query('CREATE INDEX idx_company_assessments_name ON company_assessments(name)');

        // 將現有記錄的 name 欄位填入 template_version 的值
        $this->db->query('UPDATE company_assessments SET name = template_version WHERE name IS NULL');

        // 記錄 migration 操作
        log_message('info', 'Migration: Added name column to company_assessments table and populated with template_version values');
    }

    /**
     * 回滾 Migration - 移除 name 欄位
     */
    public function down()
    {
        // 先移除索引
        $this->db->query('DROP INDEX idx_company_assessments_name ON company_assessments');

        // 移除 name 欄位
        $this->forge->dropColumn('company_assessments', 'name');

        // 記錄回滾操作
        log_message('info', 'Migration: Removed name column from company_assessments table');
    }
}
