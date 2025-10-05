<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateQuestionContentsDescriptionToAContent extends Migration
{
    public function up()
    {
        // First, add a_content column if it doesn't exist
        if (!$this->db->fieldExists('a_content', 'question_contents')) {
            $fields = [
                'a_content' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => '風險因子描述 (統一使用此欄位)',
                    'after' => 'description'
                ]
            ];

            $this->forge->addColumn('question_contents', $fields);
            echo "已在 question_contents 表新增 a_content 欄位\n";
        }

        // Copy data from description to a_content
        $this->db->query("
            UPDATE question_contents
            SET a_content = COALESCE(description, a_content)
            WHERE description IS NOT NULL
            AND (a_content IS NULL OR a_content = '')
        ");

        echo "已將 question_contents 表的 description 欄位資料複製到 a_content\n";

        // Drop the description column from question_contents
        if ($this->db->fieldExists('description', 'question_contents')) {
            $this->forge->dropColumn('question_contents', 'description');
            echo "已從 question_contents 表移除 description 欄位\n";
        }
    }

    public function down()
    {
        // Add back the description column
        $fields = [
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '題目詳細描述 (已棄用，改用 a_content)',
                'after' => 'title'
            ]
        ];

        $this->forge->addColumn('question_contents', $fields);

        // Copy data back from a_content to description
        $this->db->query("
            UPDATE question_contents
            SET description = a_content
            WHERE a_content IS NOT NULL
        ");

        echo "已恢復 question_contents 表的 description 欄位\n";

        // Drop a_content column
        if ($this->db->fieldExists('a_content', 'question_contents')) {
            $this->forge->dropColumn('question_contents', 'a_content');
            echo "已從 question_contents 表移除 a_content 欄位\n";
        }
    }
}
