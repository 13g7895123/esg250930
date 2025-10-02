<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddE2F2FieldsToQuestionResponses extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Check if e2_risk_probability already exists, if so, skip this migration
        if ($db->fieldExists('e2_risk_probability', 'question_responses')) {
            echo "⏭️  E2/F2 欄位已存在於 question_responses，跳過此 migration\n";
            return;
        }

        // Add E-2 and F-2 fields to question_responses
        $fields = [
            // E-2區域 - 風險財務影響評估
            'e2_risk_probability' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'E2區域-風險發生可能性',
                'after' => 'e1_risk_calculation'
            ],
            'e2_risk_impact' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'E2區域-風險發生衝擊程度',
                'after' => 'e2_risk_probability'
            ],
            'e2_risk_calculation' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'E2區域-風險計算說明',
                'after' => 'e2_risk_impact'
            ],

            // F-2區域 - 機會財務影響評估
            'f2_opportunity_probability' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'F2區域-機會發生可能性',
                'after' => 'f1_opportunity_calculation'
            ],
            'f2_opportunity_impact' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'F2區域-機會發生衝擊程度',
                'after' => 'f2_opportunity_probability'
            ],
            'f2_opportunity_calculation' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'F2區域-機會計算說明',
                'after' => 'f2_opportunity_impact'
            ]
        ];

        $this->forge->addColumn('question_responses', $fields);

        // Migrate existing data from e1 to e2 fields
        $db = \Config\Database::connect();
        $db->query("
            UPDATE question_responses
            SET
                e2_risk_probability = e1_risk_probability,
                e2_risk_impact = e1_risk_impact,
                e2_risk_calculation = e1_risk_calculation,
                f2_opportunity_probability = f1_opportunity_probability,
                f2_opportunity_impact = f1_opportunity_impact,
                f2_opportunity_calculation = f1_opportunity_calculation
            WHERE e1_risk_probability IS NOT NULL
               OR e1_risk_impact IS NOT NULL
               OR e1_risk_calculation IS NOT NULL
               OR f1_opportunity_probability IS NOT NULL
               OR f1_opportunity_impact IS NOT NULL
               OR f1_opportunity_calculation IS NOT NULL
        ");

        // Add indexes for new fields
        $this->forge->addKey('e2_risk_probability');
        $this->forge->addKey('e2_risk_impact');
        $this->forge->addKey('f2_opportunity_probability');
        $this->forge->addKey('f2_opportunity_impact');

        echo "✅ 已為 question_responses 表添加 E-2 和 F-2 欄位並遷移現有資料\n";
    }

    public function down()
    {
        $columns = [
            'e2_risk_probability',
            'e2_risk_impact',
            'e2_risk_calculation',
            'f2_opportunity_probability',
            'f2_opportunity_impact',
            'f2_opportunity_calculation'
        ];

        $this->forge->dropColumn('question_responses', $columns);

        echo "已從 question_responses 表移除 E-2 和 F-2 欄位\n";
    }
}
