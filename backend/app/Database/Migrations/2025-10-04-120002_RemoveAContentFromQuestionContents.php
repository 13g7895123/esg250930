<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveAContentFromQuestionContents extends Migration
{
    public function up()
    {
        // Remove a_content column from question_contents table
        // This field is now replaced by question_factors.description
        if ($this->db->fieldExists('a_content', 'question_contents')) {
            $this->forge->dropColumn('question_contents', 'a_content');
        }
    }

    public function down()
    {
        // Restore a_content column if migration is rolled back
        $fields = [
            'a_content' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '風險因子議題描述 (已棄用，改用 question_factors.description)',
            ],
        ];

        $this->forge->addColumn('question_contents', $fields);
    }
}
