<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 在 question_contents 表新增 name 欄位
 *
 * 新增題項名稱欄位，用於儲存題項的名稱屬性
 * 新增時預設使用範本版本名稱作為初始值
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-10-13
 */
class AddNameToQuestionContents extends Migration
{
    /**
     * 執行 Migration - 新增 name 欄位到 question_contents 表
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
                'after'      => 'factor_id'  // 放在 factor_id 之後
            ]
        ];

        // 新增欄位到 question_contents 表
        $this->forge->addColumn('question_contents', $fields);

        // 新增索引以提升查詢效能
        $this->db->query('CREATE INDEX idx_question_contents_name ON question_contents(name)');

        // 記錄 migration 操作
        log_message('info', 'Migration: Added name column to question_contents table');
    }

    /**
     * 回滾 Migration - 移除 name 欄位
     */
    public function down()
    {
        // 先移除索引
        $this->db->query('DROP INDEX idx_question_contents_name ON question_contents');

        // 移除 name 欄位
        $this->forge->dropColumn('question_contents', 'name');

        // 記錄回滾操作
        log_message('info', 'Migration: Removed name column from question_contents table');
    }
}
