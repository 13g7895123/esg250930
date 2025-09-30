<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCommentsToRiskAssessmentTemplates extends Migration
{
    public function up()
    {
        // Add table comment
        $this->db->query("ALTER TABLE risk_assessment_templates COMMENT='風險評估範本表 - 存儲風險評估的版本範本資訊'");
        
        // Add missing field comments
        $this->db->query("ALTER TABLE risk_assessment_templates MODIFY COLUMN id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '範本ID（主鍵）'");
        $this->db->query("ALTER TABLE risk_assessment_templates MODIFY COLUMN created_at DATETIME NOT NULL COMMENT '建立時間'");
        $this->db->query("ALTER TABLE risk_assessment_templates MODIFY COLUMN updated_at DATETIME NOT NULL COMMENT '更新時間'");
    }

    public function down()
    {
        // Remove table comment
        $this->db->query("ALTER TABLE risk_assessment_templates COMMENT=''");
        
        // Remove field comments (reset to original structure)
        $this->db->query("ALTER TABLE risk_assessment_templates MODIFY COLUMN id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
        $this->db->query("ALTER TABLE risk_assessment_templates MODIFY COLUMN created_at DATETIME NOT NULL");
        $this->db->query("ALTER TABLE risk_assessment_templates MODIFY COLUMN updated_at DATETIME NOT NULL");
    }
}