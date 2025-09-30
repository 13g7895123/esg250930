<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveSoftDeleteFromRiskAssessmentTemplates extends Migration
{
    public function up()
    {
        // Remove deleted_at column from risk_assessment_templates table
        if ($this->db->fieldExists('deleted_at', 'risk_assessment_templates')) {
            $this->forge->dropColumn('risk_assessment_templates', 'deleted_at');
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
        
        $this->forge->addColumn('risk_assessment_templates', $fields);
    }
}