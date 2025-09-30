<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use App\Controllers\Api\BaseController;
use App\Models\RiskAssessment\AssessmentQuestionModel;
use App\Models\RiskAssessment\RiskAssessmentTemplateModel;
use App\Models\RiskAssessment\RiskCategoryModel;
use App\Models\RiskAssessment\RiskTopicModel;
use App\Models\RiskAssessment\RiskFactorModel;
use CodeIgniter\HTTP\ResponseInterface;

class AssessmentQuestionController extends BaseController
{
    protected $questionModel;
    protected $templateModel;
    protected $categoryModel;
    protected $topicModel;
    protected $factorModel;

    public function __construct()
    {
        $this->questionModel = new AssessmentQuestionModel();
        $this->templateModel = new RiskAssessmentTemplateModel();
        $this->categoryModel = new RiskCategoryModel();
        $this->topicModel = new RiskTopicModel();
        $this->factorModel = new RiskFactorModel();
    }

    /**
     * Get questions list for template
     * GET /api/v1/risk-assessment/templates/{templateId}/contents
     */
    public function index()
    {
        try {
            // Get template ID from URI segment
            $templateId = $this->request->uri->getSegment(5);

            if (!$templateId || !is_numeric($templateId)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '無效的範本ID'
                ]);
            }

            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Get query parameters
            $params = [
                'search' => $this->request->getGet('search'),
                'category_id' => $this->request->getGet('category_id'),
                'topic_id' => $this->request->getGet('topic_id'),
                'status' => $this->request->getGet('status'),
                'sort' => $this->request->getGet('sort', FILTER_SANITIZE_STRING),
                'order' => $this->request->getGet('order', FILTER_SANITIZE_STRING),
                'page' => max(1, (int)$this->request->getGet('page', FILTER_VALIDATE_INT) ?: 1),
                'limit' => min(100, max(1, (int)$this->request->getGet('limit', FILTER_VALIDATE_INT) ?: 20))
            ];

            // Get questions with pagination
            $builder = $this->questionModel->getQuestionsWithRelations($templateId, $params);
            $questions = $builder->get()->getResultArray();

            // Manual pagination for now (could be improved with proper CodeIgniter pagination)
            $total = count($questions);
            $offset = ($params['page'] - 1) * $params['limit'];
            $questions = array_slice($questions, $offset, $params['limit']);

            $lastPage = ceil($total / $params['limit']);
            $hasMore = $params['page'] < $lastPage;

