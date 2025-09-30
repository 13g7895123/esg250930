<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRiskCategories extends Migration
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
            'category_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => '分類名稱',
            ],
            'category_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => '分類代碼',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '分類描述',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => '排序',
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
        $this->forge->addKey('sort_order');
        $this->forge->addForeignKey('template_id', 'risk_assessment_templates', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('risk_categories');
    }

    public function down()
    {
        $this->forge->dropTable('risk_categories');
    }
}