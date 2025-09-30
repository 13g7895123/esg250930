<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateQuestionResponsesWithSeparateFields extends Migration
{
    public function up()
    {
        // 新增個別欄位來儲存各項答案
        $fields = [
            // C區域 - 公司報導年度是否有發生實際風險/負面衝擊事件
            'c_risk_event_choice' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'comment' => 'C區域-是否有風險事件(yes/no)'
            ],
            'c_risk_event_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'C區域-風險事件描述'
            ],

            // D區域 - 公司報導年度是否有相關對應作為
            'd_counter_action_choice' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'comment' => 'D區域-是否有對應作為(yes/no)'
            ],
            'd_counter_action_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'D區域-對應作為描述'
            ],
            'd_counter_action_cost' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'D區域-對策費用'
            ],

            // E-1區域 - 相關風險
            'e1_risk_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'E1區域-風險描述'
            ],
            'e1_risk_probability' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'E1區域-風險發生可能性'
            ],
            'e1_risk_impact' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'E1區域-風險發生衝擊程度'
            ],
            'e1_risk_calculation' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'E1區域-風險計算說明'
            ],

            // F-1區域 - 相關機會
            'f1_opportunity_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'F1區域-機會描述'
            ],
            'f1_opportunity_probability' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'F1區域-機會發生可能性'
            ],
            'f1_opportunity_impact' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'F1區域-機會發生衝擊程度'
            ],
            'f1_opportunity_calculation' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'F1區域-機會計算說明'
            ],

            // G-1區域 - 對外負面衝擊
            'g1_negative_impact_level' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'G1區域-負面衝擊程度'
            ],
            'g1_negative_impact_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'G1區域-負面衝擊評分說明'
            ],

            // H-1區域 - 對外正面影響
            'h1_positive_impact_level' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'H1區域-正面影響程度'
            ],
            'h1_positive_impact_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'H1區域-正面影響評分說明'
            ]
        ];

        $this->forge->addColumn('question_responses', $fields);

        // 建立索引以提升查詢效能
        $this->forge->addKey('c_risk_event_choice');
        $this->forge->addKey('d_counter_action_choice');
        $this->forge->addKey('e1_risk_probability');
        $this->forge->addKey('e1_risk_impact');
        $this->forge->addKey('f1_opportunity_probability');
        $this->forge->addKey('f1_opportunity_impact');
        $this->forge->addKey('g1_negative_impact_level');
        $this->forge->addKey('h1_positive_impact_level');
    }

    public function down()
    {
        // 移除新增的欄位
        $fields = [
            'c_risk_event_choice',
            'c_risk_event_description',
            'd_counter_action_choice',
            'd_counter_action_description',
            'd_counter_action_cost',
            'e1_risk_description',
            'e1_risk_probability',
            'e1_risk_impact',
            'e1_risk_calculation',
            'f1_opportunity_description',
            'f1_opportunity_probability',
            'f1_opportunity_impact',
            'f1_opportunity_calculation',
            'g1_negative_impact_level',
            'g1_negative_impact_description',
            'h1_positive_impact_level',
            'h1_positive_impact_description'
        ];

        foreach ($fields as $field) {
            if ($this->db->fieldExists($field, 'question_responses')) {
                $this->forge->dropColumn('question_responses', $field);
            }
        }
    }
}