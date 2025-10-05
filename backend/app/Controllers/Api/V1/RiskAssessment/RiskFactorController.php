<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use App\Controllers\Api\BaseController;
use App\Models\RiskAssessment\RiskFactorModel;
use App\Models\RiskAssessment\RiskAssessmentTemplateModel;
use App\Models\RiskAssessment\RiskCategoryModel;
use App\Models\RiskAssessment\RiskTopicModel;
use CodeIgniter\HTTP\ResponseInterface;

class RiskFactorController extends BaseController
{
    protected $factorModel;
    protected $templateModel;
    protected $categoryModel;
    protected $topicModel;

    public function __construct()
    {
        $this->factorModel = new RiskFactorModel();
        $this->templateModel = new RiskAssessmentTemplateModel();
        $this->categoryModel = new RiskCategoryModel();
        $this->topicModel = new RiskTopicModel();
    }

    /**
     * Get factors list for template
     * GET /api/v1/risk-assessment/templates/{templateId}/factors
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

            // Check if template exists with database error handling
            try {
                $template = $this->templateModel->find($templateId);
            } catch (\Exception $dbError) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '資料庫連線失敗: ' . $dbError->getMessage()
                ]);
            }

            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            $search = $this->request->getGet('search');
            $categoryId = $this->request->getGet('category_id');
            $topicId = $this->request->getGet('topic_id');

            if ($categoryId || $topicId) {
                $factors = $this->factorModel->getFactorsByCategory($templateId, $categoryId, $topicId);
            } else {
                $factors = $this->factorModel->getFactorsWithRelations($templateId, $search);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'factors' => $factors
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk factor index error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得風險因子列表失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single risk factor
     * GET /api/v1/risk-assessment/templates/{templateId}/factors/{id}
     */
    public function show($id = null)
    {
        try {
            // Get template ID and factor ID from URI segments
            $templateId = $this->request->uri->getSegment(5);
            $factorId = $this->request->uri->getSegment(7); // factors/{id}

            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if factor exists and belongs to template
            $factor = $this->factorModel->where('template_id', $templateId)->find($factorId);
            if (!$factor) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '風險因子不存在'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $factor
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk factor show error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得風險因子失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new risk factor
     * POST /api/v1/risk-assessment/templates/{templateId}/factors
     */
    public function create()
    {
        try {
            // Get template ID from URI segment
            $templateId = $this->request->uri->getSegment(5);

            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            $rules = [
                'category_id' => 'required|integer',
                'topic_id' => 'permit_empty|integer',
                'description' => 'permit_empty',
                'scoring_method' => 'permit_empty|in_list[binary,scale_1_5,scale_1_10,percentage]',
                'weight' => 'permit_empty|decimal',
                'sort_order' => 'permit_empty|integer',
                'is_required' => 'permit_empty|in_list[0,1]',
                'status' => 'permit_empty|in_list[active,inactive]'
            ];

            // Get input data (handles both POST form data and JSON)
            $input = $this->request->getJSON(true) ?? $this->request->getPost();

            if (!$this->validate($rules, $input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Check if category exists and belongs to template
            $category = $this->categoryModel->where('template_id', $templateId)->find($input['category_id']);
            if (!$category) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '指定的風險分類不存在'
                ]);
            }

            // Check if topic exists and belongs to template and category (if provided)
            $topicId = !empty($input['topic_id']) ? $input['topic_id'] : null;
            if ($topicId !== null) {
                $topic = $this->topicModel->where('template_id', $templateId)
                    ->where('category_id', $input['category_id'])
                    ->find($topicId);
                if (!$topic) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => '指定的風險主題不存在或不屬於該分類'
                    ]);
                }
            }


            $data = [
                'template_id' => $templateId,
                'category_id' => $input['category_id'],
                'topic_id' => $topicId,  // Will be null if not provided or empty
                'factor_name' => $input['factor_name'] ?? ($input['topic'] ?? ''), // Support both field names
                'description' => !empty($input['description']) ? $input['description'] : null,
                'status' => $input['status'] ?? 'active'
            ];

            $factorId = $this->factorModel->insert($data);

