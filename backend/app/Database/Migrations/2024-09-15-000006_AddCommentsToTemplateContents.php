<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCommentsToTemplateContents extends Migration
{
    public function up()
    {
        // Add table comment
        $this->db->query("ALTER TABLE template_contents COMMENT='範本內容表 - 存儲風險評估範本的具體評估項目'");
        
        // Add missing field comments
        $this->db->query("ALTER TABLE template_contents MODIFY COLUMN id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '內容ID（主鍵）'");
        $this->db->query("ALTER TABLE template_contents MODIFY COLUMN created_at DATETIME NOT NULL COMMENT '建立時間'");
        $this->db->query("ALTER TABLE template_contents MODIFY COLUMN updated_at DATETIME NOT NULL COMMENT '更新時間'");
    }

    public function down()
    {
        // Remove table comment
        $this->db->query("ALTER TABLE template_contents COMMENT=''");
        
        // Remove field comments (reset to original structure)
        $this->db->query("ALTER TABLE template_contents MODIFY COLUMN id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
        $this->db->query("ALTER TABLE template_contents MODIFY COLUMN created_at DATETIME NOT NULL");
        $this->db->query("ALTER TABLE template_contents MODIFY COLUMN updated_at DATETIME NOT NULL");
    }
}