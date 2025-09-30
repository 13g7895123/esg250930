<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLocalCompaniesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('local_companies')) {
            return;
        }
        
        // Create local_companies table
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'auto_increment' => true,
                'unsigned' => true,
            ],
            'company_name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'comment' => '公司名稱',
            ],
            'external_id' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique' => true,
                'comment' => '外部API對應的公司ID',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '公司描述',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
                'comment' => '公司狀態',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('external_id');
        $this->forge->createTable('local_companies');

        // Insert some sample data
        $data = [
            [
                'id' => 1,
                'company_name' => '台積電股份有限公司',
                'external_id' => 'TSMC_001',
                'description' => '全球最大的晶圓代工廠',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'company_name' => '聯發科技股份有限公司',
                'external_id' => 'MTK_002',
                'description' => '全球知名IC設計公司',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 3,
                'company_name' => '鴻海精密工業股份有限公司',
                'external_id' => 'FOXCONN_003',
                'description' => '全球最大的電子製造服務商',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('local_companies')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('local_companies');
    }
}