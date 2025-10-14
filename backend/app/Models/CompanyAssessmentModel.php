<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyAssessmentModel extends Model
{
    protected $table = 'company_assessments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'company_id',
        'template_id',
        'template_version',
        'name',
        'assessment_year',
        'status',
        'copied_from',
        'include_questions',
        'include_results',
        'total_score',
        'percentage_score',
        'risk_level',
        'notes'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'company_id' => 'required|max_length[50]',
        'template_id' => 'required|integer|is_not_unique[risk_assessment_templates.id]',
        'assessment_year' => 'required|integer|greater_than[1900]|less_than_equal_to[2040]',
        'status' => 'permit_empty|in_list[pending,in_progress,completed,archived]',
        'copied_from' => 'permit_empty|integer',
        'include_questions' => 'permit_empty|in_list[0,1]',
        'include_results' => 'permit_empty|in_list[0,1]',
        'total_score' => 'permit_empty|decimal',
        'percentage_score' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'risk_level' => 'permit_empty|in_list[low,medium,high,critical]',
        'notes' => 'permit_empty'
    ];

    protected $validationMessages = [
        'company_id' => [
            'required' => '公司ID為必填項目',
            'max_length' => '公司ID不能超過50個字符'
        ],
        'template_id' => [
            'required' => '風險評估範本ID為必填項目',
            'integer' => '風險評估範本ID必須為整數',
            'is_not_unique' => '指定的風險評估範本不存在'
        ],
        'assessment_year' => [
            'required' => '評估年度為必填項目',
            'integer' => '評估年度必須為整數',
            'greater_than' => '評估年度不能小於1900',
            'less_than_equal_to' => '評估年度不能超過未來10年'
        ],
        'status' => [
            'in_list' => '狀態必須為：pending、in_progress、completed、archived 之一'
        ],
        'copied_from' => [
            'integer' => '複製來源ID必須為整數'
        ],
        'include_questions' => [
            'in_list' => '是否包含題目必須為0或1'
        ],
        'include_results' => [
            'in_list' => '是否包含結果必須為0或1'
        ],
        'total_score' => [
            'decimal' => '總分必須為數字'
        ],
        'percentage_score' => [
            'decimal' => '百分比分數必須為數字',
            'greater_than_equal_to' => '百分比分數不能小於0',
            'less_than_equal_to' => '百分比分數不能大於100'
        ],
        'risk_level' => [
            'in_list' => '風險等級必須為：low、medium、high、critical 之一'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;

    /**
     * 取得指定公司的評估項目列表
     */
    public function getAssessmentsByCompany($companyId, $search = null, $year = null, $status = null, $page = 1, $limit = 20, $sort = 'created_at', $order = 'desc')
    {
        $builder = $this->select('
                company_assessments.*,
                risk_assessment_templates.version_name as template_version_name
            ')
            ->join('risk_assessment_templates', 'risk_assessment_templates.id = company_assessments.template_id', 'left')
            ->where('company_assessments.company_id', $companyId);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('risk_assessment_templates.version_name', $search)
                ->orLike('company_assessments.name', $search)
                ->orLike('company_assessments.notes', $search)
                ->groupEnd();
        }

        if (!empty($year)) {
            $builder->where('company_assessments.assessment_year', $year);
        }

        if (!empty($status)) {
            $builder->where('company_assessments.status', $status);
        }

        $allowedSorts = ['id', 'assessment_year', 'status', 'created_at', 'updated_at'];
        if (in_array($sort, $allowedSorts)) {
            $builder->orderBy("company_assessments.$sort", $order);
        }

        return $builder;
    }

    /**
     * 取得評估項目詳細資訊（包含範本資訊）
     */
    public function getAssessmentWithTemplate($id)
    {
        return $this->select('
                company_assessments.*,
                risk_assessment_templates.version_name,
                risk_assessment_templates.description as template_description
            ')
            ->join('risk_assessment_templates', 'risk_assessment_templates.id = company_assessments.template_id', 'left')
            ->where('company_assessments.id', $id)
            ->first();
    }

    /**
     * 複製評估項目（包含完整的題項資料和結果）
     */
    public function copyAssessment($originalId, $companyId, $copyOptions = [])
    {
        log_message('info', "=== CompanyAssessmentModel::copyAssessment START ===");
        log_message('info', "Original Assessment ID: {$originalId}");
        log_message('info', "Target Company ID: {$companyId}");
        log_message('info', "Copy Options: " . json_encode($copyOptions));

        $original = $this->find($originalId);
        if (!$original) {
            log_message('error', "Original assessment not found: {$originalId}");
            return false;
        }

        $newAssessment = [
            'company_id' => $companyId,
            'template_id' => $original['template_id'],
            'template_version' => $original['template_version'],
            'name' => $original['name'] ?? $original['template_version'],
            'assessment_year' => $original['assessment_year'],
            'status' => 'pending',
            'copied_from' => $originalId,
            'include_questions' => $copyOptions['include_questions'] ?? true,
            'include_results' => $copyOptions['include_results'] ?? false,
            'notes' => $original['notes'] ?? null
        ];

        log_message('info', "Include Questions: " . ($includeQuestions ? 'YES' : 'NO'));
        log_message('info', "Include Results: " . ($includeResults ? 'YES' : 'NO'));

        // 開始資料庫事務
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. 創建新的評估記錄
            $newAssessment = [
                'company_id' => $companyId,
                'template_id' => $original['template_id'],
                'template_version' => $original['template_version'],
                'assessment_year' => $original['assessment_year'],
                'status' => 'pending',
                'copied_from' => $originalId,
                'include_questions' => $includeQuestions ? 1 : 0,
                'include_results' => $includeResults ? 1 : 0,
                'notes' => $original['notes'] ?? null
            ];

            $newAssessmentId = $this->insert($newAssessment);
            if (!$newAssessmentId) {
                throw new \Exception('建立新評估記錄失敗');
            }

            log_message('info', "New assessment created with ID: {$newAssessmentId}");

            // 2. 如果選擇複製題目，則複製所有題項資料
            if ($includeQuestions) {
                log_message('info', "Starting to copy question data...");

                // 載入所需的 Models
                $categoryModel = new \App\Models\QuestionManagement\QuestionCategoryModel();
                $topicModel = new \App\Models\QuestionManagement\QuestionTopicModel();
                $factorModel = new \App\Models\QuestionManagement\QuestionFactorModel();
                $contentModel = new \App\Models\QuestionManagement\QuestionContentModel();
                $probabilityScaleModel = new \App\Models\QuestionManagement\QuestionProbabilityScaleModel();
                $impactScaleModel = new \App\Models\QuestionManagement\QuestionImpactScaleModel();

                // 2.1 複製風險分類
                $categories = $categoryModel->where('assessment_id', $originalId)->findAll();
                log_message('info', "Found " . count($categories) . " categories to copy");

                $categoryIdMapping = [];
                foreach ($categories as $category) {
                    $newCategory = [
                        'assessment_id' => $newAssessmentId,
                        'category_name' => $category['category_name'],
                        'category_code' => $category['category_code'] ?? null,
                        'description' => $category['description'] ?? null,
                        'sort_order' => $category['sort_order'] ?? 0,
                        'copied_from_template_category' => $category['copied_from_template_category'] ?? null
                    ];

                    $newCategoryId = $categoryModel->insert($newCategory);
                    if ($newCategoryId) {
                        $categoryIdMapping[$category['id']] = $newCategoryId;
                    }
                }
                log_message('info', "Category mapping: " . json_encode($categoryIdMapping));

                // 2.2 複製風險主題
                $topics = $topicModel->where('assessment_id', $originalId)->findAll();
                log_message('info', "Found " . count($topics) . " topics to copy");

                $topicIdMapping = [];
                foreach ($topics as $topic) {
                    $newTopic = [
                        'assessment_id' => $newAssessmentId,
                        'category_id' => $categoryIdMapping[$topic['category_id']] ?? null,
                        'topic_name' => $topic['topic_name'],
                        'topic_code' => $topic['topic_code'] ?? null,
                        'description' => $topic['description'] ?? null,
                        'sort_order' => $topic['sort_order'] ?? 0,
                        'copied_from_template_topic' => $topic['copied_from_template_topic'] ?? null
                    ];

                    $newTopicId = $topicModel->insert($newTopic);
                    if ($newTopicId) {
                        $topicIdMapping[$topic['id']] = $newTopicId;
                    }
                }
                log_message('info', "Topic mapping: " . json_encode($topicIdMapping));

                // 2.3 複製風險因子
                $factors = $factorModel->where('assessment_id', $originalId)->findAll();
                log_message('info', "Found " . count($factors) . " factors to copy");

                $factorIdMapping = [];
                foreach ($factors as $factor) {
                    $newFactor = [
                        'assessment_id' => $newAssessmentId,
                        'category_id' => $categoryIdMapping[$factor['category_id']] ?? null,
                        'topic_id' => $topicIdMapping[$factor['topic_id']] ?? null,
                        'factor_name' => $factor['factor_name'],
                        'factor_code' => $factor['factor_code'] ?? null,
                        'description' => $factor['description'] ?? null,
                        'sort_order' => $factor['sort_order'] ?? 0,
                        'risk_type' => $factor['risk_type'] ?? null,
                        'copied_from_template_factor' => $factor['copied_from_template_factor'] ?? null
                    ];

                    $newFactorId = $factorModel->insert($newFactor);
                    if ($newFactorId) {
                        $factorIdMapping[$factor['id']] = $newFactorId;
                    }
                }
                log_message('info', "Factor mapping: " . json_encode($factorIdMapping));

                // 2.4 複製題項內容
                $contents = $contentModel->where('assessment_id', $originalId)->findAll();
                log_message('info', "Found " . count($contents) . " contents to copy");

                $contentIdMapping = [];
                foreach ($contents as $content) {
                    $newContent = [
                        'assessment_id' => $newAssessmentId,
                        'category_id' => $categoryIdMapping[$content['category_id']] ?? null,
                        'topic_id' => $topicIdMapping[$content['topic_id']] ?? null,
                        'factor_id' => $factorIdMapping[$content['factor_id']] ?? null,
                        'question_text' => $content['question_text'] ?? null,
                        'description' => $content['description'] ?? null,
                        'sort_order' => $content['sort_order'] ?? 0,
                        'question_type' => $content['question_type'] ?? 'text',
                        'b_reference_text' => $content['b_reference_text'] ?? null,
                        'c_risk_event_enabled' => $content['c_risk_event_enabled'] ?? 0,
                        'd_counter_action_enabled' => $content['d_counter_action_enabled'] ?? 0,
                        'e1_risk_enabled' => $content['e1_risk_enabled'] ?? 0,
                        'e2_risk_assessment_enabled' => $content['e2_risk_assessment_enabled'] ?? 0,
                        'e2_probability_placeholder' => $content['e2_probability_placeholder'] ?? null,
                        'e2_impact_placeholder' => $content['e2_impact_placeholder'] ?? null,
                        'e2_calculation_placeholder' => $content['e2_calculation_placeholder'] ?? null,
                        'f1_opportunity_enabled' => $content['f1_opportunity_enabled'] ?? 0,
                        'f2_opportunity_assessment_enabled' => $content['f2_opportunity_assessment_enabled'] ?? 0,
                        'f2_probability_placeholder' => $content['f2_probability_placeholder'] ?? null,
                        'f2_impact_placeholder' => $content['f2_impact_placeholder'] ?? null,
                        'f2_calculation_placeholder' => $content['f2_calculation_placeholder'] ?? null,
                        'g1_negative_impact_enabled' => $content['g1_negative_impact_enabled'] ?? 0,
                        'h1_positive_impact_enabled' => $content['h1_positive_impact_enabled'] ?? 0,
                        'copied_from_template_content' => $content['copied_from_template_content'] ?? null
                    ];

                    $newContentId = $contentModel->insert($newContent);
                    if ($newContentId) {
                        $contentIdMapping[$content['id']] = $newContentId;
                    }
                }
                log_message('info', "Content mapping: " . json_encode($contentIdMapping));

                // 2.5 複製量表（Probability Scales）
                $probabilityScales = $probabilityScaleModel->where('assessment_id', $originalId)->findAll();
                log_message('info', "Found " . count($probabilityScales) . " probability scales to copy");

                foreach ($probabilityScales as $scale) {
                    $newScale = [
                        'assessment_id' => $newAssessmentId,
                        'description_text' => $scale['description_text'] ?? null,
                        'show_description' => $scale['show_description'] ?? 0,
                        'selected_display_column' => $scale['selected_display_column'] ?? null,
                        'copied_from_template_scale' => $scale['copied_from_template_scale'] ?? null
                    ];

                    $newScaleId = $probabilityScaleModel->insert($newScale);

                    // 複製量表的行和列
                    if ($newScaleId) {
                        $this->copyScaleRowsAndColumns($scale['id'], $newScaleId, 'probability');
                    }
                }

                // 2.6 複製量表（Impact Scales）
                $impactScales = $impactScaleModel->where('assessment_id', $originalId)->findAll();
                log_message('info', "Found " . count($impactScales) . " impact scales to copy");

                foreach ($impactScales as $scale) {
                    $newScale = [
                        'assessment_id' => $newAssessmentId,
                        'selected_display_column' => $scale['selected_display_column'] ?? null,
                        'copied_from_template_scale' => $scale['copied_from_template_scale'] ?? null
                    ];

                    $newScaleId = $impactScaleModel->insert($newScale);

                    // 複製量表的行和列
                    if ($newScaleId) {
                        $this->copyScaleRowsAndColumns($scale['id'], $newScaleId, 'impact');
                    }
                }

                log_message('info', "Question data copied successfully");
            }

            // 3. 如果選擇複製結果，則複製所有回答資料
            if ($includeResults && !empty($contentIdMapping)) {
                log_message('info', "Starting to copy response data...");

                $responseModel = new \App\Models\QuestionManagement\QuestionResponseModel();
                $responses = $responseModel->where('assessment_id', $originalId)->findAll();
                log_message('info', "Found " . count($responses) . " responses to copy");

                foreach ($responses as $response) {
                    $originalContentId = $response['question_content_id'];
                    $newContentId = $contentIdMapping[$originalContentId] ?? null;

                    if ($newContentId) {
                        $newResponse = [
                            'assessment_id' => $newAssessmentId,
                            'question_content_id' => $newContentId,
                            'answered_by' => $response['answered_by'],
                            'c_risk_event_choice' => $response['c_risk_event_choice'] ?? null,
                            'c_risk_event_description' => $response['c_risk_event_description'] ?? null,
                            'd_counter_action_choice' => $response['d_counter_action_choice'] ?? null,
                            'd_counter_action_description' => $response['d_counter_action_description'] ?? null,
                            'd_counter_action_cost' => $response['d_counter_action_cost'] ?? null,
                            'e1_risk_description' => $response['e1_risk_description'] ?? null,
                            'e2_risk_probability' => $response['e2_risk_probability'] ?? null,
                            'e2_risk_impact' => $response['e2_risk_impact'] ?? null,
                            'e2_risk_calculation' => $response['e2_risk_calculation'] ?? null,
                            'f1_opportunity_description' => $response['f1_opportunity_description'] ?? null,
                            'f2_opportunity_probability' => $response['f2_opportunity_probability'] ?? null,
                            'f2_opportunity_impact' => $response['f2_opportunity_impact'] ?? null,
                            'f2_opportunity_calculation' => $response['f2_opportunity_calculation'] ?? null,
                            'g1_negative_impact_level' => $response['g1_negative_impact_level'] ?? null,
                            'g1_negative_impact_description' => $response['g1_negative_impact_description'] ?? null,
                            'h1_positive_impact_level' => $response['h1_positive_impact_level'] ?? null,
                            'h1_positive_impact_description' => $response['h1_positive_impact_description'] ?? null,
                            'review_status' => $response['review_status'] ?? 'pending',
                            'answered_at' => $response['answered_at'] ?? null
                        ];

                        $responseModel->insert($newResponse);
                    }
                }

                log_message('info', "Response data copied successfully");
            }

            // 完成事務
            $db->transComplete();

            if ($db->transStatus() === false) {
                log_message('error', "Transaction failed during assessment copy");
                throw new \Exception('資料庫事務失敗');
            }

            log_message('info', "=== CompanyAssessmentModel::copyAssessment END: SUCCESS ===");
            return $newAssessmentId;

        } catch (\Exception $e) {
            log_message('error', "Error during assessment copy: " . $e->getMessage());
            log_message('error', "Stack trace: " . $e->getTraceAsString());
            $db->transRollback();
            return false;
        }
    }

    /**
     * 複製量表的行和列
     */
    private function copyScaleRowsAndColumns($originalScaleId, $newScaleId, $scaleType)
    {
        if ($scaleType === 'probability') {
            $rowModel = new \App\Models\QuestionManagement\QuestionProbabilityScaleRowModel();
            $columnModel = new \App\Models\QuestionManagement\QuestionProbabilityScaleColumnModel();
        } else {
            $rowModel = new \App\Models\QuestionManagement\QuestionImpactScaleRowModel();
            $columnModel = new \App\Models\QuestionManagement\QuestionImpactScaleColumnModel();
        }

        // 複製行
        $rows = $rowModel->where('scale_id', $originalScaleId)->findAll();
        foreach ($rows as $row) {
            if ($scaleType === 'probability') {
                $newRow = [
                    'scale_id' => $newScaleId,
                    'probability' => $row['probability'] ?? null,
                    'score_range' => $row['score_range'] ?? null,
                    'dynamic_fields' => $row['dynamic_fields'] ?? null,
                    'sort_order' => $row['sort_order'] ?? 0
                ];
            } else {
                $newRow = [
                    'scale_id' => $newScaleId,
                    'impact_level' => $row['impact_level'] ?? null,
                    'score_range' => $row['score_range'] ?? null,
                    'dynamic_fields' => $row['dynamic_fields'] ?? null,
                    'sort_order' => $row['sort_order'] ?? 0
                ];
            }
            $rowModel->insert($newRow);
        }

        // 複製列
        $columns = $columnModel->where('scale_id', $originalScaleId)->findAll();
        foreach ($columns as $column) {
            if ($scaleType === 'probability') {
                $newColumn = [
                    'scale_id' => $newScaleId,
                    'column_id' => $column['column_id'],
                    'name' => $column['name'],
                    'removable' => $column['removable'] ?? 1,
                    'sort_order' => $column['sort_order'] ?? 0
                ];
            } else {
                $newColumn = [
                    'scale_id' => $newScaleId,
                    'column_id' => $column['column_id'],
                    'name' => $column['name'],
                    'amount_note' => $column['amount_note'] ?? null,
                    'removable' => $column['removable'] ?? 1,
                    'sort_order' => $column['sort_order'] ?? 0
                ];
            }
            $columnModel->insert($newColumn);
        }
    }

    /**
     * 檢查公司是否存在指定年度的評估項目
     */
    public function hasAssessmentForYear($companyId, $year, $templateId = null)
    {
        $builder = $this->where('company_id', $companyId)
            ->where('assessment_year', $year);

        if ($templateId) {
            $builder->where('template_id', $templateId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * 取得公司評估統計資訊
     */
    public function getAssessmentStats($companyId)
    {
        $total = $this->where('company_id', $companyId)->countAllResults();
        $pending = $this->where('company_id', $companyId)->where('status', 'pending')->countAllResults();
        $inProgress = $this->where('company_id', $companyId)->where('status', 'in_progress')->countAllResults();
        $completed = $this->where('company_id', $companyId)->where('status', 'completed')->countAllResults();
        $archived = $this->where('company_id', $companyId)->where('status', 'archived')->countAllResults();

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'archived' => $archived
        ];
    }

    /**
     * 更新評估狀態
     */
    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * 更新評估分數和風險等級
     */
    public function updateScoreAndRisk($id, $totalScore, $percentageScore, $riskLevel)
    {
        return $this->update($id, [
            'total_score' => $totalScore,
            'percentage_score' => $percentageScore,
            'risk_level' => $riskLevel
        ]);
    }
}