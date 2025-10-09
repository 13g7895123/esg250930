<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQuestionFieldsToTemplateContents extends Migration
{
    public function up()
    {
        // Check if columns already exist before adding
        $db = \Config\Database::connect();
        $existingFields = $db->getFieldNames('template_contents');

        $fields = [];

        if (!in_array('a_content', $existingFields)) {
            $fields['a_content'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '風險因子議題描述內容 (Section A)',
                'after' => 'is_required'
            ];
        }

        if (!in_array('b_content', $existingFields)) {
            $fields['b_content'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '參考文字&模組工具評估結果內容 (Section B)',
                'after' => 'a_content'
            ];
        }

        if (!in_array('c_placeholder', $existingFields)) {
            $fields['c_placeholder'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '風險事件描述占位符 (Section C)',
                'after' => 'b_content'
            ];
        }

        if (!in_array('d_placeholder_1', $existingFields)) {
            $fields['d_placeholder_1'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '因應措施描述占位符 (Section D-1)',
                'after' => 'c_placeholder'
            ];
        }

        if (!in_array('d_placeholder_2', $existingFields)) {
            $fields['d_placeholder_2'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '因應措施費用占位符 (Section D-2)',
                'after' => 'd_placeholder_1'
            ];
        }

        if (!in_array('e1_placeholder_1', $existingFields)) {
            $fields['e1_placeholder_1'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '風險描述占位符 (Section E1-1)',
                'after' => 'd_placeholder_2'
            ];
        }

        if (!in_array('e1_select_1', $existingFields)) {
            $fields['e1_select_1'] = [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => '風險發生可能性預設值 (Section E1 Select 1)',
                'after' => 'e1_placeholder_1'
            ];
        }

        if (!in_array('e1_select_2', $existingFields)) {
            $fields['e1_select_2'] = [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => '風險發生衝擊程度預設值 (Section E1 Select 2)',
                'after' => 'e1_select_1'
            ];
        }

        if (!in_array('e1_placeholder_2', $existingFields)) {
            $fields['e1_placeholder_2'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '風險計算說明占位符 (Section E1-2)',
                'after' => 'e1_select_2'
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('template_contents', $fields);
            echo "✅ 已為 template_contents 表添加欄位: " . implode(', ', array_keys($fields)) . "\n";
        } else {
            echo "ℹ️  template_contents 表的所有欄位已存在，跳過添加\n";
        }
    }

    public function down()
    {
        $columns = [
            'a_content',
            'b_content',
            'c_placeholder',
            'd_placeholder_1',
            'd_placeholder_2',
            'e1_placeholder_1',
            'e1_select_1',
            'e1_select_2',
            'e1_placeholder_2'
        ];

        $this->forge->dropColumn('template_contents', $columns);

        echo "已從 template_contents 表移除題目相關欄位\n";
    }
}
