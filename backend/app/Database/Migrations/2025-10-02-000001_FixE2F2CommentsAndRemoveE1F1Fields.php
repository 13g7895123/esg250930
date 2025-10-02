<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 修正 E2/F2 欄位備註並移除舊的 E1/F1 probability/impact/calculation 欄位
 *
 * 問題描述：
 * 1. question_responses 表的 e2_* 和 f2_* 欄位備註顯示為亂碼
 * 2. 舊的 e1_risk_probability, e1_risk_impact, e1_risk_calculation 欄位應該被移除
 * 3. 舊的 f1_opportunity_probability, f1_opportunity_impact, f1_opportunity_calculation 欄位應該被移除
 *
 * 解決方案：
 * - 使用 MODIFY COLUMN 重新設置正確的 UTF-8 中文備註
 * - 確保移除所有舊的 probability/impact/calculation 欄位
 * - E1 區域只保留: e1_risk_description
 * - E2 區域包含: e2_risk_probability, e2_risk_impact, e2_risk_calculation
 * - F1 區域只保留: f1_opportunity_description
 * - F2 區域包含: f2_opportunity_probability, f2_opportunity_impact, f2_opportunity_calculation
 */
class FixE2F2CommentsAndRemoveE1F1Fields extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        echo "開始修正 question_responses 表的欄位備註和結構...\n\n";

        // ===== 步驟 1: 移除舊的 E1/F1 probability/impact/calculation 欄位 =====
        echo "步驟 1: 移除舊的 E1/F1 probability/impact/calculation 欄位\n";

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
                $forge->dropColumn('question_responses', $field);
                echo "  ✅ 已移除欄位: {$field}\n";
            } else {
                echo "  ⏭️  欄位不存在，跳過: {$field}\n";
            }
        }

        // ===== 步驟 2: 確保 E2/F2 欄位存在 =====
        echo "\n步驟 2: 確保 E2/F2 欄位存在\n";

        if (!$db->fieldExists('e2_risk_probability', 'question_responses')) {
            $e2f2Fields = [
                'e2_risk_probability' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'E2區域-風險發生可能性',
                    'after' => 'e1_risk_description'
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
                'f2_opportunity_probability' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'F2區域-機會發生可能性',
                    'after' => 'f1_opportunity_description'
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

            $forge->addColumn('question_responses', $e2f2Fields);
            echo "  ✅ 已添加 E2/F2 欄位\n";
        } else {
            echo "  ✅ E2/F2 欄位已存在\n";
        }

        // ===== 步驟 3: 修正 E2/F2 欄位的備註（使用 ALTER TABLE MODIFY COLUMN） =====
        echo "\n步驟 3: 修正 E2/F2 欄位的中文備註\n";

        // 使用原生 SQL 確保 UTF-8 編碼正確
        $modifyQueries = [
            "ALTER TABLE `question_responses`
             MODIFY COLUMN `e2_risk_probability` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'E2區域-風險發生可能性'",

            "ALTER TABLE `question_responses`
             MODIFY COLUMN `e2_risk_impact` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'E2區域-風險發生衝擊程度'",

            "ALTER TABLE `question_responses`
             MODIFY COLUMN `e2_risk_calculation` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'E2區域-風險計算說明'",

            "ALTER TABLE `question_responses`
             MODIFY COLUMN `f2_opportunity_probability` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'F2區域-機會發生可能性'",

            "ALTER TABLE `question_responses`
             MODIFY COLUMN `f2_opportunity_impact` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'F2區域-機會發生衝擊程度'",

            "ALTER TABLE `question_responses`
             MODIFY COLUMN `f2_opportunity_calculation` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'F2區域-機會計算說明'"
        ];

        foreach ($modifyQueries as $query) {
            try {
                $db->query($query);
                $fieldName = preg_match('/`(\w+)`/', $query, $matches) ? $matches[1] : 'unknown';
                echo "  ✅ 已修正欄位備註: {$fieldName}\n";
            } catch (\Exception $e) {
                echo "  ⚠️  修正欄位備註失敗: " . $e->getMessage() . "\n";
            }
        }

        echo "\n===========================================\n";
        echo "✅ question_responses 表修正完成！\n";
        echo "===========================================\n";
        echo "最終結構:\n";
        echo "  E1 區域: e1_risk_description\n";
        echo "  E2 區域: e2_risk_probability, e2_risk_impact, e2_risk_calculation\n";
        echo "  F1 區域: f1_opportunity_description\n";
        echo "  F2 區域: f2_opportunity_probability, f2_opportunity_impact, f2_opportunity_calculation\n";
        echo "===========================================\n\n";
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        echo "開始回滾 question_responses 表的修正...\n\n";

        // 重新添加 E1/F1 的 probability/impact/calculation 欄位
        $fieldsToRestore = [
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

        $forge->addColumn('question_responses', $fieldsToRestore);

        // 從 E2/F2 還原資料到 E1/F1
        $db->query("
            UPDATE question_responses
            SET
                e1_risk_probability = e2_risk_probability,
                e1_risk_impact = e2_risk_impact,
                e1_risk_calculation = e2_risk_calculation,
                f1_opportunity_probability = f2_opportunity_probability,
                f1_opportunity_impact = f2_opportunity_impact,
                f1_opportunity_calculation = f2_opportunity_calculation
            WHERE e2_risk_probability IS NOT NULL
               OR e2_risk_impact IS NOT NULL
               OR e2_risk_calculation IS NOT NULL
               OR f2_opportunity_probability IS NOT NULL
               OR f2_opportunity_impact IS NOT NULL
               OR f2_opportunity_calculation IS NOT NULL
        ");

        echo "✅ 已回滾 question_responses 表的修正\n";
    }
}
