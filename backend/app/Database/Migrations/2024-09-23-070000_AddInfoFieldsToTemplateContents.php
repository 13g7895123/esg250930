<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInfoFieldsToTemplateContents extends Migration
{
    public function up()
    {
        $fields = [
            'e1_info' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'E-1 相關風險資訊圖示提示文字'
            ],
            'f1_info' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'F-1 相關機會資訊圖示提示文字',
                'after' => 'e1_info'
            ],
            'g1_info' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'G-1 對外負面衝擊資訊圖示提示文字',
                'after' => 'f1_info'
            ],
            'h1_info' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'H-1 對外正面影響資訊圖示提示文字',
                'after' => 'g1_info'
            ]
        ];

        $this->forge->addColumn('template_contents', $fields);

        echo "已為 template_contents 表添加資訊圖示提示文字欄位：e1_info、f1_info、g1_info、h1_info\n";
    }

    public function down()
    {
        $columns = [
            'e1_info',
            'f1_info',
            'g1_info',
            'h1_info'
        ];

        $this->forge->dropColumn('template_contents', $columns);

        echo "已從 template_contents 表移除資訊圖示提示文字欄位\n";
    }
}