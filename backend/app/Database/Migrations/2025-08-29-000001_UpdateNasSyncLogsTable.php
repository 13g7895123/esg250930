<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateNasSyncLogsTable extends Migration
{
    public function up()
    {
        // Drop the existing table and recreate with new structure
        $this->forge->dropTable('nas_sync_logs', true);
        
        // Create the new table structure
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
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
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'downloaded', 'uploaded', 'failed'],
                'default' => 'pending',
            ],
            'local_file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'upload_response' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('file_path');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('nas_sync_logs');
    }

    public function down()
    {
        // Restore the original table structure
        $this->forge->dropTable('nas_sync_logs', true);
        
        // Recreate the original structure
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sync_type' => [
                'type' => 'ENUM',
                'constraint' => ['full', 'folder', 'test'],
                'default' => 'full',
            ],
            'folder_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'folder_path' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'total_size' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'default' => 0,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'running', 'completed', 'failed'],
                'default' => 'pending',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'external_api_response' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'sync_started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'sync_completed_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('sync_type');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('nas_sync_logs');
    }
}