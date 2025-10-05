<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateDescriptionToAContent extends Migration
{
    public function up()
    {
        // Copy data from description to a_content for template_contents table
        $this->db->query("
            UPDATE template_contents
            SET a_content = COALESCE(description, a_content)
            WHERE description IS NOT NULL
            AND (a_content IS NULL OR a_content = '')
        ");

        echo "已將 template_contents 表的 description 欄位資料複製到 a_content\n";

        // Drop the description column from template_contents
        $this->forge->dropColumn('template_contents', 'description');

        echo "已從 template_contents 表移除 description 欄位\n";
    }

    public function down()
    {
        // Add back the description column
        $fields = [
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '風險因子描述 (已棄用)',
                'after' => 'topic'
            ]
        ];

        $this->forge->addColumn('template_contents', $fields);

        // Copy data back from a_content to description
        $this->db->query("
            UPDATE template_contents
            SET description = a_content
            WHERE a_content IS NOT NULL
        ");

        echo "已恢復 template_contents 表的 description 欄位\n";
    }
}
