<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingPlaceholderFieldsToTemplateContents extends Migration
{
    public function up()
    {
        // Add missing F1, G1, H1 placeholder fields to template_contents
        $fields = [
            'f1_placeholder_1' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'F-1 機會描述占位符 (Section F1-1)',
                'after' => 'e2_placeholder'
            ],
            'g1_placeholder_1' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'G-1 對外負面衝擊評分說明占位符 (Section G1-1)',
                'after' => 'f2_placeholder'
            ],
            'h1_placeholder_1' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'H-1 對外正面影響評分說明占位符 (Section H1-1)',
                'after' => 'g1_placeholder_1'
            ]
        ];

        $this->forge->addColumn('template_contents', $fields);

        echo "✅ 已為 template_contents 表添加缺失的 F1, G1, H1 占位符欄位\n";
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
