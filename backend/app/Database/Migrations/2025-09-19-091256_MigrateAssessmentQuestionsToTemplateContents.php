<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateAssessmentQuestionsToTemplateContents extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 啟動事務
        $db->transStart();

        try {
            // 首先清空 template_contents 表以避免重複資料
            $db->table('template_contents')->truncate();

            // 從 assessment_questions 表取得所有資料
            $questions = $db->table('assessment_questions')
                ->select('
                    template_id,
                    category_id,
                    question_title,
                    question_description,
                    sort_order,
                    created_at,
                    updated_at
                ')
                ->where('status', 'active') // 只遷移活躍的資料
                ->get()
                ->getResultArray();

            // 將資料插入到 template_contents 表
            foreach ($questions as $question) {
                $contentData = [
                    'template_id' => $question['template_id'],
                    'category_id' => $question['category_id'],
                    'topic' => $question['question_title'] ?: '未命名項目',
                    'description' => $question['question_description'] ?: '',
                    'sort_order' => $question['sort_order'] ?: 0,
                    'is_required' => 1, // 預設為必填
                    'created_at' => $question['created_at'],
                    'updated_at' => $question['updated_at']
                ];

                $db->table('template_contents')->insert($contentData);
            }

            // 完成事務
            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('資料遷移失敗');
            }

            // 備份並清空 assessment_questions 表
            // 將表重新命名為備份表
            $db->query('RENAME TABLE assessment_questions TO assessment_questions_backup_' . date('Y_m_d_H_i_s'));

            echo "資料遷移成功：已將 " . count($questions) . " 筆資料從 assessment_questions 遷移到 template_contents\n";
            echo "原始表已重新命名為備份表\n";

        } catch (\Exception $e) {
            $db->transRollback();
            throw new \Exception('資料遷移失敗：' . $e->getMessage());
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        // 啟動事務
        $db->transStart();

        try {
            // 尋找最新的備份表
            $tables = $db->listTables();
            $backupTable = null;

            foreach ($tables as $table) {
                if (strpos($table, 'assessment_questions_backup_') === 0) {
                    $backupTable = $table;
                    break;
                }
            }

            if (!$backupTable) {
                throw new \Exception('找不到 assessment_questions 備份表');
            }

            // 恢復原始表名
            $db->query("RENAME TABLE {$backupTable} TO assessment_questions");

            // 清空 template_contents 表
            $db->table('template_contents')->truncate();

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('回滾失敗');
            }

            echo "成功回滾：已恢復 assessment_questions 表並清空 template_contents 表\n";

        } catch (\Exception $e) {
            $db->transRollback();
            throw new \Exception('回滾失敗：' . $e->getMessage());
        }
    }
}
