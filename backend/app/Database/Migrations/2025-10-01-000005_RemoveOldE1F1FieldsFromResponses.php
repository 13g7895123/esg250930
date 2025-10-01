<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveOldE1F1FieldsFromResponses extends Migration
{
    public function up()
    {
        // Remove old probability/impact/calculation fields from question_responses
        // E1 and F1 sections now only contain description fields
        // E2 and F2 sections contain probability/impact/calculation fields
        $oldFields = [
            'e1_risk_probability',
            'e1_risk_impact',
            'e1_risk_calculation',
            'f1_opportunity_probability',
            'f1_opportunity_impact',
            'f1_opportunity_calculation'
        ];

        foreach ($oldFields as $field) {
            if ($this->db->fieldExists($field, 'question_responses')) {
                $this->forge->dropColumn('question_responses', $field);
            }
        }

        echo "✅ 已從 question_responses 移除舊的 e1/f1 probability/impact/calculation 欄位\n";
    }

    public function down()
    {
        // Restore old fields
        $fields = [
            'e1_risk_probability' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'E1區域-風險發生可能性',
                'after' => 'e1_risk_description'
            ],
            'e1_risk_impact' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'E1區域-風險發生衝擊程度',
                'after' => 'e1_risk_probability'
            ],
            'e1_risk_calculation' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'E1區域-風險計算說明',
                'after' => 'e1_risk_impact'
            ],
            'f1_opportunity_probability' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'F1區域-機會發生可能性',
                'after' => 'f1_opportunity_description'
            ],
            'f1_opportunity_impact' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'F1區域-機會發生衝擊程度',
                'after' => 'f1_opportunity_probability'
            ],
            'f1_opportunity_calculation' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'F1區域-機會計算說明',
                'after' => 'f1_opportunity_impact'
            ]
        ];

        $this->forge->addColumn('question_responses', $fields);

        echo "已恢復 question_responses 的舊欄位\n";
    }
}
