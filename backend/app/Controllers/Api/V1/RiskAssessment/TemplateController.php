<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RiskAssessmentTemplateModel;

class TemplateController extends ResourceController
{
    protected $modelName = 'App\Models\RiskAssessmentTemplateModel';
    protected $format = 'json';

    public function __construct()
    {
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * Return the editable properties of a resource object
     * GET /api/v1/risk-assessment/templates
     */
    public function index()
    {
        try {
            // Use direct database query instead of model
            $db = \Config\Database::connect();
            $query = $db->query('SELECT id, version_name, description, status, created_at, updated_at FROM risk_assessment_templates');
            $templates = $query->getResultArray();

            // Manual JSON response bypassing CodeIgniter's formatter
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');

            $response = [
                'success' => true,
                'data' => $templates
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;

        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            http_response_code(500);

            $response = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * Return the properties of a resource object
     * GET /api/v1/risk-assessment/templates/{id}
     */
    public function show($id = null)
    {
        try {
            $template = $this->model->find($id);

            if (!$template) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            return $this->respond([
                'success' => true,
                'data' => $template
            ]);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new resource object
     * POST /api/v1/risk-assessment/templates
     */
    public function create()
    {
        try {
            $input = $this->request->getJSON(true) ?: $this->request->getPost();

            $rules = [
                'version_name' => 'required|max_length[255]',
                'description' => 'permit_empty|string',
                'status' => 'permit_empty|in_list[active,inactive,draft]'
            ];

            if (!$this->validate($rules, $input)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }

            $data = [
                'version_name' => $input['version_name'],
                'description' => $input['description'] ?? null,
                'status' => $input['status'] ?? 'active'
            ];

            $templateId = $this->model->insert($data);

            if (!$templateId) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Failed to create template',
                    'errors' => $this->model->errors()
                ], 500);
            }

            $template = $this->model->find($templateId);

            return $this->respond([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template
            ], 201);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add or update a model resource
     * PUT /api/v1/risk-assessment/templates/{id}
     */
    public function update($id = null)
    {
        try {
            $template = $this->model->find($id);
            if (!$template) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $input = $this->request->getJSON(true);
            if (empty($input)) {
                $input = $this->request->getRawInput();
            }

            $rules = [
                'version_name' => "required|max_length[255]",
                'description' => 'permit_empty|string',
                'status' => 'permit_empty|in_list[active,inactive,draft]'
            ];

            if (!$this->validate($rules, $input)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }

            $data = [
                'version_name' => $input['version_name'],
                'description' => $input['description'] ?? $template['description'],
                'status' => $input['status'] ?? $template['status']
            ];

            $success = $this->model->update($id, $data);

            if (!$success) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Failed to update template',
                    'errors' => $this->model->errors()
                ], 500);
            }

            $updatedTemplate = $this->model->find($id);

            return $this->respond([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => $updatedTemplate
            ]);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the designated resource object
     * DELETE /api/v1/risk-assessment/templates/{id}
     */
    public function delete($id = null)
    {
        try {
            $template = $this->model->find($id);
            if (!$template) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $success = $this->model->delete($id);

            if (!$success) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Failed to delete template'
                ], 500);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copy a template
     * POST /api/v1/risk-assessment/templates/{id}/copy
     */
    public function copy($id = null)
    {
        try {
            // Find the original template
            $originalTemplate = $this->model->find($id);
            if (!$originalTemplate) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            // Get input data
            $input = $this->request->getJSON(true) ?: $this->request->getPost();

            // Validation rules
            $rules = [
                'version_name' => 'required|max_length[255]',
                'description' => 'permit_empty|string'
            ];

            if (!$this->validate($rules, $input)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }

            // Prepare data for the new template
            $data = [
                'version_name' => $input['version_name'],
                'description' => $input['description'] ?? $originalTemplate['description'],
                'status' => 'draft', // New copy starts as draft
                'copied_from' => $id
            ];

            // Create the new template
            $newTemplateId = $this->model->insert($data);

            if (!$newTemplateId) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Failed to copy template',
                    'errors' => $this->model->errors()
                ], 500);
            }

            // Copy template structure and contents
            $db = \Config\Database::connect();

            // 1. Copy risk categories with all fields
            $db->query("
                INSERT INTO risk_categories (template_id, category_name, category_code, description, sort_order, created_at, updated_at)
                SELECT ?, category_name, category_code, description, sort_order, NOW(), NOW()
                FROM risk_categories
                WHERE template_id = ?
            ", [$newTemplateId, $id]);

            // Get mapping of old category IDs to new category IDs
            $oldCategories = $db->query("SELECT id FROM risk_categories WHERE template_id = ? ORDER BY sort_order", [$id])->getResultArray();
            $newCategories = $db->query("SELECT id FROM risk_categories WHERE template_id = ? ORDER BY sort_order", [$newTemplateId])->getResultArray();

            $categoryMapping = [];
            for ($i = 0; $i < count($oldCategories); $i++) {
                if (isset($newCategories[$i])) {
                    $categoryMapping[$oldCategories[$i]['id']] = $newCategories[$i]['id'];
                }
            }

            // 3. Copy risk topics with updated category_id mapping
            foreach ($categoryMapping as $oldCategoryId => $newCategoryId) {
                $db->query("
                    INSERT INTO risk_topics (template_id, category_id, topic_name, topic_code, description, sort_order, status, created_at, updated_at)
                    SELECT ?, ?, topic_name, topic_code, description, sort_order, status, NOW(), NOW()
                    FROM risk_topics
                    WHERE template_id = ? AND category_id = ?
                ", [$newTemplateId, $newCategoryId, $id, $oldCategoryId]);
            }

            // 4. Get mapping of old topic IDs to new topic IDs
            $oldTopics = $db->query("SELECT id, category_id FROM risk_topics WHERE template_id = ? ORDER BY category_id, sort_order", [$id])->getResultArray();
            $newTopics = $db->query("SELECT id, category_id FROM risk_topics WHERE template_id = ? ORDER BY category_id, sort_order", [$newTemplateId])->getResultArray();

            $topicMapping = [];
            for ($i = 0; $i < count($oldTopics); $i++) {
                if (isset($newTopics[$i])) {
                    $topicMapping[$oldTopics[$i]['id']] = $newTopics[$i]['id'];
                }
            }

            // 5. Copy risk factors with updated topic_id and category_id mapping
            foreach ($topicMapping as $oldTopicId => $newTopicId) {
                // Get the old and new category_id for this topic
                $oldTopic = array_values(array_filter($oldTopics, function($t) use ($oldTopicId) {
                    return $t['id'] == $oldTopicId;
                }))[0] ?? null;

                $newTopic = array_values(array_filter($newTopics, function($t) use ($newTopicId) {
                    return $t['id'] == $newTopicId;
                }))[0] ?? null;

                if ($oldTopic && $newTopic) {
                    $db->query("
                        INSERT INTO risk_factors (template_id, topic_id, category_id, factor_name, description, status, created_at, updated_at)
                        SELECT ?, ?, ?, factor_name, description, status, NOW(), NOW()
                        FROM risk_factors
                        WHERE template_id = ? AND topic_id = ? AND category_id = ?
                    ", [$newTemplateId, $newTopicId, $newTopic['category_id'], $id, $oldTopicId, $oldTopic['category_id']]);
                }
            }

            // 6. Get mapping of old risk_factor IDs to new risk_factor IDs
            $oldFactors = $db->query("SELECT id, topic_id, category_id FROM risk_factors WHERE template_id = ? ORDER BY category_id, topic_id, sort_order", [$id])->getResultArray();
            $newFactors = $db->query("SELECT id, topic_id, category_id FROM risk_factors WHERE template_id = ? ORDER BY category_id, topic_id, sort_order", [$newTemplateId])->getResultArray();

            $factorMapping = [];
            for ($i = 0; $i < count($oldFactors); $i++) {
                if (isset($newFactors[$i])) {
                    $factorMapping[$oldFactors[$i]['id']] = $newFactors[$i]['id'];
                }
            }

            // 7. Copy template_contents with updated IDs
            $contents = $db->query("SELECT * FROM template_contents WHERE template_id = ?", [$id])->getResultArray();

            foreach ($contents as $content) {
                $newCategoryId = $categoryMapping[$content['category_id']] ?? null;
                $newTopicId = isset($content['topic_id']) ? ($topicMapping[$content['topic_id']] ?? null) : null;
                $newFactorId = isset($content['risk_factor_id']) ? ($factorMapping[$content['risk_factor_id']] ?? null) : null;

                // Only insert if we have valid mapped IDs (at least category_id should exist)
                if ($newCategoryId) {
                    $db->query("
                        INSERT INTO template_contents (
                            template_id, category_id, topic_id, risk_factor_id, description, sort_order, is_required,
                            a_content, b_content, c_placeholder, d_placeholder_1, d_placeholder_2,
                            e1_placeholder_1, e2_select_1, e2_select_2, e2_placeholder,
                            f2_select_1, f2_select_2, f2_placeholder,
                            e1_info, f1_info, g1_info, h1_info,
                            created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ", [
                        $newTemplateId,
                        $newCategoryId,
                        $newTopicId,
                        $newFactorId,
                        $content['description'],
                        $content['sort_order'],
                        $content['is_required'],
                        $content['a_content'],
                        $content['b_content'],
                        $content['c_placeholder'],
                        $content['d_placeholder_1'],
                        $content['d_placeholder_2'],
                        $content['e1_placeholder_1'],
                        $content['e2_select_1'],
                        $content['e2_select_2'],
                        $content['e2_placeholder'],
                        $content['f2_select_1'],
                        $content['f2_select_2'],
                        $content['f2_placeholder'],
                        $content['e1_info'],
                        $content['f1_info'],
                        $content['g1_info'],
                        $content['h1_info']
                    ]);
                }
            }

            // Get the newly created template
            $newTemplate = $this->model->find($newTemplateId);

            return $this->respond([
                'success' => true,
                'message' => 'Template copied successfully',
                'data' => $newTemplate
            ], 201);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}