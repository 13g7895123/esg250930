<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRiskAssessmentTemplates extends Migration
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
            'version_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => '版本名稱',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '範本描述',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive', 'draft'],
                'default' => 'active',
                'null' => false,
                'comment' => '狀態',
            ],
            'copied_from' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '複製來源範本ID',
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
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('copied_from', 'risk_assessment_templates', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('risk_assessment_templates');
    }

    public function down()
    {
        $this->forge->dropTable('risk_assessment_templates');
    }
}