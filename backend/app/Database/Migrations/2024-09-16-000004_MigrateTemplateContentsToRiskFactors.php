<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateTemplateContentsToRiskFactors extends Migration
{
    public function up()
    {
        // Check if template_content table exists and has data
        if ($this->db->tableExists('template_content')) {
            // Migrate existing data from template_content to risk_factors
            $this->db->query("
                INSERT INTO risk_factors (
                    template_id,
                    category_id,
                    topic_id,
                    factor_name,
                    description,
                    scoring_method,
                    weight,
                    sort_order,
                    is_required,
                    status,
                    created_at,
                    updated_at
                )
                SELECT
                    template_id,
                    category_id,
                    NULL as topic_id,  -- Initially NULL, meaning direct link to category
                    topic as factor_name,
                    COALESCE(description, '') as description,
                    COALESCE(scoring_method, 'scale_1_5') as scoring_method,
                    COALESCE(weight, 1.00) as weight,
                    COALESCE(sort_order, 0) as sort_order,
                    COALESCE(is_required, 1) as is_required,
                    'active' as status,
                    COALESCE(created_at, NOW()) as created_at,
                    COALESCE(updated_at, NOW()) as updated_at
                FROM template_content
                WHERE template_id IS NOT NULL
                  AND topic IS NOT NULL
                  AND topic != ''
            ");

            log_message('info', 'Data migration from template_content to risk_factors completed.');
        } else {
            log_message('info', 'template_content table does not exist, skipping data migration.');
        }
    }

    public function down()
    {
        // Clear the migrated data from risk_factors table
        // Note: This is a destructive operation, use with caution
        if ($this->db->tableExists('risk_factors')) {
            $this->db->query("TRUNCATE TABLE risk_factors");
            log_message('info', 'Risk factors data cleared during migration rollback.');
        }
    }
}