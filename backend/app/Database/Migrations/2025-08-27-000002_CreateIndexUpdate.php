<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIndexUpdate extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'file_path' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'industry' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'indicator' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'company' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'year' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'file_size' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'file_modified_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'processing_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'processed', 'failed', 'skipped'],
                'default' => 'pending',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'external_upload_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'uploaded', 'failed'],
                'default' => 'pending',
            ],
            'external_response' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'sync_batch_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('industry');
        $this->forge->addKey('indicator');
        $this->forge->addKey('company');
        $this->forge->addKey('year');
        $this->forge->addKey('processing_status');
        $this->forge->addKey('external_upload_status');
        $this->forge->addKey('sync_batch_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('index_update');
    }

    public function down()
    {
        $this->forge->dropTable('index_update');
    }
}