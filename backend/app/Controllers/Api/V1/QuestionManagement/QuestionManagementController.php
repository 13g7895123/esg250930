<?php

namespace App\Controllers\Api\V1\QuestionManagement;

use App\Controllers\Api\BaseController;
use App\Models\CompanyAssessmentModel;
use App\Models\QuestionManagement\QuestionCategoryModel;
use App\Models\QuestionManagement\QuestionTopicModel;
use App\Models\QuestionManagement\QuestionFactorModel;
use App\Models\QuestionManagement\QuestionContentModel;
use App\Models\QuestionManagement\QuestionResponseModel;
use App\Models\PersonnelAssignmentModel;
use App\Models\ExternalPersonnelModel;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * 題項管理控制器
 *
 * 處理題項管理的主要功能，包括：
 * - 取得評估記錄的完整架構資訊
 * - 從範本同步架構到題項管理
 * - 管理題項內容和回答
 * - 提供題項管理所需的統計資料
 *
 * @package App\Controllers\Api\V1\QuestionManagement
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class QuestionManagementController extends BaseController
{
    protected $assessmentModel;
    protected $categoryModel;
    protected $topicModel;
    protected $factorModel;
    protected $contentModel;
    protected $responseModel;
    protected $personnelAssignmentModel;
    protected $externalPersonnelModel;

    public function __construct()
    {
        $this->assessmentModel = new CompanyAssessmentModel();
        $this->categoryModel = new QuestionCategoryModel();
        $this->topicModel = new QuestionTopicModel();
        $this->factorModel = new QuestionFactorModel();
        $this->contentModel = new QuestionContentModel();
        $this->responseModel = new QuestionResponseModel();
        $this->personnelAssignmentModel = new PersonnelAssignmentModel();
        $this->externalPersonnelModel = new ExternalPersonnelModel();
    }

    /**
     * 取得評估記錄的完整架構資訊
     * GET /api/v1/question-management/assessment/{assessmentId}/structure
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getAssessmentStructure($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 驗證評估記錄是否存在
            $assessment = $this->assessmentModel->find($assessmentId);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的評估記錄'
                ]);
            }

            // 取得完整架構資訊
            $structure = [
                'assessment' => $assessment,
                'categories' => $this->categoryModel->getCategoriesByAssessment($assessmentId),
                'topics' => $this->topicModel->getTopicsByAssessment($assessmentId),
                'factors' => $this->factorModel->getFactorsByAssessment($assessmentId),
                'contents_count' => $this->contentModel->where('assessment_id', $assessmentId)->countAllResults(),
                'responses_count' => $this->responseModel->where('assessment_id', $assessmentId)->countAllResults()
            ];

            // 取得統計資料 - 臨時跳過以調試UTF-8問題
            // $contentStats = $this->contentModel->getAssessmentStats($assessmentId);
            // $responseStats = $this->responseModel->getAssessmentStats($assessmentId);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'structure' => $structure,
                    'stats' => [] // 臨時跳過統計資料
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::getAssessmentStructure - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得評估架構時發生錯誤'
            ]);
        }
    }

    /**
     * 從範本同步架構到題項管理
     * POST /api/v1/question-management/assessment/{assessmentId}/sync-from-template
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function syncFromTemplate($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 驗證評估記錄是否存在
            $assessment = $this->assessmentModel->find($assessmentId);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的評估記錄'
                ]);
            }

            $templateId = $assessment['template_id'];
            if (!$templateId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄未指定範本ID'
                ]);
            }

            // 開始資料庫事務
            $db = \Config\Database::connect();
            $db->transStart();

            // 清除現有的題項架構（由於有 CASCADE 設定，會連帶刪除相關資料）
            $this->categoryModel->deleteAllByAssessment($assessmentId);

            // 載入範本架構資料
            $riskCategoryModel = new \App\Models\RiskAssessment\RiskCategoryModel();
            $riskTopicModel = new \App\Models\RiskAssessment\RiskTopicModel();
            $riskFactorModel = new \App\Models\RiskAssessment\RiskFactorModel();
            $templateContentModel = new \App\Models\RiskAssessment\TemplateContentModel();

            // 取得範本架構資料
            $templateCategories = $riskCategoryModel->where('template_id', $templateId)->findAll();
            $templateTopics = $riskTopicModel->where('template_id', $templateId)->findAll();
            $templateFactors = $riskFactorModel->where('template_id', $templateId)->findAll();
            $templateContents = $templateContentModel->where('template_id', $templateId)->findAll();

            // 複製架構到題項管理
            error_log('[syncFromTemplate] Starting to copy categories. Template categories count: ' . count($templateCategories));
            $categoryIdMapping = $this->categoryModel->copyFromTemplateCategories($assessmentId, $templateCategories);
            error_log('[syncFromTemplate] Categories copied: ' . count($categoryIdMapping));

            error_log('[syncFromTemplate] Starting to copy topics. Template topics count: ' . count($templateTopics));
            $topicIdMapping = $this->topicModel->copyFromTemplateTopics($assessmentId, $templateTopics, $categoryIdMapping);
            error_log('[syncFromTemplate] Topics copied: ' . count($topicIdMapping));

            error_log('[syncFromTemplate] Starting to copy factors. Template factors count: ' . count($templateFactors));
            $factorIdMapping = $this->factorModel->copyFromTemplateFactors($assessmentId, $templateFactors, $topicIdMapping, $categoryIdMapping);
            error_log('[syncFromTemplate] Factors copied: ' . count($factorIdMapping));

            error_log('[syncFromTemplate] Starting to copy contents. Template contents count: ' . count($templateContents));
            $contentIdMapping = $this->contentModel->copyFromTemplateContents($assessmentId, $templateContents, $categoryIdMapping, $topicIdMapping, $factorIdMapping);
            error_log('[syncFromTemplate] Contents copied: ' . count($contentIdMapping));

            $db->transComplete();

            if ($db->transStatus() === false) {
                // 記錄事務失敗
                $dbError = $db->error();
                log_message('error', 'QuestionManagementController::syncFromTemplate - Transaction failed');
                log_message('error', 'Database error code: ' . ($dbError['code'] ?? 'N/A'));
                log_message('error', 'Database error message: ' . ($dbError['message'] ?? 'N/A'));

                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '同步範本架構時發生資料庫錯誤: ' . ($dbError['message'] ?? '未知錯誤')
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功從範本同步架構',
                'data' => [
                    'categories_copied' => count($categoryIdMapping),
                    'topics_copied' => count($topicIdMapping),
                    'factors_copied' => count($factorIdMapping),
                    'contents_copied' => count($contentIdMapping)
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::syncFromTemplate - ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            // 回滾事務（如果尚未完成）
            if (isset($db) && $db->transStatus() !== false) {
                $db->transRollback();
            }

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '同步範本時發生錯誤: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 取得評估記錄的所有內容
     * GET /api/v1/question-management/assessment/{assessmentId}/contents
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getAssessmentContents($assessmentId = null)
    {
        try {
            // 記錄請求開始
            log_message('info', "=== getAssessmentContents API Request Started ===");
            log_message('info', "Assessment ID: " . ($assessmentId ?? 'null'));

            if (!$assessmentId) {
                log_message('warning', "Missing assessment ID in request");
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 取得查詢參數
            $categoryId = $this->request->getGet('category_id');
            $topicId = $this->request->getGet('topic_id');
            $factorId = $this->request->getGet('factor_id');
            $search = $this->request->getGet('search');
            $userId = $this->request->getGet('user_id');
            $externalId = $this->request->getGet('external_id');

            // 詳細記錄所有參數
            log_message('info', "Request parameters:");
            log_message('info', "  - Assessment ID: {$assessmentId}");
            log_message('info', "  - Category ID: " . ($categoryId ?? 'null'));
            log_message('info', "  - Topic ID: " . ($topicId ?? 'null'));
            log_message('info', "  - Factor ID: " . ($factorId ?? 'null'));
            log_message('info', "  - Search: " . ($search ?? 'null'));
            log_message('info', "  - User ID: " . ($userId ?? 'null'));
            log_message('info', "  - External ID: " . ($externalId ?? 'null'));

            // 呼叫模型方法
            log_message('info', "Calling contentModel->getContentsByAssessment...");
            $contents = $this->contentModel->getContentsByAssessment(
                (int)$assessmentId,
                $categoryId ? (int)$categoryId : null,
                $topicId ? (int)$topicId : null,
                $factorId ? (int)$factorId : null,
                $search,
                $userId ? (int)$userId : null,
                $externalId ? (int)$externalId : null
            );

            log_message('info', "Model returned " . count($contents) . " contents");
            log_message('info', "=== getAssessmentContents API Request Completed Successfully ===");

            return $this->response->setJSON([
                'success' => true,
                'data' => $contents,
                'meta' => [
                    'assessment_id' => (int)$assessmentId,
                    'total_results' => count($contents),
                    'filtered_by_user' => $userId !== null,
                    'user_id' => $userId ? (int)$userId : null
                ]
            ]);

        } catch (\Exception $e) {
            // 詳細錯誤記錄
            log_message('error', "=== getAssessmentContents API Request Failed ===");
            log_message('error', "Error Type: " . get_class($e));
            log_message('error', "Error Message: " . $e->getMessage());
            log_message('error', "Error File: " . $e->getFile());
            log_message('error', "Error Line: " . $e->getLine());
            log_message('error', "Stack Trace: " . $e->getTraceAsString());
            log_message('error', "Request Parameters: assessment_id={$assessmentId}, user_id=" . ($userId ?? 'null'));

            // 檢查是否是特定的資料庫或查詢錯誤
            $isDbError = strpos($e->getMessage(), 'getCompiledSelect') !== false ||
                        strpos($e->getMessage(), 'database') !== false ||
                        strpos($e->getMessage(), 'SQL') !== false;

            $errorMessage = $isDbError ?
                '資料庫查詢錯誤，請檢查系統日誌' :
                '取得評估內容時發生錯誤';

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $errorMessage,
                'debug' => [
                    'assessment_id' => $assessmentId,
                    'error_type' => get_class($e),
                    'error_message' => $e->getMessage(),
                    'is_database_error' => $isDbError,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        }
    }

    /**
     * 取得評估記錄的統計資料
     * GET /api/v1/question-management/assessment/{assessmentId}/stats
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getAssessmentStats($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 取得各種統計資料
            $contentStats = $this->contentModel->getAssessmentStats($assessmentId);
            $responseStats = $this->responseModel->getAssessmentStats($assessmentId);
            $categoryStats = $this->responseModel->getCategoryStats($assessmentId);

            // 架構統計
            $structureStats = [
                'categories_count' => $this->categoryModel->where('assessment_id', $assessmentId)->countAllResults(),
                'topics_count' => $this->topicModel->where('assessment_id', $assessmentId)->countAllResults(),
                'factors_count' => $this->factorModel->where('assessment_id', $assessmentId)->countAllResults()
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'content_stats' => $contentStats,
                    'response_stats' => $responseStats,
                    'category_stats' => $categoryStats,
                    'structure_stats' => $structureStats
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::getAssessmentStats - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得評估統計時發生錯誤'
            ]);
        }
    }

    /**
     * 儲存評估回答
     * POST /api/v1/question-management/assessment/{assessmentId}/responses
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function saveResponses($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            $input = $this->request->getJSON(true);
            if (empty($input['responses'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '回答資料為必填項目'
                ]);
            }

            $answeredBy = $input['answered_by'] ?? null;

            // 轉換前端的索引陣列格式為後端期望的關聯陣列格式
            $formattedResponses = [];
            foreach ($input['responses'] as $responseItem) {
                if (isset($responseItem['question_content_id'])) {
                    $questionContentId = $responseItem['question_content_id'];
                    $formattedResponses[$questionContentId] = [
                        'response_value' => $responseItem['response_value'] ?? null,
                        'score' => $responseItem['score'] ?? null,
                        'notes' => $responseItem['notes'] ?? null,
                        'evidence_files' => $responseItem['evidence_files'] ?? null
                    ];
                }
            }

            log_message('info', 'QuestionManagementController::saveResponses - 原始格式: ' . json_encode($input['responses']));
            log_message('info', 'QuestionManagementController::saveResponses - 轉換後格式: ' . json_encode($formattedResponses));

            // 批次儲存回答
            $result = $this->responseModel->batchSaveResponses(
                $assessmentId,
                $formattedResponses,
                $answeredBy
            );

            if (count($result['errors']) > 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '部分回答儲存失敗',
                    'data' => $result
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功儲存所有回答',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::saveResponses - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '儲存回答時發生錯誤'
            ]);
        }
    }

    /**
     * 取得評估回答
     * GET /api/v1/question-management/assessment/{assessmentId}/responses
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getResponses($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            $reviewStatus = $this->request->getGet('review_status');
            $answeredBy = $this->request->getGet('answered_by');

            $responses = $this->responseModel->getResponsesByAssessment(
                $assessmentId,
                $reviewStatus,
                $answeredBy ? (int)$answeredBy : null
            );

            return $this->response->setJSON([
                'success' => true,
                'data' => $responses
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::getResponses - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得評估回答時發生錯誤'
            ]);
        }
    }

    /**
     * 更新回答審核狀態
     * PUT /api/v1/question-management/responses/{responseId}/review
     *
     * @param int|null $responseId 回答ID
     * @return ResponseInterface
     */
    public function updateReviewStatus($responseId = null)
    {
        try {
            if (!$responseId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '回答ID為必填項目'
                ]);
            }

            $input = $this->request->getJSON(true);
            if (empty($input['review_status'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '審核狀態為必填項目'
                ]);
            }

            $validStatuses = ['pending', 'approved', 'rejected'];
            if (!in_array($input['review_status'], $validStatuses)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '無效的審核狀態'
                ]);
            }

            $result = $this->responseModel->updateReviewStatus(
                $responseId,
                $input['review_status'],
                $input['reviewed_by'] ?? null,
                $input['review_notes'] ?? null
            );

            if (!$result) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的回答記錄'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功更新審核狀態'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::updateReviewStatus - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新審核狀態時發生錯誤'
            ]);
        }
    }

    /**
     * 刪除評估記錄的所有題項資料
     * DELETE /api/v1/question-management/assessment/{assessmentId}/clear
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function clearAssessmentData($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 由於設定了 CASCADE 約束，只需要刪除分類即可連帶刪除所有相關資料
            $result = $this->categoryModel->deleteAllByAssessment($assessmentId);

            if (!$result) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '清除評估資料失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功清除評估記錄的所有題項資料'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::clearAssessmentData - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '清除評估資料時發生錯誤'
            ]);
        }
    }

    /**
     * 取得評估記錄的所有題項內容（別名方法，用於路由相容性）
     * GET /api/v1/question-management/assessment/{assessmentId}/contents
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getContents($assessmentId = null)
    {
        return $this->getAssessmentContents($assessmentId);
    }

    /**
     * 取得單一內容項目的詳細資料
     * GET /api/v1/question-management/contents/{contentId}
     *
     * @param int|null $contentId 內容項目ID
     * @return ResponseInterface
     */
    public function getContent($contentId = null)
    {
        try {
            if (!$contentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '內容項目ID為必填項目'
                ]);
            }

            // 查詢單一內容項目，並 JOIN assessment 以取得 company_id
            $content = $this->contentModel
                ->select('question_contents.*, company_assessments.company_id, company_assessments.id as assessment_id')
                ->join('company_assessments', 'company_assessments.id = question_contents.assessment_id', 'left')
                ->where('question_contents.id', $contentId)
                ->first();

            if (!$content) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的內容項目'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'content' => $content
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', '取得內容項目詳細資料失敗: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得內容項目詳細資料時發生錯誤',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 取得評估記錄的指派狀況
     * GET /api/v1/question-management/assessment/{assessmentId}/assignment-status
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getAssignmentStatus($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 驗證評估記錄是否存在
            $assessment = $this->assessmentModel->find($assessmentId);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的評估記錄'
                ]);
            }

            // 取得該評估的所有內容項目
            $contents = $this->contentModel->getContentsByAssessment($assessmentId);

            // 統計每個內容項目的填寫狀況
            $assignmentStatus = [];
            $totalContents = count($contents);
            $completedContents = 0;

            foreach ($contents as $content) {
                // 檢查是否有回答記錄
                $response = $this->responseModel->where('assessment_id', $assessmentId)
                    ->where('question_content_id', $content['id'])
                    ->first();

                $isCompleted = false;
                $answeredAt = null;
                $answeredBy = null;

                if ($response) {
                    // 檢查是否有實際的答案內容
                    $hasAnswer = false;

                    // 檢查各個分離欄位是否有內容
                    $answerFields = [
                        'c_risk_event_choice', 'c_risk_event_description',
                        'd_counter_action_choice', 'd_counter_action_description', 'd_counter_action_cost',
                        'e1_risk_description', 'e2_risk_probability', 'e2_risk_impact', 'e2_risk_calculation',
                        'f1_opportunity_description', 'f2_opportunity_probability', 'f2_opportunity_impact', 'f2_opportunity_calculation',
                        'g1_negative_impact_level', 'g1_negative_impact_description',
                        'h1_positive_impact_level', 'h1_positive_impact_description'
                    ];

                    foreach ($answerFields as $field) {
                        if (!empty($response[$field])) {
                            $hasAnswer = true;
                            break;
                        }
                    }

                    if ($hasAnswer) {
                        $isCompleted = true;
                        $completedContents++;
                        $answeredAt = $response['answered_at'] ?? $response['updated_at'];
                        $answeredBy = $response['answered_by'];
                    }
                }

                $assignmentStatus[] = [
                    'content_id' => $content['id'],
                    'description' => $content['description'],
                    'category_name' => $content['category_name'] ?? '',
                    'topic_name' => $content['topic_name'] ?? '',
                    'factor_name' => $content['factor_name'] ?? '',
                    'is_completed' => $isCompleted,
                    'answered_at' => $answeredAt,
                    'answered_by' => $answeredBy,
                    'sort_order' => $content['sort_order'] ?? 0
                ];
            }

            // 計算完成百分比
            $completionPercentage = $totalContents > 0 ? round(($completedContents / $totalContents) * 100, 1) : 0;

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'assessment' => $assessment,
                    'assignment_status' => $assignmentStatus,
                    'statistics' => [
                        'total_contents' => $totalContents,
                        'completed_contents' => $completedContents,
                        'pending_contents' => $totalContents - $completedContents,
                        'completion_percentage' => $completionPercentage
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::getAssignmentStatus - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得指派狀況時發生錯誤'
            ]);
        }
    }

    /**
     * 取得評估的人員指派詳細資訊 (用於指派狀況頁面)
     * GET /api/v1/question-management/assessment/{assessmentId}/assignments
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getAssignments($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 驗證評估記錄是否存在
            $assessment = $this->assessmentModel->find($assessmentId);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的評估記錄'
                ]);
            }

            // 取得評估基本資訊 (需要從 templates 或其他相關表取得 templateVersion)
            // 暫時使用評估記錄的基本資訊，後續可根據實際資料表結構調整
            $assessmentInfo = [
                'id' => $assessment['id'],
                'templateVersion' => $assessment['template_version'] ?? '預設範本',
                'year' => $assessment['assessment_year'] ?? date('Y'),
                'company_id' => $assessment['company_id']
            ];

            // 取得該評估的所有內容項目總數
            $contents = $this->contentModel->getContentsByAssessment($assessmentId);
            $totalQuestions = count($contents);

            // 正確的方式：從 personnel_assignments 表中取得真正已被指派的用戶
            $assignedUsers = $this->personnelAssignmentModel
                ->select('personnel_id, personnel_name, personnel_department, personnel_position')
                ->distinct()
                ->where('assessment_id', $assessmentId)
                ->where('company_id', $assessment['company_id'])
                ->findAll();

            $assignments = [];
            $completedCount = 0;
            $inProgressCount = 0;
            $notStartedCount = 0;

            // 如果沒有找到任何指派記錄，返回空列表但不報錯
            if (empty($assignedUsers)) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => [
                        'assessment_info' => $assessmentInfo,
                        'assignments' => [],
                        'statistics' => [
                            'completed' => 0,
                            'in_progress' => 0,
                            'not_started' => 0,
                            'total_users' => 0
                        ]
                    ]
                ]);
            }

            foreach ($assignedUsers as $userRecord) {
                $userId = $userRecord['personnel_id'];
                $userName = $userRecord['personnel_name'];
                $userDepartment = $userRecord['personnel_department'];
                $userPosition = $userRecord['personnel_position'];

                // 取得該用戶的所有回答記錄
                $userResponses = $this->responseModel->where('assessment_id', $assessmentId)
                    ->where('answered_by', $userId)
                    ->findAll();

                // 從指派記錄中取得指派時間
                $assignmentRecord = $this->personnelAssignmentModel
                    ->where('assessment_id', $assessmentId)
                    ->where('personnel_id', $userId)
                    ->orderBy('assigned_at', 'ASC')
                    ->first();

                $assignedAt = $assignmentRecord ? $assignmentRecord['assigned_at'] : null;

                // 計算該用戶完成的題目數量
                $completedQuestions = 0;
                $lastUpdated = null;

                foreach ($userResponses as $response) {
                    // 檢查是否有實際的答案內容
                    $hasAnswer = false;
                    $answerFields = [
                        'c_risk_event_choice', 'c_risk_event_description',
                        'd_counter_action_choice', 'd_counter_action_description', 'd_counter_action_cost',
                        'e1_risk_description', 'e2_risk_probability', 'e2_risk_impact', 'e2_risk_calculation',
                        'f1_opportunity_description', 'f2_opportunity_probability', 'f2_opportunity_impact', 'f2_opportunity_calculation',
                        'g1_negative_impact_level', 'g1_negative_impact_description',
                        'h1_positive_impact_level', 'h1_positive_impact_description'
                    ];

                    foreach ($answerFields as $field) {
                        if (!empty($response[$field])) {
                            $hasAnswer = true;
                            break;
                        }
                    }

                    if ($hasAnswer) {
                        $completedQuestions++;
                        $lastUpdated = max($lastUpdated, $response['updated_at']);
                    }
                }

                // 判斷狀態
                $status = 'not_started';
                if ($completedQuestions > 0) {
                    if ($completedQuestions >= $totalQuestions) {
                        $status = 'completed';
                        $completedCount++;
                    } else {
                        $status = 'in_progress';
                        $inProgressCount++;
                    }
                } else {
                    $notStartedCount++;
                }

                $assignments[] = [
                    'user_id' => $userId,
                    'user_name' => $userName,
                    'department' => $userDepartment,
                    'position' => $userPosition,
                    'company_id' => $assessment['company_id'],
                    'status' => $status,
                    'completed_questions' => $completedQuestions,
                    'total_questions' => $totalQuestions,
                    'assigned_at' => $assignedAt ? date('Y-m-d H:i:s', strtotime($assignedAt)) : null,
                    'last_updated' => $lastUpdated ? date('Y-m-d H:i:s', strtotime($lastUpdated)) : null
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'assessment_info' => $assessmentInfo,
                    'assignments' => $assignments,
                    'statistics' => [
                        'completed' => $completedCount,
                        'in_progress' => $inProgressCount,
                        'not_started' => $notStartedCount,
                        'total_users' => count($assignments)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::getAssignments - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得指派資訊時發生錯誤'
            ]);
        }
    }

    /**
     * 取得人員資訊
     *
     * @param int $userId 用戶ID (對應 answered_by 欄位)
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @return array 人員資訊
     */
    private function getPersonnelInfo(int $userId, int $companyId, int $assessmentId): array
    {
        try {
            // 方法1: 先嘗試從 personnel_assignments 表取得人員資訊
            $assignmentInfo = $this->personnelAssignmentModel->where([
                'personnel_id' => $userId,
                'company_id' => $companyId,
                'assessment_id' => $assessmentId
            ])->first();

            if ($assignmentInfo) {
                return [
                    'user_name' => $assignmentInfo['personnel_name'],
                    'department' => $assignmentInfo['personnel_department'] ?? null,
                    'position' => $assignmentInfo['personnel_position'] ?? null
                ];
            }

            // 方法2: 嘗試從 external_personnel 表取得人員資訊
            $externalPersonnel = $this->externalPersonnelModel->where([
                'id' => $userId,
                'company_id' => $companyId
            ])->first();

            if ($externalPersonnel) {
                return [
                    'user_name' => $externalPersonnel['name'],
                    'department' => $externalPersonnel['department'] ?? null,
                    'position' => $externalPersonnel['position'] ?? null
                ];
            }

            // 方法3: 嘗試從任何相關的 personnel_assignments 記錄取得
            $anyAssignment = $this->personnelAssignmentModel->where('personnel_id', $userId)->first();
            if ($anyAssignment) {
                return [
                    'user_name' => $anyAssignment['personnel_name'],
                    'department' => $anyAssignment['personnel_department'] ?? null,
                    'position' => $anyAssignment['personnel_position'] ?? null
                ];
            }

            // 方法4: 嘗試從任何相關的 external_personnel 記錄取得
            $anyExternalPersonnel = $this->externalPersonnelModel->find($userId);
            if ($anyExternalPersonnel) {
                return [
                    'user_name' => $anyExternalPersonnel['name'],
                    'department' => $anyExternalPersonnel['department'] ?? null,
                    'position' => $anyExternalPersonnel['position'] ?? null
                ];
            }

        } catch (\Exception $e) {
            log_message('error', "Error getting personnel info for user {$userId}: " . $e->getMessage());
        }

        // 如果都找不到，返回預設資料
        return [
            'user_name' => "人員 {$userId}",
            'department' => null,
            'position' => null
        ];
    }

    /**
     * 取得特定用戶的詳細填寫結果
     * GET /api/v1/question-management/assessment/{assessmentId}/user/{userId}/results
     *
     * @param int|null $assessmentId 評估記錄ID
     * @param int|null $userId 用戶ID
     * @return ResponseInterface
     */
    public function getUserResults($assessmentId = null, $userId = null)
    {
        try {
            if (!$assessmentId || !$userId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID和用戶ID為必填項目'
                ]);
            }

            // 驗證評估記錄是否存在
            $assessment = $this->assessmentModel->find($assessmentId);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的評估記錄'
                ]);
            }

            // 取得評估基本資訊
            $assessmentInfo = [
                'id' => $assessment['id'],
                'templateVersion' => $assessment['template_version'] ?? '預設範本',
                'year' => $assessment['assessment_year'] ?? date('Y'),
                'company_id' => $assessment['company_id']
            ];

            // 取得真實用戶資訊
            $personnelInfo = $this->getPersonnelInfo($userId, $assessment['company_id'], $assessmentId);
            $userInfo = [
                'user_id' => $userId,
                'user_name' => $personnelInfo['user_name']
            ];

            // 取得該評估的所有內容項目
            $contents = $this->contentModel->getContentsByAssessment($assessmentId);

            // 取得該用戶的所有回答記錄
            $userResponses = $this->responseModel->where('assessment_id', $assessmentId)
                ->where('answered_by', $userId)
                ->findAll();

            // 建立回答記錄的索引 (以 question_content_id 為鍵)
            $responseIndex = [];
            foreach ($userResponses as $response) {
                $responseIndex[$response['question_content_id']] = $response;
            }

            // 整合內容項目和回答資料
            $responses = [];
            $completedQuestions = 0;
            $lastUpdated = null;

            foreach ($contents as $content) {
                $response = $responseIndex[$content['id']] ?? null;

                $isCompleted = false;
                $answeredAt = null;
                $responseValue = null;

                if ($response) {
                    // 解析 JSON 格式的 response_value
                    if (!empty($response['response_value'])) {
                        $responseValue = json_decode($response['response_value'], true);
                    }

                    // 檢查是否有實際的答案內容
                    if ($responseValue && !empty($responseValue)) {
                        // 檢查 JSON 中的欄位是否有內容
                        $answerFields = [
                            'riskEventChoice', 'riskEventDescription',
                            'counterActionChoice', 'counterActionDescription', 'counterActionCost',
                            'riskDescription', 'riskProbability', 'riskImpact', 'riskCalculation',
                            'opportunityDescription', 'opportunityProbability', 'opportunityImpact', 'opportunityCalculation',
                            'negativeImpactLevel', 'negativeImpactDescription',
                            'positiveImpactLevel', 'positiveImpactDescription'
                        ];

                        foreach ($answerFields as $field) {
                            if (!empty($responseValue[$field])) {
                                $isCompleted = true;
                                break;
                            }
                        }
                    }

                    if ($isCompleted) {
                        $completedQuestions++;
                        $answeredAt = $response['answered_at'] ?? $response['updated_at'];
                        $lastUpdated = max($lastUpdated, $response['updated_at']);
                    }
                }

                // 建立回應資料結構，包含所有答案欄位
                $responseData = [
                    'content_id' => $content['id'],
                    'description' => $content['description'],
                    'category_name' => $content['category_name'] ?? '',
                    'topic_name' => $content['topic_name'] ?? '',
                    'factor_name' => $content['factor_name'] ?? '',
                    'is_completed' => $isCompleted,
                    'answered_at' => $answeredAt,
                    'sort_order' => $content['sort_order'] ?? 0
                ];

                // 如果有回答，加入所有答案欄位 (從JSON轉換為扁平結構)
                if ($response && isset($responseValue) && $responseValue) {
                    $responseData['c_risk_event_choice'] = $responseValue['riskEventChoice'] ?? null;
                    $responseData['c_risk_event_description'] = $responseValue['riskEventDescription'] ?? null;
                    $responseData['d_counter_action_choice'] = $responseValue['counterActionChoice'] ?? null;
                    $responseData['d_counter_action_description'] = $responseValue['counterActionDescription'] ?? null;
                    $responseData['d_counter_action_cost'] = $responseValue['counterActionCost'] ?? null;
                    $responseData['e1_risk_description'] = $responseValue['riskDescription'] ?? null;
                    $responseData['e2_risk_probability'] = $responseValue['riskProbability'] ?? null;
                    $responseData['e2_risk_impact'] = $responseValue['riskImpact'] ?? null;
                    $responseData['e2_risk_calculation'] = $responseValue['riskCalculation'] ?? null;
                    $responseData['f1_opportunity_description'] = $responseValue['opportunityDescription'] ?? null;
                    $responseData['f2_opportunity_probability'] = $responseValue['opportunityProbability'] ?? null;
                    $responseData['f2_opportunity_impact'] = $responseValue['opportunityImpact'] ?? null;
                    $responseData['f2_opportunity_calculation'] = $responseValue['opportunityCalculation'] ?? null;
                    $responseData['g1_negative_impact_level'] = $responseValue['negativeImpactLevel'] ?? null;
                    $responseData['g1_negative_impact_description'] = $responseValue['negativeImpactDescription'] ?? null;
                    $responseData['h1_positive_impact_level'] = $responseValue['positiveImpactLevel'] ?? null;
                    $responseData['h1_positive_impact_description'] = $responseValue['positiveImpactDescription'] ?? null;
                } else {
                    // 沒有回答時，設定所有欄位為 null
                    $answerFields = [
                        'c_risk_event_choice', 'c_risk_event_description',
                        'd_counter_action_choice', 'd_counter_action_description', 'd_counter_action_cost',
                        'e1_risk_description', 'e2_risk_probability', 'e2_risk_impact', 'e2_risk_calculation',
                        'f1_opportunity_description', 'f2_opportunity_probability', 'f2_opportunity_impact', 'f2_opportunity_calculation',
                        'g1_negative_impact_level', 'g1_negative_impact_description',
                        'h1_positive_impact_level', 'h1_positive_impact_description'
                    ];

                    foreach ($answerFields as $field) {
                        $responseData[$field] = null;
                    }
                }

                $responses[] = $responseData;
            }

            // 根據 sort_order 排序
            usort($responses, function($a, $b) {
                return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
            });

            $totalQuestions = count($contents);
            $pendingQuestions = $totalQuestions - $completedQuestions;
            $completionPercentage = $totalQuestions > 0 ? round(($completedQuestions / $totalQuestions) * 100, 1) : 0;

            $resultData = [
                'statistics' => [
                    'completed_questions' => $completedQuestions,
                    'pending_questions' => $pendingQuestions,
                    'total_questions' => $totalQuestions,
                    'completion_percentage' => $completionPercentage,
                    'last_updated' => $lastUpdated
                ],
                'responses' => $responses
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'assessment_info' => $assessmentInfo,
                    'user_info' => $userInfo,
                    'results' => $resultData
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::getUserResults - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得用戶填寫結果時發生錯誤'
            ]);
        }
    }

    /**
     * 建立新的題項內容
     * POST /api/v1/question-management/assessment/{assessmentId}/contents
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function createContent($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 驗證評估記錄是否存在
            $assessment = $this->assessmentModel->find($assessmentId);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的評估記錄'
                ]);
            }

            $input = $this->request->getJSON(true);
            if (empty($input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '請求資料不能為空'
                ]);
            }

            // 準備資料
            $data = [
                'assessment_id' => $assessmentId,
                'category_id' => $input['category_id'] ?? null,
                'topic_id' => $input['topic_id'] ?? null,
                'factor_id' => $input['factor_id'] ?? null,
                'description' => $input['description'] ?? '',
                'sort_order' => $input['sort_order'] ?? $this->contentModel->getNextSortOrder($assessmentId)
            ];

            // 只在前端有傳送 is_required 時才設定，並確保值為 0 或 1
            if (isset($input['is_required'])) {
                $data['is_required'] = $input['is_required'] ? 1 : 0;
            }

            $contentId = $this->contentModel->insert($data);
            if (!$contentId) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '建立題項內容失敗',
                    'errors' => $this->contentModel->errors()
                ]);
            }

            // 取得建立的內容
            $newContent = $this->contentModel->getContentWithDetails($contentId);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '成功建立題項內容',
                'data' => $newContent
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::createContent - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '建立題項內容時發生錯誤'
            ]);
        }
    }

    /**
     * 更新題項內容
     * PUT /api/v1/question-management/contents/{contentId}
     *
     * @param int|null $contentId 內容ID
     * @return ResponseInterface
     */
    public function updateContent($contentId = null)
    {
        try {
            if (!$contentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '內容ID為必填項目'
                ]);
            }

            // 驗證內容是否存在
            $existingContent = $this->contentModel->find($contentId);
            if (!$existingContent) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的題項內容'
                ]);
            }

            $input = $this->request->getJSON(true);
            if (empty($input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '請求資料不能為空'
                ]);
            }

            // 準備更新資料
            $data = [];
            if (isset($input['category_id'])) $data['category_id'] = $input['category_id'];
            if (isset($input['topic_id'])) $data['topic_id'] = $input['topic_id'];
            if (isset($input['factor_id'])) $data['factor_id'] = $input['factor_id'];
            if (isset($input['description'])) $data['description'] = $input['description'];
            if (isset($input['is_required'])) $data['is_required'] = $input['is_required'] ? 1 : 0;
            if (isset($input['sort_order'])) $data['sort_order'] = $input['sort_order'];

            if (empty($data)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '沒有提供任何要更新的資料'
                ]);
            }

            $success = $this->contentModel->update($contentId, $data);
            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新題項內容失敗',
                    'errors' => $this->contentModel->errors()
                ]);
            }

            // 取得更新後的內容
            $updatedContent = $this->contentModel->getContentWithDetails($contentId);

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功更新題項內容',
                'data' => $updatedContent
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::updateContent - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新題項內容時發生錯誤'
            ]);
        }
    }

    /**
     * 刪除題項內容
     * DELETE /api/v1/question-management/contents/{contentId}
     *
     * @param int|null $contentId 內容ID
     * @return ResponseInterface
     */
    public function deleteContent($contentId = null)
    {
        try {
            if (!$contentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '內容ID為必填項目'
                ]);
            }

            // 驗證內容是否存在
            $existingContent = $this->contentModel->find($contentId);
            if (!$existingContent) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的題項內容'
                ]);
            }

            // 檢查是否有相關的回答記錄
            $responseCount = $this->responseModel->where('question_content_id', $contentId)->countAllResults();
            if ($responseCount > 0) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => "無法刪除此題項內容，因為已有 {$responseCount} 筆回答記錄"
                ]);
            }

            $success = $this->contentModel->delete($contentId);
            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '刪除題項內容失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功刪除題項內容'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::deleteContent - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除題項內容時發生錯誤'
            ]);
        }
    }

    /**
     * 取得特定用戶對特定內容的回答
     * GET /api/v1/question-management/assessment/{assessmentId}/user/{userId}/responses/{contentId}
     *
     * @param int|null $assessmentId 評估記錄ID
     * @param int|null $userId 用戶ID
     * @param int|null $contentId 內容ID
     * @return ResponseInterface
     */
    public function getUserContentResponse($assessmentId = null, $userId = null, $contentId = null)
    {
        try {
            if (!$assessmentId || !$userId || !$contentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID、用戶ID和內容ID為必填項目'
                ]);
            }

            // 查詢特定用戶對特定內容的回答
            $response = $this->responseModel->where([
                'assessment_id' => $assessmentId,
                'question_content_id' => $contentId,
                'answered_by' => $userId
            ])->first();

            if (!$response) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => null,
                    'message' => '尚未回答此問題'
                ]);
            }

            // 建構回答值物件 - 從分離的欄位組合成前端期望的格式
            $responseValue = [
                // C區域
                'riskEventChoice' => $response['c_risk_event_choice'] ?? null,
                'riskEventDescription' => $response['c_risk_event_description'] ?? null,
                // D區域
                'counterActionChoice' => $response['d_counter_action_choice'] ?? null,
                'counterActionDescription' => $response['d_counter_action_description'] ?? null,
                'counterActionCost' => $response['d_counter_action_cost'] ?? null,
                // E-1 風險描述
                'e1_risk_description' => $response['e1_risk_description'] ?? null,
                // E-2 風險財務影響評估
                'e2_risk_probability' => $response['e2_risk_probability'] ?? null,
                'e2_risk_impact' => $response['e2_risk_impact'] ?? null,
                'e2_risk_calculation' => $response['e2_risk_calculation'] ?? null,
                // F-1 機會描述
                'f1_opportunity_description' => $response['f1_opportunity_description'] ?? null,
                // F-2 機會財務影響評估
                'f2_opportunity_probability' => $response['f2_opportunity_probability'] ?? null,
                'f2_opportunity_impact' => $response['f2_opportunity_impact'] ?? null,
                'f2_opportunity_calculation' => $response['f2_opportunity_calculation'] ?? null,
                // G-1 對外負面衝擊
                'g1_negative_impact_level' => $response['g1_negative_impact_level'] ?? null,
                'g1_negative_impact_description' => $response['g1_negative_impact_description'] ?? null,
                // H-1 對外正面影響
                'h1_positive_impact_level' => $response['h1_positive_impact_level'] ?? null,
                'h1_positive_impact_description' => $response['h1_positive_impact_description'] ?? null
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'id' => $response['id'],
                    'assessment_id' => $response['assessment_id'],
                    'question_content_id' => $response['question_content_id'],
                    'response_value' => $responseValue,
                    'answered_by' => $response['answered_by'],
                    'answered_at' => $response['answered_at'],
                    'review_status' => $response['review_status'],
                    'reviewed_by' => $response['reviewed_by'],
                    'reviewed_at' => $response['reviewed_at'],
                    'review_comments' => $response['review_comments']
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::getUserContentResponse - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得用戶回答時發生錯誤'
            ]);
        }
    }

    /**
     * 取得評估表統計結果（每人每題一筆資料）
     * GET /api/v1/question-management/assessment/{assessmentId}/statistics-results
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getStatisticsResults($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 驗證評估記錄是否存在
            $assessment = $this->assessmentModel->find($assessmentId);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的評估記錄'
                ]);
            }

            // 取得所有內容項目
            $contents = $this->contentModel->getContentsByAssessment($assessmentId);

            // 建立 content_id 到內容資訊的映射
            $contentMap = [];
            foreach ($contents as $content) {
                $contentMap[$content['id']] = $content;
            }

            // 取得所有回答記錄
            $allResponses = $this->responseModel->where('assessment_id', $assessmentId)->findAll();

            // 重新組織資料：每人每題一筆
            $results = [];
            foreach ($allResponses as $response) {
                $contentId = $response['question_content_id'];
                $userId = $response['answered_by'];

                // 取得題目資訊
                $content = $contentMap[$contentId] ?? null;
                if (!$content) {
                    continue; // 跳過找不到題目的回答
                }

                // 取得人員資訊
                $personnelInfo = $this->getPersonnelInfo($userId, $assessment['company_id'], $assessmentId);

                // 建立一筆記錄
                $record = [
                    'id' => $response['id'], // response ID
                    'content_id' => $contentId,
                    'order' => $content['sort_order'] ?? 0,
                    'category_name' => $content['category_name'] ?? '',
                    'topic_name' => $content['topic_name'] ?? '',
                    'factor_name' => $content['factor_name'] ?? '',
                    'description' => $content['description'] ?? '',

                    // 人員資訊
                    'user_id' => $userId,
                    'user_name' => $personnelInfo['user_name'] ?? '',
                    'department' => $personnelInfo['department'] ?? '',
                    'position' => $personnelInfo['position'] ?? '',

                    // C 欄位 - 風險事件
                    'c_risk_event_choice' => $response['c_risk_event_choice'] ?? '',
                    'c_risk_event_description' => $response['c_risk_event_description'] ?? '',

                    // D 欄位 - 因應行動
                    'd_counter_action_choice' => $response['d_counter_action_choice'] ?? '',
                    'd_counter_action_description' => $response['d_counter_action_description'] ?? '',
                    'd_counter_action_cost' => $response['d_counter_action_cost'] ?? '',

                    // E1 欄位 - 風險描述
                    'e1_risk_description' => $response['e1_risk_description'] ?? '',
                    // E2 欄位 - 風險財務評估
                    'e2_risk_probability' => $response['e2_risk_probability'] ?? '',
                    'e2_risk_impact' => $response['e2_risk_impact'] ?? '',
                    'e2_risk_calculation' => $response['e2_risk_calculation'] ?? '',

                    // F1 欄位 - 機會描述
                    'f1_opportunity_description' => $response['f1_opportunity_description'] ?? '',
                    // F2 欄位 - 機會財務評估
                    'f2_opportunity_probability' => $response['f2_opportunity_probability'] ?? '',
                    'f2_opportunity_impact' => $response['f2_opportunity_impact'] ?? '',
                    'f2_opportunity_calculation' => $response['f2_opportunity_calculation'] ?? '',

                    // G1 欄位 - 負面衝擊
                    'g1_negative_impact_level' => $response['g1_negative_impact_level'] ?? '',
                    'g1_negative_impact_description' => $response['g1_negative_impact_description'] ?? '',

                    // H1 欄位 - 正面衝擊
                    'h1_positive_impact_level' => $response['h1_positive_impact_level'] ?? '',
                    'h1_positive_impact_description' => $response['h1_positive_impact_description'] ?? '',

                    // 其他資訊
                    'answered_at' => $response['answered_at'] ?? '',
                    'review_status' => $response['review_status'] ?? 'pending',
                ];

                $results[] = $record;
            }

            // 根據 order 排序
            usort($results, function($a, $b) {
                if ($a['order'] != $b['order']) {
                    return $a['order'] - $b['order'];
                }
                // 相同題目時，按部門排序
                return strcmp($a['department'], $b['department']);
            });

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'assessment' => [
                        'id' => $assessment['id'],
                        'template_version' => $assessment['template_version'] ?? '預設範本',
                        'year' => $assessment['assessment_year'] ?? date('Y')
                    ],
                    'results' => $results,
                    'summary' => [
                        'total_contents' => count($contents),
                        'total_responses' => count($allResponses),
                        'total_records' => count($results)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::getStatisticsResults - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得統計結果時發生錯誤'
            ]);
        }
    }

    /**
     * 取得評估記錄的量表資料（從範本獲取）
     * GET /api/v1/question-management/assessment/{assessmentId}/scales
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getAssessmentScales($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 取得評估記錄
            $assessment = $this->assessmentModel->find($assessmentId);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的評估記錄'
                ]);
            }

            $templateId = $assessment['template_id'];
            if (!$templateId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄未指定範本ID'
                ]);
            }

            // 從範本載入量表資料
            $probabilityScaleModel = new \App\Models\RiskAssessment\ProbabilityScaleModel();
            $probabilityColumnModel = new \App\Models\RiskAssessment\ProbabilityScaleColumnModel();
            $probabilityRowModel = new \App\Models\RiskAssessment\ProbabilityScaleRowModel();

            $impactScaleModel = new \App\Models\RiskAssessment\ImpactScaleModel();
            $impactColumnModel = new \App\Models\RiskAssessment\ImpactScaleColumnModel();
            $impactRowModel = new \App\Models\RiskAssessment\ImpactScaleRowModel();

            // 取得可能性量表
            $probabilityScale = $probabilityScaleModel->where('template_id', $templateId)->first();
            $probabilityData = null;

            if ($probabilityScale) {
                $probabilityColumns = $probabilityColumnModel
                    ->where('scale_id', $probabilityScale['id'])
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();

                $probabilityRows = $probabilityRowModel
                    ->where('scale_id', $probabilityScale['id'])
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();

                // 解析 JSON 欄位
                foreach ($probabilityRows as &$row) {
                    $row['dynamicFields'] = json_decode($row['dynamic_fields'], true);
                    unset($row['dynamic_fields']);
                }

                $probabilityData = [
                    'scale' => $probabilityScale,
                    'columns' => $probabilityColumns,
                    'rows' => $probabilityRows
                ];
            }

            // 取得財務衝擊量表
            $impactScale = $impactScaleModel->where('template_id', $templateId)->first();
            $impactData = null;

            if ($impactScale) {
                $impactColumns = $impactColumnModel
                    ->where('scale_id', $impactScale['id'])
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();

                $impactRows = $impactRowModel
                    ->where('scale_id', $impactScale['id'])
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();

                // 解析 JSON 欄位
                foreach ($impactRows as &$row) {
                    $row['dynamicFields'] = json_decode($row['dynamic_fields'], true);
                    unset($row['dynamic_fields']);
                }

                $impactData = [
                    'scale' => $impactScale,
                    'columns' => $impactColumns,
                    'rows' => $impactRows
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'probability_scale' => $probabilityData,
                    'impact_scale' => $impactData
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::getAssessmentScales - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得量表資料時發生錯誤: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 匯出評估統計資料為 Excel (CSV格式)
     * GET /api/v1/question-management/assessment/{assessmentId}/export
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function exportStatistics($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            // 取得匯出類型：individual (個別填寫) 或 merged (合併總表)
            $exportType = $this->request->getGet('type') ?? 'individual';

            // 驗證評估記錄是否存在
            $assessment = $this->assessmentModel->find($assessmentId);
            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的評估記錄'
                ]);
            }

            // 取得所有內容項目
            $contents = $this->contentModel->getContentsByAssessment($assessmentId);
            $contentMap = [];
            foreach ($contents as $content) {
                $contentMap[$content['id']] = $content;
            }

            // 取得所有回答記錄
            $allResponses = $this->responseModel->where('assessment_id', $assessmentId)->findAll();

            if ($exportType === 'individual') {
                return $this->exportIndividual($assessment, $contentMap, $allResponses);
            } else {
                return $this->exportMerged($assessment, $contentMap, $allResponses);
            }

        } catch (\Exception $e) {
            log_message('error', 'QuestionManagementController::exportStatistics - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '匯出資料時發生錯誤: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 個別填寫格式匯出
     */
    private function exportIndividual($assessment, $contentMap, $allResponses)
    {
        $csvData = [];

        // CSV 標題行
        $headers = [
            '編號', '部門', '姓名', '風險類別', '風險主題', '風險因子', '描述',
            'C風險事件', 'C風險事件描述',
            'D因應行動', 'D因應行動描述', 'D因應行動成本',
            'E1風險描述',
            'E2風險可能性', 'E2風險衝擊', 'E2計算說明',
            'F1機會描述',
            'F2機會可能性', 'F2機會衝擊', 'F2計算說明',
            'G1負面衝擊程度', 'G1評分說明',
            'H1正面影響程度', 'H1評分說明',
            '填答時間'
        ];
        $csvData[] = $headers;

        // 組織資料
        $results = [];
        foreach ($allResponses as $response) {
            $contentId = $response['question_content_id'];
            $userId = $response['answered_by'];

            $content = $contentMap[$contentId] ?? null;
            if (!$content) continue;

            $personnelInfo = $this->getPersonnelInfo($userId, $assessment['company_id'], $assessment['id']);

            $row = [
                $content['sort_order'] ?? 0,
                $personnelInfo['department'] ?? '',
                $personnelInfo['user_name'] ?? '',
                $content['category_name'] ?? '',
                $content['topic_name'] ?? '',
                $content['factor_name'] ?? '',
                strip_tags($content['description'] ?? ''),

                $response['c_risk_event_choice'] === 'yes' ? '是' : ($response['c_risk_event_choice'] === 'no' ? '否' : ''),
                strip_tags($response['c_risk_event_description'] ?? ''),

                $response['d_counter_action_choice'] === 'yes' ? '是' : ($response['d_counter_action_choice'] === 'no' ? '否' : ''),
                strip_tags($response['d_counter_action_description'] ?? ''),
                $response['d_counter_action_cost'] ?? '',

                strip_tags($response['e1_risk_description'] ?? ''),
                $response['e2_risk_probability'] ?? '',
                $response['e2_risk_impact'] ?? '',
                strip_tags($response['e2_risk_calculation'] ?? ''),

                strip_tags($response['f1_opportunity_description'] ?? ''),
                $response['f2_opportunity_probability'] ?? '',
                $response['f2_opportunity_impact'] ?? '',
                strip_tags($response['f2_opportunity_calculation'] ?? ''),

                $response['g1_negative_impact_level'] ?? '',
                strip_tags($response['g1_negative_impact_description'] ?? ''),

                $response['h1_positive_impact_level'] ?? '',
                strip_tags($response['h1_positive_impact_description'] ?? ''),

                $response['answered_at'] ?? ''
            ];

            $results[] = [
                'order' => $content['sort_order'] ?? 0,
                'department' => $personnelInfo['department'] ?? '',
                'row' => $row
            ];
        }

        // 排序
        usort($results, function($a, $b) {
            if ($a['order'] != $b['order']) {
                return $a['order'] - $b['order'];
            }
            return strcmp($a['department'], $b['department']);
        });

        foreach ($results as $item) {
            $csvData[] = $item['row'];
        }

        return $this->outputExcel($csvData, $assessment, '個別填寫');
    }

    /**
     * 合併總表格式匯出
     */
    private function exportMerged($assessment, $contentMap, $allResponses)
    {
        $csvData = [];

        // CSV 標題行
        $headers = [
            '編號', '風險類別', '風險主題', '風險因子', '描述',
            'C風險事件', 'C風險事件描述',
            'D因應行動', 'D因應行動描述', 'D因應行動成本',
            'E1風險描述',
            'E2風險可能性(平均)', 'E2風險衝擊(最大)', 'E2計算說明',
            'F1機會描述',
            'F2機會可能性(平均)', 'F2機會衝擊(最大)', 'F2計算說明',
            'G1負面衝擊程度(平均)', 'G1評分說明',
            'H1正面影響程度(平均)', 'H1評分說明'
        ];
        $csvData[] = $headers;

        // 按 content_id 分組
        $grouped = [];
        foreach ($allResponses as $response) {
            $contentId = $response['question_content_id'];
            if (!isset($grouped[$contentId])) {
                $grouped[$contentId] = [];
            }
            $grouped[$contentId][] = $response;
        }

        // 處理每個題目
        $results = [];
        foreach ($grouped as $contentId => $responses) {
            $content = $contentMap[$contentId] ?? null;
            if (!$content) continue;

            // 收集人員資訊
            $personnelNames = [];
            foreach ($responses as $response) {
                $userId = $response['answered_by'];
                $personnelInfo = $this->getPersonnelInfo($userId, $assessment['company_id'], $assessment['id']);
                $deptName = $personnelInfo['department'] ?? '';
                $userName = $personnelInfo['user_name'] ?? '';
                if ($deptName && $userName) {
                    $personnelNames[] = "<{$deptName}/{$userName}>";
                }
            }

            // 合併文字欄位 (C, D, E1, E2計算, F1, F2計算, G1, H1)
            $cDescriptions = [];
            $dDescriptions = [];
            $e1Descriptions = [];
            $e2Calculations = [];
            $f1Descriptions = [];
            $f2Calculations = [];
            $g1Descriptions = [];
            $h1Descriptions = [];

            // 數值欄位 (用於計算平均/最大)
            $e2Probabilities = [];
            $e2Impacts = [];
            $f2Probabilities = [];
            $f2Impacts = [];
            $g1Levels = [];
            $h1Levels = [];

            foreach ($responses as $idx => $response) {
                $personnelLabel = $personnelNames[$idx] ?? '';

                if (!empty($response['c_risk_event_description'])) {
                    $cDescriptions[] = strip_tags($response['c_risk_event_description']) . ' ' . $personnelLabel;
                }
                if (!empty($response['d_counter_action_description'])) {
                    $dDescriptions[] = strip_tags($response['d_counter_action_description']) . ' ' . $personnelLabel;
                }
                if (!empty($response['e1_risk_description'])) {
                    $e1Descriptions[] = strip_tags($response['e1_risk_description']) . ' ' . $personnelLabel;
                }
                if (!empty($response['e2_risk_calculation'])) {
                    $e2Calculations[] = strip_tags($response['e2_risk_calculation']) . ' ' . $personnelLabel;
                }
                if (!empty($response['f1_opportunity_description'])) {
                    $f1Descriptions[] = strip_tags($response['f1_opportunity_description']) . ' ' . $personnelLabel;
                }
                if (!empty($response['f2_opportunity_calculation'])) {
                    $f2Calculations[] = strip_tags($response['f2_opportunity_calculation']) . ' ' . $personnelLabel;
                }
                if (!empty($response['g1_negative_impact_description'])) {
                    $g1Descriptions[] = strip_tags($response['g1_negative_impact_description']) . ' ' . $personnelLabel;
                }
                if (!empty($response['h1_positive_impact_description'])) {
                    $h1Descriptions[] = strip_tags($response['h1_positive_impact_description']) . ' ' . $personnelLabel;
                }

                // 收集數值
                if (!empty($response['e2_risk_probability']) && is_numeric($response['e2_risk_probability'])) {
                    $e2Probabilities[] = floatval($response['e2_risk_probability']);
                }
                if (!empty($response['e2_risk_impact']) && is_numeric($response['e2_risk_impact'])) {
                    $e2Impacts[] = floatval($response['e2_risk_impact']);
                }
                if (!empty($response['f2_opportunity_probability']) && is_numeric($response['f2_opportunity_probability'])) {
                    $f2Probabilities[] = floatval($response['f2_opportunity_probability']);
                }
                if (!empty($response['f2_opportunity_impact']) && is_numeric($response['f2_opportunity_impact'])) {
                    $f2Impacts[] = floatval($response['f2_opportunity_impact']);
                }

                // G1, H1 的等級需要轉換成數字再計算
                $g1Level = $this->levelToNumber($response['g1_negative_impact_level'] ?? '');
                if ($g1Level > 0) $g1Levels[] = $g1Level;

                $h1Level = $this->levelToNumber($response['h1_positive_impact_level'] ?? '');
                if ($h1Level > 0) $h1Levels[] = $h1Level;
            }

            // 計算平均值和最大值
            $e2ProbAvg = !empty($e2Probabilities) ? round(array_sum($e2Probabilities) / count($e2Probabilities), 2) : '';
            $e2ImpactMax = !empty($e2Impacts) ? max($e2Impacts) : '';
            $f2ProbAvg = !empty($f2Probabilities) ? round(array_sum($f2Probabilities) / count($f2Probabilities), 2) : '';
            $f2ImpactMax = !empty($f2Impacts) ? max($f2Impacts) : '';
            $g1Avg = !empty($g1Levels) ? $this->numberToLevel(round(array_sum($g1Levels) / count($g1Levels))) : '';
            $h1Avg = !empty($h1Levels) ? $this->numberToLevel(round(array_sum($h1Levels) / count($h1Levels))) : '';

            // 組合文字欄位
            $cChoice = $responses[0]['c_risk_event_choice'] ?? '';
            $dChoice = $responses[0]['d_counter_action_choice'] ?? '';
            $dCost = $responses[0]['d_counter_action_cost'] ?? '';

            $row = [
                $content['sort_order'] ?? 0,
                $content['category_name'] ?? '',
                $content['topic_name'] ?? '',
                $content['factor_name'] ?? '',
                strip_tags($content['description'] ?? ''),

                $cChoice === 'yes' ? '是' : ($cChoice === 'no' ? '否' : ''),
                implode('; ', $cDescriptions),

                $dChoice === 'yes' ? '是' : ($dChoice === 'no' ? '否' : ''),
                implode('; ', $dDescriptions),
                $dCost,

                implode('; ', $e1Descriptions),
                $e2ProbAvg,
                $e2ImpactMax,
                implode('; ', $e2Calculations),

                implode('; ', $f1Descriptions),
                $f2ProbAvg,
                $f2ImpactMax,
                implode('; ', $f2Calculations),

                $g1Avg,
                implode('; ', $g1Descriptions),

                $h1Avg,
                implode('; ', $h1Descriptions),
            ];

            $results[] = [
                'order' => $content['sort_order'] ?? 0,
                'row' => $row
            ];
        }

        // 排序
        usort($results, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        foreach ($results as $item) {
            $csvData[] = $item['row'];
        }

        return $this->outputExcel($csvData, $assessment, '合併總表');
    }

    /**
     * 將等級轉換為數字 (用於計算平均值)
     */
    private function levelToNumber($level)
    {
        $mapping = [
            'level-1' => 1,
            'level-2' => 2,
            'level-3' => 3,
            'level-4' => 4,
            'level-5' => 5
        ];
        return $mapping[$level] ?? 0;
    }

    /**
     * 將數字轉換回等級
     */
    private function numberToLevel($number)
    {
        $mapping = [
            1 => 'level-1',
            2 => 'level-2',
            3 => 'level-3',
            4 => 'level-4',
            5 => 'level-5'
        ];
        return $mapping[$number] ?? '';
    }

    /**
     * 輸出 Excel 檔案 (.xlsx 格式)
     */
    private function outputExcel($data, $assessment, $sheetName = '統計資料')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);

        // 寫入資料
        $rowIndex = 1;
        foreach ($data as $rowData) {
            $colIndex = 'A';
            foreach ($rowData as $cellValue) {
                $sheet->setCellValue($colIndex . $rowIndex, $cellValue);
                $colIndex++;
            }
            $rowIndex++;
        }

        // 美化標題列（第一列）
        $lastColumn = chr(ord('A') + count($data[0]) - 1);
        $headerRange = 'A1:' . $lastColumn . '1';

        // 標題樣式
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'] // 藍色背景
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // 設定所有儲存格邊框
        $dataRange = 'A1:' . $lastColumn . ($rowIndex - 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // 設定資料列樣式（斑馬紋）
        for ($i = 2; $i < $rowIndex; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':' . $lastColumn . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2'] // 淺灰色背景
                    ]
                ]);
            }
        }

        // 自動調整欄寬
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 凍結首列
        $sheet->freezePane('A2');

        // 設定檔案名稱
        $filename = '評估統計_' . ($assessment['template_version'] ?? '預設範本') . '_' . date('Y-m-d_His') . '.xlsx';

        // 輸出檔案
        $writer = new Xlsx($spreadsheet);

        // 設定回應標頭
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // 輸出到 php://output
        $writer->save('php://output');
        exit;
    }
}