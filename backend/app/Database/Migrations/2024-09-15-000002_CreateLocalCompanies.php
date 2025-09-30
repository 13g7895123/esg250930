<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLocalCompanies extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('local_companies')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '本地公司記錄ID',
            ],
            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => '公司名稱（來自外部API的com_title）',
            ],
            'external_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => false,
                'comment'    => '外部API的公司ID（com_id）',
            ],
            'abbreviation' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => '公司簡稱或縮寫',
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
        $this->forge->addKey('company_name');
        $this->forge->addKey('created_at');
        
        // Add unique constraint on external_id to prevent duplicates
        $this->forge->addUniqueKey('external_id');
        
        $this->forge->createTable('local_companies', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => '本地公司管理表 - 存儲從外部API選擇的公司資料，取代前端localStorage存儲'
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('local_companies');
    }
}