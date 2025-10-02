<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixE1F1Fields extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 首先檢查 E-2/F-2 欄位是否存在
        if (!$db->fieldExists('e2_risk_probability', 'question_responses')) {
            // 如果E-2/F-2欄位不存在，先創建它們
            $fields = [
                // E-2區域 - 風險財務影響評估
                'e2_risk_probability' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'E2區域-風險發生可能性'
                ],
                'e2_risk_impact' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'E2區域-風險發生衝擊程度'
                ],
                'e2_risk_calculation' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'E2區域-風險計算說明'
                ],
                // F-2區域 - 機會財務影響評估
                'f2_opportunity_probability' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'F2區域-機會發生可能性'
                ],
                'f2_opportunity_impact' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'F2區域-機會發生衝擊程度'
                ],
                'f2_opportunity_calculation' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'F2區域-機會計算說明'
                ]
            ];

            $this->forge->addColumn('question_responses', $fields);
            echo "✅ 已添加 E-2 和 F-2 欄位\n";
        }

        // 遷移 E-1 的 probability/impact/calculation 資料到 E-2
        if ($db->fieldExists('e1_risk_probability', 'question_responses')) {
            $db->query("
                UPDATE question_responses
                SET
                    e2_risk_probability = e1_risk_probability,
                    e2_risk_impact = e1_risk_impact,
                    e2_risk_calculation = e1_risk_calculation
                WHERE e1_risk_probability IS NOT NULL
                   OR e1_risk_impact IS NOT NULL
                   OR e1_risk_calculation IS NOT NULL
            ");
            echo "✅ 已遷移 E-1 資料到 E-2\n";
        } else {
            echo "⏭️  E-1 欄位已不存在，跳過資料遷移\n";
        }

        // 遷移 F-1 的 probability/impact/calculation 資料到 F-2
        if ($db->fieldExists('f1_opportunity_probability', 'question_responses')) {
            $db->query("
                UPDATE question_responses
                SET
                    f2_opportunity_probability = f1_opportunity_probability,
                    f2_opportunity_impact = f1_opportunity_impact,
                    f2_opportunity_calculation = f1_opportunity_calculation
                WHERE f1_opportunity_probability IS NOT NULL
                   OR f1_opportunity_impact IS NOT NULL
                   OR f1_opportunity_calculation IS NOT NULL
            ");
            echo "✅ 已遷移 F-1 資料到 F-2\n";
        } else {
            echo "⏭️  F-1 欄位已不存在，跳過資料遷移\n";
        }

        // 刪除 E-1 的 probability/impact/calculation 欄位（這些應該只在 E-2）
        $fieldsToRemove = [
            'e1_risk_probability',
            'e1_risk_impact',
            'e1_risk_calculation',
            'f1_opportunity_probability',
            'f1_opportunity_impact',
            'f1_opportunity_calculation'
        ];

        foreach ($fieldsToRemove as $field) {
            if ($db->fieldExists($field, 'question_responses')) {
                $this->forge->dropColumn('question_responses', $field);
                echo "✅ 已移除 {$field}\n";
            }
        }

        echo "✅ E-1/F-1 欄位修正完成！\n";
        echo "   E-1 現在只有: e1_risk_description\n";
        echo "   E-2 現在有: e2_risk_probability, e2_risk_impact, e2_risk_calculation\n";
        echo "   F-1 現在只有: f1_opportunity_description\n";
        echo "   F-2 現在有: f2_opportunity_probability, f2_opportunity_impact, f2_opportunity_calculation\n";
    }

    public function down()
    {
        // 還原：重新添加 E-1/F-1 的 probability/impact/calculation 欄位
        $fields = [
            'e1_risk_probability' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'E1區域-風險發生可能性'
            ],
            'e1_risk_impact' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'E1區域-風險發生衝擊程度'
            ],
            'e1_risk_calculation' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'E1區域-風險計算說明'
            ],
            'f1_opportunity_probability' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'F1區域-機會發生可能性'
            ],
            'f1_opportunity_impact' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'F1區域-機會發生衝擊程度'
            ],
            'f1_opportunity_calculation' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'F1區域-機會計算說明'
            ]
        ];

        $this->forge->addColumn('question_responses', $fields);

        // 從 E-2 還原資料到 E-1
        $db = \Config\Database::connect();
        $db->query("
            UPDATE question_responses
            SET
                e1_risk_probability = e2_risk_probability,
                e1_risk_impact = e2_risk_impact,
                e1_risk_calculation = e2_risk_calculation
            WHERE e2_risk_probability IS NOT NULL
               OR e2_risk_impact IS NOT NULL
               OR e2_risk_calculation IS NOT NULL
        ");

        // 從 F-2 還原資料到 F-1
        $db->query("
            UPDATE question_responses
            SET
                f1_opportunity_probability = f2_opportunity_probability,
                f1_opportunity_impact = f2_opportunity_impact,
                f1_opportunity_calculation = f2_opportunity_calculation
            WHERE f2_opportunity_probability IS NOT NULL
               OR f2_opportunity_impact IS NOT NULL
               OR f2_opportunity_calculation IS NOT NULL
        ");

        echo "已還原 E-1/F-1 欄位結構\n";
    }
}
