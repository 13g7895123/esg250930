<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use App\Controllers\Api\BaseController;
use App\Models\CompanyAssessmentModel;
use App\Models\RiskAssessment\RiskAssessmentTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;

class CompanyAssessmentController extends BaseController
{
    protected $assessmentModel;
    protected $templateModel;

    public function __construct()
    {
        $this->assessmentModel = new CompanyAssessmentModel();
        $this->templateModel = new RiskAssessmentTemplateModel();
    }

    /**
     * Get assessments for a specific company
     * GET /api/v1/risk-assessment/company-assessments/company/{companyId}
     */
    public function getByCompany($companyId = null)
    {
        try {
            if (!$companyId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '公司ID為必填項目'
                ]);
            }

            log_message('info', "CompanyAssessment getByCompany called with companyId: {$companyId} (type: " . gettype($companyId) . ")");

            $search = $this->request->getGet('search');
            $year = $this->request->getGet('year');
            $status = $this->request->getGet('status');
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 20);
            $sort = $this->request->getGet('sort') ?? 'created_at';
            $order = $this->request->getGet('order') ?? 'desc';

            $builder = $this->assessmentModel->getAssessmentsByCompany(
                $companyId, $search, $year, $status, $page, $limit, $sort, $order
            );

            // Get total count first (with false to preserve builder state)
            $total = $builder->countAllResults(false);

            // Get paginated results - use findAll() instead of paginate() after countAllResults()
            $offset = ($page - 1) * $limit;
            $assessments = $builder->limit($limit, $offset)->findAll();

            // Get the complete SQL query with bound parameters
            $db = \Config\Database::connect();
            $rawSql = $db->getLastQuery()->getQuery();
            // Clean up SQL: remove newlines and extra spaces for direct use
            $sql = preg_replace('/\s+/', ' ', str_replace(["\n", "\r", "\t"], ' ', $rawSql));

            log_message('info', "CompanyAssessment getByCompany found {$total} assessments for company {$companyId}");
            log_message('info', "CompanyAssessment getByCompany returned " . count($assessments) . " assessments");
            log_message('info', "CompanyAssessment getByCompany SQL: " . $sql);

            $pagination = [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'assessments' => $assessments,
                    'pagination' => $pagination
                ],
                'debug' => [
                    'sql' => $sql,
                    'params' => [
                        'company_id' => $companyId,
                        'company_id_type' => gettype($companyId),
                        'search' => $search,
                        'year' => $year,
                        'status' => $status,
                        'page' => $page,
                        'limit' => $limit,
                        'sort' => $sort,
                        'order' => $order
                    ],
                    'total_found' => $total,
                    'returned_count' => count($assessments)
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CompanyAssessment getByCompany error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得公司評估列表失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all assessments list
     * GET /api/v1/risk-assessment/company-assessments
     */
    public function index()
    {
        try {
            $search = $this->request->getGet('search');
            $companyId = $this->request->getGet('company_id');
            $year = $this->request->getGet('year');
            $status = $this->request->getGet('status');
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 20);
            $sort = $this->request->getGet('sort') ?? 'created_at';
            $order = $this->request->getGet('order') ?? 'desc';

            $builder = $this->assessmentModel->select('
                    company_assessments.*,
                    risk_assessment_templates.version_name as template_version_name
                ')
                ->join('risk_assessment_templates', 'risk_assessment_templates.id = company_assessments.template_id', 'left');

            if (!empty($search)) {
                $builder->groupStart()
                    ->like('risk_assessment_templates.version_name', $search)
                    ->orLike('company_assessments.notes', $search)
                    ->groupEnd();
            }

            if (!empty($companyId)) {
                $builder->where('company_assessments.company_id', $companyId);
            }

            if (!empty($year)) {
                $builder->where('company_assessments.assessment_year', $year);
            }

            if (!empty($status)) {
                $builder->where('company_assessments.status', $status);
            }

            $allowedSorts = ['id', 'company_id', 'assessment_year', 'status', 'created_at', 'updated_at'];
            if (in_array($sort, $allowedSorts)) {
                $builder->orderBy("company_assessments.$sort", $order);
            }

            $total = $builder->countAllResults(false);
            $assessments = $builder->paginate($limit, 'default', $page);

            $pagination = [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'assessments' => $assessments,
                    'pagination' => $pagination
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CompanyAssessment index error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得評估列表失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single assessment details
     * GET /api/v1/risk-assessment/company-assessments/{id}
     */
    public function show($id = null)
    {
        try {
            $assessment = $this->assessmentModel->getAssessmentWithTemplate($id);

            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '評估項目不存在'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $assessment
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CompanyAssessment show error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得評估詳細資訊失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new assessment for company
     * POST /api/v1/risk-assessment/company-assessments
     */
    public function create()
    {
        log_message('info', '=== CompanyAssessment CREATE START ===');

        try {
            $input = $this->request->getJSON(true) ?: $this->request->getPost();
            log_message('info', 'CompanyAssessment create input: ' . json_encode($input));

            $rules = [
                'company_id' => 'required|max_length[50]',
                'template_id' => 'required|integer|is_not_unique[risk_assessment_templates.id]',
                'assessment_year' => 'required|integer|greater_than[1900]|less_than_equal_to[2040]',
                'template_version' => 'permit_empty|max_length[255]',
                'status' => 'permit_empty|in_list[pending,in_progress,completed,archived]',
                'notes' => 'permit_empty|string'
            ];

            if (!$this->validate($rules, $input)) {
                log_message('error', 'CompanyAssessment create validation failed: ' . json_encode($this->validator->getErrors()));
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            log_message('info', 'CompanyAssessment create validation passed');

            // Check if assessment already exists for this company, template and year
            log_message('info', "Checking if assessment exists for company: {$input['company_id']}, year: {$input['assessment_year']}, template: {$input['template_id']}");
            if ($this->assessmentModel->hasAssessmentForYear(
                $input['company_id'],
                $input['assessment_year'],
                $input['template_id']
            )) {
                log_message('warning', 'Assessment already exists for this company, template and year');
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => '該公司在此年度已存在相同範本的評估項目'
                ]);
            }

            // Get template version name if not provided
            $templateVersionName = $input['template_version'] ?? null;
            if (!$templateVersionName) {
                log_message('info', "Getting template version name for template ID: {$input['template_id']}");
                $template = $this->templateModel->find($input['template_id']);
                if ($template) {
                    $templateVersionName = $template['version_name'];
                    log_message('info', "Found template version name: {$templateVersionName}");
                } else {
                    log_message('warning', "Template not found for ID: {$input['template_id']}");
                }
            }

            $data = [
                'company_id' => $input['company_id'],
                'template_id' => $input['template_id'],
                'template_version' => $templateVersionName,
                'assessment_year' => $input['assessment_year'],
                'status' => $input['status'] ?? 'pending',
                'notes' => $input['notes'] ?? null
            ];

            log_message('info', 'Inserting assessment data: ' . json_encode($data));
            log_message('info', "company_id type: " . gettype($data['company_id']) . ", value: '{$data['company_id']}'");
            $assessmentId = $this->assessmentModel->insert($data);
            log_message('info', "Assessment inserted with ID: {$assessmentId}");

            if (!$assessmentId) {
                log_message('error', 'Failed to insert assessment. Errors: ' . json_encode($this->assessmentModel->errors()));
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '建立評估項目失敗',
                    'errors' => $this->assessmentModel->errors()
                ]);
            }

            log_message('info', "=== STARTING TEMPLATE SYNC FOR ASSESSMENT {$assessmentId} ===");

            // 自動同步範本架構到題項管理
            try {
                log_message('info', "Calling autoSyncTemplateStructure for assessment {$assessmentId}, template {$input['template_id']}");
                $this->autoSyncTemplateStructure($assessmentId, $input['template_id']);
                log_message('info', "Assessment {$assessmentId}: Template structure synced successfully");
            } catch (\Exception $e) {
                // 記錄錯誤但不影響評估建立
                log_message('error', "Assessment {$assessmentId}: Failed to sync template structure - " . $e->getMessage());
                log_message('error', "Template sync error stack trace: " . $e->getTraceAsString());
            }

            log_message('info', "Getting assessment with template for ID: {$assessmentId}");
            $assessment = $this->assessmentModel->getAssessmentWithTemplate($assessmentId);

            log_message('info', "=== CompanyAssessment CREATE COMPLETED FOR ASSESSMENT {$assessmentId} ===");

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '評估項目建立成功',
                'data' => $assessment
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CompanyAssessment create error: ' . $e->getMessage());
            log_message('error', 'CompanyAssessment create error stack trace: ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '建立評估項目失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update assessment
     * PUT /api/v1/risk-assessment/company-assessments/{id}
     */
    public function update($id = null)
    {
        try {
            log_message('info', '=== CompanyAssessmentController::update 被呼叫 ===');
            log_message('info', 'Assessment ID: ' . ($id ?? 'null'));
            log_message('info', 'Request Time: ' . date('Y-m-d H:i:s'));

            $assessment = $this->assessmentModel->find($id);
            if (!$assessment) {
                log_message('warning', 'Assessment 不存在: ' . $id);
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '評估項目不存在'
                ]);
            }

            log_message('info', '原始 Assessment 資料: ' . json_encode($assessment));

            $input = $this->request->getJSON(true);
            if (empty($input)) {
                $input = $this->request->getRawInput();
            }
            if (empty($input)) {
                $input = $this->request->getPost();
            }

            $rules = [
                'template_id' => 'permit_empty|integer|is_not_unique[risk_assessment_templates.id]',
                'assessment_year' => 'permit_empty|integer|greater_than[1900]|less_than_equal_to[2040]',
                'template_version' => 'permit_empty|max_length[255]',
                'status' => 'permit_empty|in_list[pending,in_progress,completed,archived]',
                'total_score' => 'permit_empty|decimal',
                'percentage_score' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
                'risk_level' => 'permit_empty|in_list[low,medium,high,critical]',
                'notes' => 'permit_empty|string'
            ];
            
            if (!$this->validate($rules, $input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $data = array_intersect_key($input, array_flip([
                'template_id', 'template_version', 'assessment_year', 'status',
                'total_score', 'percentage_score', 'risk_level', 'notes'
            ]));

            log_message('info', '請求輸入資料: ' . json_encode($input));
            log_message('info', '準備更新的資料: ' . json_encode($data));

            // 檢測範本是否變更
            $templateChanged = false;
            if (isset($data['template_id']) && $data['template_id'] != $assessment['template_id']) {
                $templateChanged = true;
                log_message('info', '檢測到範本變更！');
                log_message('info', '舊範本ID: ' . $assessment['template_id']);
                log_message('info', '新範本ID: ' . $data['template_id']);
            } else {
                log_message('info', '範本未變更（舊: ' . ($assessment['template_id'] ?? 'null') . '，新: ' . ($data['template_id'] ?? 'null') . '）');
            }

            $success = $this->assessmentModel->update($id, $data);

            if (!$success) {
                log_message('error', '更新 Assessment 失敗: ' . json_encode($this->assessmentModel->errors()));
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新評估項目失敗',
                    'errors' => $this->assessmentModel->errors()
                ]);
            }

            log_message('info', 'Assessment 更新成功');

            $updatedAssessment = $this->assessmentModel->getAssessmentWithTemplate($id);

            log_message('info', '更新後的 Assessment 資料: ' . json_encode($updatedAssessment));

            return $this->response->setJSON([
                'success' => true,
                'message' => '評估項目更新成功',
                'data' => $updatedAssessment,
                'template_changed' => $templateChanged
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CompanyAssessment update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新評估項目失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete assessment
     * DELETE /api/v1/risk-assessment/company-assessments/{id}
     */
    public function delete($id = null)
    {
        try {
            $assessment = $this->assessmentModel->find($id);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '評估項目不存在'
                ]);
            }

            $success = $this->assessmentModel->delete($id);
            
            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '刪除評估項目失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '評估項目刪除成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CompanyAssessment delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除評估項目失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Copy assessment
     * POST /api/v1/risk-assessment/company-assessments/{id}/copy
     */
    public function copy($id)
    {
        try {
            $originalAssessment = $this->assessmentModel->find($id);
            if (!$originalAssessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '原評估項目不存在'
                ]);
            }

            $input = $this->request->getJSON(true) ?: $this->request->getPost();
            
            $rules = [
                'company_id' => 'required|max_length[50]',
                'assessment_year' => 'permit_empty|integer|greater_than[1900]|less_than_equal_to[2040]',
                'include_questions' => 'permit_empty|in_list[0,1]',
                'include_results' => 'permit_empty|in_list[0,1]'
            ];
            
            if (!$this->validate($rules, $input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $copyOptions = [
                'include_questions' => $input['include_questions'] ?? true,
                'include_results' => $input['include_results'] ?? false
            ];

            $newAssessmentId = $this->assessmentModel->copyAssessment(
                $id, 
                $input['company_id'], 
                $copyOptions
            );

            if (!$newAssessmentId) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '複製評估項目失敗',
                    'errors' => $this->assessmentModel->errors()
                ]);
            }

            // Update assessment year if provided
            if (!empty($input['assessment_year'])) {
                $this->assessmentModel->update($newAssessmentId, [
                    'assessment_year' => $input['assessment_year']
                ]);
            }

            $newAssessment = $this->assessmentModel->getAssessmentWithTemplate($newAssessmentId);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '評估項目複製成功',
                'data' => $newAssessment
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CompanyAssessment copy error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '複製評估項目失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get company assessment statistics
     * GET /api/v1/risk-assessment/company-assessments/company/{companyId}/stats
     */
    public function getCompanyStats($companyId = null)
    {
        try {
            if (!$companyId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '公司ID為必填項目'
                ]);
            }

            $stats = $this->assessmentModel->getAssessmentStats($companyId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CompanyAssessment getCompanyStats error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得公司評估統計失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update assessment status
     * PATCH /api/v1/risk-assessment/company-assessments/{id}/status
     */
    public function updateStatus($id = null)
    {
        try {
            $assessment = $this->assessmentModel->find($id);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '評估項目不存在'
                ]);
            }

            $input = $this->request->getJSON(true) ?: $this->request->getPost();
            
            $rules = [
                'status' => 'required|in_list[pending,in_progress,completed,archived]'
            ];
            
            if (!$this->validate($rules, $input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $success = $this->assessmentModel->updateStatus($id, $input['status']);
            
            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新評估狀態失敗'
                ]);
            }

            $updatedAssessment = $this->assessmentModel->getAssessmentWithTemplate($id);

            return $this->response->setJSON([
                'success' => true,
                'message' => '評估狀態更新成功',
                'data' => $updatedAssessment
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CompanyAssessment updateStatus error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新評估狀態失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 自動同步範本架構到題項管理
     *
     * @param int $assessmentId 評估記錄ID
     * @param int $templateId 範本ID
     * @throws \Exception 同步失敗時拋出異常
     */
    private function autoSyncTemplateStructure(int $assessmentId, int $templateId): void
    {
        log_message('info', ">>> autoSyncTemplateStructure START: Assessment={$assessmentId}, Template={$templateId}");

        // 載入必要的模型
        log_message('info', "Loading QuestionManagement models...");
        $categoryModel = new \App\Models\QuestionManagement\QuestionCategoryModel();
        $topicModel = new \App\Models\QuestionManagement\QuestionTopicModel();
        $factorModel = new \App\Models\QuestionManagement\QuestionFactorModel();
        $contentModel = new \App\Models\QuestionManagement\QuestionContentModel();

        log_message('info', "Loading RiskAssessment models...");
        $riskCategoryModel = new \App\Models\RiskAssessment\RiskCategoryModel();
        $riskTopicModel = new \App\Models\RiskAssessment\RiskTopicModel();
        $riskFactorModel = new \App\Models\RiskAssessment\RiskFactorModel();
        $templateContentModel = new \App\Models\RiskAssessment\TemplateContentModel();

        // 開始資料庫事務
        log_message('info', "Starting database transaction...");
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 取得範本架構資料
            log_message('info', "Fetching template structure data for template_id: {$templateId}");
            $templateCategories = $riskCategoryModel->where('template_id', $templateId)->findAll();
            log_message('info', "Found " . count($templateCategories) . " template categories");

            $templateTopics = $riskTopicModel->where('template_id', $templateId)->findAll();
            log_message('info', "Found " . count($templateTopics) . " template topics");

            $templateFactors = $riskFactorModel->where('template_id', $templateId)->findAll();
            log_message('info', "Found " . count($templateFactors) . " template factors");

            $templateContents = $templateContentModel->where('template_id', $templateId)->findAll();
            log_message('info', "Found " . count($templateContents) . " template contents");

            // 檢查是否有資料需要複製
            if (empty($templateCategories) && empty($templateTopics) && empty($templateFactors) && empty($templateContents)) {
                log_message('warning', "No template structure data found for template_id: {$templateId}");
                $db->transComplete();
                return;
            }

            // 複製架構到題項管理
            log_message('info', "Starting to copy template structure...");

            log_message('info', "Copying categories...");
            $categoryIdMapping = $categoryModel->copyFromTemplateCategories($assessmentId, $templateCategories);
            log_message('info', "Category mapping: " . json_encode($categoryIdMapping));

            log_message('info', "Copying topics...");
            $topicIdMapping = $topicModel->copyFromTemplateTopics($assessmentId, $templateTopics, $categoryIdMapping);
            log_message('info', "Topic mapping: " . json_encode($topicIdMapping));

            log_message('info', "Copying factors...");
            $factorIdMapping = $factorModel->copyFromTemplateFactors($assessmentId, $templateFactors, $topicIdMapping, $categoryIdMapping);
            log_message('info', "Factor mapping: " . json_encode($factorIdMapping));

            log_message('info', "Copying contents...");
            $contentIdMapping = $contentModel->copyFromTemplateContents($assessmentId, $templateContents, $categoryIdMapping, $topicIdMapping, $factorIdMapping);
            log_message('info', "Content mapping: " . json_encode($contentIdMapping));

            log_message('info', "Completing database transaction...");
            $db->transComplete();

            if ($db->transStatus() === false) {
                log_message('error', "Database transaction failed for assessment {$assessmentId}");
                throw new \Exception('資料庫事務失敗');
            }

            log_message('info', "Template sync completed successfully: {$assessmentId} from template {$templateId} - " .
                "Categories: " . count($categoryIdMapping) . ", " .
                "Topics: " . count($topicIdMapping) . ", " .
                "Factors: " . count($factorIdMapping) . ", " .
                "Contents: " . count($contentIdMapping));

            log_message('info', "<<< autoSyncTemplateStructure END: SUCCESS");

        } catch (\Exception $e) {
            log_message('error', "Exception in autoSyncTemplateStructure: " . $e->getMessage());
            log_message('error', "Rolling back database transaction...");
            $db->transRollback();
            log_message('error', "<<< autoSyncTemplateStructure END: FAILED");
            throw new \Exception('範本架構同步失敗: ' . $e->getMessage());
        }
    }
}