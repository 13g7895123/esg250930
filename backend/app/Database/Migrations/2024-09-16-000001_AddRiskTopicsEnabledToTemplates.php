<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRiskTopicsEnabledToTemplates extends Migration
{
    public function up()
    {
        $this->forge->addColumn('risk_assessment_templates', [
            'risk_topics_enabled' => [
                'type'    => 'BOOLEAN',
                'default' => true,
                'null'    => false,
                'comment' => '是否啟用風險主題層級',
                'after'   => 'status'
            ]
        ]);

        // Add index for performance
        $this->forge->addKey('risk_topics_enabled', false, false, 'idx_risk_topics_enabled');
        $this->forge->processIndexes('risk_assessment_templates');
    }

    public function down()
    {
        $this->forge->dropColumn('risk_assessment_templates', 'risk_topics_enabled');
    }
}