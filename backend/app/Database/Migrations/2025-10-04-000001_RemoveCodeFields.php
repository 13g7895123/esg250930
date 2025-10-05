<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 移除代碼欄位
 *
 * 此 Migration 移除系統中未使用的代碼欄位：
 * - risk_categories.category_code
 * - risk_topics.topic_code
 * - risk_factors.factor_code
 * - question_categories.category_code
 * - question_topics.topic_code
 * - question_factors.factor_code
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-10-04
 */
class RemoveCodeFields extends Migration
{
    /**
     * 執行 Migration - 移除代碼欄位
     */
    public function up()
    {
        // 移除 risk_categories 的 category_code
        if ($this->db->fieldExists('category_code', 'risk_categories')) {
            $this->forge->dropColumn('risk_categories', 'category_code');
        }

        // 移除 risk_topics 的 topic_code
        if ($this->db->fieldExists('topic_code', 'risk_topics')) {
            $this->forge->dropColumn('risk_topics', 'topic_code');
        }

        // 移除 risk_factors 的 factor_code
        if ($this->db->fieldExists('factor_code', 'risk_factors')) {
            $this->forge->dropColumn('risk_factors', 'factor_code');
        }

        // 移除 question_categories 的 category_code (如果表存在)
        if ($this->db->tableExists('question_categories') && $this->db->fieldExists('category_code', 'question_categories')) {
            $this->forge->dropColumn('question_categories', 'category_code');
        }

        // 移除 question_topics 的 topic_code (如果表存在)
        if ($this->db->tableExists('question_topics') && $this->db->fieldExists('topic_code', 'question_topics')) {
            $this->forge->dropColumn('question_topics', 'topic_code');
        }

        // 移除 question_factors 的 factor_code (如果表存在)
        if ($this->db->tableExists('question_factors') && $this->db->fieldExists('factor_code', 'question_factors')) {
            $this->forge->dropColumn('question_factors', 'factor_code');
        }
    }

    /**
     * 回滾 Migration - 恢復代碼欄位
     */
    public function down()
    {
        // 恢復 risk_categories.category_code
        $this->forge->addColumn('risk_categories', [
            'category_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => '分類代碼',
                'after'      => 'category_name'
            ]
        ]);

        // 恢復 risk_topics.topic_code
        $this->forge->addColumn('risk_topics', [
            'topic_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => '主題代碼',
                'after'      => 'topic_name'
            ]
        ]);

        // 恢復 risk_factors.factor_code
        $this->forge->addColumn('risk_factors', [
            'factor_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => '因子代碼',
                'after'      => 'factor_name'
            ]
        ]);

        // 恢復 question_categories.category_code (如果表存在)
        if ($this->db->tableExists('question_categories')) {
            $this->forge->addColumn('question_categories', [
                'category_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'null'       => true,
                    'comment'    => '風險分類代碼，用於程式識別',
                    'after'      => 'category_name'
                ]
            ]);
        }

        // 恢復 question_topics.topic_code (如果表存在)
        if ($this->db->tableExists('question_topics')) {
            $this->forge->addColumn('question_topics', [
                'topic_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'null'       => true,
                    'comment'    => '風險主題代碼，用於程式識別',
                    'after'      => 'topic_name'
                ]
            ]);
        }

        // 恢復 question_factors.factor_code (如果表存在)
        if ($this->db->tableExists('question_factors')) {
            $this->forge->addColumn('question_factors', [
                'factor_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'null'       => true,
                    'comment'    => '風險因子代碼，用於程式識別',
                    'after'      => 'factor_name'
                ]
            ]);
        }
    }
}
