<?php

namespace App\Models\QuestionManagement;

use CodeIgniter\Model;

/**
 * 題項風險因子模型
 *
 * 管理題項管理中的風險因子資料
 * 提供最細緻的風險分組層級，可歸屬於主題或直接歸屬於分類
 * 為具體的評估內容提供詳細的風險項目分類
 *
 * @package App\Models\QuestionManagement
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class QuestionFactorModel extends Model
{
    /**
     * 資料表名稱
     */
    protected $table = 'question_factors';

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
        'topic_id',
        'category_id',
        'factor_name',
        'description',
        'sort_order',
        'copied_from_template_factor'
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
        'topic_id' => 'permit_empty|integer|is_not_unique[question_topics.id]',
        'category_id' => 'permit_empty|integer|is_not_unique[question_categories.id]',
        'factor_name' => 'required|max_length[255]',
        'description' => 'permit_empty',
        'sort_order' => 'permit_empty|integer',
        'copied_from_template_factor' => 'permit_empty|integer'
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
        'topic_id' => [
            'integer' => '主題ID必須為整數',
            'is_not_unique' => '指定的風險主題不存在'
        ],
        'category_id' => [
            'integer' => '分類ID必須為整數',
            'is_not_unique' => '指定的風險分類不存在'
        ],
        'factor_name' => [
            'required' => '風險因子名稱為必填項目',
            'max_length' => '風險因子名稱不能超過255個字符'
        ],
        'sort_order' => [
            'integer' => '排序必須為整數'
        ],
        'copied_from_template_factor' => [
            'integer' => '範本因子ID必須為整數'
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
     * 取得指定評估記錄的所有風險因子
     *
     * @param int $assessmentId 評估記錄ID
     * @param int|null $topicId 篩選特定主題下的因子
     * @param int|null $categoryId 篩選特定分類下的因子
     * @param string|null $search 搜尋關鍵字（因子名稱）
     * @return array 風險因子列表，包含主題和分類資訊
     */
    public function getFactorsByAssessment(int $assessmentId, ?int $topicId = null, ?int $categoryId = null, ?string $search = null): array
    {
        $builder = $this->select('
                question_factors.*,
                question_topics.topic_name,
                COALESCE(question_categories.category_name, topic_categories.category_name) as category_name,
                (SELECT COUNT(*) FROM question_contents WHERE factor_id = question_factors.id) as content_count
            ')
            ->join('question_topics', 'question_topics.id = question_factors.topic_id', 'left')
            ->join('question_categories', 'question_categories.id = question_factors.category_id', 'left')
            ->join('question_categories as topic_categories', 'topic_categories.id = question_topics.category_id', 'left')
            ->where('question_factors.assessment_id', $assessmentId);

        // 篩選特定主題
        if ($topicId !== null) {
            $builder->where('question_factors.topic_id', $topicId);
        }

        // 篩選特定分類 - 改進邏輯：考慮透過topic關聯的category
        if ($categoryId !== null) {
            $builder->groupStart()
                ->where('question_factors.category_id', $categoryId)  // 直接關聯到分類
                ->orWhere('question_topics.category_id', $categoryId) // 透過主題關聯到分類
                ->groupEnd();
        }

        // 搜尋條件
        if (!empty($search)) {
            $builder->like('question_factors.factor_name', $search);
        }

        // 排序：先按分類、主題，再按因子排序
        $builder->orderBy('question_categories.sort_order', 'ASC')
            ->orderBy('question_topics.sort_order', 'ASC')
            ->orderBy('question_factors.sort_order', 'ASC')
            ->orderBy('question_factors.factor_name', 'ASC');

        $results = $builder->findAll();

        // 確保計數欄位為整數類型
        foreach ($results as &$result) {
            $result['content_count'] = (int)($result['content_count'] ?? 0);
        }

        return $results;
    }

    /**
     * 取得因子詳細資訊，包含主題和分類資訊
     *
     * @param int $factorId 因子ID
     * @return array|null 因子詳細資訊
     */
    public function getFactorWithStats(int $factorId): ?array
    {
        $result = $this->select('
                question_factors.*,
                question_topics.topic_name,
                question_categories.category_name,
                (SELECT COUNT(*) FROM question_contents WHERE factor_id = question_factors.id) as content_count
            ')
            ->join('question_topics', 'question_topics.id = question_factors.topic_id', 'left')
            ->join('question_categories', 'question_categories.id = question_factors.category_id', 'left')
            ->where('question_factors.id', $factorId)
            ->first();

        if ($result) {
            // 確保計數欄位為整數類型
            $result['content_count'] = (int)($result['content_count'] ?? 0);
        }

        return $result;
    }

    /**
     * 取得指定主題下的所有因子
     *
     * @param int $topicId 主題ID
     * @return array 因子列表
     */
    public function getFactorsByTopic(int $topicId): array
    {
        $results = $this->select('
                question_factors.*,
                (SELECT COUNT(*) FROM question_contents WHERE factor_id = question_factors.id) as content_count
            ')
            ->where('topic_id', $topicId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('factor_name', 'ASC')
            ->findAll();

        // 確保計數欄位為整數類型
        foreach ($results as &$result) {
            $result['content_count'] = (int)($result['content_count'] ?? 0);
        }

        return $results;
    }

    /**
     * 取得指定分類下的所有因子（直接歸屬於分類，不透過主題）
     *
     * @param int $categoryId 分類ID
     * @return array 因子列表
     */
    public function getFactorsByCategory(int $categoryId): array
    {
        $results = $this->select('
                question_factors.*,
                (SELECT COUNT(*) FROM question_contents WHERE factor_id = question_factors.id) as content_count
            ')
            ->where('category_id', $categoryId)
            ->where('topic_id IS NULL') // 只取直接歸屬於分類的因子
            ->orderBy('sort_order', 'ASC')
            ->orderBy('factor_name', 'ASC')
            ->findAll();

        // 確保計數欄位為整數類型
        foreach ($results as &$result) {
            $result['content_count'] = (int)($result['content_count'] ?? 0);
        }

        return $results;
    }

    /**
     * 取得未分類的因子（topic_id 和 category_id 都為 null）
     *
     * @param int $assessmentId 評估記錄ID
     * @return array 未分類因子列表
     */
    public function getUncategorizedFactors(int $assessmentId): array
    {
        $results = $this->select('
                question_factors.*,
                (SELECT COUNT(*) FROM question_contents WHERE factor_id = question_factors.id) as content_count
            ')
            ->where('assessment_id', $assessmentId)
            ->where('topic_id IS NULL')
            ->where('category_id IS NULL')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('factor_name', 'ASC')
            ->findAll();

        // 確保計數欄位為整數類型
        foreach ($results as &$result) {
            $result['content_count'] = (int)($result['content_count'] ?? 0);
        }

        return $results;
    }


    /**
     * 取得指定評估記錄、主題或分類的下一個排序號
     *
     * @param int $assessmentId 評估記錄ID
     * @param int|null $topicId 主題ID（可選）
     * @param int|null $categoryId 分類ID（可選）
     * @return int 下一個排序號
     */
    public function getNextSortOrder(int $assessmentId, ?int $topicId = null, ?int $categoryId = null): int
    {
        $builder = $this->selectMax('sort_order')
            ->where('assessment_id', $assessmentId);

        if ($topicId !== null) {
            $builder->where('topic_id', $topicId);
        } else {
            $builder->where('topic_id IS NULL');
        }

        if ($categoryId !== null) {
            $builder->where('category_id', $categoryId);
        } else {
            $builder->where('category_id IS NULL');
        }

        $maxOrder = $builder->get()
            ->getRow()
            ->sort_order ?? 0;

        return $maxOrder + 1;
    }

    /**
     * 從範本因子複製到題項因子
     *
     * @param int $assessmentId 目標評估記錄ID
     * @param array $templateFactors 範本因子資料陣列
     * @param array $topicIdMapping 主題ID對應表 [範本主題ID => 題項主題ID]
     * @param array $categoryIdMapping 分類ID對應表 [範本分類ID => 題項分類ID]
     * @return array 新建立的因子ID對應表 [原始範本因子ID => 新題項因子ID]
     */
    public function copyFromTemplateFactors(int $assessmentId, array $templateFactors, array $topicIdMapping = [], array $categoryIdMapping = []): array
    {
        log_message('info', "QuestionFactorModel::copyFromTemplateFactors START - Assessment: {$assessmentId}, Factors to copy: " . count($templateFactors));
        log_message('info', "Topic ID mapping: " . json_encode($topicIdMapping));
        log_message('info', "Category ID mapping: " . json_encode($categoryIdMapping));

        $idMapping = [];

        foreach ($templateFactors as $index => $templateFactor) {
            log_message('info', "Copying factor {$index}: " . json_encode($templateFactor));

            $newTopicId = null;
            if (isset($templateFactor['topic_id']) && $templateFactor['topic_id'] && isset($topicIdMapping[$templateFactor['topic_id']])) {
                $newTopicId = $topicIdMapping[$templateFactor['topic_id']];
                log_message('info', "Mapped topic: {$templateFactor['topic_id']} -> {$newTopicId}");
            }

            $newCategoryId = null;
            if (isset($templateFactor['category_id']) && $templateFactor['category_id'] && isset($categoryIdMapping[$templateFactor['category_id']])) {
                $newCategoryId = $categoryIdMapping[$templateFactor['category_id']];
                log_message('info', "Mapped category: {$templateFactor['category_id']} -> {$newCategoryId}");
            }

            $newFactorData = [
                'assessment_id' => $assessmentId,
                'topic_id' => $newTopicId,
                'category_id' => $newCategoryId,
                'factor_name' => $templateFactor['factor_name'],
                'description' => $templateFactor['description'] ?? null,
                'sort_order' => $templateFactor['sort_order'] ?? 1,
                'copied_from_template_factor' => $templateFactor['id']
            ];

            log_message('info', "Inserting factor data: " . json_encode($newFactorData));
            $newId = $this->insert($newFactorData);

            if ($newId) {
                $idMapping[$templateFactor['id']] = $newId;
                log_message('info', "Factor copied successfully: Template ID {$templateFactor['id']} -> Question ID {$newId}");
            } else {
                log_message('error', "Failed to copy factor: Template ID {$templateFactor['id']}, Errors: " . json_encode($this->errors()));
            }
        }

        log_message('info', "QuestionFactorModel::copyFromTemplateFactors END - Created " . count($idMapping) . " factors");
        return $idMapping;
    }

    /**
     * 移動因子到不同主題或分類
     *
     * @param int $factorId 因子ID
     * @param int|null $newTopicId 新主題ID（null 表示不歸屬主題）
     * @param int|null $newCategoryId 新分類ID（null 表示不歸屬分類）
     * @return bool 是否成功
     */
    public function moveToTopicOrCategory(int $factorId, ?int $newTopicId, ?int $newCategoryId): bool
    {
        return $this->update($factorId, [
            'topic_id' => $newTopicId,
            'category_id' => $newCategoryId
        ]);
    }

    /**
     * 刪除評估記錄的所有因子（連帶刪除相關的內容）
     *
     * @param int $assessmentId 評估記錄ID
     * @return bool 是否成功
     */
    public function deleteAllByAssessment(int $assessmentId): bool
    {
        // 由於設定了 CASCADE 外鍵約束，刪除因子會自動刪除相關的內容
        return $this->where('assessment_id', $assessmentId)->delete();
    }

    /**
     * 刪除指定主題下的所有因子
     *
     * @param int $topicId 主題ID
     * @return bool 是否成功
     */
    public function deleteAllByTopic(int $topicId): bool
    {
        return $this->where('topic_id', $topicId)->delete();
    }

    /**
     * 刪除指定分類下的所有因子
     *
     * @param int $categoryId 分類ID
     * @return bool 是否成功
     */
    public function deleteAllByCategory(int $categoryId): bool
    {
        return $this->where('category_id', $categoryId)->delete();
    }

    /**
     * 更新因子的排序
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