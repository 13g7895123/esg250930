<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveOldE1FieldsFromContents extends Migration
{
    public function up()
    {
        // Remove old e1_select_1, e1_select_2, e1_placeholder_2 from template_contents
        // These fields are now replaced by e2_select_1, e2_select_2, e2_placeholder
        $oldFields = [
            'e1_select_1',
            'e1_select_2',
            'e1_placeholder_2'
        ];

        // Drop from template_contents
        foreach ($oldFields as $field) {
            if ($this->db->fieldExists($field, 'template_contents')) {
                $this->forge->dropColumn('template_contents', $field);
            }
        }

        // Drop from question_contents
        foreach ($oldFields as $field) {
            if ($this->db->fieldExists($field, 'question_contents')) {
                $this->forge->dropColumn('question_contents', $field);
            }
        }

        echo "✅ 已從 template_contents 和 question_contents 移除舊的 e1_select_1, e1_select_2, e1_placeholder_2 欄位\n";
    }

    public function down()
    {
        // Restore old fields to template_contents
        $fields = [
            'e1_select_1' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => '風險發生可能性預設值 (Section E1 Select 1)',
                'after' => 'e1_placeholder_1'
            ],
            'e1_select_2' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => '風險發生衝擊程度預設值 (Section E1 Select 2)',
                'after' => 'e1_select_1'
            ],
            'e1_placeholder_2' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '風險計算說明占位符 (Section E1-2)',
                'after' => 'e1_select_2'
            ]
        ];

        $this->forge->addColumn('template_contents', $fields);
        $this->forge->addColumn('question_contents', $fields);

        echo "已恢復 template_contents 和 question_contents 的舊欄位\n";
    }
}
