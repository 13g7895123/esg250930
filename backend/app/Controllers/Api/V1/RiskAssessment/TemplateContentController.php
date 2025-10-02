<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use App\Controllers\Api\BaseController;
use App\Models\RiskAssessment\TemplateContentModel;
use App\Models\RiskAssessment\RiskAssessmentTemplateModel;
use App\Models\RiskAssessment\RiskCategoryModel;
use CodeIgniter\HTTP\ResponseInterface;

class TemplateContentController extends BaseController
{
    protected $contentModel;
    protected $templateModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->contentModel = new TemplateContentModel();
        $this->templateModel = new RiskAssessmentTemplateModel();
        $this->categoryModel = new RiskCategoryModel();

        // Set UTF-8 encoding for all responses
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * Get template contents list with pagination
     * GET /api/v1/risk-assessment/templates/{templateId}/contents
     */
    public function index()
    {
        try {
            // Get template ID from URI segment
            $templateId = $this->request->uri->getSegment(5); // /api/v1/risk-assessment/templates/{templateId}/contents
            
            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            $search = $this->request->getGet('search');
            $categoryId = $this->request->getGet('category_id');
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 20);
            $sort = $this->request->getGet('sort') ?? 'sort_order';
            $order = $this->request->getGet('order') ?? 'asc';

            $builder = $this->contentModel->getContentsWithCategory($templateId, $search, $categoryId, $page, $limit, $sort, $order);

            // Get total for pagination
            $total = $builder->countAllResults(false);
            $contents = $builder->paginate($limit, 'default', $page);

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
                    'contents' => $contents,
                    'pagination' => $pagination
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template content index error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得範本內容列表失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single template content
     * GET /api/v1/risk-assessment/templates/{templateId}/contents/{id}
     */
    public function show($templateId = null, $id = null)
    {
        try {
            
            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            $content = $this->contentModel->getContentWithCategory($id);

            if (!$content || $content['template_id'] != $templateId) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本內容不存在'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $content
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template content show error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得範本內容詳細資訊失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new template content
     * POST /api/v1/risk-assessment/templates/{templateId}/contents
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
                'category_id' => 'permit_empty|integer',
                'topic_id' => 'permit_empty|integer',
                'risk_factor_id' => 'permit_empty|integer',
                'description' => 'required',
                'is_required' => 'permit_empty|in_list[0,1]',
                // 新增的題目欄位驗證規則
                'a_content' => 'permit_empty|string',
                'b_content' => 'permit_empty|string',
                'c_placeholder' => 'permit_empty|string',
                'd_placeholder_1' => 'permit_empty|string',
                'd_placeholder_2' => 'permit_empty|string',
                'e1_placeholder_1' => 'permit_empty|string',
                'e2_select_1' => 'permit_empty|max_length[50]',
                'e2_select_2' => 'permit_empty|max_length[50]',
                'e2_placeholder' => 'permit_empty|string',
                'f2_select_1' => 'permit_empty|max_length[50]',
                'f2_select_2' => 'permit_empty|max_length[50]',
                'f2_placeholder' => 'permit_empty|string',
                // 資訊圖示懸浮文字欄位
                'e1_info' => 'permit_empty|string',
                'f1_info' => 'permit_empty|string',
                'g1_info' => 'permit_empty|string',
                'h1_info' => 'permit_empty|string'
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
            $categoryId = $input['categoryId'] ?? null;
            if (!empty($categoryId)) {
                $category = $this->categoryModel->where('id', $categoryId)
                                                ->where('template_id', $templateId)
                                                ->first();
                if (!$category) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => '指定的風險分類不存在或不屬於此範本'
                    ]);
                }
            }

            $data = [
                'template_id' => $templateId,
                'category_id' => $categoryId,
                'topic_id' => $input['topicId'] ?? null,
                'risk_factor_id' => $input['riskFactorId'] ?? null,
                'description' => $input['description'],
                'sort_order' => $this->contentModel->getNextSortOrder($templateId),
                'is_required' => $input['is_required'] ?? 1,
                // 新增的題目欄位
                'a_content' => $input['a_content'] ?? null,
                'b_content' => $input['b_content'] ?? null,
                'c_placeholder' => $input['c_placeholder'] ?? null,
                'd_placeholder_1' => $input['d_placeholder_1'] ?? null,
                'd_placeholder_2' => $input['d_placeholder_2'] ?? null,
                'e1_placeholder_1' => $input['e1_placeholder_1'] ?? null,
                'e2_select_1' => $input['e2_select_1'] ?? null,
                'e2_select_2' => $input['e2_select_2'] ?? null,
                'e2_placeholder' => $input['e2_placeholder'] ?? null,
                'f2_select_1' => $input['f2_select_1'] ?? null,
                'f2_select_2' => $input['f2_select_2'] ?? null,
                'f2_placeholder' => $input['f2_placeholder'] ?? null,
                // 資訊圖示懸浮文字欄位
                'e1_info' => $input['e1_info'] ?? null,
                'f1_info' => $input['f1_info'] ?? null,
                'g1_info' => $input['g1_info'] ?? null,
                'h1_info' => $input['h1_info'] ?? null
            ];

            $contentId = $this->contentModel->insert($data);
            
