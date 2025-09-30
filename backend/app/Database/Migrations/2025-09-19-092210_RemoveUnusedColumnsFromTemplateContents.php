<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUnusedColumnsFromTemplateContents extends Migration
{
    public function up()
    {
        // 移除不需要的欄位：scoring_method 和 weight
        $this->forge->dropColumn('template_contents', ['scoring_method', 'weight']);

        echo "已從 template_contents 表移除 scoring_method 和 weight 欄位\n";
    }

    public function down()
    {
        // 恢復移除的欄位以便回滾
        $fields = [
            'scoring_method' => [
                'type' => 'ENUM',
                'constraint' => ['binary', 'scale_1_5', 'scale_1_10', 'percentage'],
                'default' => 'scale_1_5',
                'null' => false,
                'after' => 'description'
            ],
            'weight' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 1.00,
                'null' => false,
                'after' => 'scoring_method'
            ]
        ];

        $this->forge->addColumn('template_contents', $fields);

        echo "已恢復 template_contents 表的 scoring_method 和 weight 欄位\n";
    }
}
