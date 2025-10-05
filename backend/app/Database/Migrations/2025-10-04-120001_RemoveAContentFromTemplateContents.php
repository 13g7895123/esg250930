<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveAContentFromTemplateContents extends Migration
{
    public function up()
    {
        // Remove a_content column from template_contents table
        // This field is now replaced by risk_factors.description
        if ($this->db->fieldExists('a_content', 'template_contents')) {
            $this->forge->dropColumn('template_contents', 'a_content');
        }
    }

    public function down()
    {
        // Restore a_content column if migration is rolled back
        $fields = [
            'a_content' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '風險因子議題描述 (已棄用，改用 risk_factors.description)',
            ],
        ];

        $this->forge->addColumn('template_contents', $fields);
    }
}
