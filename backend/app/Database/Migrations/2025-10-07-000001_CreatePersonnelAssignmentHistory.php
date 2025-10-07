<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 人員指派歷史記錄表
 *
 * 記錄所有指派和移除操作的完整歷史
 */
class CreatePersonnelAssignmentHistory extends Migration
{
    public function up()
    {
        // 建立人員指派歷史表
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'assignment_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'comment' => '原始指派記錄ID（移除時記錄）',
            ],
            'company_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'comment' => '公司ID',
            ],
            'assessment_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'comment' => '評估記錄ID',
            ],
            'question_content_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => '題項內容ID（支援UUID字串）',
            ],
            'personnel_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'comment' => '人員ID',
            ],
            'personnel_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '人員姓名（快照）',
            ],
            'personnel_department' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '人員部門（快照）',
            ],
            'personnel_position' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '人員職位（快照）',
            ],
            'assignment_status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '指派狀態（快照）',
            ],
            'assignment_note' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '指派備註（快照）',
            ],
            'action_type' => [
                'type' => 'ENUM',
                'constraint' => ['created', 'removed', 'updated'],
                'default' => 'created',
                'comment' => '操作類型：created=新增, removed=移除, updated=更新',
            ],
            'action_by' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'comment' => '操作人員ID',
            ],
            'action_by_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '操作人員姓名',
            ],
            'action_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '操作原因/備註',
            ],
            'action_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'comment' => '操作時間',
            ],
            'original_assigned_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'comment' => '原始指派時間（移除時記錄）',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);

        // 建立索引以提升查詢效能
        $this->forge->addKey(['company_id', 'assessment_id']);
        $this->forge->addKey(['personnel_id']);
        $this->forge->addKey(['question_content_id']);
        $this->forge->addKey(['action_type']);
        $this->forge->addKey(['action_at']);

        $this->forge->createTable('personnel_assignment_history');

        // 記錄遷移操作
        if ($this->db->tableExists('activity_logs')) {
            $this->db->query("
                INSERT INTO activity_logs (action, resource_type, description, created_at)
                VALUES (
                    'SCHEMA_MIGRATION',
                    'DATABASE',
                    '建立人員指派歷史記錄表 - 記錄所有指派新增、移除、更新操作',
                    NOW()
                )
            ");
        }
    }

    public function down()
    {
        $this->forge->dropTable('personnel_assignment_history', true);

        // 記錄回滾操作
        if ($this->db->tableExists('activity_logs')) {
            $this->db->query("
                INSERT INTO activity_logs (action, resource_type, description, created_at)
                VALUES (
                    'SCHEMA_ROLLBACK',
                    'DATABASE',
                    '回滾人員指派歷史記錄表 - 移除 personnel_assignment_history 表',
                    NOW()
                )
            ");
        }
    }
}
