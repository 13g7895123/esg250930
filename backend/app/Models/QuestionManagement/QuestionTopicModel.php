<?php

namespace App\Models\QuestionManagement;

use CodeIgniter\Model;

/**
 * 題項風險主題模型
 *
 * 管理題項管理中的風險主題資料
 * 提供比分類更細緻的風險項目分組
 * 每個主題可歸屬於某個風險分類下，也可獨立存在
 *
 * @package App\Models\QuestionManagement
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class QuestionTopicModel extends Model
{
    /**
     * 資料表名稱
     */
    protected $table = 'question_topics';

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
        'topic_name',
        'topic_code',
        'description',
        'sort_order',
        'copied_from_template_topic'
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
        'topic_name' => 'required|max_length[255]',
        'topic_code' => 'permit_empty|max_length[50]|is_unique_with[question_topics.assessment_id.{assessment_id}]',
        'description' => 'permit_empty',
        'sort_order' => 'permit_empty|integer',
        'copied_from_template_topic' => 'permit_empty|integer'
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
        'topic_name' => [
            'required' => '風險主題名稱為必填項目',
            'max_length' => '風險主題名稱不能超過255個字符'
        ],
        'topic_code' => [
            'max_length' => '風險主題代碼不能超過50個字符',
            'is_unique_with' => '此評估記錄中已存在相同的主題代碼'
        ],
        'sort_order' => [
            'integer' => '排序必須為整數'
        ],
        'copied_from_template_topic' => [
            'integer' => '範本主題ID必須為整數'
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
     * 取得指定評估記錄的所有風險主題
     *
     * @param int $assessmentId 評估記錄ID
     * @param int|null $categoryId 篩選特定分類下的主題
     * @param string|null $search 搜尋關鍵字（主題名稱）
     * @return array 風險主題列表，包含分類資訊和統計
     */
    public function getTopicsByAssessment(int $assessmentId, ?int $categoryId = null, ?string $search = null): array
    {
        $builder = $this->select('
                question_topics.*,
                question_categories.category_name,
                question_categories.category_code,
                (SELECT COUNT(*) FROM question_contents WHERE topic_id = question_topics.id) as content_count,
                (SELECT COUNT(*) FROM question_factors WHERE topic_id = question_topics.id) as factor_count
            ')
            ->join('question_categories', 'question_categories.id = question_topics.category_id', 'left')
            ->where('question_topics.assessment_id', $assessmentId);

        // 篩選特定分類
        if ($categoryId !== null) {
            $builder->where('question_topics.category_id', $categoryId);
        }

        // 搜尋條件
        if (!empty($search)) {
            $builder->like('question_topics.topic_name', $search);
        }

        // 排序：先按分類，再按主題排序
        $builder->orderBy('question_categories.sort_order', 'ASC')
            ->orderBy('question_topics.sort_order', 'ASC')
            ->orderBy('question_topics.topic_name', 'ASC');

        $results = $builder->findAll();

        // 確保計數欄位為整數類型
        foreach ($results as &$result) {
            $result['content_count'] = (int)($result['content_count'] ?? 0);
            $result['factor_count'] = (int)($result['factor_count'] ?? 0);
        }

        return $results;
    }

    /**
     * 取得主題詳細資訊，包含分類資訊和統計資料
     *
     * @param int $topicId 主題ID
     * @return array|null 主題詳細資訊
     */
    public function getTopicWithStats(int $topicId): ?array
    {
        $result = $this->select('
                question_topics.*,
                question_categories.category_name,
                question_categories.category_code,
                (SELECT COUNT(*) FROM question_contents WHERE topic_id = question_topics.id) as content_count,
                (SELECT COUNT(*) FROM question_factors WHERE topic_id = question_topics.id) as factor_count
            ')
            ->join('question_categories', 'question_categories.id = question_topics.category_id', 'left')
            ->where('question_topics.id', $topicId)
            ->first();

        if ($result) {
            // 確保計數欄位為整數類型
            $result['content_count'] = (int)($result['content_count'] ?? 0);
            $result['factor_count'] = (int)($result['factor_count'] ?? 0);
        }

        return $result;
    }

    /**
     * 取得指定分類下的所有主題
     *
     * @param int $categoryId 分類ID
     * @return array 主題列表
     */
    public function getTopicsByCategory(int $categoryId): array
    {
        $results = $this->select('
                question_topics.*,
                (SELECT COUNT(*) FROM question_contents WHERE topic_id = question_topics.id) as content_count,
                (SELECT COUNT(*) FROM question_factors WHERE topic_id = question_topics.id) as factor_count
            ')
            ->where('category_id', $categoryId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('topic_name', 'ASC')
            ->findAll();

        // 確保計數欄位為整數類型
        foreach ($results as &$result) {
            $result['content_count'] = (int)($result['content_count'] ?? 0);
            $result['factor_count'] = (int)($result['factor_count'] ?? 0);
        }

        return $results;
    }

    /**
     * 取得未分類的主題（category_id 為 null）
     *
     * @param int $assessmentId 評估記錄ID
     * @return array 未分類主題列表
     */
    public function getUncategorizedTopics(int $assessmentId): array
    {
        $results = $this->select('
                question_topics.*,
                (SELECT COUNT(*) FROM question_contents WHERE topic_id = question_topics.id) as content_count,
                (SELECT COUNT(*) FROM question_factors WHERE topic_id = question_topics.id) as factor_count
            ')
            ->where('assessment_id', $assessmentId)
            ->where('category_id IS NULL')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('topic_name', 'ASC')
            ->findAll();

        // 確保計數欄位為整數類型
        foreach ($results as &$result) {
            $result['content_count'] = (int)($result['content_count'] ?? 0);
            $result['factor_count'] = (int)($result['factor_count'] ?? 0);
        }

        return $results;
    }

    /**
     * 檢查主題代碼在指定評估記錄中是否唯一
     *
     * @param int $assessmentId 評估記錄ID
     * @param string $topicCode 主題代碼
     * @param int|null $excludeId 排除的主題ID（編輯時使用）
     * @return bool 是否唯一
     */
    public function isTopicCodeUniqueInAssessment(int $assessmentId, string $topicCode, ?int $excludeId = null): bool
    {
        $builder = $this->where('assessment_id', $assessmentId)
            ->where('topic_code', $topicCode);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first() === null;
    }

    /**
     * 取得指定評估記錄或分類的下一個排序號
     *
     * @param int $assessmentId 評估記錄ID
     * @param int|null $categoryId 分類ID（可選）
     * @return int 下一個排序號
     */
    public function getNextSortOrder(int $assessmentId, ?int $categoryId = null): int
    {
        $builder = $this->selectMax('sort_order')
            ->where('assessment_id', $assessmentId);

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
     * 從範本主題複製到題項主題
     *
     * @param int $assessmentId 目標評估記錄ID
     * @param array $templateTopics 範本主題資料陣列
     * @param array $categoryIdMapping 分類ID對應表 [範本分類ID => 題項分類ID]
     * @return array 新建立的主題ID對應表 [原始範本主題ID => 新題項主題ID]
     */
    public function copyFromTemplateTopics(int $assessmentId, array $templateTopics, array $categoryIdMapping = []): array
    {
        $idMapping = [];

        foreach ($templateTopics as $templateTopic) {
            $newCategoryId = null;
            if ($templateTopic['category_id'] && isset($categoryIdMapping[$templateTopic['category_id']])) {
                $newCategoryId = $categoryIdMapping[$templateTopic['category_id']];
            }

            $newTopicData = [
                'assessment_id' => $assessmentId,
                'category_id' => $newCategoryId,
                'topic_name' => $templateTopic['topic_name'],
                'topic_code' => $templateTopic['topic_code'],
                'description' => $templateTopic['description'],
                'sort_order' => $templateTopic['sort_order'],
                'copied_from_template_topic' => $templateTopic['id']
            ];

            $newId = $this->insert($newTopicData);
            if ($newId) {
                $idMapping[$templateTopic['id']] = $newId;
            }
        }

        return $idMapping;
    }

    /**
     * 移動主題到不同分類
     *
     * @param int $topicId 主題ID
     * @param int|null $newCategoryId 新分類ID（null 表示取消分類）
     * @return bool 是否成功
     */
    public function moveToCategory(int $topicId, ?int $newCategoryId): bool
    {
        return $this->update($topicId, ['category_id' => $newCategoryId]);
    }

    /**
     * 刪除評估記錄的所有主題（連帶刪除相關的因子、內容）
     *
     * @param int $assessmentId 評估記錄ID
     * @return bool 是否成功
     */
    public function deleteAllByAssessment(int $assessmentId): bool
    {
        // 由於設定了 CASCADE 外鍵約束，刪除主題會自動刪除相關的因子、內容
        return $this->where('assessment_id', $assessmentId)->delete();
    }

    /**
     * 刪除指定分類下的所有主題
     *
     * @param int $categoryId 分類ID
     * @return bool 是否成功
     */
    public function deleteAllByCategory(int $categoryId): bool
    {
        // 由於設定了 CASCADE 外鍵約束，刪除主題會自動刪除相關的因子、內容
        return $this->where('category_id', $categoryId)->delete();
    }

    /**
     * 更新主題的排序
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