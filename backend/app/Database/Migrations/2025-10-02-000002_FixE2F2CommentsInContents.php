<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 修正 template_contents 和 question_contents 表的 E2/F2 欄位備註
 *
 * 問題描述：
 * - template_contents 和 question_contents 表的 e2_* 和 f2_* 欄位備註可能顯示為亂碼
 *
 * 解決方案：
 * - 使用 MODIFY COLUMN 重新設置正確的 UTF-8 中文備註
 * - 確保所有欄位使用 utf8mb4_unicode_ci 編碼
 */
class FixE2F2CommentsInContents extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        echo "開始修正 template_contents 和 question_contents 表的欄位備註...\n\n";

        // ===== 修正 template_contents 表 =====
        echo "步驟 1: 修正 template_contents 表的 E2/F2 欄位備註\n";

        $templateModifyQueries = [
            "ALTER TABLE `template_contents`
             MODIFY COLUMN `e2_select_1` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'E2區域-風險發生可能性'",

            "ALTER TABLE `template_contents`
             MODIFY COLUMN `e2_select_2` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'E2區域-風險發生衝擊程度'",

            "ALTER TABLE `template_contents`
             MODIFY COLUMN `e2_placeholder` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'E2區域-風險計算說明'",

            "ALTER TABLE `template_contents`
             MODIFY COLUMN `f2_select_1` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'F2區域-機會發生可能性'",

            "ALTER TABLE `template_contents`
             MODIFY COLUMN `f2_select_2` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'F2區域-機會發生衝擊程度'",

            "ALTER TABLE `template_contents`
             MODIFY COLUMN `f2_placeholder` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'F2區域-機會計算說明'"
        ];

        foreach ($templateModifyQueries as $query) {
            try {
                $db->query($query);
                preg_match('/`(\w+)`/', $query, $matches);
                $fieldName = $matches[1] ?? 'unknown';
                echo "  ✅ template_contents.{$fieldName} 備註已修正\n";
            } catch (\Exception $e) {
                echo "  ⚠️  修正失敗: " . $e->getMessage() . "\n";
            }
        }

        // ===== 修正 question_contents 表 =====
        echo "\n步驟 2: 修正 question_contents 表的 E2/F2 欄位備註\n";

        $questionModifyQueries = [
            "ALTER TABLE `question_contents`
             MODIFY COLUMN `e2_select_1` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'E2區域-風險發生可能性'",

            "ALTER TABLE `question_contents`
             MODIFY COLUMN `e2_select_2` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'E2區域-風險發生衝擊程度'",

            "ALTER TABLE `question_contents`
             MODIFY COLUMN `e2_placeholder` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'E2區域-風險計算說明'",

            "ALTER TABLE `question_contents`
             MODIFY COLUMN `f2_select_1` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'F2區域-機會發生可能性'",

            "ALTER TABLE `question_contents`
             MODIFY COLUMN `f2_select_2` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'F2區域-機會發生衝擊程度'",

            "ALTER TABLE `question_contents`
             MODIFY COLUMN `f2_placeholder` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL
             COMMENT 'F2區域-機會計算說明'"
        ];

        foreach ($questionModifyQueries as $query) {
            try {
                $db->query($query);
                preg_match('/`(\w+)`/', $query, $matches);
                $fieldName = $matches[1] ?? 'unknown';
                echo "  ✅ question_contents.{$fieldName} 備註已修正\n";
            } catch (\Exception $e) {
                echo "  ⚠️  修正失敗: " . $e->getMessage() . "\n";
            }
        }

        echo "\n===========================================\n";
        echo "✅ template_contents 和 question_contents 表修正完成！\n";
        echo "===========================================\n";
        echo "已修正欄位:\n";
        echo "  E2: e2_select_1, e2_select_2, e2_placeholder\n";
        echo "  F2: f2_select_1, f2_select_2, f2_placeholder\n";
        echo "===========================================\n\n";
    }

    public function down()
    {
        echo "此 migration 僅修正欄位備註，無需回滾\n";
    }
}
