<?php

namespace App\Models\QuestionManagement;

use CodeIgniter\Model;

/**
 * 題項風險分類模型
 *
 * 管理題項管理中的風險分類資料
 * 與範本管理的 RiskCategoryModel 完全獨立
 * 每個 company_assessment 可以有自己的風險分類系統
 *
 * @package App\Models\QuestionManagement
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class QuestionCategoryModel extends Model
{
    /**
     * 資料表名稱
     */
    protected $table = 'question_categories';

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
        'category_name',
        'description',
        'sort_order',
        'copied_from_template_category'
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
        'category_name' => 'required|max_length[255]',
        'description' => 'permit_empty',
        'sort_order' => 'permit_empty|integer',
        'copied_from_template_category' => 'permit_empty|integer'
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
        'category_name' => [
            'required' => '風險分類名稱為必填項目',
            'max_length' => '風險分類名稱不能超過255個字符'
        ],
        'sort_order' => [
            'integer' => '排序必須為整數'
        ],
        'copied_from_template_category' => [
            'integer' => '範本分類ID必須為整數'
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
     * 取得指定評估記錄的所有風險分類
     *
     * @param int $assessmentId 評估記錄ID
     * @param string|null $search 搜尋關鍵字（分類名稱）
     * @return array 風險分類列表，包含題項統計
     */
    public function getCategoriesByAssessment(int $assessmentId, ?string $search = null): array
    {
        $builder = $this->select('
                question_categories.*,
                (SELECT COUNT(*) FROM question_contents WHERE category_id = question_categories.id) as content_count,
                (SELECT COUNT(*) FROM question_topics WHERE category_id = question_categories.id) as topic_count
            ')
            ->where('assessment_id', $assessmentId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('category_name', 'ASC');

        // 搜尋條件
        if (!empty($search)) {
            $builder->like('category_name', $search);
        }

        $results = $builder->findAll();

        // 確保計數欄位為整數類型
        foreach ($results as &$result) {
            $result['content_count'] = (int)($result['content_count'] ?? 0);
            $result['topic_count'] = (int)($result['topic_count'] ?? 0);
        }

        return $results;
    }

    /**
     * 取得分類詳細資訊，包含統計資料
     *
     * @param int $categoryId 分類ID
     * @return array|null 分類詳細資訊
     */
    public function getCategoryWithStats(int $categoryId): ?array
    {
        $result = $this->select('
                question_categories.*,
                (SELECT COUNT(*) FROM question_contents WHERE category_id = question_categories.id) as content_count,
                (SELECT COUNT(*) FROM question_topics WHERE category_id = question_categories.id) as topic_count,
                (SELECT COUNT(*) FROM question_factors WHERE category_id = question_categories.id) as factor_count
            ')
            ->where('id', $categoryId)
            ->first();

        if ($result) {
            // 確保計數欄位為整數類型
            $result['content_count'] = (int)($result['content_count'] ?? 0);
            $result['topic_count'] = (int)($result['topic_count'] ?? 0);
            $result['factor_count'] = (int)($result['factor_count'] ?? 0);
        }

        return $result;
    }


    /**
     * 取得指定評估記錄的下一個排序號
     *
     * @param int $assessmentId 評估記錄ID
     * @return int 下一個排序號
     */
    public function getNextSortOrder(int $assessmentId): int
    {
        $maxOrder = $this->selectMax('sort_order')
            ->where('assessment_id', $assessmentId)
            ->get()
            ->getRow()
            ->sort_order ?? 0;

        return $maxOrder + 1;
    }

    /**
     * 從範本分類複製到題項分類
     *
     * @param int $assessmentId 目標評估記錄ID
     * @param array $templateCategories 範本分類資料陣列
     * @return array 新建立的分類ID對應表 [原始範本分類ID => 新題項分類ID]
     */
    public function copyFromTemplateCategories(int $assessmentId, array $templateCategories): array
    {
        log_message('info', "QuestionCategoryModel::copyFromTemplateCategories START - Assessment: {$assessmentId}, Categories to copy: " . count($templateCategories));

        $idMapping = [];

        foreach ($templateCategories as $index => $templateCategory) {
            log_message('info', "Copying category {$index}: ID={$templateCategory['id']}, Name={$templateCategory['category_name']}");

            $newCategoryData = [
                'assessment_id' => $assessmentId,
                'category_name' => $templateCategory['category_name'],
                'description' => $templateCategory['description'],
                'sort_order' => $templateCategory['sort_order'],
                'copied_from_template_category' => $templateCategory['id']
            ];

            log_message('info', "Inserting category data: " . json_encode($newCategoryData));
            $newId = $this->insert($newCategoryData);

            if ($newId) {
                $idMapping[$templateCategory['id']] = $newId;
                log_message('info', "Category copied successfully: Template ID {$templateCategory['id']} -> Question ID {$newId}");
            } else {
                log_message('error', "Failed to copy category: Template ID {$templateCategory['id']}, Errors: " . json_encode($this->errors()));
            }
        }

        log_message('info', "QuestionCategoryModel::copyFromTemplateCategories END - Created " . count($idMapping) . " categories");
        return $idMapping;
    }

    /**
     * 刪除評估記錄的所有分類（連帶刪除相關的主題、因子、內容）
     *
     * @param int $assessmentId 評估記錄ID
     * @return bool 是否成功
     */
    public function deleteAllByAssessment(int $assessmentId): bool
    {
        // 由於設定了 CASCADE 外鍵約束，刪除分類會自動刪除相關的主題、因子、內容
        return $this->where('assessment_id', $assessmentId)->delete();
    }

    /**
     * 更新分類的排序
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