            // Format response to match existing API structure
            $formattedQuestions = array_map(function($question) {
                return [
                    'id' => $question['id'],
                    'template_id' => $question['template_id'],
                    'category_id' => $question['category_id'],
                    'topic_id' => $question['topic_id'],
                    'risk_factor_id' => $question['risk_factor_id'],
                    'factor_name' => $question['question_title'], // 映射到前端期待的字段
                    'description' => $question['question_description'],
                    'question_type' => $question['question_type'],
                    'scoring_method' => $question['scoring_method'],
                    'scoring_options' => $question['scoring_options'],
                    'weight' => $question['weight'],
                    'sort_order' => $question['sort_order'],
                    'status' => $question['status'],
                    'created_at' => $question['created_at'],
                    'updated_at' => $question['updated_at'],
                    'category_name' => $question['category_name'],
                    'topic_name' => $question['topic_name'],
                    'risk_factor_name' => $question['factor_name']
                ];
            }, $questions);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'factors' => $formattedQuestions, // 保持兼容性，使用 'factors' 作為 key
                    'pagination' => [
                        'current_page' => $params['page'],
                        'per_page' => $params['limit'],
                        'total' => $total,
                        'last_page' => $lastPage,
                        'has_more' => $hasMore
                    ]
                ],
                'message' => '評估題目列表獲取成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Assessment questions index error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '獲取評估題目列表失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single question
     * GET /api/v1/risk-assessment/templates/{templateId}/contents/{id}
     */
    public function show($templateId = null, $id = null)
    {
        try {
            if (!$templateId || !$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '缺少必要參數'
                ]);
            }

            $question = $this->questionModel->getQuestionWithRelations($templateId, $id);

            if (!$question) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '評估題目不存在'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $question,
                'message' => '評估題目獲取成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Assessment question show error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '獲取評估題目失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new question
     * POST /api/v1/risk-assessment/templates/{templateId}/contents
     */
    public function create($templateId = null)
    {
        try {
            if (!$templateId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '缺少範本ID'
                ]);
            }

            $input = $this->request->getJSON(true);

            if (empty($input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '請提供有效的請求資料'
                ]);
            }

            // Validate template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Get risk factor name for question title
            $riskFactorName = '未命名題目';
            if (!empty($input['riskFactorId'])) {
                $riskFactor = $this->factorModel->find($input['riskFactorId']);
                if ($riskFactor && !empty($riskFactor['factor_name'])) {
                    $riskFactorName = $riskFactor['factor_name'];
                }
            }

            // Prepare data for insertion
            $questionData = [
                'template_id' => $templateId,
                'category_id' => $input['categoryId'] ?? null,
                'topic_id' => $input['topicId'] ?? null,
                'risk_factor_id' => $input['riskFactorId'] ?? null,
                'question_title' => $riskFactorName,
                'question_description' => $input['description'] ?? '',
                'question_type' => $input['questionType'] ?? 'scale_rating',
                'scoring_method' => $input['scoringMethod'] ?? 'scale_1_5',
                'scoring_options' => isset($input['scoringOptions']) ? json_encode($input['scoringOptions']) : null,
                'weight' => $input['weight'] ?? 1.00,
                'sort_order' => $input['sortOrder'] ?? 0,
                'status' => $input['status'] ?? 'active'
            ];

            // Insert question
            $questionId = $this->questionModel->insert($questionData);

            if (!$questionId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '新增評估題目失敗',
                    'errors' => $this->questionModel->errors()
                ]);
            }

            // Get the created question with relations
            $newQuestion = $this->questionModel->getQuestionWithRelations($templateId, $questionId);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'data' => $newQuestion,
                'message' => '評估題目新增成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Assessment question create error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '新增評估題目失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update question
     * PUT /api/v1/risk-assessment/templates/{templateId}/contents/{id}
     */
    public function update($templateId = null, $id = null)
    {
        try {
            if (!$templateId || !$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '缺少必要參數'
                ]);
            }

            // Check if question exists
            $existingQuestion = $this->questionModel
                ->where('template_id', $templateId)
                ->where('id', $id)
                ->first();

            if (!$existingQuestion) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '評估題目不存在'
                ]);
            }

            $input = $this->request->getJSON(true);

            if (empty($input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '請提供有效的更新資料'
                ]);
            }

            // Prepare data for update
            $updateData = [];
            if (isset($input['categoryId'])) $updateData['category_id'] = $input['categoryId'];
            if (isset($input['topicId'])) $updateData['topic_id'] = $input['topicId'];
            if (isset($input['riskFactorId'])) {
                $updateData['risk_factor_id'] = $input['riskFactorId'];

                // Update question_title based on new risk factor
                $riskFactor = $this->factorModel->find($input['riskFactorId']);
                if ($riskFactor && !empty($riskFactor['factor_name'])) {
                    $updateData['question_title'] = $riskFactor['factor_name'];
                } else {
                    $updateData['question_title'] = '未命名題目';
                }
            }
            if (isset($input['description'])) $updateData['question_description'] = $input['description'];
            if (isset($input['questionType'])) $updateData['question_type'] = $input['questionType'];
            if (isset($input['scoringMethod'])) $updateData['scoring_method'] = $input['scoringMethod'];
            if (isset($input['scoringOptions'])) {
                $updateData['scoring_options'] = json_encode($input['scoringOptions']);
            }
            if (isset($input['weight'])) $updateData['weight'] = $input['weight'];
            if (isset($input['sortOrder'])) $updateData['sort_order'] = $input['sortOrder'];
            if (isset($input['status'])) $updateData['status'] = $input['status'];

            // Update question
            $success = $this->questionModel
                ->where('template_id', $templateId)
                ->where('id', $id)
                ->set($updateData)
                ->update();

            if (!$success) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '更新評估題目失敗',
                    'errors' => $this->questionModel->errors()
                ]);
            }

            // Get updated question with relations
            $updatedQuestion = $this->questionModel->getQuestionWithRelations($templateId, $id);

            return $this->response->setJSON([
                'success' => true,
                'data' => $updatedQuestion,
                'message' => '評估題目更新成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Assessment question update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新評估題目失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete question
     * DELETE /api/v1/risk-assessment/templates/{templateId}/contents/{id}
     */
    public function delete($templateId = null, $id = null)
    {
        try {
            if (!$templateId || !$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '缺少必要參數'
                ]);
            }

            // Check if question exists
            $question = $this->questionModel
                ->where('template_id', $templateId)
                ->where('id', $id)
                ->first();

            if (!$question) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '評估題目不存在'
                ]);
            }

            // Delete question
            $success = $this->questionModel
                ->where('template_id', $templateId)
                ->where('id', $id)
                ->delete();

            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '刪除評估題目失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '評估題目刪除成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Assessment question delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除評估題目失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reorder questions
     * PUT /api/v1/risk-assessment/templates/{templateId}/contents/reorder
     */
    public function reorder($templateId = null)
    {
        try {
            if (!$templateId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '缺少範本ID'
                ]);
            }

            $input = $this->request->getJSON(true);

            if (empty($input['questionIds']) || !is_array($input['questionIds'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '請提供有效的題目ID順序'
                ]);
            }

            // Reorder questions
            $success = $this->questionModel->reorderQuestions($templateId, $input['questionIds']);

            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '重新排序失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '題目順序更新成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Assessment questions reorder error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '重新排序失敗：' . $e->getMessage()
            ]);
        }
    }
}