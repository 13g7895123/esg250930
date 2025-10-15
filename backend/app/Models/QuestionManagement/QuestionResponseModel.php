<?php

namespace App\Models\QuestionManagement;

use CodeIgniter\Model;

/**
 * 題項回答模型
 *
 * 管理題項管理中的評估回答資料
 * 儲存對題項內容的具體回答，替代原本的 assessment_responses
 * 提供完整的回答追蹤、審核機制和統計功能
 *
 * @package App\Models\QuestionManagement
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class QuestionResponseModel extends Model
{
    /**
     * 最後執行的 SQL（用於調試）
     */
    private $lastExecutedSQL = '';

    /**
     * 資料表名稱
     */
    protected $table = 'question_responses';

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
        'question_content_id',
        // C區域欄位
        'c_risk_event_choice',
        'c_risk_event_description',
        // D區域欄位
        'd_counter_action_choice',
        'd_counter_action_description',
        'd_counter_action_cost',
        // E-1區域欄位
        'e1_risk_description',
        // E-2區域欄位
        'e2_risk_probability',
        'e2_risk_impact',
        'e2_risk_calculation',
        // F-1區域欄位
        'f1_opportunity_description',
        // F-2區域欄位
        'f2_opportunity_probability',
        'f2_opportunity_impact',
        'f2_opportunity_calculation',
        // G-1區域欄位
        'g1_negative_impact_level',
        'g1_negative_impact_description',
        // H-1區域欄位
        'h1_positive_impact_level',
        'h1_positive_impact_description',
        // 其他欄位
        'score',
        'notes',
        'evidence_files',
        'answered_at',
        'answered_by',
        'review_status',
        'reviewed_by',
        'reviewed_at'
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
        'question_content_id' => 'required|integer|is_not_unique[question_contents.id]',
        'score' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'notes' => 'permit_empty',
        'evidence_files' => 'permit_empty|valid_json',
        'answered_by' => 'permit_empty|integer',
        'review_status' => 'permit_empty|in_list[not_filled,pending,rejected,completed]',
        'reviewed_by' => 'permit_empty|integer'
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
        'question_content_id' => [
            'required' => '題項內容ID為必填項目',
            'integer' => '題項內容ID必須為整數',
            'is_not_unique' => '指定的題項內容不存在'
        ],
        'score' => [
            'decimal' => '分數必須為數字',
            'greater_than_equal_to' => '分數不能小於0'
        ],
        'evidence_files' => [
            'valid_json' => '佐證文件必須為有效的JSON格式'
        ],
        'answered_by' => [
            'integer' => '回答人員ID必須為整數'
        ],
        'review_status' => [
            'in_list' => '審核狀態必須為：not_filled、pending、rejected、completed 之一'
        ],
        'reviewed_by' => [
            'integer' => '審核人員ID必須為整數'
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
     * 取得指定評估記錄的所有回答
     *
     * @param int $assessmentId 評估記錄ID
     * @param string|null $reviewStatus 篩選審核狀態
     * @param int|null $answeredBy 篩選回答人員
     * @return array 回答列表，包含題目和分類資訊
     */
    public function getResponsesByAssessment(int $assessmentId, ?string $reviewStatus = null, ?int $answeredBy = null): array
    {
        $builder = $this->select('
                question_responses.*,
                question_factors.description as factor_description,
                question_contents.is_required,
                question_contents.b_content,
                question_categories.category_name,
                question_topics.topic_name,
                question_factors.factor_name
            ')
            ->join('question_contents', 'question_contents.id = question_responses.question_content_id')
            ->join('question_categories', 'question_categories.id = question_contents.category_id', 'left')
            ->join('question_topics', 'question_topics.id = question_contents.topic_id', 'left')
            ->join('question_factors', 'question_factors.id = question_contents.factor_id', 'left')
            ->where('question_responses.assessment_id', $assessmentId);

        // 篩選審核狀態
        if ($reviewStatus) {
            $builder->where('question_responses.review_status', $reviewStatus);
        }

        // 篩選回答人員
        if ($answeredBy) {
            $builder->where('question_responses.answered_by', $answeredBy);
        }

        // 排序
        $builder->orderBy('question_categories.sort_order', 'ASC')
            ->orderBy('question_topics.sort_order', 'ASC')
            ->orderBy('question_factors.sort_order', 'ASC')
            ->orderBy('question_contents.sort_order', 'ASC');

        $results = $builder->findAll();

        // 處理每筆回答資料
        foreach ($results as &$result) {
            // 建構分離的回答物件 - 使用 snake_case 格式與前端一致
            $result['response_fields'] = [
                // C區域
                'riskEventChoice' => $result['c_risk_event_choice'] ?? null,
                'riskEventDescription' => $result['c_risk_event_description'] ?? null,
                // D區域
                'counterActionChoice' => $result['d_counter_action_choice'] ?? null,
                'counterActionDescription' => $result['d_counter_action_description'] ?? null,
                'counterActionCost' => $result['d_counter_action_cost'] ?? null,
                // E-1 風險描述
                'e1_risk_description' => $result['e1_risk_description'] ?? null,
                // E-2 風險財務影響評估
                'e2_risk_probability' => $result['e2_risk_probability'] ?? null,
                'e2_risk_impact' => $result['e2_risk_impact'] ?? null,
                'e2_risk_calculation' => $result['e2_risk_calculation'] ?? null,
                // F-1 機會描述
                'f1_opportunity_description' => $result['f1_opportunity_description'] ?? null,
                // F-2 機會財務影響評估
                'f2_opportunity_probability' => $result['f2_opportunity_probability'] ?? null,
                'f2_opportunity_impact' => $result['f2_opportunity_impact'] ?? null,
                'f2_opportunity_calculation' => $result['f2_opportunity_calculation'] ?? null,
                // G-1 對外負面衝擊
                'g1_negative_impact_level' => $result['g1_negative_impact_level'] ?? null,
                'g1_negative_impact_description' => $result['g1_negative_impact_description'] ?? null,
                // H-1 對外正面影響
                'h1_positive_impact_level' => $result['h1_positive_impact_level'] ?? null,
                'h1_positive_impact_description' => $result['h1_positive_impact_description'] ?? null
            ];

            // 解析 JSON 欄位
            if ($result['evidence_files']) {
                $result['evidence_files'] = json_decode($result['evidence_files'], true);
            }
        }

        return $results;
    }

    /**
     * 取得特定題目的回答
     *
     * @param int $assessmentId 評估記錄ID
     * @param int $questionContentId 題項內容ID
     * @return array|null 回答資料
     */
    public function getResponse(int $assessmentId, int $questionContentId): ?array
    {
        $result = $this->select('
                question_responses.*,
                question_contents.title as question_title,
                question_factors.description as factor_description,
                question_contents.scoring_method,
                question_contents.weight,
                question_contents.is_required,
                question_contents.b_content
            ')
            ->join('question_contents', 'question_contents.id = question_responses.question_content_id')
            ->join('question_factors', 'question_factors.id = question_contents.factor_id', 'left')
            ->where('question_responses.assessment_id', $assessmentId)
            ->where('question_responses.question_content_id', $questionContentId)
            ->first();

        if ($result) {
            // 建構分離的回答物件 - 使用 snake_case 格式與前端一致
            $result['response_fields'] = [
                // C區域
                'riskEventChoice' => $result['c_risk_event_choice'] ?? null,
                'riskEventDescription' => $result['c_risk_event_description'] ?? null,
                // D區域
                'counterActionChoice' => $result['d_counter_action_choice'] ?? null,
                'counterActionDescription' => $result['d_counter_action_description'] ?? null,
                'counterActionCost' => $result['d_counter_action_cost'] ?? null,
                // E-1 風險描述
                'e1_risk_description' => $result['e1_risk_description'] ?? null,
                // E-2 風險財務影響評估
                'e2_risk_probability' => $result['e2_risk_probability'] ?? null,
                'e2_risk_impact' => $result['e2_risk_impact'] ?? null,
                'e2_risk_calculation' => $result['e2_risk_calculation'] ?? null,
                // F-1 機會描述
                'f1_opportunity_description' => $result['f1_opportunity_description'] ?? null,
                // F-2 機會財務影響評估
                'f2_opportunity_probability' => $result['f2_opportunity_probability'] ?? null,
                'f2_opportunity_impact' => $result['f2_opportunity_impact'] ?? null,
                'f2_opportunity_calculation' => $result['f2_opportunity_calculation'] ?? null,
                // G-1 對外負面衝擊
                'g1_negative_impact_level' => $result['g1_negative_impact_level'] ?? null,
                'g1_negative_impact_description' => $result['g1_negative_impact_description'] ?? null,
                // H-1 對外正面影響
                'h1_positive_impact_level' => $result['h1_positive_impact_level'] ?? null,
                'h1_positive_impact_description' => $result['h1_positive_impact_description'] ?? null
            ];

            // 解析 JSON 欄位
            if ($result['evidence_files']) {
                $result['evidence_files'] = json_decode($result['evidence_files'], true);
            }
        }

        return $result;
    }

    /**
     * 儲存或更新回答（使用分離欄位儲存）
     *
     * @param int $assessmentId 評估記錄ID
     * @param int $questionContentId 題項內容ID
     * @param mixed $responseValue 回答值（可以是陣列或物件）
     * @param float|null $score 分數（可選，可以自動計算）
     * @param string|null $notes 備註
     * @param array|null $evidenceFiles 佐證文件陣列
     * @param int|null $answeredBy 回答人員ID
     * @return int|bool 新建立的ID或更新成功的布林值
     */
    public function saveResponse(
        int $assessmentId,
        int $questionContentId,
        $responseValue,
        ?float $score = null,
        ?string $notes = null,
        ?array $evidenceFiles = null,
        ?int $answeredBy = null
    ) {
        // 檢查是否已存在回答
        $existingResponse = $this->where('assessment_id', $assessmentId)
            ->where('question_content_id', $questionContentId)
            ->first();

        // 準備基本資料
        $data = [
            'assessment_id' => $assessmentId,
            'question_content_id' => $questionContentId,
            'score' => $score,
            'notes' => $notes,
            'evidence_files' => $evidenceFiles ? json_encode($evidenceFiles) : null,
            'answered_at' => date('Y-m-d H:i:s'),
            'answered_by' => $answeredBy,
            'review_status' => 'not_filled' // 預設為未填寫
        ];

        // 處理回答值 - 將其分離到各個欄位
        if (is_array($responseValue) || is_object($responseValue)) {
            $responseArray = (array)$responseValue;

            // 記錄收到的原始資料
            log_message('info', 'QuestionResponseModel::saveResponse - 收到的 responseArray: ' . json_encode($responseArray, JSON_UNESCAPED_UNICODE));

            // C區域
            $data['c_risk_event_choice'] = $responseArray['riskEventChoice'] ?? null;
            $data['c_risk_event_description'] = $responseArray['riskEventDescription'] ?? null;

            // D區域
            $data['d_counter_action_choice'] = $responseArray['counterActionChoice'] ?? null;
            $data['d_counter_action_description'] = $responseArray['counterActionDescription'] ?? null;
            $data['d_counter_action_cost'] = $responseArray['counterActionCost'] ?? null;

            // E-1區域 - 接受兩種格式以保持相容性
            $data['e1_risk_description'] = $responseArray['e1_risk_description'] ?? $responseArray['riskDescription'] ?? null;

            // E-2區域 - 接受兩種格式以保持相容性
            $data['e2_risk_probability'] = $responseArray['e2_risk_probability'] ?? $responseArray['riskProbability'] ?? null;
            $data['e2_risk_impact'] = $responseArray['e2_risk_impact'] ?? $responseArray['riskImpact'] ?? null;
            $data['e2_risk_calculation'] = $responseArray['e2_risk_calculation'] ?? $responseArray['riskCalculation'] ?? null;

            // F-1區域 - 接受兩種格式以保持相容性
            $data['f1_opportunity_description'] = $responseArray['f1_opportunity_description'] ?? $responseArray['opportunityDescription'] ?? null;

            // F-2區域 - 接受兩種格式以保持相容性
            $data['f2_opportunity_probability'] = $responseArray['f2_opportunity_probability'] ?? $responseArray['opportunityProbability'] ?? null;
            $data['f2_opportunity_impact'] = $responseArray['f2_opportunity_impact'] ?? $responseArray['opportunityImpact'] ?? null;
            $data['f2_opportunity_calculation'] = $responseArray['f2_opportunity_calculation'] ?? $responseArray['opportunityCalculation'] ?? null;

            // G-1區域 - 接受兩種格式以保持相容性
            $data['g1_negative_impact_level'] = $responseArray['g1_negative_impact_level'] ?? $responseArray['negativeImpactLevel'] ?? null;
            $data['g1_negative_impact_description'] = $responseArray['g1_negative_impact_description'] ?? $responseArray['negativeImpactDescription'] ?? null;

            // H-1區域 - 接受兩種格式以保持相容性
            $data['h1_positive_impact_level'] = $responseArray['h1_positive_impact_level'] ?? $responseArray['positiveImpactLevel'] ?? null;
            $data['h1_positive_impact_description'] = $responseArray['h1_positive_impact_description'] ?? $responseArray['positiveImpactDescription'] ?? null;

            // 記錄關鍵欄位的映射結果
            log_message('info', 'QuestionResponseModel::saveResponse - 欄位映射結果:');
            log_message('info', '  e1_risk_description: ' . ($data['e1_risk_description'] ?? '(null)'));
            log_message('info', '  e2_risk_probability: ' . ($data['e2_risk_probability'] ?? '(null)'));
            log_message('info', '  e2_risk_impact: ' . ($data['e2_risk_impact'] ?? '(null)'));
            log_message('info', '  e2_risk_calculation: ' . ($data['e2_risk_calculation'] ?? '(null)'));
            log_message('info', '  f1_opportunity_description: ' . ($data['f1_opportunity_description'] ?? '(null)'));
            log_message('info', '  f2_opportunity_probability: ' . ($data['f2_opportunity_probability'] ?? '(null)'));
            log_message('info', '  f2_opportunity_impact: ' . ($data['f2_opportunity_impact'] ?? '(null)'));
            log_message('info', '  f2_opportunity_calculation: ' . ($data['f2_opportunity_calculation'] ?? '(null)'));
        }

        if ($existingResponse) {
            // 更新現有回答
            unset($data['assessment_id']); // 不更新關聯欄位
            unset($data['question_content_id']);
            $result = $this->update($existingResponse['id'], $data);

            // 立即捕獲執行的 SQL
            $this->lastExecutedSQL = $this->db->getLastQuery()->getQuery();
            log_message('info', 'QuestionResponseModel::saveResponse - UPDATE SQL: ' . $this->lastExecutedSQL);

            return $result;
        } else {
            // 建立新回答
            $result = $this->insert($data);

            // 立即捕獲執行的 SQL
            $this->lastExecutedSQL = $this->db->getLastQuery()->getQuery();
            log_message('info', 'QuestionResponseModel::saveResponse - INSERT SQL: ' . $this->lastExecutedSQL);

            return $result;
        }
    }

    /**
     * 取得最後執行的 SQL（用於調試）
     *
     * @return string 最後執行的 SQL 語句
     */
    public function getLastExecutedSQL(): string
    {
        return $this->lastExecutedSQL;
    }

    /**
     * 根據評分方法自動計算分數
     *
     * @param mixed $responseValue 回答值
     * @param string $scoringMethod 評分方法
     * @param float $weight 權重
     * @return float|null 計算出的分數
     */
    public function calculateScore($responseValue, string $scoringMethod, float $weight = 1.0): ?float
    {
        if (empty($responseValue)) {
            return null;
        }

        // 根據評分方法處理回答值
        $value = is_array($responseValue) ? ($responseValue['value'] ?? null) : $responseValue;

        if ($value === null || $value === '') {
            return null;
        }

        $score = 0;

        switch ($scoringMethod) {
            case 'binary':
                // 二元評分：是=1分，否=0分
                $score = ($value === true || $value === 'yes' || $value === '1' || $value === 1) ? 1 : 0;
                break;

            case 'scale_1_5':
                // 1-5分量表
                $numericValue = (float)$value;
                $score = max(0, min(5, $numericValue));
                break;

            case 'scale_1_10':
                // 1-10分量表
                $numericValue = (float)$value;
                $score = max(0, min(10, $numericValue));
                break;

            case 'percentage':
                // 百分比評分
                $numericValue = (float)$value;
                $score = max(0, min(100, $numericValue));
                break;

            default:
                return null;
        }

        // 應用權重
        return $score * $weight;
    }

    /**
     * 批次儲存回答
     *
     * @param int $assessmentId 評估記錄ID
     * @param array $responses 回答資料陣列 [questionContentId => responseData]
     * @param int|null $answeredBy 回答人員ID
     * @return array 處理結果 ['success' => count, 'errors' => errors, 'sql_executed' => sql]
     */
    public function batchSaveResponses(int $assessmentId, array $responses, ?int $answeredBy = null): array
    {
        $successCount = 0;
        $errors = [];
        $sqlExecuted = [];

        foreach ($responses as $questionContentId => $responseData) {
            try {
                $result = $this->saveResponse(
                    $assessmentId,
                    (int)$questionContentId,
                    $responseData['response_value'] ?? null,
                    $responseData['score'] ?? null,
                    $responseData['notes'] ?? null,
                    $responseData['evidence_files'] ?? null,
                    $answeredBy
                );

                if ($result) {
                    $successCount++;

                    // 使用 getter 取得最後執行的 SQL
                    $sql = $this->getLastExecutedSQL();
                    if (!empty($sql)) {
                        $sqlExecuted[] = $sql;
                        log_message('info', 'QuestionResponseModel::batchSaveResponses - 收集 SQL: ' . $sql);
                    }
                } else {
                    $errors[] = "Failed to save response for question {$questionContentId}";
                }
            } catch (\Exception $e) {
                $errors[] = "Error saving question {$questionContentId}: " . $e->getMessage();
                log_message('error', 'QuestionResponseModel::batchSaveResponses - 錯誤: ' . $e->getMessage());
            }
        }

        $sqlString = implode(";\n\n", $sqlExecuted);
        log_message('info', 'QuestionResponseModel::batchSaveResponses - 最終 SQL 字串: ' . $sqlString);

        return [
            'success' => $successCount,
            'errors' => $errors,
            'sql_executed' => $sqlString // 合併所有 SQL 語句
        ];
    }

    /**
     * 更新審核狀態
     *
     * @param int $responseId 回答ID
     * @param string $status 審核狀態（pending, approved, rejected）
     * @param int|null $reviewedBy 審核人員ID
     * @param string|null $reviewNotes 審核備註
     * @return bool 是否成功
     */
    public function updateReviewStatus(int $responseId, string $status, ?int $reviewedBy = null, ?string $reviewNotes = null): bool
    {
        $data = [
            'review_status' => $status,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s')
        ];

        if ($reviewNotes) {
            $data['notes'] = $reviewNotes;
        }

        return $this->update($responseId, $data);
    }

    /**
     * 取得評估統計資料
     *
     * @param int $assessmentId 評估記錄ID
     * @return array 統計資料
     */
    public function getAssessmentStats(int $assessmentId): array
    {
        // 總回答數
        $totalResponses = $this->where('assessment_id', $assessmentId)->countAllResults();

        // 各審核狀態統計
        $pendingCount = $this->where('assessment_id', $assessmentId)
            ->where('review_status', 'pending')
            ->countAllResults();

        $approvedCount = $this->where('assessment_id', $assessmentId)
            ->where('review_status', 'approved')
            ->countAllResults();

        $rejectedCount = $this->where('assessment_id', $assessmentId)
            ->where('review_status', 'rejected')
            ->countAllResults();

        // 平均分數
        $scoreStats = $this->selectAvg('score')
            ->selectMax('score', 'max_score')
            ->selectMin('score', 'min_score')
            ->where('assessment_id', $assessmentId)
            ->where('score IS NOT NULL')
            ->get()
            ->getRow();

        // 總分和權重總分
        $totalScoreQuery = $this->db->query("
            SELECT
                SUM(qr.score) as total_score,
                SUM(qc.weight * COALESCE(qr.score, 0)) as weighted_total_score,
                SUM(qc.weight) as total_weight
            FROM question_responses qr
            JOIN question_contents qc ON qc.id = qr.question_content_id
            WHERE qr.assessment_id = ? AND qr.score IS NOT NULL
        ", [$assessmentId]);

        $totalScoreData = $totalScoreQuery->getRow();

        return [
            'total_responses' => (int)$totalResponses,
            'pending_count' => (int)$pendingCount,
            'approved_count' => (int)$approvedCount,
            'rejected_count' => (int)$rejectedCount,
            'average_score' => $scoreStats->score ? round((float)$scoreStats->score, 2) : 0,
            'max_score' => $scoreStats->max_score ? (float)$scoreStats->max_score : 0,
            'min_score' => $scoreStats->min_score ? (float)$scoreStats->min_score : 0,
            'total_score' => $totalScoreData->total_score ? (float)$totalScoreData->total_score : 0,
            'weighted_total_score' => $totalScoreData->weighted_total_score ? (float)$totalScoreData->weighted_total_score : 0,
            'total_weight' => $totalScoreData->total_weight ? (float)$totalScoreData->total_weight : 0,
            'completion_rate' => $totalResponses > 0 ? round(($approvedCount / $totalResponses) * 100, 2) : 0
        ];
    }

    /**
     * 取得分類別統計
     *
     * @param int $assessmentId 評估記錄ID
     * @return array 各分類的統計資料
     */
    public function getCategoryStats(int $assessmentId): array
    {
        $query = $this->db->query("
            SELECT
                qc.category_name,
                COUNT(qr.id) as response_count,
                AVG(qr.score) as avg_score,
                SUM(qr.score) as total_score,
                SUM(qcontent.weight) as total_weight
            FROM question_responses qr
            JOIN question_contents qcontent ON qcontent.id = qr.question_content_id
            JOIN question_categories qc ON qc.id = qcontent.category_id
            WHERE qr.assessment_id = ? AND qr.score IS NOT NULL
            GROUP BY qc.id, qc.category_name
            ORDER BY qc.sort_order ASC
        ", [$assessmentId]);

        $results = $query->getResultArray();

        foreach ($results as &$result) {
            $result['response_count'] = (int)$result['response_count'];
            $result['avg_score'] = $result['avg_score'] ? round((float)$result['avg_score'], 2) : 0;
            $result['total_score'] = $result['total_score'] ? (float)$result['total_score'] : 0;
            $result['total_weight'] = $result['total_weight'] ? (float)$result['total_weight'] : 0;
        }

        return $results;
    }

    /**
     * 刪除評估記錄的所有回答
     *
     * @param int $assessmentId 評估記錄ID
     * @return bool 是否成功
     */
    public function deleteAllByAssessment(int $assessmentId): bool
    {
        return $this->where('assessment_id', $assessmentId)->delete();
    }

    /**
     * 刪除特定題目的回答
     *
     * @param int $questionContentId 題項內容ID
     * @return bool 是否成功
     */
    public function deleteByQuestionContent(int $questionContentId): bool
    {
        return $this->where('question_content_id', $questionContentId)->delete();
    }
}