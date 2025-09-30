<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ResetRiskFactorsWithProperData extends Migration
{
    public function up()
    {
        log_message('info', 'Starting reset of risk_factors table with proper risk factor data');

        if ($this->db->tableExists('risk_factors')) {
            // 暫時禁用外鍵檢查
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

            // 清空 risk_factors 表中的錯誤資料
            $this->db->table('risk_factors')->truncate();
            log_message('info', 'Cleared existing incorrect data from risk_factors table');

            // 重新啟用外鍵檢查
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

            // 重新填充正確的風險因子資料
            $this->insertProperRiskFactors();
            log_message('info', 'Inserted proper risk factor data');
        }
    }

    public function down()
    {
        // 回滾時清空風險因子資料
        if ($this->db->tableExists('risk_factors')) {
            $this->db->table('risk_factors')->truncate();
            log_message('info', 'Risk factors data cleared during rollback');
        }
    }

    private function insertProperRiskFactors()
    {
        // 查詢現有的範本、分類和主題ID
        $templateId = $this->db->table('risk_assessment_templates')->select('id')->limit(1)->get()->getRow()->id ?? null;
        $categoryId = $this->db->table('risk_categories')->select('id')->limit(1)->get()->getRow()->id ?? null;
        $topicId = $this->db->table('risk_topics')->select('id')->limit(1)->get()->getRow()->id ?? null;

        if (!$templateId || !$categoryId || !$topicId) {
            log_message('warning', 'Missing required reference data for risk factors. Skipping insertion.');
            return;
        }

        // 定義常見的風險因子
        $riskFactors = [
            // 財務風險因子
            [
                'factor_name' => '現金流預測能力',
                'factor_code' => 'CF_FORECAST',
                'description' => '企業對未來現金流入和流出的預測準確性和管理能力',
                'category' => '財務風險',
                'topic' => '現金流管理'
            ],
            [
                'factor_name' => '負債管理能力',
                'factor_code' => 'DEBT_MGMT',
                'description' => '企業對債務結構和償債能力的管理水準',
                'category' => '財務風險',
                'topic' => '資本結構管理'
            ],
            [
                'factor_name' => '投資決策品質',
                'factor_code' => 'INV_QUALITY',
                'description' => '企業投資決策的合理性和回報率',
                'category' => '財務風險',
                'topic' => '投資管理'
            ],

            // 營運風險因子
            [
                'factor_name' => '供應商集中度風險',
                'factor_code' => 'SUPPLIER_CONC',
                'description' => '對少數關鍵供應商的依賴程度及相關風險',
                'category' => '營運風險',
                'topic' => '供應鏈管理'
            ],
            [
                'factor_name' => '產品品質控制',
                'factor_code' => 'QUALITY_CTRL',
                'description' => '產品或服務品質的穩定性和控制能力',
                'category' => '營運風險',
                'topic' => '品質管理'
            ],
            [
                'factor_name' => '資訊系統安全',
                'factor_code' => 'IT_SECURITY',
                'description' => '資訊系統的安全防護和資料保護能力',
                'category' => '營運風險',
                'topic' => '資訊科技管理'
            ],

            // 市場風險因子
            [
                'factor_name' => '客戶集中度風險',
                'factor_code' => 'CUSTOMER_CONC',
                'description' => '對少數大客戶的依賴程度及相關風險',
                'category' => '市場風險',
                'topic' => '客戶關係管理'
            ],
            [
                'factor_name' => '市場競爭強度',
                'factor_code' => 'MARKET_COMP',
                'description' => '所處市場的競爭激烈程度和競爭優勢',
                'category' => '市場風險',
                'topic' => '競爭分析'
            ],
            [
                'factor_name' => '產品生命週期',
                'factor_code' => 'PRODUCT_LIFECYCLE',
                'description' => '主要產品或服務的生命週期階段和更新能力',
                'category' => '市場風險',
                'topic' => '產品管理'
            ],

            // 法規風險因子
            [
                'factor_name' => '法規遵循程度',
                'factor_code' => 'COMPLIANCE_LEVEL',
                'description' => '對相關法規的遵循程度和合規管理能力',
                'category' => '法規風險',
                'topic' => '法規遵循'
            ],
            [
                'factor_name' => '政策變更適應性',
                'factor_code' => 'POLICY_ADAPT',
                'description' => '對政策變更的適應能力和應對策略',
                'category' => '法規風險',
                'topic' => '政策風險管理'
            ],

            // ESG風險因子
            [
                'factor_name' => '環境影響管理',
                'factor_code' => 'ENV_IMPACT',
                'description' => '對環境影響的管理和減緩措施',
                'category' => 'ESG風險',
                'topic' => '環境管理'
            ],
            [
                'factor_name' => '社會責任履行',
                'factor_code' => 'SOCIAL_RESP',
                'description' => '企業社會責任的履行程度和利害關係人關係',
                'category' => 'ESG風險',
                'topic' => '社會責任'
            ],
            [
                'factor_name' => '公司治理品質',
                'factor_code' => 'GOVERNANCE_QUALITY',
                'description' => '公司治理結構的完善程度和透明度',
                'category' => 'ESG風險',
                'topic' => '公司治理'
            ]
        ];

        // 插入風險因子資料（僅作為範例，實際應用時會關聯到具體的分類和主題）
        foreach ($riskFactors as $index => $factor) {
            $this->db->table('risk_factors')->insert([
                'template_id' => $templateId,
                'category_id' => $categoryId,
                'topic_id' => $topicId,
                'factor_name' => $factor['factor_name'],
                'factor_code' => $factor['factor_code'],
                'description' => $factor['description'],
                'scoring_method' => 'scale_1_5',
                'weight' => 1.00,
                'sort_order' => $index + 1,
                'is_required' => true,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}