            if (!$factorId) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '建立風險因子失敗',
                    'errors' => $this->factorModel->errors()
                ]);
            }

            $factors = $this->factorModel->getFactorsWithRelations($templateId);
            $createdFactor = array_filter($factors, function($f) use ($factorId) {
                return $f['id'] == $factorId;
            });
            $createdFactor = reset($createdFactor);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '風險因子建立成功',
                'data' => $createdFactor ?: $this->factorModel->find($factorId)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk factor create error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '建立風險因子失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update risk factor
     * PUT /api/v1/risk-assessment/templates/{templateId}/factors/{id}
     */
    public function update($id = null)
    {
        try {
            // Get template ID and factor ID from URI segments
            $templateId = $this->request->uri->getSegment(5);
            $factorId = $this->request->uri->getSegment(7); // factors/{id}

            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if factor exists and belongs to template
            $factor = $this->factorModel->where('template_id', $templateId)->find($factorId);
            if (!$factor) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '風險因子不存在'
                ]);
            }

            $rules = [
                'factor_name' => 'permit_empty|max_length[255]',
                'topic' => 'permit_empty|max_length[255]',
                'category_id' => 'required|integer',
                'topic_id' => 'permit_empty|integer',
                'description' => 'permit_empty',
                'scoring_method' => 'permit_empty|in_list[binary,scale_1_5,scale_1_10,percentage]',
                'weight' => 'permit_empty|decimal',
                'sort_order' => 'permit_empty|integer',
                'is_required' => 'permit_empty|in_list[0,1]',
                'status' => 'permit_empty|in_list[active,inactive]'
            ];

            // Get input data (handles both POST form data and JSON)
            $input = $this->request->getJSON(true) ?? $this->request->getRawInput();

            if (!$this->validate($rules, $input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Check if category exists and belongs to template
            $category = $this->categoryModel->where('template_id', $templateId)->find($input['category_id']);
            if (!$category) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '指定的風險分類不存在'
                ]);
            }

            // Check if topic exists and belongs to template and category (if provided)
            $topicId = !empty($input['topic_id']) ? $input['topic_id'] : null;
            if ($topicId !== null) {
                $topic = $this->topicModel->where('template_id', $templateId)
                    ->where('category_id', $input['category_id'])
                    ->find($topicId);
                if (!$topic) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => '指定的風險主題不存在或不屬於該分類'
                    ]);
                }
            }


            $data = [
                'category_id' => $input['category_id'],
                'topic_id' => $topicId,  // Will be null if not provided or empty
                'factor_name' => $input['factor_name'] ?? ($input['topic'] ?? ''), // Support both field names
                'description' => isset($input['description']) ? (!empty($input['description']) ? $input['description'] : null) : $factor['description'],
                'status' => $input['status'] ?? $factor['status']
            ];

            $success = $this->factorModel->update($factorId, $data);

            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新風險因子失敗',
                    'errors' => $this->factorModel->errors()
                ]);
            }

            $factors = $this->factorModel->getFactorsWithRelations($templateId);
            $updatedFactor = array_filter($factors, function($f) use ($factorId) {
                return $f['id'] == $factorId;
            });
            $updatedFactor = reset($updatedFactor);

            return $this->response->setJSON([
                'success' => true,
                'message' => '風險因子更新成功',
                'data' => $updatedFactor ?: $this->factorModel->find($factorId)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk factor update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新風險因子失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete risk factor (hard delete)
     * DELETE /api/v1/risk-assessment/templates/{templateId}/factors/{id}
     */
    public function delete($id = null)
    {
        try {
            // Get template ID and factor ID from URI segments
            $templateId = $this->request->uri->getSegment(5);
            $factorId = $this->request->uri->getSegment(7); // factors/{id}

            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if factor exists and belongs to template
            $factor = $this->factorModel->where('template_id', $templateId)->find($factorId);
            if (!$factor) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '風險因子不存在'
                ]);
            }

            // Hard delete factor
            $success = $this->factorModel->delete($factorId, true);

            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '刪除風險因子失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '風險因子刪除成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk factor delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除風險因子失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reorder factors within a template
     * PUT /api/v1/risk-assessment/templates/{templateId}/factors/reorder
     */
    public function reorder()
    {
        try {
            $templateId = $this->request->uri->getSegment(5);

            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            $input = $this->request->getJSON(true) ?? $this->request->getRawInput();

            if (!isset($input['orders']) || !is_array($input['orders'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '排序資料格式錯誤'
                ]);
            }

            $success = $this->factorModel->reorderFactors($templateId, $input['orders']);

            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '重新排序失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '重新排序成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk factor reorder error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '重新排序失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get factor statistics for a template
     * GET /api/v1/risk-assessment/templates/{templateId}/factors/stats
     */
    public function stats()
    {
        try {
            $templateId = $this->request->uri->getSegment(5);

            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            $stats = $this->factorModel->getFactorStats($templateId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk factor stats error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得統計資料失敗: ' . $e->getMessage()
            ]);
        }
    }
}