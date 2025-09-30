<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TemplateContentModel;
use App\Models\RiskAssessmentTemplateModel;
use App\Models\RiskCategoryModel;
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
    }

    /**
     * Get template contents list with pagination
     * GET /api/admin/templates/{templateId}/contents
     */
    public function index($templateId)
    {
        try {
            // Check if template exists
            $template = $this->templateModel->where('deleted_at', null)->find($templateId);
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
     * GET /api/admin/templates/{templateId}/contents/{id}
     */
    public function show($templateId, $id)
    {
        try {
            // Check if template exists
            $template = $this->templateModel->where('deleted_at', null)->find($templateId);
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
     * POST /api/admin/templates/{templateId}/contents
     */
    public function create($templateId)
    {
        try {
            // Check if template exists
            $template = $this->templateModel->where('deleted_at', null)->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            $rules = [
                'category_id' => 'permit_empty|integer',
                'topic' => 'required|max_length[255]',
                'description' => 'required',
                'scoring_method' => 'required|in_list[binary,scale_1_5,scale_1_10,percentage]',
                'weight' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[10]',
                'is_required' => 'permit_empty|in_list[0,1]'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Check if category exists and belongs to template
            $categoryId = $this->request->getPost('category_id');
            if (!empty($categoryId)) {
                $category = $this->categoryModel->where('id', $categoryId)
                                                ->where('template_id', $templateId)
                                                ->where('deleted_at', null)
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
                'topic' => $this->request->getPost('topic'),
                'description' => $this->request->getPost('description'),
                'scoring_method' => $this->request->getPost('scoring_method'),
                'weight' => $this->request->getPost('weight') ?? 1.0,
                'sort_order' => $this->contentModel->getNextSortOrder($templateId),
                'is_required' => $this->request->getPost('is_required') ?? 1
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
     * PUT /api/admin/templates/{templateId}/contents/{id}
     */
    public function update($templateId, $id)
    {
        try {
            // Check if template exists
            $template = $this->templateModel->where('deleted_at', null)->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if content exists and belongs to template
            $content = $this->contentModel->where('template_id', $templateId)
                                          ->where('deleted_at', null)
                                          ->find($id);
            if (!$content) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本內容不存在'
                ]);
            }

            $rules = [
                'category_id' => 'permit_empty|integer',
                'topic' => 'required|max_length[255]',
                'description' => 'required',
                'scoring_method' => 'required|in_list[binary,scale_1_5,scale_1_10,percentage]',
                'weight' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[10]',
                'is_required' => 'permit_empty|in_list[0,1]'
            ];

            $input = $this->request->getRawInput();
            if (!$this->validate($rules, $input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Check if category exists and belongs to template
            $categoryId = $input['category_id'] ?? null;
            if (!empty($categoryId)) {
                $category = $this->categoryModel->where('id', $categoryId)
                                                ->where('template_id', $templateId)
                                                ->where('deleted_at', null)
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
                'topic' => $input['topic'],
                'description' => $input['description'],
                'scoring_method' => $input['scoring_method'],
                'weight' => $input['weight'] ?? $content['weight'],
                'is_required' => $input['is_required'] ?? $content['is_required']
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
     * Delete template content
     * DELETE /api/admin/templates/{templateId}/contents/{id}
     */
    public function delete($templateId, $id)
    {
        try {
            // Check if template exists
            $template = $this->templateModel->where('deleted_at', null)->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if content exists and belongs to template
            $content = $this->contentModel->where('template_id', $templateId)
                                          ->where('deleted_at', null)
                                          ->find($id);
            if (!$content) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本內容不存在'
                ]);
            }

            // Soft delete content
            $success = $this->contentModel->delete($id);
            
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
     * PUT /api/admin/templates/{templateId}/contents/reorder
     */
    public function reorder($templateId)
    {
        try {
            // Check if template exists
            $template = $this->templateModel->where('deleted_at', null)->find($templateId);
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

            $input = $this->request->getRawInput();
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
                                                  ->where('deleted_at', null)
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