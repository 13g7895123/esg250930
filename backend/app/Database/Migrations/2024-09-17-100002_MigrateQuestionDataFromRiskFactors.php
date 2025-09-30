<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateQuestionDataFromRiskFactors extends Migration
{
    public function up()
    {
        // 首先備份 risk_factors 中的題目資料到 assessment_questions
        log_message('info', 'Starting migration of question data from risk_factors to assessment_questions');

        if ($this->db->tableExists('risk_factors') && $this->db->tableExists('assessment_questions')) {
            // 檢查是否有資料需要遷移
            $existingData = $this->db->table('risk_factors')->countAllResults();

            if ($existingData > 0) {
                log_message('info', "Found {$existingData} records in risk_factors to migrate");

                // 遷移資料 - 將 risk_factors 中的資料視為題目
                $this->db->query("
                    INSERT INTO assessment_questions (
                        template_id,
                        category_id,
                        topic_id,
                        risk_factor_id,
                        question_title,
                        question_description,
                        question_type,
                        scoring_method,
                        weight,
                        sort_order,
                        is_required,
                        status,
                        created_at,
                        updated_at
                    )
                    SELECT
                        rf.template_id,
                        rf.category_id,
                        rf.topic_id,
                        1 as risk_factor_id,  -- 暫時設為 1，稍後需要修正
                        COALESCE(NULLIF(rf.factor_name, ''), '未命名題目') as question_title,
                        COALESCE(rf.description, '') as question_description,
                        'scale_rating' as question_type,
                        rf.scoring_method,
                        rf.weight,
                        rf.sort_order,
                        rf.is_required,
                        rf.status,
                        rf.created_at,
                        rf.updated_at
                    FROM risk_factors rf
                    WHERE rf.template_id IS NOT NULL
                ");

                log_message('info', 'Question data migration completed successfully');
            } else {
                log_message('info', 'No data found in risk_factors to migrate');
            }
        } else {
            log_message('warning', 'Required tables not found for migration');
        }
    }

    public function down()
    {
        // 清空 assessment_questions 表 (危險操作，僅用於回滾)
        if ($this->db->tableExists('assessment_questions')) {
            $this->db->table('assessment_questions')->truncate();
            log_message('info', 'Assessment questions data cleared during migration rollback');
        }
    }
}