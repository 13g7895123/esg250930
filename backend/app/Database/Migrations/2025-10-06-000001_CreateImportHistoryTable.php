<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImportHistoryTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        $db = \Config\Database::connect();
        if ($db->tableExists('import_history')) {
            echo "ℹ️  Table 'import_history' already exists, skipping creation\n";
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'template_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'comment' => '範本ID (如果是範本匯入)',
            ],
            'question_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'comment' => '題項ID (如果是題項匯入)',
            ],
            'import_type' => [
                'type' => 'ENUM',
                'constraint' => ['template_content', 'question_content', 'template_structure', 'question_structure'],
                'default' => 'template_content',
                'comment' => '匯入類型',
            ],
            'batch_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => '批次ID (用於區分同一次匯入的多筆記錄)',
            ],
            'row_number' => [
                'type' => 'INT',
                'unsigned' => true,
                'comment' => 'Excel行號',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['success', 'skipped', 'error'],
                'comment' => '匯入狀態',
            ],
            'reason' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '跳過或錯誤原因',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '錯誤訊息',
            ],
            'inserted_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'comment' => '成功插入的記錄ID',
            ],
            'duplicate_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'comment' => '重複的記錄ID',
            ],
            'category_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '風險類別',
            ],
            'topic_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '風險主題',
            ],
            'factor_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '風險因子',
            ],
            'factor_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '風險因子描述 (純文字)',
            ],
            'data_json' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '完整資料JSON',
            ],
            'sql_preview' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'SQL語句預覽',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('batch_id');
        $this->forge->addKey('template_id');
        $this->forge->addKey('question_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        $this->forge->createTable('import_history');
    }

    public function down()
    {
        $this->forge->dropTable('import_history');
    }
}
