<?php

namespace App\Database\migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * 創建題項管理獨立架構
 *
 * 此 Migration 為題項管理建立獨立的資料結構，包含：
 * - 題項風險分類表 (question_categories)
 * - 題項風險主題表 (question_topics)
 * - 題項風險因子表 (question_factors)
 * - 題項內容表 (question_contents)
 * - 題項回答表 (question_responses)
 *
 * 目的：讓題項管理與範本管理完全獨立運作，
 * 但可透過複製機制從範本載入初始架構
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class CreateQuestionManagementStructure extends Migration
{
    /**
     * 執行 Migration - 建立題項管理獨立架構表
     */
    public function up()
    {
        // =============================================
        // 1. 題項風險分類表
        // =============================================

        /**
         * question_categories - 題項風險分類表
         *
         * 為每個 company_assessment 建立獨立的風險分類系統
         * 與範本的 risk_categories 完全分離，但可從範本複製初始資料
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID'
            ],
            'assessment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => '關聯到 company_assessments 表的評估記錄ID'
            ],
            'category_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => '風險分類名稱，如：財務風險、營運風險等'
            ],
            'category_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '風險分類代碼，用於程式識別，如：FINANCIAL、OPERATIONAL'
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => '風險分類詳細描述'
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => '顯示排序，數字越小越靠前'
            ],
            'copied_from_template_category' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '複製來源：範本分類 ID，用於追蹤資料來源'
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
                'comment' => '建立時間'
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
                'comment' => '最後更新時間'
            ]
        ]);

        // 設定主鍵
        $this->forge->addKey('id', true);

        // 設定外鍵約束
        $this->forge->addForeignKey('assessment_id', 'company_assessments', 'id', 'CASCADE', 'CASCADE');

        // 建立索引提升查詢效能
        $this->forge->addKey(['assessment_id'], false, false, 'idx_question_categories_assessment_id');
        $this->forge->addKey(['sort_order'], false, false, 'idx_question_categories_sort_order');
        $this->forge->addKey(['category_code'], false, false, 'idx_question_categories_category_code');

        // 建立表格
        $this->forge->createTable('question_categories', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => '題項風險分類表 - 為題項管理建立獨立的風險分類系統'
        ]);

        // =============================================
        // 2. 題項風險主題表
        // =============================================

        /**
         * question_topics - 題項風險主題表
         *
         * 為每個評估建立風險主題層級，可歸屬於某個風險分類下
         * 提供更細緻的風險項目分組
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID'
            ],
            'assessment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => '關聯到 company_assessments 表的評估記錄ID'
            ],
            'category_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '所屬風險分類ID，可選，允許跨分類主題'
            ],
            'topic_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => '風險主題名稱，如：流動性管理、供應鏈風險等'
            ],
            'topic_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '風險主題代碼，用於程式識別'
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => '風險主題詳細描述'
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => '在所屬分類下的顯示排序'
            ],
            'copied_from_template_topic' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '複製來源：範本主題 ID，用於追蹤資料來源'
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
                'comment' => '建立時間'
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
                'comment' => '最後更新時間'
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('assessment_id', 'company_assessments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'question_categories', 'id', 'SET NULL', 'CASCADE');

        $this->forge->addKey(['assessment_id'], false, false, 'idx_question_topics_assessment_id');
        $this->forge->addKey(['category_id'], false, false, 'idx_question_topics_category_id');
        $this->forge->addKey(['sort_order'], false, false, 'idx_question_topics_sort_order');

        $this->forge->createTable('question_topics', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => '題項風險主題表 - 提供更細緻的風險項目分組'
        ]);

        // =============================================
        // 3. 題項風險因子表
        // =============================================

        /**
         * question_factors - 題項風險因子表
         *
         * 最細緻的風險分組層級，可歸屬於主題或分類
         * 提供最詳細的風險評估項目分類
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID'
            ],
            'assessment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => '關聯到 company_assessments 表的評估記錄ID'
            ],
            'topic_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '所屬風險主題ID，可選'
            ],
            'category_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '所屬風險分類ID，可選，用於直接歸類到分類下'
            ],
            'factor_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => '風險因子名稱，具體的風險項目'
            ],
            'factor_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '風險因子代碼，用於程式識別'
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => '風險因子詳細描述'
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => '在所屬主題或分類下的顯示排序'
            ],
            'copied_from_template_factor' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '複製來源：範本因子 ID，用於追蹤資料來源'
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
                'comment' => '建立時間'
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
                'comment' => '最後更新時間'
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('assessment_id', 'company_assessments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('topic_id', 'question_topics', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'question_categories', 'id', 'SET NULL', 'CASCADE');

        $this->forge->addKey(['assessment_id'], false, false, 'idx_question_factors_assessment_id');
        $this->forge->addKey(['topic_id'], false, false, 'idx_question_factors_topic_id');
        $this->forge->addKey(['category_id'], false, false, 'idx_question_factors_category_id');
        $this->forge->addKey(['sort_order'], false, false, 'idx_question_factors_sort_order');

        $this->forge->createTable('question_factors', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => '題項風險因子表 - 提供最詳細的風險評估項目分類'
        ]);

        // =============================================
        // 4. 題項內容表
        // =============================================

        /**
         * question_contents - 題項內容表
         *
         * 實際的評估題目內容，完全獨立於範本內容
         * 包含所有評估表單所需的欄位定義
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID'
            ],
            'assessment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => '關聯到 company_assessments 表的評估記錄ID'
            ],
            'category_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '所屬風險分類ID'
            ],
            'topic_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '所屬風險主題ID'
            ],
            'factor_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '所屬風險因子ID'
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => '題目標題，顯示在評估表單上的主要標題'
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => '題目詳細描述，提供評估者更多背景資訊'
            ],
            'assessment_criteria' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => '評估標準的JSON格式資料，定義評分規則'
            ],
            'scoring_method' => [
                'type'       => 'ENUM',
                'constraint' => ['binary', 'scale_1_5', 'scale_1_10', 'percentage'],
                'default'    => 'scale_1_5',
                'comment'    => '評分方法：binary(是否)、scale_1_5(1-5分)、scale_1_10(1-10分)、percentage(百分比)'
            ],
            'weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00,
                'comment'    => '題目權重，用於計算總分時的加權'
            ],
            'is_required' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
                'comment'    => '是否為必填題目'
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => '題目顯示排序'
            ],

            // 風險評估表單專用欄位（從 template_content 移植）
            'a_content' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'A欄位內容：通常用於風險描述或評估項目說明'
            ],
            'b_content' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'B欄位內容：通常用於風險影響說明或評估標準'
            ],
            'c_placeholder' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'C欄位佔位符：用於文字輸入欄位的提示文字'
            ],
            'd_placeholder_1' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'D欄位佔位符1：第一個輸入欄位的提示文字'
            ],
            'd_placeholder_2' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'D欄位佔位符2：第二個輸入欄位的提示文字'
            ],
            'e1_placeholder_1' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'E1欄位佔位符1：E1區塊第一個輸入欄位的提示文字'
            ],
            'e1_select_1' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'E1選項1：下拉選單或選項的第一個選擇'
            ],
            'e1_select_2' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'E1選項2：下拉選單或選項的第二個選擇'
            ],
            'e1_placeholder_2' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'E1欄位佔位符2：E1區塊第二個輸入欄位的提示文字'
            ],

            // 資訊圖示懸浮提示文字欄位
            'e1_info' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'E1資訊提示：E1欄位旁資訊圖示的懸浮說明文字'
            ],
            'f1_info' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'F1資訊提示：F1欄位旁資訊圖示的懸浮說明文字'
            ],
            'g1_info' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'G1資訊提示：G1欄位旁資訊圖示的懸浮說明文字'
            ],
            'h1_info' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'H1資訊提示：H1欄位旁資訊圖示的懸浮說明文字'
            ],

            'copied_from_template_content' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '複製來源：範本內容 ID，用於追蹤資料來源'
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
                'comment' => '建立時間'
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
                'comment' => '最後更新時間'
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('assessment_id', 'company_assessments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'question_categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('topic_id', 'question_topics', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('factor_id', 'question_factors', 'id', 'SET NULL', 'CASCADE');

        $this->forge->addKey(['assessment_id'], false, false, 'idx_question_contents_assessment_id');
        $this->forge->addKey(['category_id'], false, false, 'idx_question_contents_category_id');
        $this->forge->addKey(['topic_id'], false, false, 'idx_question_contents_topic_id');
        $this->forge->addKey(['factor_id'], false, false, 'idx_question_contents_factor_id');
        $this->forge->addKey(['sort_order'], false, false, 'idx_question_contents_sort_order');

        $this->forge->createTable('question_contents', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => '題項內容表 - 實際的評估題目內容，完全獨立於範本'
        ]);

        // =============================================
        // 5. 題項回答表
        // =============================================

        /**
         * question_responses - 題項回答表
         *
         * 儲存對題項內容的具體回答，替代原本的 assessment_responses
         * 提供完整的回答追蹤和審核機制
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '主鍵ID'
            ],
            'assessment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => '關聯到 company_assessments 表的評估記錄ID'
            ],
            'question_content_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'comment'    => '對應的題項內容ID，關聯到 question_contents 表'
            ],
            'response_value' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => '回答值的JSON格式資料，根據評分方法儲存不同格式的答案'
            ],
            'score' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => true,
                'comment'    => '根據回答計算出的得分'
            ],
            'notes' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => '回答的備註說明，提供額外的背景資訊'
            ],
            'evidence_files' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => '佐證文件的檔案路徑陣列，以JSON格式儲存'
            ],
            'answered_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'comment' => '回答時間，記錄何時完成此題目'
            ],
            'answered_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '回答人員ID，未來可關聯到使用者表'
            ],
            'review_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'pending',
                'comment'    => '審核狀態：pending(待審核)、approved(已核准)、rejected(已駁回)'
            ],
            'reviewed_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '審核人員ID，未來可關聯到使用者表'
            ],
            'reviewed_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'comment' => '審核時間，記錄何時完成審核'
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
                'comment' => '建立時間'
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
                'comment' => '最後更新時間'
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('assessment_id', 'company_assessments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('question_content_id', 'question_contents', 'id', 'CASCADE', 'CASCADE');

        // 確保每個評估的每個題目只能有一個回答
        $this->forge->addUniqueKey(['assessment_id', 'question_content_id'], 'uk_assessment_question');

        $this->forge->addKey(['assessment_id'], false, false, 'idx_question_responses_assessment_id');
        $this->forge->addKey(['question_content_id'], false, false, 'idx_question_responses_question_content_id');
        $this->forge->addKey(['review_status'], false, false, 'idx_question_responses_review_status');
        $this->forge->addKey(['answered_by'], false, false, 'idx_question_responses_answered_by');
        $this->forge->addKey(['reviewed_by'], false, false, 'idx_question_responses_reviewed_by');

        $this->forge->createTable('question_responses', true, [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => '題項回答表 - 儲存對題項內容的具體回答和審核狀態'
        ]);

        // =============================================
        // 6. 記錄操作日誌
        // =============================================

        // 檢查 activity_logs 表是否存在，如果存在則記錄此次架構變更
        if ($this->db->tableExists('activity_logs')) {
            $this->db->query("
                INSERT INTO activity_logs (action, resource_type, description, created_at)
                VALUES (
                    'SCHEMA_MIGRATION',
                    'DATABASE',
                    '建立題項管理獨立架構 - 新增 question_categories, question_topics, question_factors, question_contents, question_responses 表',
                    NOW()
                )
            ");
        }
    }

    /**
     * 回滾 Migration - 移除題項管理獨立架構表
     *
     * 注意：由於有外鍵約束，需要按照依賴關係倒序刪除
     */
    public function down()
    {
        // 按照依賴關係倒序刪除表格

        // 5. 先刪除 question_responses（依賴 question_contents）
        $this->forge->dropTable('question_responses', true);

        // 4. 刪除 question_contents（依賴 question_categories, question_topics, question_factors）
        $this->forge->dropTable('question_contents', true);

        // 3. 刪除 question_factors（依賴 question_topics, question_categories）
        $this->forge->dropTable('question_factors', true);

        // 2. 刪除 question_topics（依賴 question_categories）
        $this->forge->dropTable('question_topics', true);

        // 1. 最後刪除 question_categories（被其他表依賴）
        $this->forge->dropTable('question_categories', true);

        // 記錄回滾操作
        if ($this->db->tableExists('activity_logs')) {
            $this->db->query("
                INSERT INTO activity_logs (action, resource_type, description, created_at)
                VALUES (
                    'SCHEMA_ROLLBACK',
                    'DATABASE',
                    '回滾題項管理獨立架構 - 移除 question_categories, question_topics, question_factors, question_contents, question_responses 表',
                    NOW()
                )
            ");
        }
    }
}