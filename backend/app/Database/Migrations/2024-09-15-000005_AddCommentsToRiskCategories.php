<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCommentsToRiskCategories extends Migration
{
    public function up()
    {
        // Add table comment
        $this->db->query("ALTER TABLE risk_categories COMMENT='風險分類表 - 存儲風險評估的分類資訊'");
        
        // Add missing field comments
        $this->db->query("ALTER TABLE risk_categories MODIFY COLUMN id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分類ID（主鍵）'");
        $this->db->query("ALTER TABLE risk_categories MODIFY COLUMN created_at DATETIME NOT NULL COMMENT '建立時間'");
        $this->db->query("ALTER TABLE risk_categories MODIFY COLUMN updated_at DATETIME NOT NULL COMMENT '更新時間'");
    }

    public function down()
    {
        // Remove table comment
        $this->db->query("ALTER TABLE risk_categories COMMENT=''");
        
        // Remove field comments (reset to original structure)
        $this->db->query("ALTER TABLE risk_categories MODIFY COLUMN id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
        $this->db->query("ALTER TABLE risk_categories MODIFY COLUMN created_at DATETIME NOT NULL");
        $this->db->query("ALTER TABLE risk_categories MODIFY COLUMN updated_at DATETIME NOT NULL");
    }
}