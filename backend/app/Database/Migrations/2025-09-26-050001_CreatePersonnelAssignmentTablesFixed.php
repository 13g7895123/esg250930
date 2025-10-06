<?php

namespace App\Database\migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 創建人員指派管理相關表格 (修復版)
 *
 * 此 Migration 為人員指派功能建立所需的資料表結構，包含：
 * - 外部人員資料表 (external_personnel)
 * - 人員指派表 (personnel_assignments)
 *
 * 修復問題：解決中文備註亂碼問題
 *
 * @author Claude Code Assistant
 * @version 1.1
 * @since 2025-09-26
 */
class CreatePersonnelAssignmentTablesFixed extends Migration
{
    /**
     * 執行 Migration - 建立人員指派相關表格
     */
    public function up()
    {
        // 設定連線字符集
        $this->db->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

        // =============================================
        // 1. 外部人員資料表
        // =============================================

        /**
         * external_personnel - 外部人員資料表
         *
         * 儲存從外部 API 取得的人員基本資料，作為指派功能的人員來源
         * 提供人員資料的本地快取和查詢功能
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID'
            ],
            'external_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => '外部系統的人員ID，對應外部API返回的人員識別碼'
            ],
            'company_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => '所屬公司ID，關聯到 local_companies 表'
            ],
            'external_company_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => '外部系統的公司ID，對應 local_companies.external_id'
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => '人員姓名'
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '電子郵件地址'
            ],
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '所屬部門，如：人資部、財務部等'
            ],
            'position' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '職位名稱，如：經理、主管、專員等'
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '聯絡電話'
            ],
            'avatar' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => '頭像圖片URL'
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
                'comment'    => '人員狀態：active(啟用)、inactive(停用)'
            ],
            'groups' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => '人員群組資訊，以JSON格式儲存外部系統的群組資料'
            ],
            'last_synced_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'comment' => '最後同步時間，記錄從外部API最後更新資料的時間'
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
                'comment' => '建立時間'
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
                'comment' => '最後更新時間'
            ]
        ]);

        // 設定主鍵
        $this->forge->addKey('id', true);

        // 建立唯一約束：同一公司內的外部人員ID不能重複
        $this->forge->addUniqueKey(['company_id', 'external_id'], 'uk_external_personnel_company_external_id');

        // 建立索引提升查詢效能
        $this->forge->addKey(['company_id'], false, false, 'idx_external_personnel_company_id');
        $this->forge->addKey(['external_id'], false, false, 'idx_external_personnel_external_id');
        $this->forge->addKey(['status'], false, false, 'idx_external_personnel_status');
        $this->forge->addKey(['department'], false, false, 'idx_external_personnel_department');
        $this->forge->addKey(['position'], false, false, 'idx_external_personnel_position');
        $this->forge->addKey(['last_synced_at'], false, false, 'idx_external_personnel_last_synced_at');

        // 建立表格（使用 utf8mb4 字符集確保中文正確顯示）
        $this->forge->createTable('external_personnel', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => '外部人員資料表 - 儲存從外部API取得的人員基本資料'
        ]);

        // =============================================
        // 2. 人員指派表
        // =============================================

        /**
         * personnel_assignments - 人員指派表
         *
         * 記錄人員對題項內容的指派關係（多對多關係）
         * 替代目前 localStorage 的指派資料存儲方式
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID'
            ],
            'company_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => '所屬公司ID，關聯到 local_companies 表'
            ],
            'assessment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => '評估記錄ID，關聯到 company_assessments 表'
            ],
            'question_content_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => '題項內容ID，關聯到 question_contents 表（支援UUID字串格式）'
            ],
            'personnel_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => '指派的人員ID，關聯到 external_personnel 表'
            ],

            // 冗餘欄位：提升查詢效能，避免多表 JOIN
            'personnel_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => '人員姓名（冗餘欄位，提升查詢效能）'
            ],
            'personnel_department' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '人員部門（冗餘欄位，提升查詢效能）'
            ],
            'personnel_position' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '人員職位（冗餘欄位，提升查詢效能）'
            ],

            'assignment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['assigned', 'accepted', 'declined', 'completed'],
                'default'    => 'assigned',
                'comment'    => '指派狀態：assigned(已指派)、accepted(已接受)、declined(已拒絕)、completed(已完成)'
            ],
            'assignment_note' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => '指派備註，可記錄指派原因或特殊說明'
            ],
            'assigned_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '指派人員ID，未來可關聯到使用者表'
            ],
            'assigned_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
                'comment' => '指派時間'
            ],
            'accepted_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'comment' => '接受指派時間'
            ],
            'completed_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'comment' => '完成評估時間'
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
                'comment' => '建立時間'
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
                'comment' => '最後更新時間'
            ]
        ]);

        // 設定主鍵
        $this->forge->addKey('id', true);

        // 建立唯一約束：防止重複指派同一人員到同一題項內容
        $this->forge->addUniqueKey(
            ['assessment_id', 'question_content_id', 'personnel_id'],
            'uk_personnel_assignment_unique'
        );

        // 建立索引提升查詢效能
        $this->forge->addKey(['company_id'], false, false, 'idx_personnel_assignments_company_id');
        $this->forge->addKey(['assessment_id'], false, false, 'idx_personnel_assignments_assessment_id');
        $this->forge->addKey(['question_content_id'], false, false, 'idx_personnel_assignments_question_content_id');
        $this->forge->addKey(['personnel_id'], false, false, 'idx_personnel_assignments_personnel_id');
        $this->forge->addKey(['assignment_status'], false, false, 'idx_personnel_assignments_assignment_status');
        $this->forge->addKey(['assigned_at'], false, false, 'idx_personnel_assignments_assigned_at');
        $this->forge->addKey(['assigned_by'], false, false, 'idx_personnel_assignments_assigned_by');

        // 複合索引：常用查詢組合
        $this->forge->addKey(['company_id', 'assessment_id'], false, false, 'idx_personnel_assignments_company_assessment');
        $this->forge->addKey(['assessment_id', 'personnel_id'], false, false, 'idx_personnel_assignments_assessment_personnel');

        // 建立表格（使用 utf8mb4 字符集確保中文正確顯示）
        $this->forge->createTable('personnel_assignments', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => '人員指派表 - 記錄人員對題項內容的指派關係（多對多）'
        ]);

        // =============================================
        // 3. 添加外鍵約束（等相關表存在後）
        // =============================================

        // 由於可能部分相關表格尚未建立，先檢查表格是否存在再添加約束
        if ($this->db->tableExists('local_companies')) {
            $this->forge->addForeignKey('company_id', 'local_companies', 'id', 'CASCADE', 'CASCADE', 'external_personnel');
            $this->forge->addForeignKey('company_id', 'local_companies', 'id', 'CASCADE', 'CASCADE', 'personnel_assignments');
        }

        if ($this->db->tableExists('company_assessments')) {
            $this->forge->addForeignKey('assessment_id', 'company_assessments', 'id', 'CASCADE', 'CASCADE', 'personnel_assignments');
        }

        // 註解：question_content_id 使用字串UUID格式，無法建立外鍵約束
        // if ($this->db->tableExists('question_contents')) {
        //     $this->forge->addForeignKey('question_content_id', 'question_contents', 'id', 'CASCADE', 'CASCADE', 'personnel_assignments');
        // }

        // personnel_assignments 到 external_personnel 的外鍵約束
        $this->forge->addForeignKey('personnel_id', 'external_personnel', 'id', 'CASCADE', 'CASCADE', 'personnel_assignments');

        // =============================================
        // 4. 記錄操作日誌
        // =============================================

        // 檢查 activity_logs 表是否存在，如果存在則記錄此次架構變更
        if ($this->db->tableExists('activity_logs')) {
            $this->db->query("
                INSERT INTO activity_logs (action, resource_type, description, created_at)
                VALUES (
                    'SCHEMA_MIGRATION',
                    'DATABASE',
                    '建立人員指派管理表格 (修復版) - 新增 external_personnel, personnel_assignments 表，解決中文備註問題',
                    NOW()
                )
            ");
        }
    }

    /**
     * 回滾 Migration - 移除人員指派相關表格
     *
     * 注意：由於有外鍵約束，需要按照依賴關係倒序刪除
     */
    public function down()
    {
        // 按照依賴關係倒序刪除表格

        // 2. 先刪除 personnel_assignments（依賴 external_personnel）
        $this->forge->dropTable('personnel_assignments', true);

        // 1. 再刪除 external_personnel
        $this->forge->dropTable('external_personnel', true);

        // 記錄回滾操作
        if ($this->db->tableExists('activity_logs')) {
            $this->db->query("
                INSERT INTO activity_logs (action, resource_type, description, created_at)
                VALUES (
                    'SCHEMA_ROLLBACK',
                    'DATABASE',
                    '回滾人員指派管理表格 (修復版) - 移除 external_personnel, personnel_assignments 表',
                    NOW()
                )
            ");
        }
    }
}