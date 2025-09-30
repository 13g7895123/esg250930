<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUnnecessaryFieldsFromRiskFactors extends Migration
{
    public function up()
    {
        log_message('info', 'Starting removal of unnecessary fields from risk_factors table');

        // 移除不必要的欄位
        $fieldsToRemove = [
            'factor_code',
            'scoring_method',
            'weight',
            'sort_order',
            'is_required'
        ];

        foreach ($fieldsToRemove as $field) {
            if ($this->db->fieldExists($field, 'risk_factors')) {
                $this->forge->dropColumn('risk_factors', $field);
                log_message('info', "Removed field: {$field} from risk_factors table");
            }
        }

        log_message('info', 'Completed removal of unnecessary fields from risk_factors table');
    }

    public function down()
    {
        log_message('info', 'Starting rollback - adding back removed fields to risk_factors table');

        // 回滾時重新添加這些欄位
        $fields = [
            'factor_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => '因子代碼',
                'after'      => 'factor_name'
            ],
            'scoring_method' => [
                'type' => 'ENUM',
                'constraint' => ['binary', 'scale_1_5', 'scale_1_10', 'percentage'],
                'default' => 'scale_1_5',
                'null' => false,
                'comment' => '評分方式',
                'after' => 'description'
            ],
            'weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00,
                'null'       => false,
                'comment'    => '權重',
                'after'      => 'scoring_method'
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'comment'    => '排序',
                'after'      => 'weight'
            ],
            'is_required' => [
                'type'    => 'BOOLEAN',
                'default' => true,
                'null'    => false,
                'comment' => '是否必填',
                'after'   => 'sort_order'
            ]
        ];

        $this->forge->addColumn('risk_factors', $fields);

        log_message('info', 'Completed rollback - added back removed fields to risk_factors table');
    }
}