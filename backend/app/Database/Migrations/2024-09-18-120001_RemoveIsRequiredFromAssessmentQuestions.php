<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveIsRequiredFromAssessmentQuestions extends Migration
{
    public function up()
    {
        // Remove is_required column from assessment_questions table
        if ($this->db->fieldExists('is_required', 'assessment_questions')) {
            log_message('info', 'Removing is_required column from assessment_questions table');
            $this->forge->dropColumn('assessment_questions', 'is_required');
            log_message('info', 'Successfully removed is_required column from assessment_questions table');
        } else {
            log_message('info', 'is_required column does not exist in assessment_questions table, skipping removal');
        }
    }

    public function down()
    {
        // Add back is_required column if needed for rollback
        if (!$this->db->fieldExists('is_required', 'assessment_questions')) {
            log_message('info', 'Adding back is_required column to assessment_questions table');

            $this->forge->addColumn('assessment_questions', [
                'is_required' => [
                    'type'    => 'BOOLEAN',
                    'default' => true,
                    'null'    => false,
                    'comment' => '是否必填',
                    'after'   => 'sort_order'
                ]
            ]);

            log_message('info', 'Successfully added back is_required column to assessment_questions table');
        } else {
            log_message('info', 'is_required column already exists in assessment_questions table, skipping addition');
        }
    }
}