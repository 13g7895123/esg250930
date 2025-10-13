<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 從 question_contents 表移除錯誤新增的 name 欄位
 *
 * 此欄位原本被錯誤地加到 question_contents 表
 * 實際上應該加到 company_assessments 表
 * 此 migration 用於清理錯誤的欄位
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-10-13
 */
class RemoveNameFromQuestionContents extends Migration
{
    /**
     * 執行 Migration - 從 question_contents 表移除 name 欄位
     */
    public function up()
    {
        // 檢查欄位是否存在，如果存在才執行刪除
        if ($this->db->fieldExists('name', 'question_contents')) {
            log_message('info', 'Migration: Removing name column from question_contents table...');

            // 先移除索引（如果存在）
            try {
                $this->db->query('DROP INDEX IF EXISTS idx_question_contents_name ON question_contents');
                log_message('info', 'Migration: Dropped index idx_question_contents_name');
            } catch (\Exception $e) {
                log_message('warning', 'Migration: Index idx_question_contents_name not found or already dropped: ' . $e->getMessage());
            }

            // 移除 name 欄位
            $this->forge->dropColumn('question_contents', 'name');

            log_message('info', 'Migration: Successfully removed name column from question_contents table');
        } else {
            log_message('info', 'Migration: name column does not exist in question_contents table, skipping removal');
        }
    }

    /**
     * 回滾 Migration - 重新新增 name 欄位（僅為保持 migration 完整性）
     *
     * 注意：此回滾不應該被執行，因為 name 欄位本就不應存在於此表
     */
    public function down()
    {
        log_message('warning', 'Migration: Attempting to rollback RemoveNameFromQuestionContents - This should not happen in production');

        // 重新新增欄位（僅用於 migration 回滾）
        $fields = [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '題項名稱（此欄位不應存在，僅用於 migration 回滾）',
                'after'      => 'factor_id'
            ]
        ];

        $this->forge->addColumn('question_contents', $fields);

        // 重新建立索引
        $this->db->query('CREATE INDEX idx_question_contents_name ON question_contents(name)');

        log_message('warning', 'Migration: Rollback completed - name column re-added to question_contents (should be removed in production)');
    }
}
