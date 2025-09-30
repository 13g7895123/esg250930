<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCommentsToCompanies extends Migration
{
    public function up()
    {
        // Add table comment
        $this->db->query("ALTER TABLE companies COMMENT='公司基本資料表 - 存儲公司的基本聯絡資訊'");
        
        // Add field comments
        $this->db->query("ALTER TABLE companies MODIFY COLUMN id INT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '公司ID（主鍵）'");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN name VARCHAR(255) NOT NULL COMMENT '公司名稱'");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN email VARCHAR(255) NOT NULL COMMENT '公司電子郵件'");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN phone VARCHAR(50) NULL COMMENT '公司電話'");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN address TEXT NULL COMMENT '公司地址'");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN created_at DATETIME NOT NULL COMMENT '建立時間'");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN updated_at DATETIME NOT NULL COMMENT '更新時間'");
    }

    public function down()
    {
        // Remove table comment
        $this->db->query("ALTER TABLE companies COMMENT=''");
        
        // Remove field comments (reset to original structure)
        $this->db->query("ALTER TABLE companies MODIFY COLUMN id INT(5) UNSIGNED NOT NULL AUTO_INCREMENT");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN name VARCHAR(255) NOT NULL");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN email VARCHAR(255) NOT NULL");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN phone VARCHAR(50) NULL");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN address TEXT NULL");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN created_at DATETIME NOT NULL");
        $this->db->query("ALTER TABLE companies MODIFY COLUMN updated_at DATETIME NOT NULL");
    }
}