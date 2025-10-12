<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 添加 G1/H1 select 欄位到 question_contents 和 template_contents 表
 *
 * 修復問題：Unknown column 'g1_select' in 'field list'
 *
 * 這些欄位用於：
 * - g1_select: G 對外負面衝擊評分
 * - h1_select: H 對外正面影響評分
 */
class AddG1H1SelectFieldsToContents extends Migration
{
    public function up()
    {
        // 添加欄位到 question_contents 表
        $this->addFieldsToQuestionContents();

        // 添加欄位到 template_contents 表
        $this->addFieldsToTemplateContents();
    }

    public function down()
    {
        // 從 question_contents 表移除欄位
        if ($this->db->fieldExists('g1_select', 'question_contents')) {
            $this->forge->dropColumn('question_contents', 'g1_select');
        }
        if ($this->db->fieldExists('h1_select', 'question_contents')) {
            $this->forge->dropColumn('question_contents', 'h1_select');
        }

        // 從 template_contents 表移除欄位
        if ($this->db->fieldExists('g1_select', 'template_contents')) {
            $this->forge->dropColumn('template_contents', 'g1_select');
        }
        if ($this->db->fieldExists('h1_select', 'template_contents')) {
            $this->forge->dropColumn('template_contents', 'h1_select');
        }

        echo "✅ 已移除 g1_select 和 h1_select 欄位\n";
    }

    /**
     * 添加欄位到 question_contents 表
     */
    private function addFieldsToQuestionContents()
    {
        $fields = $this->db->getFieldNames('question_contents');
        $fieldsToAdd = [];

        // 添加 g1_select 欄位（在 f2_placeholder 之後）
        if (!in_array('g1_select', $fields)) {
            $fieldsToAdd['g1_select'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'G-1 對外負面衝擊評分 (Section G1 select)',
                'after' => 'f2_placeholder'
            ];
        }

        // 添加 g1_placeholder_1 欄位（在 g1_select 之後）- 如果不存在
        if (!in_array('g1_placeholder_1', $fields)) {
            $fieldsToAdd['g1_placeholder_1'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'G-1 對外負面衝擊評分說明占位符 (Section G1-1)',
                'after' => 'g1_select'
            ];
        }

        // 添加 h1_select 欄位（在 g1_placeholder_1 之後）
        if (!in_array('h1_select', $fields)) {
            $fieldsToAdd['h1_select'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'H-1 對外正面影響評分 (Section H1 select)',
                'after' => 'g1_placeholder_1'
            ];
        }

        // 添加 h1_placeholder_1 欄位（在 h1_select 之後）- 如果不存在
        if (!in_array('h1_placeholder_1', $fields)) {
            $fieldsToAdd['h1_placeholder_1'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'H-1 對外正面影響評分說明占位符 (Section H1-1)',
                'after' => 'h1_select'
            ];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('question_contents', $fieldsToAdd);
            echo "✅ 已為 question_contents 表添加欄位: " . implode(', ', array_keys($fieldsToAdd)) . "\n";
        } else {
            echo "ℹ️  question_contents 表的所有欄位已存在，跳過添加\n";
        }
    }

    /**
     * 添加欄位到 template_contents 表
     */
    private function addFieldsToTemplateContents()
    {
        $fields = $this->db->getFieldNames('template_contents');
        $fieldsToAdd = [];

        // 添加 g1_select 欄位（在 f2_placeholder 之後）
        if (!in_array('g1_select', $fields)) {
            $fieldsToAdd['g1_select'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'G-1 對外負面衝擊評分 (Section G1 select)',
                'after' => 'f2_placeholder'
            ];
        }

        // 添加 g1_placeholder_1 欄位（在 g1_select 之後）- 如果不存在
        if (!in_array('g1_placeholder_1', $fields)) {
            $fieldsToAdd['g1_placeholder_1'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'G-1 對外負面衝擊評分說明占位符 (Section G1-1)',
                'after' => 'g1_select'
            ];
        }

        // 添加 h1_select 欄位（在 g1_placeholder_1 之後）
        if (!in_array('h1_select', $fields)) {
            $fieldsToAdd['h1_select'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'H-1 對外正面影響評分 (Section H1 select)',
                'after' => 'g1_placeholder_1'
            ];
        }

        // 添加 h1_placeholder_1 欄位（在 h1_select 之後）- 如果不存在
        if (!in_array('h1_placeholder_1', $fields)) {
            $fieldsToAdd['h1_placeholder_1'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'H-1 對外正面影響評分說明占位符 (Section H1-1)',
                'after' => 'h1_select'
            ];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('template_contents', $fieldsToAdd);
            echo "✅ 已為 template_contents 表添加欄位: " . implode(', ', array_keys($fieldsToAdd)) . "\n";
        } else {
            echo "ℹ️  template_contents 表的所有欄位已存在，跳過添加\n";
        }
    }
}
