<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveResponseValueFromQuestionResponses extends Migration
{
    public function up()
    {
        // 移除 response_value 欄位
        if ($this->db->fieldExists('response_value', 'question_responses')) {
            $this->forge->dropColumn('question_responses', 'response_value');
        }
    }

    public function down()
    {
        // 如果需要回滾，重新添加 response_value 欄位
        $fields = [
            'response_value' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '回答值（JSON格式）- 向後相容用途',
                'after' => 'question_content_id'
            ]
        ];

        $this->forge->addColumn('question_responses', $fields);
    }
}