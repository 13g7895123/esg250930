<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 移除 question_contents 表格中的指定欄位
 *
 * 此 Migration 移除以下欄位：
 * - title: 題目標題
 * - assessment_criteria: 評估標準 (JSON格式)
 * - scoring_method: 評分方法 (ENUM)
 * - weight: 題目權重 (DECIMAL)
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-25
 */
class RemoveFieldsFromQuestionContents extends Migration
{
    /**
     * 執行 Migration - 移除指定欄位
     */
    public function up()
    {
        // 檢查表格是否存在
        if (!$this->db->tableExists('question_contents')) {
            throw new \Exception('Table question_contents does not exist');
        }

        // 移除 title 欄位
        if ($this->db->fieldExists('title', 'question_contents')) {
            $this->forge->dropColumn('question_contents', 'title');
        }

        // 移除 assessment_criteria 欄位
        if ($this->db->fieldExists('assessment_criteria', 'question_contents')) {
            $this->forge->dropColumn('question_contents', 'assessment_criteria');
        }

        // 移除 scoring_method 欄位
        if ($this->db->fieldExists('scoring_method', 'question_contents')) {
            $this->forge->dropColumn('question_contents', 'scoring_method');
        }

        // 移除 weight 欄位
        if ($this->db->fieldExists('weight', 'question_contents')) {
            $this->forge->dropColumn('question_contents', 'weight');
        }

        // 記錄操作日誌
        if ($this->db->tableExists('activity_logs')) {
            $this->db->query("
                INSERT INTO activity_logs (action, resource_type, description, created_at)
                VALUES (
                    'SCHEMA_MIGRATION',
                    'DATABASE',
                    '移除 question_contents 表格欄位 - 刪除 title, assessment_criteria, scoring_method, weight 欄位',
                    NOW()
                )
            ");
        }
    }

    /**
     * 回滾 Migration - 恢復被移除的欄位
     */
    public function down()
    {
        // 檢查表格是否存在
        if (!$this->db->tableExists('question_contents')) {
            throw new \Exception('Table question_contents does not exist');
        }

        // 重新新增 title 欄位
        if (!$this->db->fieldExists('title', 'question_contents')) {
            $this->forge->addColumn('question_contents', [
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'after'      => 'factor_id',
                    'comment'    => '題目標題，顯示在評估表單上的主要標題'
                ]
            ]);
        }

        // 重新新增 assessment_criteria 欄位
        if (!$this->db->fieldExists('assessment_criteria', 'question_contents')) {
            $this->forge->addColumn('question_contents', [
                'assessment_criteria' => [
                    'type'    => 'JSON',
                    'null'    => true,
                    'after'   => 'description',
                    'comment' => '評估標準的JSON格式資料，定義評分規則'
                ]
            ]);
        }

        // 重新新增 scoring_method 欄位
        if (!$this->db->fieldExists('scoring_method', 'question_contents')) {
            $this->forge->addColumn('question_contents', [
                'scoring_method' => [
                    'type'       => 'ENUM',
                    'constraint' => ['binary', 'scale_1_5', 'scale_1_10', 'percentage'],
                    'default'    => 'scale_1_5',
                    'after'      => 'assessment_criteria',
                    'comment'    => '評分方法：binary(是否)、scale_1_5(1-5分)、scale_1_10(1-10分)、percentage(百分比)'
                ]
            ]);
        }

        // 重新新增 weight 欄位
        if (!$this->db->fieldExists('weight', 'question_contents')) {
            $this->forge->addColumn('question_contents', [
                'weight' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'default'    => 1.00,
                    'after'      => 'scoring_method',
                    'comment'    => '題目權重，用於計算總分時的加權'
                ]
            ]);
        }

        // 記錄回滾操作
        if ($this->db->tableExists('activity_logs')) {
            $this->db->query("
                INSERT INTO activity_logs (action, resource_type, description, created_at)
                VALUES (
                    'SCHEMA_ROLLBACK',
                    'DATABASE',
                    '回滾 question_contents 表格欄位 - 恢復 title, assessment_criteria, scoring_method, weight 欄位',
                    NOW()
                )
            ");
        }
    }
}
