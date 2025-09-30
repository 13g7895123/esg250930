<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssessmentQuestions extends Migration
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
            'risk_factor_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '關聯的風險因子ID',
            ],
            'question_title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => '題目標題',
            ],
            'question_description' => [
                'type' => 'TEXT',
                'null' => false,
                'comment' => '題目詳細說明',
            ],
            'question_type' => [
                'type' => 'ENUM',
                'constraint' => ['single_choice', 'multiple_choice', 'scale_rating', 'text_input', 'file_upload'],
                'default' => 'scale_rating',
                'null' => false,
                'comment' => '題目類型',
            ],
            'scoring_method' => [
                'type' => 'ENUM',
                'constraint' => ['binary', 'scale_1_5', 'scale_1_10', 'percentage', 'custom'],
                'default' => 'scale_1_5',
                'null' => false,
                'comment' => '評分方式',
            ],
            'scoring_options' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '評分選項配置 (JSON格式)',
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
                'constraint' => ['active', 'inactive', 'draft'],
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
        $this->forge->addKey('risk_factor_id');
        $this->forge->addKey('sort_order');
        $this->forge->addKey('status');

        $this->forge->addForeignKey('template_id', 'risk_assessment_templates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'risk_categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('topic_id', 'risk_topics', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('risk_factor_id', 'risk_factors', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('assessment_questions');
    }

    public function down()
    {
        $this->forge->dropTable('assessment_questions');
    }
}