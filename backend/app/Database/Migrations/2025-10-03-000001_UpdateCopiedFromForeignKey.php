<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCopiedFromForeignKey extends Migration
{
    public function up()
    {
        // Drop existing foreign key constraint
        $this->forge->dropForeignKey('risk_assessment_templates', 'risk_assessment_templates_copied_from_foreign');

        // Add new foreign key constraint with SET NULL on delete
        $this->db->query('
            ALTER TABLE risk_assessment_templates
            ADD CONSTRAINT risk_assessment_templates_copied_from_foreign
            FOREIGN KEY (copied_from)
            REFERENCES risk_assessment_templates(id)
            ON DELETE SET NULL
            ON UPDATE SET NULL
        ');
    }

    public function down()
    {
        // Drop the modified foreign key constraint
        $this->forge->dropForeignKey('risk_assessment_templates', 'risk_assessment_templates_copied_from_foreign');

        // Restore original foreign key constraint with CASCADE
        $this->db->query('
            ALTER TABLE risk_assessment_templates
            ADD CONSTRAINT risk_assessment_templates_copied_from_foreign
            FOREIGN KEY (copied_from)
            REFERENCES risk_assessment_templates(id)
            ON DELETE CASCADE
            ON UPDATE SET NULL
        ');
    }
}
