<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use App\Controllers\Api\BaseController;
use App\Models\RiskAssessment\RiskCategoryModel;
use App\Models\RiskAssessment\RiskAssessmentTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;

class CategoryController extends BaseController
{
    protected $categoryModel;
    protected $templateModel;

    public function __construct()
    {
        $this->categoryModel = new RiskCategoryModel();
        $this->templateModel = new RiskAssessmentTemplateModel();
    }

    /**
     * Get categories list for template
     * GET /api/v1/risk-assessment/templates/{templateId}/categories
     */
    public function index()
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

            $search = $this->request->getGet('search');
            
            $categories = $this->categoryModel->getCategoriesWithStats($templateId, $search);

            $response = [
                'success' => true,
                'data' => [
                    'categories' => $categories
                ]
            ];

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            exit;

        } catch (\Exception $e) {
            log_message('error', 'Category index error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得風險分類列表失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new category
     * POST /api/v1/risk-assessment/templates/{templateId}/categories
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
                'category_name' => 'required|max_length[255]',
                'description' => 'permit_empty|string',
                'sort_order' => 'permit_empty|integer'
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

            $data = [
                'template_id' => $templateId,
                'category_name' => $input['category_name'] ?? null,
                'description' => $input['description'] ?? null,
                'sort_order' => $input['sort_order'] ?? 0
            ];

            $categoryId = $this->categoryModel->insert($data);
            
            if (!$categoryId) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '建立風險分類失敗',
                    'errors' => $this->categoryModel->errors()
                ]);
            }

            $category = $this->categoryModel->find($categoryId);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '風險分類建立成功',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Category create error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '建立風險分類失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update category
     * PUT /api/v1/risk-assessment/templates/{templateId}/categories/{id}
     */
    public function update($id = null)
    {
        try {
            // Get template ID and category ID from URI segments
            $templateId = $this->request->uri->getSegment(5);
            $categoryId = $this->request->uri->getSegment(7); // categories/{id}
            
            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if category exists and belongs to template
            $category = $this->categoryModel->where('template_id', $templateId)->find($categoryId);
            if (!$category) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '風險分類不存在'
                ]);
            }

            $rules = [
                'category_name' => 'required|max_length[255]',
                'description' => 'permit_empty|string',
                'sort_order' => 'permit_empty|integer'
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

            $data = [
                'category_name' => $input['category_name'],
                'description' => $input['description'] ?? $category['description'],
                'sort_order' => $input['sort_order'] ?? $category['sort_order']
            ];

            $success = $this->categoryModel->update($categoryId, $data);
            
            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新風險分類失敗',
                    'errors' => $this->categoryModel->errors()
                ]);
            }

            $updatedCategory = $this->categoryModel->find($categoryId);

            return $this->response->setJSON([
                'success' => true,
                'message' => '風險分類更新成功',
                'data' => $updatedCategory
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Category update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新風險分類失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete category (hard delete)
     * DELETE /api/v1/risk-assessment/templates/{templateId}/categories/{id}
     */
    public function delete($id = null)
    {
        try {
            // Get template ID and category ID from URI segments
            $templateId = $this->request->uri->getSegment(5);
            $categoryId = $this->request->uri->getSegment(7); // categories/{id}
            
            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if category exists and belongs to template
            $category = $this->categoryModel->where('template_id', $templateId)->find($categoryId);
            if (!$category) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '風險分類不存在'
                ]);
            }

            // Hard delete category (template contents with this category_id will have category_id set to NULL due to FK constraint)
            $success = $this->categoryModel->delete($categoryId, true);
            
            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '刪除風險分類失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '風險分類刪除成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Category delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除風險分類失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reorder categories
     * PUT /api/v1/risk-assessment/templates/{templateId}/categories/reorder
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

            // Get input data
            $input = $this->request->getJSON(true) ?? $this->request->getRawInput();

            if (!isset($input['orders']) || !is_array($input['orders'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '請提供有效的排序資料'
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                foreach ($input['orders'] as $item) {
                    if (!isset($item['id']) || !isset($item['sort_order'])) {
                        continue;
                    }

                    // Verify category belongs to this template
                    $category = $this->categoryModel
                        ->where('template_id', $templateId)
                        ->find($item['id']);

                    if ($category) {
                        $this->categoryModel->update($item['id'], [
                            'sort_order' => $item['sort_order']
                        ]);
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('更新排序失敗');
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => '風險分類排序更新成功'
                ]);

            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            log_message('error', 'Category reorder error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新風險分類排序失敗: ' . $e->getMessage()
            ]);
        }
    }
}