<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingPlaceholderFieldsToTemplateContents extends Migration
{
    public function up()
    {
        // Check if columns already exist before adding
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('template_contents');

        $fieldsToAdd = [];

        if (!in_array('f1_placeholder_1', $fields)) {
            $fieldsToAdd['f1_placeholder_1'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'F-1 機會描述占位符 (Section F1-1)',
                'after' => 'e2_placeholder'
            ];
        }

        if (!in_array('g1_placeholder_1', $fields)) {
            $fieldsToAdd['g1_placeholder_1'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'G-1 對外負面衝擊評分說明占位符 (Section G1-1)',
                'after' => 'f2_placeholder'
            ];
        }

        if (!in_array('h1_placeholder_1', $fields)) {
            $fieldsToAdd['h1_placeholder_1'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'H-1 對外正面影響評分說明占位符 (Section H1-1)',
                'after' => 'g1_placeholder_1'
            ];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('template_contents', $fieldsToAdd);
            echo "✅ 已為 template_contents 表添加缺失的占位符欄位: " . implode(', ', array_keys($fieldsToAdd)) . "\n";
        } else {
            echo "ℹ️  template_contents 表的所有占位符欄位已存在，跳過添加\n";
        }
    }

    public function down()
    {
        $columns = [
            'f1_placeholder_1',
            'g1_placeholder_1',
            'h1_placeholder_1'
        ];

        $this->forge->dropColumn('template_contents', $columns);

        echo "已從 template_contents 表移除 F1, G1, H1 占位符欄位\n";
    }
}
