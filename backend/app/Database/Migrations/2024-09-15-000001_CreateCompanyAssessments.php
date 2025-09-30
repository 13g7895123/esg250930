<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyAssessments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'company_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => false,
                'comment'    => '公司ID（對應外部API的公司ID）',
            ],
            'template_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '風險評估範本ID',
            ],
            'template_version' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => '範本版本名稱（冗餘欄位，便於顯示）',
            ],
            'assessment_year' => [
                'type'       => 'YEAR',
                'null'       => false,
                'comment'    => '評估年度',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'in_progress', 'completed', 'archived'],
                'default'    => 'pending',
                'null'       => false,
                'comment'    => '評估狀態：待開始、進行中、已完成、已封存',
            ],
            'copied_from' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '複製來源的評估項目ID',
            ],
            'include_questions' => [
                'type'    => 'BOOLEAN',
                'default' => true,
                'null'    => false,
                'comment' => '複製時是否包含題目',
            ],
            'include_results' => [
                'type'    => 'BOOLEAN',
                'default' => false,
                'null'    => false,
                'comment' => '複製時是否包含結果',
            ],
            'total_score' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => true,
                'comment'    => '總分',
            ],
            'percentage_score' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => '百分比分數',
            ],
            'risk_level' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'critical'],
                'null'       => true,
                'comment'    => '風險等級：低、中、高、嚴重',
            ],
            'notes' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => '備註說明',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'comment' => '建立時間',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'comment' => '更新時間',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('company_id');
        $this->forge->addKey('template_id');
        $this->forge->addKey(['company_id', 'assessment_year']);
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        
        $this->forge->addForeignKey('template_id', 'risk_assessment_templates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('copied_from', 'company_assessments', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('company_assessments', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => '公司風險評估表 - 存儲各公司的風險評估項目和執行狀態，取代前端localStorage存儲'
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('company_assessments');
    }
}