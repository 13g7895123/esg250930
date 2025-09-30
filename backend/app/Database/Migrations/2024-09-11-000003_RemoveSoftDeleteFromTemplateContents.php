<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveSoftDeleteFromTemplateContents extends Migration
{
    public function up()
    {
        // Remove deleted_at column from template_contents table
        if ($this->db->fieldExists('deleted_at', 'template_contents')) {
            $this->forge->dropColumn('template_contents', 'deleted_at');
        }
    }

    public function down()
    {
        // Add back deleted_at column
        $fields = [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];
        
        $this->forge->addColumn('template_contents', $fields);
    }
}