            if (!$contentId) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '建立範本內容失敗',
                    'errors' => $this->contentModel->errors()
                ]);
            }

            $content = $this->contentModel->getContentWithCategory($contentId);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '範本內容建立成功',
                'data' => $content
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template content create error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '建立範本內容失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update template content
     * PUT /api/v1/risk-assessment/templates/{templateId}/contents/{id}
     */
    public function update($templateId = null, $id = null)
    {
        try {
            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if content exists and belongs to template
            $content = $this->contentModel->where('template_id', $templateId)->find($id);
            if (!$content) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本內容不存在'
                ]);
            }

            $rules = [
                'category_id' => 'permit_empty|integer',
                'topic_id' => 'permit_empty|integer',
                'risk_factor_id' => 'permit_empty|integer',
                'description' => 'permit_empty|string',
                'is_required' => 'permit_empty|in_list[0,1]',
                // 新增的題目欄位驗證規則
                'a_content' => 'permit_empty|string',
                'b_content' => 'permit_empty|string',
                'c_placeholder' => 'permit_empty|string',
                'd_placeholder_1' => 'permit_empty|string',
                'd_placeholder_2' => 'permit_empty|string',
                'e1_placeholder_1' => 'permit_empty|string',
                'e2_select_1' => 'permit_empty|max_length[50]',
                'e2_select_2' => 'permit_empty|max_length[50]',
                'e2_placeholder' => 'permit_empty|string',
                'f2_select_1' => 'permit_empty|max_length[50]',
                'f2_select_2' => 'permit_empty|max_length[50]',
                'f2_placeholder' => 'permit_empty|string',
                // 資訊圖示懸浮文字欄位
                'e1_info' => 'permit_empty|string',
                'f1_info' => 'permit_empty|string',
                'g1_info' => 'permit_empty|string',
                'h1_info' => 'permit_empty|string'
            ];

            // Get input data (handles both PUT form data and JSON)
            $input = $this->request->getJSON(true) ?? $this->request->getRawInput();
            if (!$this->validate($rules, $input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Check if category exists and belongs to template
            // IMPORTANT: Preserve original category_id if not provided in input
            $categoryId = isset($input['categoryId']) ? $input['categoryId'] : $content['category_id'];
            if (!empty($categoryId)) {
                $category = $this->categoryModel->where('id', $categoryId)
                                                ->where('template_id', $templateId)
                                                ->first();
                if (!$category) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => '指定的風險分類不存在或不屬於此範本'
                    ]);
                }
            }

            $data = [
                'category_id' => $categoryId,
                'topic_id' => isset($input['topicId']) ? $input['topicId'] : ($content['topic_id'] ?? null),
                'risk_factor_id' => isset($input['riskFactorId']) ? $input['riskFactorId'] : ($content['risk_factor_id'] ?? null),
                'description' => $input['description'] ?? $content['description'],
                'is_required' => $input['is_required'] ?? $content['is_required'],
                // 新增的題目欄位
                'a_content' => $input['a_content'] ?? $content['a_content'],
                'b_content' => $input['b_content'] ?? $content['b_content'],
                'c_placeholder' => $input['c_placeholder'] ?? $content['c_placeholder'],
                'd_placeholder_1' => $input['d_placeholder_1'] ?? $content['d_placeholder_1'],
                'd_placeholder_2' => $input['d_placeholder_2'] ?? $content['d_placeholder_2'],
                'e1_placeholder_1' => $input['e1_placeholder_1'] ?? $content['e1_placeholder_1'],
                'e1_select_1' => $input['e1_select_1'] ?? $content['e1_select_1'],
                'e1_select_2' => $input['e1_select_2'] ?? $content['e1_select_2'],
                'e1_placeholder_2' => $input['e1_placeholder_2'] ?? $content['e1_placeholder_2'],
                // 資訊圖示懸浮文字欄位
                'e1_info' => $input['e1_info'] ?? $content['e1_info'],
                'f1_info' => $input['f1_info'] ?? $content['f1_info'],
                'g1_info' => $input['g1_info'] ?? $content['g1_info'],
                'h1_info' => $input['h1_info'] ?? $content['h1_info']
            ];

            $success = $this->contentModel->update($id, $data);
            
            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新範本內容失敗',
                    'errors' => $this->contentModel->errors()
                ]);
            }

            $updatedContent = $this->contentModel->getContentWithCategory($id);

            return $this->response->setJSON([
                'success' => true,
                'message' => '範本內容更新成功',
                'data' => $updatedContent
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template content update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新範本內容失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete template content (hard delete)
     * DELETE /api/v1/risk-assessment/templates/{templateId}/contents/{id}
     */
    public function delete($templateId = null, $id = null)
    {
        try {
            
            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if content exists and belongs to template
            $content = $this->contentModel->where('template_id', $templateId)->find($id);
            if (!$content) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本內容不存在'
                ]);
            }

            // Hard delete content
            $success = $this->contentModel->delete($id, true);
            
            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '刪除範本內容失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '範本內容刪除成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template content delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除範本內容失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reorder template contents
     * PUT /api/v1/risk-assessment/templates/{templateId}/contents/reorder
     */
    public function reorder()
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
                'orders' => 'required|array',
                'orders.*.id' => 'required|integer',
                'orders.*.sort_order' => 'required|integer'
            ];

            // Get input data (handles both PUT form data and JSON)
            $input = $this->request->getJSON(true) ?? $this->request->getRawInput();
            if (!$this->validate($rules, $input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                foreach ($input['orders'] as $order) {
                    // Verify content belongs to template
                    $content = $this->contentModel->where('id', $order['id'])
                                                  ->where('template_id', $templateId)
                                                  ->first();
                    
                    if (!$content) {
                        throw new \Exception("範本內容 ID {$order['id']} 不存在或不屬於此範本");
                    }

                    // Update sort order
                    $this->contentModel->update($order['id'], ['sort_order' => $order['sort_order']]);
                }

                $db->transComplete();

                if ($db->transStatus() === FALSE) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'success' => false,
                        'message' => '更新排序失敗'
                    ]);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => '排序更新成功'
                ]);

            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            log_message('error', 'Template content reorder error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新排序失敗: ' . $e->getMessage()
            ]);
        }
    }
}