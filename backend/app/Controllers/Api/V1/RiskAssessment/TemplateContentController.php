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

            // If limit is 0, return all contents without pagination
            if ($limit === 0) {
                $contents = $builder->orderBy($sort, $order)->findAll();

                return $this->response->setJSON([
                    'success' => true,
                    'data' => [
                        'contents' => $contents,
                        'pagination' => [
                            'current_page' => 1,
                            'per_page' => $total,
                            'total' => $total,
                            'total_pages' => 1,
                            'has_next' => false,
                            'has_prev' => false
                        ]
                    ]
                ]);
            }

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
                'is_required' => 'permit_empty|in_list[0,1]',
                // 題目欄位驗證規則
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

            // Update risk factor description if provided
            if (!empty($input['riskFactorId']) && isset($input['factorDescription'])) {
                $factorModel = new \App\Models\RiskAssessment\RiskFactorModel();
                $factorModel->update($input['riskFactorId'], [
                    'description' => $input['factorDescription']
                ]);
            }

            $data = [
                'template_id' => $templateId,
                'category_id' => $categoryId,
                'topic_id' => $input['topicId'] ?? null,
                'risk_factor_id' => $input['riskFactorId'] ?? null,
                'sort_order' => $this->contentModel->getNextSortOrder($templateId),
                'is_required' => $input['is_required'] ?? 1,
                // 新增的題目欄位
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
                'is_required' => 'permit_empty|in_list[0,1]',
                // 題目欄位驗證規則
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

            // Update risk factor description if provided
            $riskFactorId = isset($input['riskFactorId']) ? $input['riskFactorId'] : ($content['risk_factor_id'] ?? null);
            if (!empty($riskFactorId) && isset($input['factorDescription'])) {
                $factorModel = new \App\Models\RiskAssessment\RiskFactorModel();
                $factorModel->update($riskFactorId, [
                    'description' => $input['factorDescription']
                ]);
            }

            $data = [
                'category_id' => $categoryId,
                'topic_id' => isset($input['topicId']) ? $input['topicId'] : ($content['topic_id'] ?? null),
                'risk_factor_id' => $riskFactorId,
                'is_required' => $input['is_required'] ?? ($content['is_required'] ?? 1),
                // 新增的題目欄位
                'b_content' => $input['b_content'] ?? ($content['b_content'] ?? null),
                'c_placeholder' => $input['c_placeholder'] ?? ($content['c_placeholder'] ?? null),
                'd_placeholder_1' => $input['d_placeholder_1'] ?? ($content['d_placeholder_1'] ?? null),
                'd_placeholder_2' => $input['d_placeholder_2'] ?? ($content['d_placeholder_2'] ?? null),
                'e1_placeholder_1' => $input['e1_placeholder_1'] ?? ($content['e1_placeholder_1'] ?? null),
                'e2_select_1' => $input['e2_select_1'] ?? ($content['e2_select_1'] ?? null),
                'e2_select_2' => $input['e2_select_2'] ?? ($content['e2_select_2'] ?? null),
                'e2_placeholder' => $input['e2_placeholder'] ?? ($content['e2_placeholder'] ?? null),
                'f2_select_1' => $input['f2_select_1'] ?? ($content['f2_select_1'] ?? null),
                'f2_select_2' => $input['f2_select_2'] ?? ($content['f2_select_2'] ?? null),
                'f2_placeholder' => $input['f2_placeholder'] ?? ($content['f2_placeholder'] ?? null),
                // 資訊圖示懸浮文字欄位
                'e1_info' => $input['e1_info'] ?? ($content['e1_info'] ?? null),
                'f1_info' => $input['f1_info'] ?? ($content['f1_info'] ?? null),
                'g1_info' => $input['g1_info'] ?? ($content['g1_info'] ?? null),
                'h1_info' => $input['h1_info'] ?? ($content['h1_info'] ?? null)
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

    /**
     * Batch import template contents
     * POST /api/v1/risk-assessment/templates/{templateId}/contents/batch-import
     */
    public function batchImport()
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

            $input = $this->request->getJSON(true);
            $items = $input['items'] ?? [];

            if (empty($items)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '沒有要匯入的資料'
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $imported = 0;
            $errors = [];

            // Load required models
            $topicModel = new \App\Models\RiskAssessment\RiskTopicModel();
            $factorModel = new \App\Models\RiskAssessment\RiskFactorModel();

            foreach ($items as $index => $item) {
                try {
                    // Validate required fields
                    if (empty($item['categoryName']) || empty($item['factorName'])) {
                        $errors[] = "第 " . ($index + 2) . " 行：缺少必填欄位（風險類別、風險因子）";
                        continue;
                    }

                    // 1. Find or create category
                    $category = $this->categoryModel
                        ->where('template_id', $templateId)
                        ->where('category_name', $item['categoryName'])
                        ->first();

                    if (!$category) {
                        $categoryId = $this->categoryModel->insert([
                            'template_id' => $templateId,
                            'category_name' => $item['categoryName']
                        ]);
                        $category = ['id' => $categoryId];
                    }

                    // 2. Find or create topic (if provided)
                    $topicId = null;
                    if (!empty($item['topicName'])) {
                        $topic = $topicModel
                            ->where('template_id', $templateId)
                            ->where('category_id', $category['id'])
                            ->where('topic_name', $item['topicName'])
                            ->first();

                        if (!$topic) {
                            $topicId = $topicModel->insert([
                                'template_id' => $templateId,
                                'category_id' => $category['id'],
                                'topic_name' => $item['topicName']
                            ]);
                        } else {
                            $topicId = $topic['id'];
                        }
                    }

                    // 3. Find or create factor (if provided)
                    $factorId = null;
                    if (!empty($item['factorName'])) {
                        $factor = $factorModel
                            ->where('template_id', $templateId)
                            ->where('category_id', $category['id'])
                            ->where('factor_name', $item['factorName'])
                            ->first();

                        if (!$factor) {
                            $factorData = [
                                'template_id' => $templateId,
                                'category_id' => $category['id'],
                                'factor_name' => $item['factorName']
                            ];

                            // Add topic_id if exists
                            if ($topicId) {
                                $factorData['topic_id'] = $topicId;
                            }

                            $factorId = $factorModel->insert($factorData);
                        } else {
                            $factorId = $factor['id'];
                        }
                    }

                    // 4. Create content
                    $contentData = [
                        'template_id' => $templateId,
                        'category_id' => $category['id'],
                        'topic_id' => $topicId,
                        'risk_factor_id' => $factorId,
                        'sort_order' => $item['sort_order'] ?? ($imported + 1),
                        'is_required' => $item['is_required'] ?? 0,
                        'b_content' => $item['b_content'] ?? null,
                        'c_placeholder' => $item['c_placeholder'] ?? null,
                        'd_placeholder_1' => $item['d_placeholder_1'] ?? null,
                        'd_placeholder_2' => $item['d_placeholder_2'] ?? null,
                        'e1_placeholder_1' => $item['e1_placeholder_1'] ?? null,
                        'e2_select_1' => $item['e2_select_1'] ?? null,
                        'e2_select_2' => $item['e2_select_2'] ?? null,
                        'e2_placeholder' => $item['e2_placeholder'] ?? null,
                        'f2_select_1' => $item['f2_select_1'] ?? null,
                        'f2_select_2' => $item['f2_select_2'] ?? null,
                        'f2_placeholder' => $item['f2_placeholder'] ?? null,
                        'e1_info' => $item['e1_info'] ?? null,
                        'f1_info' => $item['f1_info'] ?? null,
                        'g1_info' => $item['g1_info'] ?? null,
                        'h1_info' => $item['h1_info'] ?? null
                    ];

                    $this->contentModel->insert($contentData);
                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "第 " . ($index + 2) . " 行：" . $e->getMessage();
                }
            }

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '匯入失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "成功匯入 {$imported} 筆資料",
                'imported' => $imported,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template content batch import error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '匯入失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export template contents to Excel with RichText support
     * POST /api/v1/risk-assessment/templates/{templateId}/export-excel
     */
    public function exportExcel()
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

            // Get template contents with category information
            $contents = $this->contentModel->getContentsWithCategory($templateId)->findAll();

            // Log for debugging: check if factor_description is included
            if (!empty($contents)) {
                $firstContent = $contents[0];
                $hasFactorDesc = isset($firstContent['factor_description']);
                log_message('info', "Export Excel: First content has factor_description: " . ($hasFactorDesc ? 'yes' : 'no'));
                if ($hasFactorDesc) {
                    log_message('info', "Export Excel: Sample factor_description: " . substr($firstContent['factor_description'] ?? '', 0, 100));
                }
            }

            // Create Excel file
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('範本內容');

            // Initialize converters
            $htmlToRichText = new \App\Libraries\HtmlToRichTextConverter();

            // Set headers (matches download template format)
            $headers = [
                'A' => '風險類別',
                'B' => '風險主題',
                'C' => '風險因子',
                'D' => 'A風險因子描述',
                'E' => 'B參考文字',
                'F' => 'C風險事件描述',
                'G' => 'D對應作為描述',
                'H' => 'D對應作為費用',
                'I' => 'E風險描述',
                'J' => 'E風險計算說明',
                'K' => 'F機會描述',
                'L' => 'F機會計算說明',
                'M' => 'G對外負面衝擊評分說明',
                'N' => 'H對外正面影響評分說明',
                'O' => 'E1資訊提示',
                'P' => 'F1資訊提示',
                'Q' => 'G1資訊提示',
                'R' => 'H1資訊提示',
                'S' => '備註'
            ];

            // Apply header styling
            foreach ($headers as $col => $header) {
                $cell = $sheet->getCell($col . '1');
                $cell->setValue($header);

                $cell->getStyle()->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ]
                ]);
            }

            // Set column widths (matches download template)
            $sheet->getColumnDimension('A')->setWidth(15); // 風險類別
            $sheet->getColumnDimension('B')->setWidth(15); // 風險主題
            $sheet->getColumnDimension('C')->setWidth(15); // 風險因子
            $sheet->getColumnDimension('D')->setWidth(50); // A風險因子描述
            $sheet->getColumnDimension('E')->setWidth(50); // B參考文字
            $sheet->getColumnDimension('F')->setWidth(40); // C風險事件描述
            $sheet->getColumnDimension('G')->setWidth(40); // D對應作為描述
            $sheet->getColumnDimension('H')->setWidth(20); // D對應作為費用
            $sheet->getColumnDimension('I')->setWidth(40); // E風險描述
            $sheet->getColumnDimension('J')->setWidth(30); // E風險計算說明
            $sheet->getColumnDimension('K')->setWidth(40); // F機會描述
            $sheet->getColumnDimension('L')->setWidth(30); // F機會計算說明
            $sheet->getColumnDimension('M')->setWidth(40); // G對外負面衝擊評分說明
            $sheet->getColumnDimension('N')->setWidth(40); // H對外正面影響評分說明
            $sheet->getColumnDimension('O')->setWidth(30); // E1資訊提示
            $sheet->getColumnDimension('P')->setWidth(30); // F1資訊提示
            $sheet->getColumnDimension('Q')->setWidth(30); // G1資訊提示
            $sheet->getColumnDimension('R')->setWidth(30); // H1資訊提示
            $sheet->getColumnDimension('S')->setWidth(15); // 備註

            // Fill data rows (removed checkbox and select fields: F, H, L, M, P, Q, S, U from original)
            $row = 2;
            foreach ($contents as $content) {
                $sheet->setCellValue('A' . $row, $content['category_name'] ?? '');
                $sheet->setCellValue('B' . $row, $content['topic_name'] ?? '');
                $sheet->setCellValue('C' . $row, $content['factor_name'] ?? '');

                // Convert HTML to RichText for factor_description (column D - A欄位)
                if (!empty($content['factor_description'])) {
                    $richText = $htmlToRichText->convert($content['factor_description']);
                    $sheet->setCellValue('D' . $row, $richText);
                } else {
                    $sheet->setCellValue('D' . $row, '');
                }

                // Convert HTML to RichText for b_content (column E - B欄位)
                if (!empty($content['b_content'])) {
                    $richText = $htmlToRichText->convert($content['b_content']);
                    $sheet->setCellValue('E' . $row, $richText);
                } else {
                    $sheet->setCellValue('E' . $row, '');
                }

                // C section - only description (removed checkbox)
                $sheet->setCellValue('F' . $row, $content['c_placeholder'] ?? '');

                // D section - only descriptions (removed checkbox)
                $sheet->setCellValue('G' . $row, $content['d_placeholder_1'] ?? '');
                $sheet->setCellValue('H' . $row, $content['d_placeholder_2'] ?? '');

                // E section - only description and calculation (removed selects)
                $sheet->setCellValue('I' . $row, $content['e1_placeholder_1'] ?? '');
                $sheet->setCellValue('J' . $row, $content['e2_placeholder'] ?? '');

                // F section - only description and calculation (removed selects)
                $sheet->setCellValue('K' . $row, $content['f1_placeholder_1'] ?? '');
                $sheet->setCellValue('L' . $row, $content['f2_placeholder'] ?? '');

                // G section - only description (removed select)
                $sheet->setCellValue('M' . $row, $content['g1_placeholder_1'] ?? '');

                // H section - only description (removed select)
                $sheet->setCellValue('N' . $row, $content['h1_placeholder_1'] ?? '');

                // Info hover texts
                $sheet->setCellValue('O' . $row, $content['e1_info'] ?? '');
                $sheet->setCellValue('P' . $row, $content['f1_info'] ?? '');
                $sheet->setCellValue('Q' . $row, $content['g1_info'] ?? '');
                $sheet->setCellValue('R' . $row, $content['h1_info'] ?? '');

                // Remark column (S) - leave empty for exported data
                $sheet->setCellValue('S' . $row, '');

                // Apply row styling (A-S columns)
                $rowStyle = [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $row % 2 === 0 ? 'FFFFFF' : 'F2F2F2']
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'D0D0D0']
                        ]
                    ]
                ];

                $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray($rowStyle);

                $row++;
            }

            // Create help sheet
            $helpSheet = $spreadsheet->createSheet();
            $helpSheet->setTitle('填寫說明');

            $helpData = [
                ['欄位名稱', '說明', '範例'],
                ['風險類別', '必填。風險所屬的分類', '財務風險'],
                ['風險主題', '選填。更細緻的風險分類（依據範本設定）', '市場風險'],
                ['風險因子', '必填。具體的風險因子', '匯率波動'],
                ['A風險因子描述', '選填。風險因子議題描述（支援富文本格式）', '企業營運高度依賴【自然資源】的_風險評估_...'],
                ['B參考文字', '選填。參考資訊（支援富文本格式）', '請參考【最近一年】的_財報資料_...'],
                ['C是否有風險事件', '選填。是否發生風險事件，填寫「是」或「否」', '否'],
                ['C風險事件描述', '選填。風險事件的詳細描述', '請描述可能發生的風險事件'],
                ['D是否有對應作為', '選填。是否有對應措施，填寫「是」或「否」', '是'],
                ['D對應作為描述', '選填。對應措施的詳細說明', '請說明對應的處理措施'],
                ['D對應作為費用', '選填。對應措施所需費用', '預估所需費用'],
                ['E風險描述', '選填。風險情境描述', '描述風險情境'],
                ['E風險可能性', '選填。風險發生可能性（填寫數字1-5）', ''],
                ['E風險衝擊程度', '選填。風險衝擊程度（填寫數字1-5）', ''],
                ['E風險計算說明', '選填。風險計算方式說明', '風險值 = 可能性 × 衝擊'],
                ['F機會描述', '選填。機會情境描述', '描述機會情境'],
                ['F機會可能性', '選填。機會發生可能性（填寫數字1-5）', ''],
                ['F機會衝擊程度', '選填。機會效益程度（填寫數字1-5）', ''],
                ['F機會計算說明', '選填。機會計算方式說明', '機會值 = 可能性 × 效益'],
                ['G對外負面衝擊程度', '選填。對外負面衝擊程度（填寫level-1至level-5）', ''],
                ['G對外負面衝擊描述', '選填。負面衝擊詳細描述', '負面影響描述'],
                ['H對外正面影響程度', '選填。對外正面影響程度（填寫level-1至level-5）', ''],
                ['H對外正面影響描述', '選填。正面影響詳細描述', '正面影響描述'],
                ['E1資訊提示', '選填。E1區塊資訊提示文字', '風險評估說明'],
                ['F1資訊提示', '選填。F1區塊資訊提示文字', '機會評估說明'],
                ['G1資訊提示', '選填。G1區塊資訊提示文字', '負面影響說明'],
                ['H1資訊提示', '選填。H1區塊資訊提示文字', '正面影響說明'],
                ['', '', ''],
                ['注意事項', '', ''],
                ['1. 第一行為範例資料，匯入時會自動跳過', '', ''],
                ['2. 風險類別和風險因子為必填欄位', '', ''],
                ['3. A、B欄位支援富文本格式：【文字】=粗體、_文字_=斜體', '', ''],
                ['4. 如果類別、主題、因子不存在，系統會自動建立', '', ''],
                ['5. 匯出時HTML格式會轉換為Excel富文本格式，匯入時自動還原為HTML', '', ''],
                ['6. 可能性與衝擊程度欄位請填寫數字1-5', '', ''],
                ['7. 程度等級欄位請填寫level-1至level-5格式', '', '']
            ];

            foreach ($helpData as $rowIndex => $rowData) {
                $helpSheet->fromArray($rowData, null, 'A' . ($rowIndex + 1));
            }

            // Output Excel file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $filename = '範本內容_' . $templateId . '_' . time() . '.xlsx';

            // Save to temporary file first
            $tempFile = WRITEPATH . 'uploads/' . $filename;
            $writer->save($tempFile);

            // Read file content
            $fileContent = file_get_contents($tempFile);

            // Delete temporary file
            unlink($tempFile);

            // Return file using CodeIgniter's response
            return $this->response
                ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->setHeader('Content-Disposition', 'attachment;filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'max-age=0')
                ->setBody($fileContent);

        } catch (\Exception $e) {
            log_message('error', 'Template content export Excel error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '匯出失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Import template contents from Excel with RichText support
     * POST /api/v1/risk-assessment/templates/{templateId}/import-excel
     */
    public function importExcel()
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

            // Get uploaded file
            $file = $this->request->getFile('file');

            if (!$file || !$file->isValid()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '請上傳有效的 Excel 檔案'
                ]);
            }

            // Load Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();

            // Initialize converters
            $richTextToHtml = new \App\Libraries\RichTextToHtmlConverter();

            // Get data (skip first row which is header)
            $rows = $sheet->toArray();
            $totalRowsInExcel = count($rows); // Total rows including header

            log_message('info', "=== Excel 匯入開始 ===");
            log_message('info', "Excel 檔案總行數（包含標題）: {$totalRowsInExcel}");

            array_shift($rows); // Remove header row only
            // Example data will be skipped by checking remark column

            $dataRowCount = count($rows);
            log_message('info', "實際資料行數（移除標題後）: {$dataRowCount}");

            if (empty($rows)) {
                log_message('warning', "Excel 檔案中沒有資料行");
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Excel 檔案中沒有資料'
                ]);
            }

            $imported = 0;
            $skipped = 0; // Counter for duplicate records
            $errors = [];
            $debugLog = [];

            // Models for category, topic, factor lookup/creation
            $categoryModel = new RiskCategoryModel();
            $topicModel = new \App\Models\RiskAssessment\RiskTopicModel();
            $factorModel = new \App\Models\RiskAssessment\RiskFactorModel();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because: +1 for 0-index, +1 for header row

                try {
                    // Clean UTF-8 encoding for all row data to prevent JSON encoding errors
                    $row = array_map(function($value) {
                        if (is_string($value)) {
                            // Remove invalid UTF-8 characters
                            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                            // Additional cleanup: remove null bytes and other problematic characters
                            $value = str_replace("\0", '', $value);
                        }
                        return $value;
                    }, $row);

                    // Log raw row data
                    log_message('info', "--- 處理第 {$rowNumber} 行 ---");
                    log_message('debug', "第 {$rowNumber} 行原始資料: " . json_encode($row, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE));

                    // Check remark column (column 18 / position 18) - skip if it contains "範例資料"
                    $remark = trim($row[18] ?? '');
                    if ($remark === '範例資料') {
                        log_message('info', "第 {$rowNumber} 行：備註欄位為「範例資料」，跳過此行");
                        continue;
                    }

                    // Parse row data (updated field mapping - removed 排序 and 是否必填)
                    $categoryName = trim($row[0] ?? '');
                    $topicName = trim($row[1] ?? '');
                    $factorName = trim($row[2] ?? '');
                    // Position 3 is now B參考文字 (will be processed later with RichText)
                    // Removed: sortOrder (position 3) and isRequired (position 4)

                    log_message('info', "第 {$rowNumber} 行解析: 類別='{$categoryName}', 主題='{$topicName}', 因子='{$factorName}'");

                    // Skip completely empty rows
                    if (empty($categoryName) && empty($factorName) && empty($topicName)) {
                        log_message('info', "第 {$rowNumber} 行為空白行，跳過");
                        continue;
                    }

                    // Validate required fields
                    if (empty($categoryName) || empty($factorName)) {
                        $missing = [];
                        if (empty($categoryName)) $missing[] = '風險類別';
                        if (empty($factorName)) $missing[] = '風險因子';
                        $errorMsg = "第 {$rowNumber} 行：缺少必填欄位（" . implode('、', $missing) . "）";
                        $errors[] = $errorMsg;
                        log_message('warning', $errorMsg);
                        // Add to debug log for error tracking
                        $debugLog[] = [
                            'row' => $rowNumber,
                            'status' => 'error',
                            'reason' => '缺少必填欄位',
                            'error' => "缺少必填欄位：" . implode('、', $missing),
                            'data' => [
                                'category' => $categoryName ?: null,
                                'topic' => $topicName ?: null,
                                'factor' => $factorName ?: null
                            ]
                        ];
                        continue;
                    }

                    // Find or create category
                    $category = $categoryModel->where('template_id', $templateId)
                        ->where('category_name', $categoryName)
                        ->first();

                    if (!$category) {
                        // Get next sort order manually
                        $maxSort = $categoryModel->where('template_id', $templateId)
                            ->selectMax('sort_order')
                            ->first();
                        $nextSort = ($maxSort['sort_order'] ?? 0) + 1;

                        $categoryId = $categoryModel->insert([
                            'template_id' => $templateId,
                            'category_name' => $categoryName,
                            'sort_order' => $nextSort
                        ]);
                    } else {
                        $categoryId = $category['id'];
                    }

                    // Find or create topic (if provided)
                    $topicId = null;
                    if (!empty($topicName)) {
                        $topic = $topicModel->where('template_id', $templateId)
                            ->where('topic_name', $topicName)
                            ->where('category_id', $categoryId)
                            ->first();

                        if (!$topic) {
                            // Get next sort order manually
                            $maxSort = $topicModel->where('template_id', $templateId)
                                ->where('category_id', $categoryId)
                                ->selectMax('sort_order')
                                ->first();
                            $nextSort = ($maxSort['sort_order'] ?? 0) + 1;

                            $topicId = $topicModel->insert([
                                'template_id' => $templateId,
                                'category_id' => $categoryId,
                                'topic_name' => $topicName,
                                'sort_order' => $nextSort
                            ]);
                        } else {
                            $topicId = $topic['id'];
                        }
                    }

                    // Find or create factor
                    $factor = $factorModel->where('template_id', $templateId)
                        ->where('factor_name', $factorName)
                        ->first();

                    if (!$factor) {
                        $factorId = $factorModel->insert([
                            'template_id' => $templateId,
                            'category_id' => $categoryId,
                            'topic_id' => $topicId,
                            'factor_name' => $factorName
                            // Note: sort_order has been removed from risk_factors table
                        ]);
                    } else {
                        $factorId = $factor['id'];
                    }

                    // Convert RichText to HTML for A column (factor_description at position 3)
                    $factorDescriptionHtml = '';
                    $cellD = $sheet->getCell('D' . $rowNumber);

                    // Try to get RichText object first
                    $richTextObj = null;
                    try {
                        // Check if cell has rich text formatting
                        $cellValue = $cellD->getValue();

                        if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                            // Already a RichText object
                            $richTextObj = $cellValue;
                        } elseif (!empty($cellValue)) {
                            // Check if the cell has any formatting by examining the cell style
                            // If there's text but no RichText object, convert styled text to HTML
                            $cellStyle = $cellD->getStyle();
                            $font = $cellStyle->getFont();

                            // Check if there's any formatting applied
                            $hasFormatting = $font->getBold() ||
                                           $font->getItalic() ||
                                           $font->getUnderline() ||
                                           $font->getStrikethrough() ||
                                           ($font->getColor() && $font->getColor()->getRGB());

                            if ($hasFormatting) {
                                // Create a synthetic RichText-like HTML
                                $text = htmlspecialchars($cellValue);

                                // Apply formatting
                                if ($font->getBold()) {
                                    $text = '<strong>' . $text . '</strong>';
                                }
                                if ($font->getItalic()) {
                                    $text = '<em>' . $text . '</em>';
                                }
                                if ($font->getUnderline() && $font->getUnderline() !== \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_NONE) {
                                    $text = '<u>' . $text . '</u>';
                                }
                                if ($font->getStrikethrough()) {
                                    $text = '<s>' . $text . '</s>';
                                }

                                // Apply color
                                if ($font->getColor() && $font->getColor()->getRGB()) {
                                    $rgb = $font->getColor()->getRGB();
                                    if ($rgb && strtoupper($rgb) !== '000000' && strtoupper($rgb) !== 'FF000000') {
                                        $color = '#' . $rgb;
                                        $text = '<span style="color: ' . $color . '">' . $text . '</span>';
                                    }
                                }

                                $factorDescriptionHtml = '<p>' . $text . '</p>';
                            } else {
                                // Plain text - wrap in paragraph
                                $factorDescriptionHtml = '<p>' . htmlspecialchars($cellValue) . '</p>';
                            }
                        }

                        // If we have a RichText object, convert it
                        if ($richTextObj) {
                            $factorDescriptionHtml = $richTextToHtml->convert($richTextObj);
                        }
                    } catch (\Exception $e) {
                        // Fallback to plain text if anything goes wrong
                        log_message('warning', 'RichText conversion error for factor description: ' . $e->getMessage());
                        $cellValue = $cellD->getValue();
                        if (!empty($cellValue)) {
                            $factorDescriptionHtml = '<p>' . htmlspecialchars($cellValue) . '</p>';
                        }
                    }

                    // Update factor description if provided
                    if (!empty($factorDescriptionHtml)) {
                        $factorModel->update($factorId, [
                            'description' => $factorDescriptionHtml
                        ]);
                    }

                    // Convert RichText to HTML for B column (b_content at position 4)
                    $bContentHtml = '';
                    $cellE = $sheet->getCell('E' . $rowNumber);

                    // Try to get RichText object first
                    $richTextObj = null;
                    try {
                        // Check if cell has rich text formatting
                        $cellValue = $cellE->getValue();

                        if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                            // Already a RichText object
                            $richTextObj = $cellValue;
                        } elseif (!empty($cellValue)) {
                            // Check if the cell has any formatting by examining the cell style
                            // If there's text but no RichText object, convert styled text to HTML
                            $cellStyle = $cellE->getStyle();
                            $font = $cellStyle->getFont();

                            // Check if there's any formatting applied
                            $hasFormatting = $font->getBold() ||
                                           $font->getItalic() ||
                                           $font->getUnderline() ||
                                           $font->getStrikethrough() ||
                                           ($font->getColor() && $font->getColor()->getRGB());

                            if ($hasFormatting) {
                                // Create a synthetic RichText-like HTML
                                $text = htmlspecialchars($cellValue);

                                // Apply formatting
                                if ($font->getBold()) {
                                    $text = '<strong>' . $text . '</strong>';
                                }
                                if ($font->getItalic()) {
                                    $text = '<em>' . $text . '</em>';
                                }
                                if ($font->getUnderline() && $font->getUnderline() !== \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_NONE) {
                                    $text = '<u>' . $text . '</u>';
                                }
                                if ($font->getStrikethrough()) {
                                    $text = '<s>' . $text . '</s>';
                                }

                                // Apply color
                                if ($font->getColor() && $font->getColor()->getRGB()) {
                                    $rgb = $font->getColor()->getRGB();
                                    if ($rgb && strtoupper($rgb) !== '000000' && strtoupper($rgb) !== 'FF000000') {
                                        $color = '#' . $rgb;
                                        $text = '<span style="color: ' . $color . '">' . $text . '</span>';
                                    }
                                }

                                $bContentHtml = '<p>' . $text . '</p>';
                            } else {
                                // Plain text - wrap in paragraph
                                $bContentHtml = '<p>' . htmlspecialchars($cellValue) . '</p>';
                            }
                        }

                        // If we have a RichText object, convert it
                        if ($richTextObj) {
                            $bContentHtml = $richTextToHtml->convert($richTextObj);
                        }
                    } catch (\Exception $e) {
                        // Fallback to plain text if anything goes wrong
                        log_message('warning', 'RichText conversion error for b_content: ' . $e->getMessage());
                        $cellValue = $cellE->getValue();
                        if (!empty($cellValue)) {
                            $bContentHtml = '<p>' . htmlspecialchars($cellValue) . '</p>';
                        }
                    }

                    // Auto-generate next sort order
                    $maxSort = $this->contentModel->where('template_id', $templateId)
                        ->selectMax('sort_order')
                        ->first();
                    $sortOrder = ($maxSort['sort_order'] ?? 0) + 1;

                    // Insert content (updated field mapping - removed checkbox and select fields)
                    // New Excel columns (0-17): 風險類別, 風險主題, 風險因子, A風險因子描述, B參考文字,
                    // C風險事件描述, D對應作為描述, D對應作為費用, E風險描述, E風險計算說明,
                    // F機會描述, F機會計算說明, G對外負面衝擊評分說明, H對外正面影響評分說明,
                    // E1資訊提示, F1資訊提示, G1資訊提示, H1資訊提示
                    $contentData = [
                        'template_id' => $templateId,
                        'category_id' => $categoryId,
                        'topic_id' => $topicId,
                        'risk_factor_id' => $factorId,
                        'sort_order' => $sortOrder,
                        'is_required' => 1, // Default to required
                        // Note: A column (factor_description) is handled separately above
                        'b_content' => $bContentHtml, // Position 4
                        // These fields are not imported (keep existing values):
                        // c_has_risk_event, d_has_counter_action, e2_select_1, e2_select_2, f2_select_1, f2_select_2, g1_select, h1_select
                        'c_placeholder' => $row[5] ?? null,
                        'd_placeholder_1' => $row[6] ?? null,
                        'd_placeholder_2' => $row[7] ?? null,
                        'e1_placeholder_1' => $row[8] ?? null,
                        'e2_placeholder' => $row[9] ?? null,
                        'f1_placeholder_1' => $row[10] ?? null,
                        'f2_placeholder' => $row[11] ?? null,
                        'g1_placeholder_1' => $row[12] ?? null,
                        'h1_placeholder_1' => $row[13] ?? null,
                        'e1_info' => $row[14] ?? null,
                        'f1_info' => $row[15] ?? null,
                        'g1_info' => $row[16] ?? null,
                        'h1_info' => $row[17] ?? null
                    ];

                    // Log content data to be inserted
                    log_message('info', "第 {$rowNumber} 行準備寫入資料: " . json_encode([
                        'template_id' => $templateId,
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'topic_id' => $topicId,
                        'topic_name' => $topicName,
                        'risk_factor_id' => $factorId,
                        'factor_name' => $factorName,
                        'sort_order' => $sortOrder,
                        'has_b_content' => !empty($bContentHtml),
                        'has_c_placeholder' => !empty($row[5]),
                        'has_d_placeholder_1' => !empty($row[6]),
                        'has_d_placeholder_2' => !empty($row[7]),
                        'has_e1_placeholder_1' => !empty($row[8]),
                        'has_e2_placeholder' => !empty($row[9]),
                        'has_f1_placeholder_1' => !empty($row[10]),
                        'has_f2_placeholder' => !empty($row[11]),
                        'has_g1_placeholder_1' => !empty($row[12]),
                        'has_h1_placeholder_1' => !empty($row[13]),
                        'has_e1_info' => !empty($row[14]),
                        'has_f1_info' => !empty($row[15]),
                        'has_g1_info' => !empty($row[16]),
                        'has_h1_info' => !empty($row[17])
                    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE));

                    // Check for duplicate content - compare all important fields
                    $existingContent = $this->contentModel
                        ->where('template_id', $templateId)
                        ->where('risk_factor_id', $factorId)
                        ->findAll();

                    $isDuplicate = false;
                    foreach ($existingContent as $existing) {
                        // Normalize data for comparison (trim and handle nulls)
                        $normalizeValue = function($val) {
                            if ($val === null || $val === '') return '';
                            return trim((string)$val);
                        };

                        // Compare all content fields
                        $fieldsMatch = true;
                        $compareFields = [
                            'b_content', 'c_placeholder', 'd_placeholder_1', 'd_placeholder_2',
                            'e1_placeholder_1', 'e2_placeholder', 'f1_placeholder_1', 'f2_placeholder',
                            'g1_placeholder_1', 'h1_placeholder_1', 'e1_info', 'f1_info', 'g1_info', 'h1_info'
                        ];

                        foreach ($compareFields as $field) {
                            $existingVal = $normalizeValue($existing[$field] ?? null);
                            $newVal = $normalizeValue($contentData[$field] ?? null);

                            if ($existingVal !== $newVal) {
                                $fieldsMatch = false;
                                break;
                            }
                        }

                        if ($fieldsMatch) {
                            $isDuplicate = true;
                            $skipped++;
                            log_message('info', "第 {$rowNumber} 行：發現重複資料（與ID {$existing['id']} 完全相同），跳過匯入");
                            $debugLog[] = [
                                'row' => $rowNumber,
                                'status' => 'skipped',
                                'reason' => 'duplicate',
                                'duplicate_id' => $existing['id'],
                                'data' => [
                                    'category' => $categoryName,
                                    'topic' => $topicName,
                                    'factor' => $factorName,
                                    'factor_description' => strip_tags($factorDescriptionHtml ?: '')
                                ]
                            ];
                            break;
                        }
                    }

                    // Skip if duplicate found
                    if ($isDuplicate) {
                        continue;
                    }

                    // Generate INSERT SQL for logging and debug
                    $fields = array_keys($contentData);
                    $values = array_map(function($val) {
                        if ($val === null) return 'NULL';
                        if (is_numeric($val)) return $val;
                        return "'" . str_replace("'", "''", substr((string)$val, 0, 50)) . (strlen((string)$val) > 50 ? '...' : '') . "'";
                    }, array_values($contentData));
                    $sqlPreview = "INSERT INTO template_contents (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
                    log_message('debug', "第 {$rowNumber} 行 SQL（簡化）: {$sqlPreview}");

                    $insertResult = $this->contentModel->insert($contentData);

                    if ($insertResult === false) {
                        // Insert failed - get validation errors
                        $validationErrors = $this->contentModel->errors();
                        if (!empty($validationErrors)) {
                            $errorMsg = is_array($validationErrors) ? implode(', ', $validationErrors) : $validationErrors;
                            $errors[] = "第 {$rowNumber} 行：驗證失敗 - {$errorMsg}";
                            log_message('error', "第 {$rowNumber} 行：驗證失敗 - {$errorMsg}");
                        } else {
                            $errors[] = "第 {$rowNumber} 行：資料插入失敗";
                            log_message('error', "第 {$rowNumber} 行：資料插入失敗");
                        }
                        $debugLog[] = [
                            'row' => $rowNumber,
                            'status' => 'failed',
                            'error' => $validationErrors,
                            'sql' => $sqlPreview,
                            'data' => [
                                'category' => $categoryName,
                                'topic' => $topicName,
                                'factor' => $factorName
                            ]
                        ];
                    } else {
                        $imported++;
                        log_message('info', "第 {$rowNumber} 行：成功寫入，插入ID: {$insertResult}");
                        $debugLog[] = [
                            'row' => $rowNumber,
                            'status' => 'success',
                            'inserted_id' => $insertResult,
                            'sql' => $sqlPreview,
                            'data' => [
                                'category' => $categoryName,
                                'topic' => $topicName,
                                'factor' => $factorName,
                                'factor_description' => strip_tags($factorDescriptionHtml ?: '')
                            ]
                        ];
                    }

                } catch (\Exception $e) {
                    $errorMsg = "第 {$rowNumber} 行：" . $e->getMessage();
                    $errors[] = $errorMsg;
                    log_message('error', $errorMsg);
                    $debugLog[] = [
                        'row' => $rowNumber,
                        'status' => 'error',
                        'reason' => '系統錯誤',
                        'error' => $e->getMessage(),
                        'data' => [
                            'category' => $categoryName ?? null,
                            'topic' => $topicName ?? null,
                            'factor' => $factorName ?? null
                        ]
                    ];
                }
            }

            // Final summary log
            log_message('info', "=== Excel 匯入完成 ===");
            log_message('info', "總共處理: {$dataRowCount} 行");
            log_message('info', "成功匯入: {$imported} 行");
            log_message('info', "重複跳過: {$skipped} 行");
            log_message('info', "失敗筆數: " . count($errors) . " 行");
            if (!empty($errors)) {
                log_message('warning', "錯誤列表: " . json_encode($errors, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE));
            }

            // Save import history to database
            $batchId = uniqid('batch_', true); // Generate unique batch ID
            $importHistoryModel = new \App\Models\ImportHistoryModel();

            if (!empty($debugLog)) {
                $historyRecords = [];
                foreach ($debugLog as $log) {
                    $historyRecords[] = [
                        'template_id' => $templateId,
                        'question_id' => null,
                        'import_type' => 'template_content',
                        'batch_id' => $batchId,
                        'row_number' => $log['row'],
                        'status' => $log['status'],
                        'reason' => $log['reason'] ?? null,
                        'error_message' => $log['error'] ?? null,
                        'inserted_id' => $log['inserted_id'] ?? null,
                        'duplicate_id' => $log['duplicate_id'] ?? null,
                        'category_name' => $log['data']['category'] ?? null,
                        'topic_name' => $log['data']['topic'] ?? null,
                        'factor_name' => $log['data']['factor'] ?? null,
                        'factor_description' => $log['data']['factor_description'] ?? null,
                        'data_json' => json_encode($log['data'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE),
                        'sql_preview' => $log['sql'] ?? null,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                }

                try {
                    $importHistoryModel->saveBatch($historyRecords);
                    log_message('info', "匯入歷史已儲存，批次ID: {$batchId}");

                    // Clean old history (keep last 50 batches)
                    $importHistoryModel->cleanOldHistory($templateId, 50);
                } catch (\Exception $e) {
                    log_message('error', "儲存匯入歷史失敗: " . $e->getMessage());
                }
            }

            // Build response message
            $message = "成功匯入 {$imported} 筆資料";
            if ($skipped > 0) {
                $message .= "，跳過 {$skipped} 筆重複資料";
            }
            if (count($errors) > 0) {
                $message .= "，{" . count($errors) . "} 筆失敗";
            }

            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
                'debug' => [
                    'total_rows_in_excel' => $totalRowsInExcel,
                    'data_rows_processed' => $dataRowCount,
                    'rows_imported' => $imported,
                    'rows_skipped' => $skipped,
                    'rows_failed' => count($errors),
                    'criteria' => [
                        'description' => '實際資料行數判斷準則',
                        'rules' => [
                            '1. Excel 檔案總行數減去標題行（第1行）',
                            '2. 檢查備註欄位，若為「範例資料」則跳過該行',
                            '3. 跳過完全空白的行（類別、主題、因子皆為空）',
                            '4. 驗證必填欄位（風險類別和風險因子不可為空）',
                            '5. 檢查是否與系統現有資料完全重複，重複則跳過',
                            '6. 符合以上條件的行才會被匯入'
                        ],
                        'example' => "例如：Excel有10行 → 減去標題1行 → 檢查備註欄位跳過範例1行 → 跳過重複資料2行 → 剩餘6行待處理 → 實際匯入的資料行數"
                    ],
                    'details' => $debugLog
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template content import Excel error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '匯入失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get import history for a template
     *
     * @param int $templateId
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getImportHistory(int $templateId)
    {
        try {
            $importHistoryModel = new \App\Models\ImportHistoryModel();

            // Get pagination parameters
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 20);
            $search = $this->request->getGet('search') ?? '';

            $page = max(1, $page);
            $limit = max(1, min(100, $limit));
            $offset = ($page - 1) * $limit;

            // Get batches with summary
            $batches = $importHistoryModel->getTemplateBatches($templateId, $limit, $offset);
            $total = $importHistoryModel->countTemplateBatches($templateId);

            // Add search filtering if needed
            if (!empty($search)) {
                $batches = array_filter($batches, function($batch) use ($search, $importHistoryModel) {
                    $records = $importHistoryModel->getByBatchId($batch['batch_id']);
                    foreach ($records as $record) {
                        if (
                            stripos($record['category_name'] ?? '', $search) !== false ||
                            stripos($record['topic_name'] ?? '', $search) !== false ||
                            stripos($record['factor_name'] ?? '', $search) !== false ||
                            stripos($record['factor_description'] ?? '', $search) !== false
                        ) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'data' => $batches,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get import history error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '獲取匯入歷史失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get import history details for a specific batch
     *
     * @param string $batchId
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getImportHistoryBatch(string $batchId)
    {
        try {
            $importHistoryModel = new \App\Models\ImportHistoryModel();

            // Get all records for this batch
            $records = $importHistoryModel->getByBatchId($batchId);

            if (empty($records)) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到該批次的匯入記錄'
                ]);
            }

            // Calculate summary
            $summary = [
                'total' => count($records),
                'success' => 0,
                'skipped' => 0,
                'error' => 0
            ];

            foreach ($records as &$record) {
                $summary[$record['status']]++;
                // Parse JSON data
                if (!empty($record['data_json'])) {
                    $record['data'] = json_decode($record['data_json'], true);
                }
            }

            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'batch_id' => $batchId,
                'summary' => $summary,
                'records' => $records
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get import history batch error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '獲取批次詳情失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get latest import batch summary
     *
     * @param int $templateId
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getLatestImportSummary(int $templateId)
    {
        try {
            $importHistoryModel = new \App\Models\ImportHistoryModel();

            $latestBatch = $importHistoryModel->getLatestBatch($templateId);

            if (empty($latestBatch)) {
                return $this->response->setStatusCode(200)->setJSON([
                    'success' => true,
                    'has_data' => false,
                    'summary' => null
                ]);
            }

            // Calculate summary
            $summary = [
                'total_rows_in_excel' => 0,
                'data_rows_processed' => count($latestBatch),
                'rows_imported' => 0,
                'rows_skipped' => 0,
                'rows_failed' => 0,
                'created_at' => $latestBatch[0]['created_at']
            ];

            foreach ($latestBatch as $record) {
                switch ($record['status']) {
                    case 'success':
                        $summary['rows_imported']++;
                        break;
                    case 'skipped':
                        $summary['rows_skipped']++;
                        break;
                    case 'error':
                        $summary['rows_failed']++;
                        break;
                }
            }

            $summary['total_rows_in_excel'] = $summary['data_rows_processed'] + 1; // +1 for header

            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'has_data' => true,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get latest import summary error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '獲取最新匯入摘要失敗：' . $e->getMessage()
            ]);
        }
    }
}