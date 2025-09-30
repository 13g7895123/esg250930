<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRiskFactors extends Migration
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
            'template_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '所屬範本ID',
            ],
            'category_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '所屬風險分類ID',
            ],
            'topic_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '所屬風險主題ID (可選)',
            ],
            'factor_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => '因子名稱',
            ],
            'factor_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => '因子代碼',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false,
                'comment' => '因子詳細說明',
            ],
            'scoring_method' => [
                'type' => 'ENUM',
                'constraint' => ['binary', 'scale_1_5', 'scale_1_10', 'percentage'],
                'default' => 'scale_1_5',
                'null' => false,
                'comment' => '評分方式',
            ],
            'weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00,
                'null'       => false,
                'comment'    => '權重',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => '排序',
            ],
            'is_required' => [
                'type'    => 'BOOLEAN',
                'default' => true,
                'null'    => false,
                'comment' => '是否必填',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
                'null' => false,
                'comment' => '狀態',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('template_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('topic_id');
        $this->forge->addKey('sort_order');
        $this->forge->addKey('status');

        $this->forge->addForeignKey('template_id', 'risk_assessment_templates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'risk_categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('topic_id', 'risk_topics', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('risk_factors');
    }

    public function down()
    {
        $this->forge->dropTable('risk_factors');
    }
}