<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use App\Controllers\Api\BaseController;
use App\Models\RiskAssessment\RiskTopicModel;
use App\Models\RiskAssessment\RiskAssessmentTemplateModel;
use App\Models\RiskAssessment\RiskCategoryModel;
use CodeIgniter\HTTP\ResponseInterface;

class RiskTopicController extends BaseController
{
    protected $topicModel;
    protected $templateModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->topicModel = new RiskTopicModel();
        $this->templateModel = new RiskAssessmentTemplateModel();
        $this->categoryModel = new RiskCategoryModel();
    }

    /**
     * Get topics list for template
     * GET /api/v1/risk-assessment/templates/{templateId}/topics
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
            $categoryId = $this->request->getGet('category_id');

            if ($categoryId) {
                $topics = $this->topicModel->getTopicsByCategory($templateId, $categoryId);
            } else {
                $topics = $this->topicModel->getTopicsWithCategory($templateId, $search);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'topics' => $topics
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk topic index error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '取得風險主題列表失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new risk topic
     * POST /api/v1/risk-assessment/templates/{templateId}/topics
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
                'topic_name' => 'required|max_length[255]',
                'category_id' => 'required|integer',
                'topic_code' => 'permit_empty|max_length[50]',
                'description' => 'permit_empty|string',
                'sort_order' => 'permit_empty|integer',
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

            // Check topic code uniqueness within template
            $topicCode = $input['topic_code'] ?? null;
            if (!empty($topicCode) && !$this->topicModel->isTopicCodeUniqueInTemplate($templateId, $topicCode)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '主題代碼在此範本中已存在'
                ]);
            }

            $data = [
                'template_id' => $templateId,
                'category_id' => $input['category_id'],
                'topic_name' => $input['topic_name'],
                'topic_code' => $topicCode,
                'description' => $input['description'] ?? '',
                'sort_order' => $input['sort_order'] ?? $this->topicModel->getNextSortOrder($templateId),
                'status' => $input['status'] ?? 'active'
            ];

            $topicId = $this->topicModel->insert($data);

            if (!$topicId) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '建立風險主題失敗',
                    'errors' => $this->topicModel->errors()
                ]);
            }

            $topic = $this->topicModel->getTopicsWithCategory($templateId);
            $createdTopic = array_filter($topic, function($t) use ($topicId) {
                return $t['id'] == $topicId;
            });
            $createdTopic = reset($createdTopic);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '風險主題建立成功',
                'data' => $createdTopic ?: $this->topicModel->find($topicId)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk topic create error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '建立風險主題失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update risk topic
     * PUT /api/v1/risk-assessment/templates/{templateId}/topics/{id}
     */
    public function update($id = null)
    {
        try {
            // Get template ID and topic ID from URI segments
            $templateId = $this->request->uri->getSegment(5);
            $topicId = $this->request->uri->getSegment(7); // topics/{id}

            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if topic exists and belongs to template
            $topic = $this->topicModel->where('template_id', $templateId)->find($topicId);
            if (!$topic) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '風險主題不存在'
                ]);
            }

            $rules = [
                'topic_name' => 'required|max_length[255]',
                'category_id' => 'required|integer',
                'topic_code' => 'permit_empty|max_length[50]',
                'description' => 'permit_empty|string',
                'sort_order' => 'permit_empty|integer',
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

            // Check topic code uniqueness within template
            $topicCode = $input['topic_code'] ?? '';
            if (!empty($topicCode) && !$this->topicModel->isTopicCodeUniqueInTemplate($templateId, $topicCode, $topicId)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '主題代碼在此範本中已存在'
                ]);
            }

            $data = [
                'category_id' => $input['category_id'],
                'topic_name' => $input['topic_name'],
                'topic_code' => $topicCode,
                'description' => $input['description'] ?? $topic['description'],
                'sort_order' => $input['sort_order'] ?? $topic['sort_order'],
                'status' => $input['status'] ?? $topic['status']
            ];

            $success = $this->topicModel->update($topicId, $data);

            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新風險主題失敗',
                    'errors' => $this->topicModel->errors()
                ]);
            }

            $topics = $this->topicModel->getTopicsWithCategory($templateId);
            $updatedTopic = array_filter($topics, function($t) use ($topicId) {
                return $t['id'] == $topicId;
            });
            $updatedTopic = reset($updatedTopic);

            return $this->response->setJSON([
                'success' => true,
                'message' => '風險主題更新成功',
                'data' => $updatedTopic ?: $this->topicModel->find($topicId)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk topic update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新風險主題失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete risk topic (hard delete)
     * DELETE /api/v1/risk-assessment/templates/{templateId}/topics/{id}
     */
    public function delete($id = null)
    {
        try {
            // Get template ID and topic ID from URI segments
            $templateId = $this->request->uri->getSegment(5);
            $topicId = $this->request->uri->getSegment(7); // topics/{id}

            // Check if template exists
            $template = $this->templateModel->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Check if topic exists and belongs to template
            $topic = $this->topicModel->where('template_id', $templateId)->find($topicId);
            if (!$topic) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '風險主題不存在'
                ]);
            }

            // Hard delete topic (risk factors with this topic_id will have topic_id set to NULL due to FK constraint)
            $success = $this->topicModel->delete($topicId, true);

            if (!$success) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '刪除風險主題失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '風險主題刪除成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Risk topic delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除風險主題失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reorder topics within a template
     * PUT /api/v1/risk-assessment/templates/{templateId}/topics/reorder
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

            $success = $this->topicModel->reorderTopics($templateId, $input['orders']);

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
            log_message('error', 'Risk topic reorder error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '重新排序失敗: ' . $e->getMessage()
            ]);
        }
    }
}