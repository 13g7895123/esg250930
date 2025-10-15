<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 更新 question_responses 表的 review_status 欄位
 *
 * 將原本的三種狀態 (pending, approved, rejected) 調整為四種狀態：
 * - not_filled: 未填寫（灰色）
 * - pending: 待審核（黃色，使用者送出後）
 * - rejected: 拒絕（紅色）
 * - completed: 完成（綠色）
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-10-15
 */
class UpdateQuestionResponsesReviewStatus extends Migration
{
    /**
     * 執行 Migration - 更新 review_status 欄位
     */
    public function up()
    {
        // 修改 review_status 欄位的 ENUM 定義
        $this->db->query("
            ALTER TABLE `question_responses`
            MODIFY COLUMN `review_status` ENUM('not_filled', 'pending', 'rejected', 'completed')
            DEFAULT 'not_filled'
            COMMENT '審核狀態：not_filled(未填寫-灰色)、pending(待審核-黃色)、rejected(拒絕-紅色)、completed(完成-綠色)'
        ");

        // 將現有的 'approved' 狀態更新為 'completed'
        $this->db->query("
            UPDATE `question_responses`
            SET `review_status` = 'completed'
            WHERE `review_status` = 'approved'
        ");

        // 記錄操作日誌
        if ($this->db->tableExists('activity_logs')) {
            $this->db->query("
                INSERT INTO activity_logs (action, resource_type, description, created_at)
                VALUES (
                    'SCHEMA_MIGRATION',
                    'DATABASE',
                    '更新 question_responses.review_status 欄位：調整為四種狀態（not_filled, pending, rejected, completed）',
                    NOW()
                )
            ");
        }
    }

    /**
     * 回滾 Migration - 還原為原本的三種狀態
     */
    public function down()
    {
        // 將 'completed' 還原為 'approved'
        $this->db->query("
            UPDATE `question_responses`
            SET `review_status` = 'approved'
            WHERE `review_status` = 'completed'
        ");

        // 將 'not_filled' 還原為 'pending'
        $this->db->query("
            UPDATE `question_responses`
            SET `review_status` = 'pending'
            WHERE `review_status` = 'not_filled'
        ");

        // 還原 ENUM 定義
        $this->db->query("
            ALTER TABLE `question_responses`
            MODIFY COLUMN `review_status` ENUM('pending', 'approved', 'rejected')
            DEFAULT 'pending'
            COMMENT '審核狀態：pending(待審核)、approved(已核准)、rejected(已駁回)'
        ");

        // 記錄回滾操作
        if ($this->db->tableExists('activity_logs')) {
            $this->db->query("
                INSERT INTO activity_logs (action, resource_type, description, created_at)
                VALUES (
                    'SCHEMA_ROLLBACK',
                    'DATABASE',
                    '回滾 question_responses.review_status 欄位：還原為三種狀態（pending, approved, rejected）',
                    NOW()
                )
            ");
        }
    }
}
