<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RiskAssessmentTemplateModel;
use App\Models\RiskCategoryModel;
use App\Models\TemplateContentModel;
use CodeIgniter\HTTP\ResponseInterface;

class TemplateController extends BaseController
{
    protected $templateModel;
    protected $categoryModel;
    protected $contentModel;

    public function __construct()
    {
        $this->templateModel = new RiskAssessmentTemplateModel();
        $this->categoryModel = new RiskCategoryModel();
        $this->contentModel = new TemplateContentModel();
    }

    /**
     * Get templates list
     * GET /api/admin/templates
     */
    public function index()
    {
        try {
            $search = $this->request->getGet('search');
            $status = $this->request->getGet('status');
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 20);
            $sort = $this->request->getGet('sort') ?? 'created_at';
            $order = $this->request->getGet('order') ?? 'desc';

            $builder = $this->templateModel->getTemplatesWithStats($search, $status, $page, $limit, $sort, $order);

            // Get total for pagination
            $total = $builder->countAllResults(false);
            $templates = $builder->paginate($limit, 'default', $page);

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
                    'templates' => $templates,
                    'pagination' => $pagination
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template index error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得範本列表失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single template details
     * GET /api/admin/templates/{id}
     */
    public function show($id)
    {
        try {
            $template = $this->templateModel->getTemplateWithStats($id);

            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $template
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template show error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得範本詳細資訊失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new template
     * POST /api/admin/templates
     */
    public function create()
    {
        try {
            $rules = [
                'version_name' => 'required|max_length[255]|is_unique[risk_assessment_templates.version_name]',
                'description' => 'permit_empty|string',
                'status' => 'permit_empty|in_list[active,inactive,draft]'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $data = [
                'version_name' => $this->request->getPost('version_name'),
                'description' => $this->request->getPost('description'),
                'status' => $this->request->getPost('status') ?? 'active'
            ];

            $templateId = $this->templateModel->insert($data);
            
            if (!$templateId) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '建立範本失敗',
                    'errors' => $this->templateModel->errors()
                ]);
            }

            $template = $this->templateModel->find($templateId);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '範本建立成功',
                'data' => $template
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template create error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '建立範本失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update template
     * PUT /api/admin/templates/{id}
     */
    public function update($id)
    {
        try {
            $template = $this->templateModel->where('deleted_at', null)->find($id);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            $rules = [
                'version_name' => "required|max_length[255]|is_unique[risk_assessment_templates.version_name,id,$id]",
                'description' => 'permit_empty|string',
                'status' => 'permit_empty|in_list[active,inactive,draft]'
            ];

            $input = $this->request->getRawInput();
            if (!$this->validate($rules, $input)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $data = [
                'version_name' => $input['version_name'],
                'description' => $input['description'] ?? $template['description'],
                'status' => $input['status'] ?? $template['status']
            ];

            $success = $this->templateModel->update($id, $data);
            
            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新範本失敗',
                    'errors' => $this->templateModel->errors()
                ]);
            }

            $updatedTemplate = $this->templateModel->find($id);

            return $this->response->setJSON([
                'success' => true,
                'message' => '範本更新成功',
                'data' => $updatedTemplate
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Template update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新範本失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete template (soft delete)
     * DELETE /api/admin/templates/{id}
     */
    public function delete($id)
    {
        try {
            $template = $this->templateModel->where('deleted_at', null)->find($id);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Soft delete template and related data
                $this->templateModel->delete($id);
                $this->categoryModel->where('template_id', $id)->delete();
                $this->contentModel->where('template_id', $id)->delete();

                $db->transComplete();

                if ($db->transStatus() === FALSE) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'success' => false,
                        'message' => '刪除範本失敗'
                    ]);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => '範本刪除成功'
                ]);

            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            log_message('error', 'Template delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除範本失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Copy template
     * POST /api/admin/templates/{id}/copy
     */
    public function copy($id)
    {
        try {
            $originalTemplate = $this->templateModel->where('deleted_at', null)->find($id);
            if (!$originalTemplate) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '原範本不存在'
                ]);
            }

            $rules = [
                'version_name' => 'required|max_length[255]|is_unique[risk_assessment_templates.version_name]',
                'description' => 'permit_empty|string'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Create new template
                $newTemplateData = [
                    'version_name' => $this->request->getPost('version_name'),
                    'description' => $this->request->getPost('description') ?? $originalTemplate['description'],
                    'status' => 'active',
                    'copied_from' => $id
                ];

                $newTemplateId = $this->templateModel->insert($newTemplateData);

                // Copy categories
                $categories = $this->categoryModel->where('template_id', $id)->where('deleted_at', null)->findAll();
                $categoryMapping = [];

                foreach ($categories as $category) {
                    $newCategoryData = [
                        'template_id' => $newTemplateId,
                        'category_name' => $category['category_name'],
                        'category_code' => $category['category_code'],
                        'description' => $category['description'],
                        'sort_order' => $category['sort_order']
                    ];
                    
                    $newCategoryId = $this->categoryModel->insert($newCategoryData);
                    $categoryMapping[$category['id']] = $newCategoryId;
                }

                // Copy contents
                $contents = $this->contentModel->where('template_id', $id)->where('deleted_at', null)->findAll();

                foreach ($contents as $content) {
                    $newContentData = [
                        'template_id' => $newTemplateId,
                        'category_id' => isset($categoryMapping[$content['category_id']]) ? $categoryMapping[$content['category_id']] : null,
                        'topic' => $content['topic'],
                        'description' => $content['description'],
                        'scoring_method' => $content['scoring_method'],
                        'weight' => $content['weight'],
                        'sort_order' => $content['sort_order'],
                        'is_required' => $content['is_required']
                    ];

                    $this->contentModel->insert($newContentData);
                }

                $db->transComplete();

                if ($db->transStatus() === FALSE) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'success' => false,
                        'message' => '複製範本失敗'
                    ]);
                }

                $newTemplate = $this->templateModel->find($newTemplateId);

                return $this->response->setStatusCode(201)->setJSON([
                    'success' => true,
                    'message' => '範本複製成功',
                    'data' => $newTemplate
                ]);

            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            log_message('error', 'Template copy error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '複製範本失敗: ' . $e->getMessage()
            ]);
        }
    }
}