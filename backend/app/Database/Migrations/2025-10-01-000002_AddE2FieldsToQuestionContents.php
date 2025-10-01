<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddE2FieldsToQuestionContents extends Migration
{
    public function up()
    {
        // Add new E-2 fields
        $fields = [
            'e2_select_1' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => 'E-2風險發生可能性 (Section E2 Select 1)',
                'after' => 'e1_placeholder_2'
            ],
            'e2_select_2' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => 'E-2風險發生衝擊程度 (Section E2 Select 2)',
                'after' => 'e2_select_1'
            ],
            'e2_placeholder' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'E-2計算說明 (Section E2 Placeholder)',
                'after' => 'e2_select_2'
            ],
            'f2_select_1' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => 'F-2機會發生可能性 (Section F2 Select 1)',
                'after' => 'e2_placeholder'
            ],
            'f2_select_2' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => 'F-2機會發生衝擊程度 (Section F2 Select 2)',
                'after' => 'f2_select_1'
            ],
            'f2_placeholder' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'F-2計算說明 (Section F2 Placeholder)',
                'after' => 'f2_select_2'
            ]
        ];

        $this->forge->addColumn('question_contents', $fields);

        // Migrate existing data from e1_select_1/2 and e1_placeholder_2 to new e2 fields
        $db = \Config\Database::connect();

        // Copy data to new fields
        $db->query("
            UPDATE question_contents
            SET
                e2_select_1 = e1_select_1,
                e2_select_2 = e1_select_2,
                e2_placeholder = e1_placeholder_2
            WHERE e1_select_1 IS NOT NULL
               OR e1_select_2 IS NOT NULL
               OR e1_placeholder_2 IS NOT NULL
        ");

        echo "✅ 已為 question_contents 表添加 E-2 和 F-2 欄位並遷移現有資料\n";
    }

    public function down()
    {
        $columns = [
            'e2_select_1',
            'e2_select_2',
            'e2_placeholder',
            'f2_select_1',
            'f2_select_2',
            'f2_placeholder'
        ];

        $this->forge->dropColumn('question_contents', $columns);

        echo "已從 question_contents 表移除 E-2 和 F-2 欄位\n";
    }
}
