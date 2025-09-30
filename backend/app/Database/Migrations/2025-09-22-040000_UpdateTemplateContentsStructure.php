<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateTemplateContentsStructure extends Migration
{
    public function up()
    {
        // Add topic_id and risk_factor_id columns
        $fields = [
            'topic_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'comment' => '所屬風險主題ID',
                'after' => 'category_id'
            ],
            'risk_factor_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'comment' => '所屬風險因子ID',
                'after' => 'topic_id'
            ]
        ];

        $this->forge->addColumn('template_contents', $fields);

        // Add foreign key constraints
        $this->forge->addForeignKey('topic_id', 'risk_topics', 'id', 'SET NULL', 'CASCADE', 'template_contents');
        $this->forge->addForeignKey('risk_factor_id', 'risk_factors', 'id', 'SET NULL', 'CASCADE', 'template_contents');

        // Migrate existing data: move topic to description if description is empty
        $this->db->query("
            UPDATE template_contents
            SET description = CONCAT(COALESCE(topic, ''), CASE WHEN description IS NOT NULL AND description != '' THEN CONCAT(': ', description) ELSE '' END)
            WHERE topic IS NOT NULL AND topic != ''
        ");

        // Remove the topic column
        $this->forge->dropColumn('template_contents', 'topic');

        echo "已更新 template_contents 表結構：添加 topic_id 和 risk_factor_id 欄位，移除 topic 欄位\n";
    }

    public function down()
    {
        // Add back the topic column
        $fields = [
            'topic' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'comment' => '評估主題',
                'after' => 'category_id'
            ]
        ];

        $this->forge->addColumn('template_contents', $fields);

        // Drop foreign key constraints
        $this->forge->dropForeignKey('template_contents', 'template_contents_topic_id_foreign');
        $this->forge->dropForeignKey('template_contents', 'template_contents_risk_factor_id_foreign');

        // Remove the new columns
        $this->forge->dropColumn('template_contents', ['topic_id', 'risk_factor_id']);

        echo "已恢復 template_contents 表結構：移除 topic_id 和 risk_factor_id 欄位，恢復 topic 欄位\n";
    }
}