<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyRiskFactorsDescriptionToNullable extends Migration
{
    public function up()
    {
        log_message('info', 'Starting modification of description field in risk_factors table to allow NULL');

        // 檢查表格是否存在
        if (!$this->db->tableExists('risk_factors')) {
            log_message('error', 'risk_factors table does not exist');
            return;
        }

        // 修改 description 欄位允許 NULL
        if ($this->db->fieldExists('description', 'risk_factors')) {
            try {
                // 先更新所有空字串為 NULL (如果有的話)
                $this->db->query("UPDATE risk_factors SET description = NULL WHERE description = ''");

                $fields = [
                    'description' => [
                        'type' => 'TEXT',
                        'null' => true,
                        'comment' => '因子詳細說明 (選填)',
                    ]
                ];

                $this->forge->modifyColumn('risk_factors', $fields);
                log_message('info', 'Modified description field in risk_factors table to allow NULL');

                // 驗證修改是否成功
                $fieldData = $this->db->getFieldData('risk_factors');
                $descriptionField = null;
                foreach ($fieldData as $field) {
                    if ($field->name === 'description') {
                        $descriptionField = $field;
                        break;
                    }
                }

                if ($descriptionField && $descriptionField->nullable) {
                    log_message('info', 'Verification: description field is now nullable');
                } else {
                    log_message('warning', 'Verification: description field modification may have failed');
                }

            } catch (Exception $e) {
                log_message('error', 'Failed to modify description field: ' . $e->getMessage());
                throw $e;
            }
        } else {
            log_message('info', 'description field does not exist in risk_factors table');
        }

        log_message('info', 'Completed modification of description field in risk_factors table');
    }

    public function down()
    {
        log_message('info', 'Starting rollback - making description field NOT NULL in risk_factors table');

        // 回滾時讓 description 欄位不允許 NULL（但需要先設定預設值）
        if ($this->db->fieldExists('description', 'risk_factors')) {
            // 先更新所有 NULL 值為空字串
            $this->db->query("UPDATE risk_factors SET description = '' WHERE description IS NULL");

            $fields = [
                'description' => [
                    'type' => 'TEXT',
                    'null' => false,
                    'comment' => '因子詳細說明',
                ]
            ];

            $this->forge->modifyColumn('risk_factors', $fields);
            log_message('info', 'Reverted description field in risk_factors table to NOT NULL');
        }

        log_message('info', 'Completed rollback of description field modification');
    }
}