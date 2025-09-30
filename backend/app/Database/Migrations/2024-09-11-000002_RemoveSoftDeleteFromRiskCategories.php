<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveSoftDeleteFromRiskCategories extends Migration
{
    public function up()
    {
        // Remove deleted_at column from risk_categories table
        if ($this->db->fieldExists('deleted_at', 'risk_categories')) {
            $this->forge->dropColumn('risk_categories', 'deleted_at');
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
        
        $this->forge->addColumn('risk_categories', $fields);
    }
}