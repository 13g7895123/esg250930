<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddE2FieldsToTemplateContents extends Migration
{
    public function up()
    {
        // Check if columns already exist before adding
        $db = \Config\Database::connect();
        $existingFields = $db->getFieldNames('template_contents');

        // Add new E-2 fields
        $fields = [];

        if (!in_array('e2_select_1', $existingFields)) {
            $fields['e2_select_1'] = [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => 'E-2風險發生可能性 (Section E2 Select 1)',
                'after' => 'e1_placeholder_2'
            ];
        }

        if (!in_array('e2_select_2', $existingFields)) {
            $fields['e2_select_2'] = [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => 'E-2風險發生衝擊程度 (Section E2 Select 2)',
                'after' => 'e2_select_1'
            ];
        }

        if (!in_array('e2_placeholder', $existingFields)) {
            $fields['e2_placeholder'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'E-2計算說明 (Section E2 Placeholder)',
                'after' => 'e2_select_2'
            ];
        }

        if (!in_array('f2_select_1', $existingFields)) {
            $fields['f2_select_1'] = [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => 'F-2機會發生可能性 (Section F2 Select 1)',
                'after' => 'e2_placeholder'
            ];
        }

        if (!in_array('f2_select_2', $existingFields)) {
            $fields['f2_select_2'] = [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => 'F-2機會發生衝擊程度 (Section F2 Select 2)',
                'after' => 'f2_select_1'
            ];
        }

        if (!in_array('f2_placeholder', $existingFields)) {
            $fields['f2_placeholder'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'F-2計算說明 (Section F2 Placeholder)',
                'after' => 'f2_select_2'
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('template_contents', $fields);
            echo "✅ 已為 template_contents 表添加欄位: " . implode(', ', array_keys($fields)) . "\n";
        } else {
            echo "ℹ️  template_contents 表的所有 E2/F2 欄位已存在，跳過添加\n";
        }

        // Migrate existing data from e1_select_1/2 and e1_placeholder_2 to new e2 fields
        // Copy data to new fields
        $db->query("
            UPDATE template_contents
            SET
                e2_select_1 = e1_select_1,
                e2_select_2 = e1_select_2,
                e2_placeholder = e1_placeholder_2
            WHERE e1_select_1 IS NOT NULL
               OR e1_select_2 IS NOT NULL
               OR e1_placeholder_2 IS NOT NULL
        ");

        // Clear old fields (optional - keep for backward compatibility)
        // $db->query("UPDATE template_contents SET e1_select_1 = NULL, e1_select_2 = NULL, e1_placeholder_2 = NULL");
    }

    public function down()
    {
        $columns = [
            'e2_select_1',
            'e2_select_2',
            'e2_placeholder',
            'f2_select_1',
            'f2_select_2',
            'f2_placeholder'
        ];

        $this->forge->dropColumn('template_contents', $columns);

        echo "已從 template_contents 表移除 E-2 和 F-2 欄位\n";
    }
}
