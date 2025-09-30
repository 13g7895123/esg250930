<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTemplateContents extends Migration
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
                'null'       => true,
                'comment'    => '所屬風險分類ID',
            ],
            'topic' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => '評估主題',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false,
                'comment' => '風險因子描述',
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
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('template_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('sort_order');
        $this->forge->addForeignKey('template_id', 'risk_assessment_templates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'risk_categories', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('template_contents');
    }

    public function down()
    {
        $this->forge->dropTable('template_contents');
    }
}