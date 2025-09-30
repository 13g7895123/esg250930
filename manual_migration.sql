-- Manual execution of personnel assignment migration
-- Based on: backend/app/Database/Migrations/2025-09-24-120001_CreatePersonnelAssignmentTables.php

-- Create external_personnel table
CREATE TABLE IF NOT EXISTS `external_personnel` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主鍵ID',
    `external_id` VARCHAR(50) NOT NULL COMMENT '外部系統的人員ID，對應外部API返回的人員識別碼',
    `company_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '所屬公司ID，關聯到 local_companies 表',
    `external_company_id` VARCHAR(50) NOT NULL COMMENT '外部系統的公司ID，對應 local_companies.external_id',
    `name` VARCHAR(255) NOT NULL COMMENT '人員姓名',
    `email` VARCHAR(255) NULL COMMENT '電子郵件地址',
    `department` VARCHAR(255) NULL COMMENT '所屬部門，如：人資部、財務部等',
    `position` VARCHAR(255) NULL COMMENT '職位名稱，如：經理、主管、專員等',
    `phone` VARCHAR(50) NULL COMMENT '聯絡電話',
    `avatar` VARCHAR(500) NULL COMMENT '頭像圖片URL',
    `status` ENUM('active', 'inactive') DEFAULT 'active' COMMENT '人員狀態：active(啟用)、inactive(停用)',
    `groups` JSON NULL COMMENT '人員群組資訊，以JSON格式儲存外部系統的群組資料',
    `last_synced_at` TIMESTAMP NULL COMMENT '最後同步時間，記錄從外部API最後更新資料的時間',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最後更新時間',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_external_personnel_company_external_id` (`company_id`, `external_id`),
    KEY `idx_external_personnel_company_id` (`company_id`),
    KEY `idx_external_personnel_external_id` (`external_id`),
    KEY `idx_external_personnel_status` (`status`),
    KEY `idx_external_personnel_department` (`department`),
    KEY `idx_external_personnel_position` (`position`),
    KEY `idx_external_personnel_last_synced_at` (`last_synced_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='外部人員資料表 - 儲存從外部API取得的人員基本資料';

-- Create personnel_assignments table
CREATE TABLE IF NOT EXISTS `personnel_assignments` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主鍵ID',
    `company_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '所屬公司ID，關聯到 local_companies 表',
    `assessment_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '評估記錄ID，關聯到 company_assessments 表',
    `question_content_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '題項內容ID，關聯到 question_contents 表',
    `personnel_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '指派的人員ID，關聯到 external_personnel 表',
    `personnel_name` VARCHAR(255) NOT NULL COMMENT '人員姓名（冗餘欄位，提升查詢效能）',
    `personnel_department` VARCHAR(255) NULL COMMENT '人員部門（冗餘欄位，提升查詢效能）',
    `personnel_position` VARCHAR(255) NULL COMMENT '人員職位（冗餘欄位，提升查詢效能）',
    `assignment_status` ENUM('assigned', 'accepted', 'declined', 'completed') DEFAULT 'assigned' COMMENT '指派狀態：assigned(已指派)、accepted(已接受)、declined(已拒絕)、completed(已完成)',
    `assignment_note` TEXT NULL COMMENT '指派備註，可記錄指派原因或特殊說明',
    `assigned_by` BIGINT(20) UNSIGNED NULL COMMENT '指派人員ID，未來可關聯到使用者表',
    `assigned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '指派時間',
    `accepted_at` TIMESTAMP NULL COMMENT '接受指派時間',
    `completed_at` TIMESTAMP NULL COMMENT '完成評估時間',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最後更新時間',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_personnel_assignment_unique` (`assessment_id`, `question_content_id`, `personnel_id`),
    KEY `idx_personnel_assignments_company_id` (`company_id`),
    KEY `idx_personnel_assignments_assessment_id` (`assessment_id`),
    KEY `idx_personnel_assignments_question_content_id` (`question_content_id`),
    KEY `idx_personnel_assignments_personnel_id` (`personnel_id`),
    KEY `idx_personnel_assignments_assignment_status` (`assignment_status`),
    KEY `idx_personnel_assignments_assigned_at` (`assigned_at`),
    KEY `idx_personnel_assignments_assigned_by` (`assigned_by`),
    KEY `idx_personnel_assignments_company_assessment` (`company_id`, `assessment_id`),
    KEY `idx_personnel_assignments_assessment_personnel` (`assessment_id`, `personnel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='人員指派表 - 記錄人員對題項內容的指派關係（多對多）';

-- Add foreign key constraints (if referenced tables exist)
-- Note: These will be added when the referenced tables are available
-- ALTER TABLE `external_personnel` ADD CONSTRAINT `fk_external_personnel_company` FOREIGN KEY (`company_id`) REFERENCES `local_companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
-- ALTER TABLE `personnel_assignments` ADD CONSTRAINT `fk_personnel_assignments_company` FOREIGN KEY (`company_id`) REFERENCES `local_companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
-- ALTER TABLE `personnel_assignments` ADD CONSTRAINT `fk_personnel_assignments_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `company_assessments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
-- ALTER TABLE `personnel_assignments` ADD CONSTRAINT `fk_personnel_assignments_question_content` FOREIGN KEY (`question_content_id`) REFERENCES `question_contents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
-- ALTER TABLE `personnel_assignments` ADD CONSTRAINT `fk_personnel_assignments_personnel` FOREIGN KEY (`personnel_id`) REFERENCES `external_personnel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;