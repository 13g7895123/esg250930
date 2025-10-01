<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 創建題項管理的量表資料表
 *
 * 此 Migration 為題項管理建立獨立的量表資料結構，包含：
 * - 題項可能性量表主表 (question_probability_scales)
 * - 題項可能性量表欄位定義表 (question_probability_scale_columns)
 * - 題項可能性量表資料列表 (question_probability_scale_rows)
 * - 題項財務衝擊量表主表 (question_impact_scales)
 * - 題項財務衝擊量表欄位定義表 (question_impact_scale_columns)
 * - 題項財務衝擊量表資料列表 (question_impact_scale_rows)
 *
 * 目的：讓題項管理與範本管理的量表完全獨立運作，
 * 但可透過複製機制從範本載入初始量表設定
 */
class CreateQuestionScaleTables extends Migration
{
    public function up()
    {
        // ==========================================
        // 1. 題項可能性量表主表
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID',
            ],
            'assessment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '評估記錄ID,關聯company_assessments表',
            ],
            'description_text' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => '說明文字內容',
            ],
            'show_description' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => '是否顯示說明文字: 0=不顯示, 1=顯示',
            ],
            'selected_display_column' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '預設顯示欄位: probability=發生可能性程度, 或欄位ID數字',
            ],
            'copied_from_template_scale' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '複製來源：範本量表 ID，用於追蹤資料來源',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '建立時間',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '更新時間',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('assessment_id');
        $this->forge->addForeignKey('assessment_id', 'company_assessments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('question_probability_scales', true, ['COMMENT' => '題項可能性量表主表 - 存儲題項的可能性量表基本設定']);

        // ==========================================
        // 2. 題項可能性量表欄位定義表
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID',
            ],
            'scale_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '量表ID,關聯question_probability_scales表',
            ],
            'column_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'comment'    => '欄位ID (前端使用的唯一識別碼)',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'comment'    => '欄位名稱,如:如風險不曾發生過',
            ],
            'removable' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'comment'    => '是否可移除: 0=不可移除, 1=可移除',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => '排序順序,數字越小越前面',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '建立時間',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '更新時間',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('scale_id');
        $this->forge->addKey('sort_order');
        $this->forge->addForeignKey('scale_id', 'question_probability_scales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('question_probability_scale_columns', true, ['COMMENT' => '題項可能性量表欄位定義表 - 存儲量表的動態欄位設定']);

        // ==========================================
        // 3. 題項可能性量表資料列表
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID',
            ],
            'scale_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '量表ID,關聯question_probability_scales表',
            ],
            'probability' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '發生可能性程度 (固定欄位)',
            ],
            'score_range' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '分數級距 (固定欄位)',
            ],
            'dynamic_fields' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => '動態欄位資料,以 JSON 格式儲存,key 為 column_id',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => '排序順序,數字越小越前面',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '建立時間',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '更新時間',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('scale_id');
        $this->forge->addKey('sort_order');
        $this->forge->addForeignKey('scale_id', 'question_probability_scales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('question_probability_scale_rows', true, ['COMMENT' => '題項可能性量表資料列表 - 存儲量表的實際資料列']);

        // ==========================================
        // 4. 題項財務衝擊量表主表
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID',
            ],
            'assessment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '評估記錄ID,關聯company_assessments表',
            ],
            'selected_display_column' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '預設顯示欄位: impactLevel=財務衝擊程度, 或欄位ID數字',
            ],
            'copied_from_template_scale' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '複製來源：範本量表 ID，用於追蹤資料來源',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '建立時間',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '更新時間',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('assessment_id');
        $this->forge->addForeignKey('assessment_id', 'company_assessments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('question_impact_scales', true, ['COMMENT' => '題項財務衝擊量表主表 - 存儲題項的財務衝擊量表基本設定']);

        // ==========================================
        // 5. 題項財務衝擊量表欄位定義表
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID',
            ],
            'scale_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '量表ID,關聯question_impact_scales表',
            ],
            'column_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'comment'    => '欄位ID (前端使用的唯一識別碼)',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'comment'    => '欄位名稱,如:股東權益金額',
            ],
            'amount_note' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '金額說明備註',
            ],
            'removable' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'comment'    => '是否可移除: 0=不可移除, 1=可移除',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => '排序順序,數字越小越前面',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '建立時間',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '更新時間',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('scale_id');
        $this->forge->addKey('sort_order');
        $this->forge->addForeignKey('scale_id', 'question_impact_scales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('question_impact_scale_columns', true, ['COMMENT' => '題項財務衝擊量表欄位定義表 - 存儲量表的動態欄位設定']);

        // ==========================================
        // 6. 題項財務衝擊量表資料列表
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID',
            ],
            'scale_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '量表ID,關聯question_impact_scales表',
            ],
            'impact_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '財務衝擊程度 (固定欄位)',
            ],
            'score_range' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '分數級距 (固定欄位)',
            ],
            'dynamic_fields' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => '動態欄位資料,以 JSON 格式儲存,key 為 column_id',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => '排序順序,數字越小越前面',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '建立時間',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => '更新時間',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('scale_id');
        $this->forge->addKey('sort_order');
        $this->forge->addForeignKey('scale_id', 'question_impact_scales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('question_impact_scale_rows', true, ['COMMENT' => '題項財務衝擊量表資料列表 - 存儲量表的實際資料列']);
    }

    public function down()
    {
        // 按照依賴關係倒序刪除表格
        $this->forge->dropTable('question_impact_scale_rows', true);
        $this->forge->dropTable('question_impact_scale_columns', true);
        $this->forge->dropTable('question_impact_scales', true);
        $this->forge->dropTable('question_probability_scale_rows', true);
        $this->forge->dropTable('question_probability_scale_columns', true);
        $this->forge->dropTable('question_probability_scales', true);
    }
}