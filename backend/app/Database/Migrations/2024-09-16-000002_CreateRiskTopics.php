<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRiskTopics extends Migration
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
            'topic_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => '主題名稱',
            ],
            'topic_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => '主題代碼',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '主題描述',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => '排序',
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
        $this->forge->addKey('sort_order');
        $this->forge->addKey('status');

        $this->forge->addForeignKey('template_id', 'risk_assessment_templates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'risk_categories', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('risk_topics');
    }

    public function down()
    {
        $this->forge->dropTable('risk_topics');
    }
}