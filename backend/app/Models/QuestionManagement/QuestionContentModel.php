<?php

namespace App\Models\QuestionManagement;

use CodeIgniter\Model;

/**
 * 題項內容模型
 *
 * 管理題項管理中的實際評估題目內容
 * 完全獨立於範本內容，但可從範本複製初始資料
 * 包含所有評估表單所需的欄位定義和配置
 *
 * @package App\Models\QuestionManagement
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class QuestionContentModel extends Model
{
    /**
     * 資料表名稱
     */
    protected $table = 'question_contents';

    /**
     * 主鍵欄位
     */
    protected $primaryKey = 'id';

    /**
     * 使用自動遞增主鍵
     */
    protected $useAutoIncrement = true;

    /**
     * 回傳類型為陣列
     */
    protected $returnType = 'array';

    /**
     * 不使用軟刪除（直接刪除）
     */
    protected $useSoftDeletes = false;

    /**
     * 保護欄位，只允許指定欄位被批量賦值
     */
    protected $protectFields = true;

    /**
     * 允許批量賦值的欄位
     */
    protected $allowedFields = [
        'assessment_id',
        'category_id',
        'topic_id',
        'factor_id',
        'title',
        'description',
        'assessment_criteria',
        'scoring_method',
        'weight',
        'is_required',
        'sort_order',
        // 'a_content', // REMOVED: Now using question_factors.description instead
        'b_content',
        'c_placeholder',
        'd_placeholder_1',
        'd_placeholder_2',
        'e1_placeholder_1',
        'e2_select_1',
        'e2_select_2',
        'e2_placeholder',
        'f1_placeholder_1',
        'f2_select_1',
        'f2_select_2',
        'f2_placeholder',
        'g1_placeholder_1',
        'h1_placeholder_1',
        'e1_info',
        'f1_info',
        'g1_info',
        'h1_info',
        'copied_from_template_content'
    ];

    /**
     * 啟用時間戳記自動管理
     */
    protected $useTimestamps = true;

    /**
     * 建立時間欄位名稱
     */
    protected $createdField = 'created_at';

    /**
     * 更新時間欄位名稱
     */
    protected $updatedField = 'updated_at';

    /**
     * 驗證規則
     */
    protected $validationRules = [
        'assessment_id' => 'required|integer|is_not_unique[company_assessments.id]',
        'category_id' => 'permit_empty|integer|is_not_unique[question_categories.id]',
        'topic_id' => 'permit_empty|integer|is_not_unique[question_topics.id]',
        'factor_id' => 'permit_empty|integer|is_not_unique[question_factors.id]',
        'description' => 'permit_empty',
        'is_required' => 'permit_empty|in_list[0,1]',
        'sort_order' => 'permit_empty|integer',
        'copied_from_template_content' => 'permit_empty|integer'
    ];

    /**
     * 驗證錯誤訊息
     */
    protected $validationMessages = [
        'assessment_id' => [
            'required' => '評估記錄ID為必填項目',
            'integer' => '評估記錄ID必須為整數',
            'is_not_unique' => '指定的評估記錄不存在'
        ],
        'category_id' => [
            'integer' => '分類ID必須為整數',
            'is_not_unique' => '指定的風險分類不存在'
        ],
        'topic_id' => [
            'integer' => '主題ID必須為整數',
            'is_not_unique' => '指定的風險主題不存在'
        ],
        'factor_id' => [
            'integer' => '因子ID必須為整數',
            'is_not_unique' => '指定的風險因子不存在'
        ],
        'is_required' => [
            'in_list' => '是否必填必須為0或1'
        ],
        'sort_order' => [
            'integer' => '排序必須為整數'
        ],
        'copied_from_template_content' => [
            'integer' => '範本內容ID必須為整數'
        ]
    ];

    /**
     * 不跳過驗證
     */
    protected $skipValidation = false;

    /**
     * 清理驗證規則
     */
    protected $cleanValidationRules = true;

    /**
     * 允許回調函數
     */
    protected $allowCallbacks = true;

    /**
     * 取得指定評估記錄的所有題目內容
     *
     * @param int $assessmentId 評估記錄ID
     * @param int|null $categoryId 篩選特定分類下的內容
     * @param int|null $topicId 篩選特定主題下的內容
     * @param int|null $factorId 篩選特定因子下的內容
     * @param string|null $search 搜尋關鍵字（標題或描述）
     * @param int|null $userId 篩選特定用戶被指派的內容
     * @param int|null $externalId 篩選特定外部用戶被指派的內容
     * @return array 題目內容列表，包含分類、主題、因子資訊
     */
    public function getContentsByAssessment(
        int $assessmentId,
        ?int $categoryId = null,
        ?int $topicId = null,
        ?int $factorId = null,
        ?string $search = null,
        ?int $userId = null,
        ?int $externalId = null
    ): array {
        // 前置檢查：驗證評估記錄是否存在
        log_message('info', "🚀 Starting getContentsByAssessment for assessment {$assessmentId}");

        try {
            $db = \Config\Database::connect();

            // 檢查評估記錄是否存在
            $assessmentExists = $db->query("SELECT COUNT(*) as count FROM company_assessments WHERE id = ?", [$assessmentId])->getRow();
            if (!$assessmentExists || $assessmentExists->count == 0) {
                log_message('warning', "⚠️ Assessment {$assessmentId} does not exist in company_assessments table");
                return []; // 返回空陣列而不是拋出異常
            }

            // 如果有用戶篩選，檢查用戶是否存在
            if ($userId !== null) {
                $userExists = $db->query("SELECT COUNT(*) as count FROM external_personnel WHERE id = ?", [$userId])->getRow();
                if (!$userExists || $userExists->count == 0) {
                    log_message('warning', "⚠️ User {$userId} does not exist in external_personnel table");
                    return []; // 返回空陣列
                }

                // 檢查該用戶是否有任何指派記錄
                $assignmentsExist = $db->query("SELECT COUNT(*) as count FROM personnel_assignments WHERE personnel_id = ? AND assessment_id = ?", [$userId, $assessmentId])->getRow();
                log_message('info', "📋 User {$userId} has {$assignmentsExist->count} assignments for assessment {$assessmentId}");
            }

        } catch (\Exception $e) {
            log_message('error', "💥 Pre-check failed: " . $e->getMessage());
            throw $e;
        }
        // 根據是否有篩選條件決定SELECT字段
        if ($userId !== null || $externalId !== null) {
            $builder = $this->select('
                    question_contents.*,
                    question_categories.category_name,
                    question_topics.topic_name,
                    question_factors.factor_name,
                    personnel_assignments.personnel_id,
                    personnel_assignments.assignment_status,
                    (SELECT COUNT(*) FROM question_responses WHERE question_content_id = question_contents.id) as response_count
                ')
                ->join('question_categories', 'question_categories.id = question_contents.category_id', 'left')
                ->join('question_topics', 'question_topics.id = question_contents.topic_id', 'left')
                ->join('question_factors', 'question_factors.id = question_contents.factor_id', 'left')
                ->where('question_contents.assessment_id', $assessmentId);
        } else {
            $builder = $this->select('
                    question_contents.*,
                    question_categories.category_name,
                    question_topics.topic_name,
                    question_factors.factor_name,
                    (SELECT COUNT(*) FROM question_responses WHERE question_content_id = question_contents.id) as response_count
                ')
                ->join('question_categories', 'question_categories.id = question_contents.category_id', 'left')
                ->join('question_topics', 'question_topics.id = question_contents.topic_id', 'left')
                ->join('question_factors', 'question_factors.id = question_contents.factor_id', 'left')
                ->where('question_contents.assessment_id', $assessmentId);
        }

        // 篩選條件
        if ($categoryId !== null) {
            $builder->where('question_contents.category_id', $categoryId);
        }

        if ($topicId !== null) {
            $builder->where('question_contents.topic_id', $topicId);
        }

        if ($factorId !== null) {
            $builder->where('question_contents.factor_id', $factorId);
        }

        // 搜尋條件
        if (!empty($search)) {
            $builder->groupStart()
                ->like('question_contents.description', $search)
                // ->orLike('question_contents.a_content', $search) // REMOVED: Field no longer exists
                ->orLike('question_contents.b_content', $search)
                ->groupEnd();
        }

        // 用戶篩選條件 - 通過 personnel_assignments 表實現篩選
        if ($userId !== null || $externalId !== null) {
            log_message('info', "🔍 User filtering requested - user_id: {$userId}, external_id: {$externalId}");

            // 通過 personnel_assignments 表進行 JOIN，只返回指派給指定用戶的題目
            $builder->join('personnel_assignments',
                'personnel_assignments.question_content_id = question_contents.id AND ' .
                'personnel_assignments.assessment_id = question_contents.assessment_id',
                'inner'
            );

            // 確保只查詢有效的指派記錄（排除已拒絕的指派）
            $builder->where('personnel_assignments.assignment_status !=', 'declined');

            // 篩選邏輯修正：user_id 和 external_id 應該指向同一個用戶，優先使用 user_id
            if ($userId !== null) {
                // 有 userId，直接使用（最精確的篩選）
                $builder->where('personnel_assignments.personnel_id', $userId);
                log_message('info', "✅ Applied user_id filter: {$userId} (ignoring external_id if provided)");

            } elseif ($externalId !== null) {
                // 只有 externalId，需要通過 external_personnel 表轉換
                // 使用額外的 JOIN 方式替代子查詢，更穩定
                $builder->join('external_personnel',
                    'external_personnel.id = personnel_assignments.personnel_id',
                    'inner'
                );
                $builder->where('external_personnel.external_id', $externalId);
                log_message('info', "✅ Applied external_id filter: {$externalId}");
            }

            // 添加分組，避免因為多個指派記錄導致的重複結果
            $builder->groupBy('question_contents.id');

            // 額外調試：查看 personnel_assignments 表中的實際數據
            try {
                $db = \Config\Database::connect();
                if ($userId !== null) {
                    $assignmentQuery = "SELECT COUNT(*) as count FROM personnel_assignments WHERE assessment_id = ? AND personnel_id = ? AND assignment_status != 'declined'";
                    $assignments = $db->query($assignmentQuery, [$assessmentId, $userId])->getRow();
                    log_message('info', "🗂️ Personnel assignments count for assessment {$assessmentId}, user {$userId}: {$assignments->count}");
                } elseif ($externalId !== null) {
                    $assignmentQuery = "SELECT COUNT(*) as count FROM personnel_assignments pa
                                       JOIN external_personnel ep ON pa.personnel_id = ep.id
                                       WHERE pa.assessment_id = ? AND ep.external_id = ? AND pa.assignment_status != 'declined'";
                    $assignments = $db->query($assignmentQuery, [$assessmentId, $externalId])->getRow();
                    log_message('info', "🗂️ Personnel assignments count for assessment {$assessmentId}, external_id {$externalId}: {$assignments->count}");
                }
            } catch (\Exception $debugError) {
                log_message('warning', "🚨 Debug query failed: " . $debugError->getMessage());
            }

            log_message('info', "🎯 User filtering implemented successfully for assessment {$assessmentId}");
        } else {
            log_message('info', "📋 No user filtering - returning all contents for assessment {$assessmentId}");
        }

        // 排序：先按分類、主題、因子，再按內容排序
        $builder->orderBy('question_categories.sort_order', 'ASC')
            ->orderBy('question_topics.sort_order', 'ASC')
            ->orderBy('question_factors.sort_order', 'ASC')
            ->orderBy('question_contents.sort_order', 'ASC')
            ->orderBy('question_contents.id', 'ASC');

        // 執行查詢並記錄除錯資訊
        try {
            log_message('info', "🔍 About to execute query for assessment {$assessmentId}");

            $results = $builder->findAll();

            // 查詢執行後記錄最後的SQL（如果可用）
            if ($userId !== null || $externalId !== null) {
                try {
                    $lastQuery = $this->db->getLastQuery();
                    log_message('info', "🔍 Last executed SQL query: " . $lastQuery);
                } catch (\Exception $sqlLogError) {
                    log_message('info', "🔍 Could not retrieve last SQL query: " . $sqlLogError->getMessage());
                }
            }

            // 調試：記錄查詢結果
            log_message('info', "📊 Query returned " . count($results) . " results for assessment {$assessmentId}");

            if ($userId !== null || $externalId !== null) {
                log_message('info', "🧪 Filtering results for user_id: {$userId}, external_id: {$externalId}");
                if (count($results) === 0) {
                    log_message('warning', "⚠️ No results found for user filtering. This might indicate:");
                    log_message('warning', "  1. No personnel assignments exist for this user and assessment");
                    log_message('warning', "  2. User ID {$userId} doesn't exist in personnel_assignments table");
                    log_message('warning', "  3. Assessment {$assessmentId} has no question contents");
                    log_message('warning', "  4. Database connection or query execution issue");

                    // 額外檢查：直接查詢指派表
                    try {
                        $assignmentCheck = $this->db->query(
                            "SELECT COUNT(*) as count FROM personnel_assignments WHERE assessment_id = ? AND personnel_id = ?",
                            [$assessmentId, $userId]
                        )->getRow();
                        log_message('warning', "  📋 Direct assignment check: {$assignmentCheck->count} records found");

                        // 如果有指派記錄但查詢結果為空，使用備用查詢方法
                        if ($assignmentCheck->count > 0) {
                            log_message('warning', "  🔄 Found assignments but main query returned empty, trying fallback method...");
                            $results = $this->getFallbackContentsByAssessment($assessmentId, $userId);
                            log_message('info', "  🔄 Fallback method returned " . count($results) . " results");
                        }
                    } catch (\Exception $checkError) {
                        log_message('warning', "  ❌ Assignment check failed: " . $checkError->getMessage());
                    }

                } else {
                    foreach ($results as $index => $result) {
                        log_message('info', "  - Result {$index}: content_id={$result['id']}, title=" . ($result['title'] ?? 'no title') . ", description=" . substr($result['description'] ?? '', 0, 50));
                    }
                }
            }

        } catch (\Exception $e) {
            log_message('error', "💥 SQL Query execution failed: " . $e->getMessage());
            log_message('error', "💥 Error details: " . $e->getTraceAsString());
            log_message('error', "💥 Parameters: assessment_id={$assessmentId}, user_id={$userId}, external_id={$externalId}");

            // 嘗試記錄最後的查詢（如果可能）
            try {
                $lastQuery = $this->db->getLastQuery();
                log_message('error', "💥 Last attempted SQL: " . $lastQuery);
            } catch (\Exception $sqlLogError) {
                log_message('error', "💥 Could not retrieve last SQL query: " . $sqlLogError->getMessage());
            }

            throw $e; // Re-throw to be caught by controller
        }

        // 確保計數欄位為整數類型，並處理JSON欄位
        foreach ($results as &$result) {
            $result['response_count'] = (int)($result['response_count'] ?? 0);

            // 移除此處不再需要處理 assessment_criteria 欄位
        }

        return $results;
    }

    /**
     * 備用方法：使用簡單查詢獲取指派給用戶的評估內容
     *
     * @param int $assessmentId 評估記錄ID
     * @param int $userId 用戶ID
     * @return array 題目內容列表
     */
    private function getFallbackContentsByAssessment(int $assessmentId, int $userId): array
    {
        try {
            log_message('info', "🔄 Using fallback method for assessment {$assessmentId}, user {$userId}");

            // 使用原生SQL，更直接和可靠
            $sql = "
                SELECT DISTINCT
                    qc.*,
                    cat.category_name,
                    top.topic_name,
                    fac.factor_name,
                    (SELECT COUNT(*) FROM question_responses WHERE question_content_id = qc.id) as response_count
                FROM question_contents qc
                INNER JOIN personnel_assignments pa ON pa.question_content_id = qc.id
                    AND pa.assessment_id = qc.assessment_id
                LEFT JOIN question_categories cat ON cat.id = qc.category_id
                LEFT JOIN question_topics top ON top.id = qc.topic_id
                LEFT JOIN question_factors fac ON fac.id = qc.factor_id
                WHERE qc.assessment_id = ?
                AND pa.personnel_id = ?
                AND pa.assignment_status != 'declined'
                ORDER BY cat.sort_order ASC, top.sort_order ASC, fac.sort_order ASC, qc.sort_order ASC, qc.id ASC
            ";

            $results = $this->db->query($sql, [$assessmentId, $userId])->getResultArray();

            log_message('info', "🔄 Fallback method raw query returned " . count($results) . " results");

            // 確保計數欄位為整數類型
            foreach ($results as &$result) {
                $result['response_count'] = (int)($result['response_count'] ?? 0);
            }

            return $results;

        } catch (\Exception $e) {
            log_message('error', "🔄 Fallback method failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 取得內容詳細資訊，包含分類、主題、因子資訊及其描述
     *
     * @param int $contentId 內容ID
     * @return array|null 內容詳細資訊
     */
    public function getContentWithDetails(int $contentId): ?array
    {
        $result = $this->select('
                question_contents.*,
                question_categories.category_name,
                question_categories.description as category_description,
                question_topics.topic_name,
                question_topics.description as topic_description,
                question_factors.factor_name,
                question_factors.description as factor_description,
                (SELECT COUNT(*) FROM question_responses WHERE question_content_id = question_contents.id) as response_count
            ')
            ->join('question_categories', 'question_categories.id = question_contents.category_id', 'left')
            ->join('question_topics', 'question_topics.id = question_contents.topic_id', 'left')
            ->join('question_factors', 'question_factors.id = question_contents.factor_id', 'left')
            ->where('question_contents.id', $contentId)
            ->first();

        if ($result) {
            $result['response_count'] = (int)($result['response_count'] ?? 0);

            // 移除此處不再需要處理 assessment_criteria 欄位
        }

        return $result;
    }

    /**
     * 取得指定分類下的所有內容
     *
     * @param int $categoryId 分類ID
     * @return array 內容列表
     */
    public function getContentsByCategory(int $categoryId): array
    {
        $results = $this->select('
                question_contents.*,
                question_topics.topic_name,
                question_factors.factor_name,
                (SELECT COUNT(*) FROM question_responses WHERE question_content_id = question_contents.id) as response_count
            ')
            ->join('question_topics', 'question_topics.id = question_contents.topic_id', 'left')
            ->join('question_factors', 'question_factors.id = question_contents.factor_id', 'left')
            ->where('category_id', $categoryId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($results as &$result) {
            $result['response_count'] = (int)($result['response_count'] ?? 0);
            if ($result['assessment_criteria']) {
                $result['assessment_criteria'] = json_decode($result['assessment_criteria'], true);
            }
        }

        return $results;
    }

    /**
     * 取得指定主題下的所有內容
     *
     * @param int $topicId 主題ID
     * @return array 內容列表
     */
    public function getContentsByTopic(int $topicId): array
    {
        $results = $this->select('
                question_contents.*,
                question_factors.factor_name,
                (SELECT COUNT(*) FROM question_responses WHERE question_content_id = question_contents.id) as response_count
            ')
            ->join('question_factors', 'question_factors.id = question_contents.factor_id', 'left')
            ->where('topic_id', $topicId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($results as &$result) {
            $result['response_count'] = (int)($result['response_count'] ?? 0);
            if ($result['assessment_criteria']) {
                $result['assessment_criteria'] = json_decode($result['assessment_criteria'], true);
            }
        }

        return $results;
    }

    /**
     * 取得指定因子下的所有內容
     *
     * @param int $factorId 因子ID
     * @return array 內容列表
     */
    public function getContentsByFactor(int $factorId): array
    {
        $results = $this->select('
                question_contents.*,
                (SELECT COUNT(*) FROM question_responses WHERE question_content_id = question_contents.id) as response_count
            ')
            ->where('factor_id', $factorId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($results as &$result) {
            $result['response_count'] = (int)($result['response_count'] ?? 0);
            if ($result['assessment_criteria']) {
                $result['assessment_criteria'] = json_decode($result['assessment_criteria'], true);
            }
        }

        return $results;
    }

    /**
     * 取得指定評估記錄的下一個排序號
     *
     * @param int $assessmentId 評估記錄ID
     * @param int|null $categoryId 分類ID（可選）
     * @param int|null $topicId 主題ID（可選）
     * @param int|null $factorId 因子ID（可選）
     * @return int 下一個排序號
     */
    public function getNextSortOrder(int $assessmentId, ?int $categoryId = null, ?int $topicId = null, ?int $factorId = null): int
    {
        $builder = $this->selectMax('sort_order')
            ->where('assessment_id', $assessmentId);

        if ($categoryId !== null) {
            $builder->where('category_id', $categoryId);
        }

        if ($topicId !== null) {
            $builder->where('topic_id', $topicId);
        }

        if ($factorId !== null) {
            $builder->where('factor_id', $factorId);
        }

        $maxOrder = $builder->get()
            ->getRow()
            ->sort_order ?? 0;

        return $maxOrder + 1;
    }

    /**
     * 從範本內容複製到題項內容
     *
     * @param int $assessmentId 目標評估記錄ID
     * @param array $templateContents 範本內容資料陣列
     * @param array $categoryIdMapping 分類ID對應表
     * @param array $topicIdMapping 主題ID對應表
     * @param array $factorIdMapping 因子ID對應表
     * @return array 新建立的內容ID對應表 [原始範本內容ID => 新題項內容ID]
     */
    public function copyFromTemplateContents(
        int $assessmentId,
        array $templateContents,
        array $categoryIdMapping = [],
        array $topicIdMapping = [],
        array $factorIdMapping = []
    ): array {
        log_message('info', "QuestionContentModel::copyFromTemplateContents START - Assessment: {$assessmentId}, Contents to copy: " . count($templateContents));
        log_message('info', "Category ID mapping: " . json_encode($categoryIdMapping));
        log_message('info', "Topic ID mapping: " . json_encode($topicIdMapping));
        log_message('info', "Factor ID mapping: " . json_encode($factorIdMapping));

        $idMapping = [];

        foreach ($templateContents as $index => $templateContent) {
            log_message('info', "=== COPYING TEMPLATE CONTENT {$index} ===");
            log_message('info', "Template Content Data: " . json_encode($templateContent));

            $newCategoryId = null;
            if (isset($templateContent['category_id']) && $templateContent['category_id'] && isset($categoryIdMapping[$templateContent['category_id']])) {
                $newCategoryId = $categoryIdMapping[$templateContent['category_id']];
                log_message('info', "✓ Category mapping: {$templateContent['category_id']} -> {$newCategoryId}");
            } else {
                log_message('info', "ⓘ No category mapping for: " . ($templateContent['category_id'] ?? 'null'));
            }

            $newTopicId = null;
            if (isset($templateContent['topic_id']) && $templateContent['topic_id'] && isset($topicIdMapping[$templateContent['topic_id']])) {
                $newTopicId = $topicIdMapping[$templateContent['topic_id']];
                log_message('info', "✓ Topic mapping: {$templateContent['topic_id']} -> {$newTopicId}");
            } else {
                log_message('info', "ⓘ No topic mapping for: " . ($templateContent['topic_id'] ?? 'null'));
            }

            $newFactorId = null;
            if (isset($templateContent['risk_factor_id']) && $templateContent['risk_factor_id'] && isset($factorIdMapping[$templateContent['risk_factor_id']])) {
                $newFactorId = $factorIdMapping[$templateContent['risk_factor_id']];
                log_message('info', "✓ Factor mapping: {$templateContent['risk_factor_id']} -> {$newFactorId}");
            } else {
                log_message('info', "ⓘ No factor mapping for: " . ($templateContent['risk_factor_id'] ?? 'null'));
            }

            $newContentData = [
                'assessment_id' => $assessmentId,
                'category_id' => $newCategoryId,
                'topic_id' => $newTopicId,
                'factor_id' => $newFactorId,

                // 核心內容欄位（title, assessment_criteria, scoring_method, weight, description 已移除/遷移至 a_content）
                'is_required' => $templateContent['is_required'] ?? 1,
                'sort_order' => $templateContent['sort_order'] ?? 1,

                // 複製表單欄位
                // 'a_content' => $templateContent['a_content'] ?? null, // REMOVED: Now using factor description
                'b_content' => $templateContent['b_content'] ?? null,
                'c_placeholder' => $templateContent['c_placeholder'] ?? null,
                'd_placeholder_1' => $templateContent['d_placeholder_1'] ?? null,
                'd_placeholder_2' => $templateContent['d_placeholder_2'] ?? null,
                'e1_placeholder_1' => $templateContent['e1_placeholder_1'] ?? null,
                'e2_select_1' => $templateContent['e2_select_1'] ?? null,
                'e2_select_2' => $templateContent['e2_select_2'] ?? null,
                'e2_placeholder' => $templateContent['e2_placeholder'] ?? null,
                'f2_select_1' => $templateContent['f2_select_1'] ?? null,
                'f2_select_2' => $templateContent['f2_select_2'] ?? null,
                'f2_placeholder' => $templateContent['f2_placeholder'] ?? null,
                'e1_info' => $templateContent['e1_info'] ?? null,
                'f1_info' => $templateContent['f1_info'] ?? null,
                'g1_info' => $templateContent['g1_info'] ?? null,
                'h1_info' => $templateContent['h1_info'] ?? null,

                'copied_from_template_content' => $templateContent['id']
            ];

            log_message('info', "📝 Question Content Data to Insert:");
            log_message('info', json_encode($newContentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $newId = $this->insert($newContentData);

            if ($newId) {
                $idMapping[$templateContent['id']] = $newId;
                log_message('info', "✅ Content copied successfully: Template ID {$templateContent['id']} -> Question ID {$newId}");

                // Log which fields were copied with data
                $filledFields = [];
                foreach ($newContentData as $field => $value) {
                    if (!empty($value) && !in_array($field, ['assessment_id', 'copied_from_template_content'])) {
                        $filledFields[] = $field;
                    }
                }
                log_message('info', "📊 Fields with data: " . implode(', ', $filledFields));
            } else {
                log_message('error', "❌ Failed to copy content: Template ID {$templateContent['id']}");
                log_message('error', "📝 Validation errors: " . json_encode($this->errors()));
                log_message('error', "🔍 Last query: " . $this->db->getLastQuery());
            }
        }

        log_message('info', "QuestionContentModel::copyFromTemplateContents END - Created " . count($idMapping) . " contents");
        return $idMapping;
    }

    /**
     * 移動內容到不同分類、主題或因子
     *
     * @param int $contentId 內容ID
     * @param int|null $newCategoryId 新分類ID
     * @param int|null $newTopicId 新主題ID
     * @param int|null $newFactorId 新因子ID
     * @return bool 是否成功
     */
    public function moveToNewLocation(int $contentId, ?int $newCategoryId, ?int $newTopicId, ?int $newFactorId): bool
    {
        return $this->update($contentId, [
            'category_id' => $newCategoryId,
            'topic_id' => $newTopicId,
            'factor_id' => $newFactorId
        ]);
    }

    /**
     * 取得評估統計資料
     *
     * @param int $assessmentId 評估記錄ID
     * @return array 統計資料
     */
    public function getAssessmentStats(int $assessmentId): array
    {
        // 總題目數
        $totalQuestions = $this->where('assessment_id', $assessmentId)->countAllResults();

        // 已回答題目數
        $answeredQuestions = $this->db->query("
            SELECT COUNT(DISTINCT question_content_id) as count
            FROM question_responses qr
            JOIN question_contents qc ON qc.id = qr.question_content_id
            WHERE qc.assessment_id = ? AND qr.response_value IS NOT NULL
        ", [$assessmentId])->getRow()->count ?? 0;

        // 必填題目數
        $requiredQuestions = $this->where('assessment_id', $assessmentId)
            ->where('is_required', true)
            ->countAllResults();

        // 已完成必填題目數
        $completedRequiredQuestions = $this->db->query("
            SELECT COUNT(DISTINCT qc.id) as count
            FROM question_contents qc
            JOIN question_responses qr ON qr.question_content_id = qc.id
            WHERE qc.assessment_id = ? AND qc.is_required = 1 AND qr.response_value IS NOT NULL
        ", [$assessmentId])->getRow()->count ?? 0;

        // 計算完成度
        $totalCompletionRate = $totalQuestions > 0 ? ($answeredQuestions / $totalQuestions) * 100 : 0;
        $requiredCompletionRate = $requiredQuestions > 0 ? ($completedRequiredQuestions / $requiredQuestions) * 100 : 0;

        return [
            'total_questions' => (int)$totalQuestions,
            'answered_questions' => (int)$answeredQuestions,
            'required_questions' => (int)$requiredQuestions,
            'completed_required_questions' => (int)$completedRequiredQuestions,
            'total_completion_rate' => round($totalCompletionRate, 2),
            'required_completion_rate' => round($requiredCompletionRate, 2),
            'is_completed' => $requiredQuestions > 0 && $completedRequiredQuestions >= $requiredQuestions
        ];
    }

    /**
     * 刪除評估記錄的所有內容（連帶刪除相關的回答）
     *
     * @param int $assessmentId 評估記錄ID
     * @return bool 是否成功
     */
    public function deleteAllByAssessment(int $assessmentId): bool
    {
        // 由於設定了 CASCADE 外鍵約束，刪除內容會自動刪除相關的回答
        return $this->where('assessment_id', $assessmentId)->delete();
    }

    /**
     * 更新內容的排序
     *
     * @param array $sortData 排序資料 [['id' => 1, 'sort_order' => 1], ...]
     * @return bool 是否成功
     */
    public function updateSortOrder(array $sortData): bool
    {
        $db = $this->db;
        $db->transStart();

        try {
            foreach ($sortData as $item) {
                $this->update($item['id'], ['sort_order' => $item['sort_order']]);
            }

            $db->transComplete();
            return $db->transStatus() !== false;
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }
}