<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScaleTables extends Migration
{
    public function up()
    {
        // ==========================================
        // 可能性量表主表
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID',
            ],
            'template_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '範本ID,關聯risk_assessment_templates表',
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
        $this->forge->addKey('template_id');
        $this->forge->addForeignKey('template_id', 'risk_assessment_templates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('probability_scales', true, ['COMMENT' => '可能性量表主表 - 存儲範本的可能性量表基本設定']);

        // ==========================================
        // 可能性量表欄位定義表
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
                'comment'    => '量表ID,關聯probability_scales表',
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
        $this->forge->addForeignKey('scale_id', 'probability_scales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('probability_scale_columns', true, ['COMMENT' => '可能性量表欄位定義表 - 存儲量表的動態欄位設定']);

        // ==========================================
        // 可能性量表資料列表
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
                'comment'    => '量表ID,關聯probability_scales表',
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
                'comment'    => '分數級距 (固定欄位,用作下拉選單的value)',
            ],
            'dynamic_fields' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => '動態欄位資料,格式: {"column_id": "value"}',
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
        $this->forge->addForeignKey('scale_id', 'probability_scales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('probability_scale_rows', true, ['COMMENT' => '可能性量表資料列表 - 存儲量表的實際資料內容']);

        // ==========================================
        // 財務衝擊量表主表
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID',
            ],
            'template_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '範本ID,關聯risk_assessment_templates表',
            ],
            'selected_display_column' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '預設顯示欄位: impactLevel=財務衝擊程度, 或欄位ID數字',
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
        $this->forge->addKey('template_id');
        $this->forge->addForeignKey('template_id', 'risk_assessment_templates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('impact_scales', true, ['COMMENT' => '財務衝擊量表主表 - 存儲範本的財務衝擊量表基本設定']);

        // ==========================================
        // 財務衝擊量表欄位定義表
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
                'comment'    => '量表ID,關聯impact_scales表',
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
                'comment'    => '欄位名稱,如:股東權益金額、實際權益金額(分配後)',
            ],
            'amount_note' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '金額說明 (僅實際權益金額欄位使用,顯示為藍色提示)',
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
        $this->forge->addForeignKey('scale_id', 'impact_scales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('impact_scale_columns', true, ['COMMENT' => '財務衝擊量表欄位定義表 - 存儲量表的動態欄位設定']);

        // ==========================================
        // 財務衝擊量表資料列表
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
                'comment'    => '量表ID,關聯impact_scales表',
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
                'comment'    => '分數級距 (固定欄位,用作下拉選單的value)',
            ],
            'dynamic_fields' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => '動態欄位資料,格式: {"column_id": "value"}',
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
        $this->forge->addForeignKey('scale_id', 'impact_scales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('impact_scale_rows', true, ['COMMENT' => '財務衝擊量表資料列表 - 存儲量表的實際資料內容']);
    }

    public function down()
    {
        $this->forge->dropTable('probability_scale_rows', true);
        $this->forge->dropTable('probability_scale_columns', true);
        $this->forge->dropTable('probability_scales', true);
        $this->forge->dropTable('impact_scale_rows', true);
        $this->forge->dropTable('impact_scale_columns', true);
        $this->forge->dropTable('impact_scales', true);
    }
}