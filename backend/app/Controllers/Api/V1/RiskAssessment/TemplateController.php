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

            // Copy template contents
            $db = \Config\Database::connect();

            // 1. Copy template contents
            $db->query("
                INSERT INTO template_contents (template_id, topic, description, sort_order, created_at, updated_at)
                SELECT ?, topic, description, sort_order, NOW(), NOW()
                FROM template_contents
                WHERE template_id = ?
            ", [$newTemplateId, $id]);

            // 2. Copy risk categories association (if exists in your schema)
            // Note: Based on your schema, categories seem to be global, not template-specific
            // So we don't need to copy them

            // 3. Copy risk topics
            $db->query("
                INSERT INTO risk_topics (template_id, topic_name, sort_order, created_at, updated_at)
                SELECT ?, topic_name, sort_order, NOW(), NOW()
                FROM risk_topics
                WHERE template_id = ?
            ", [$newTemplateId, $id]);

            // 4. Copy risk factors
            // First, we need to get the mapping of old topic IDs to new topic IDs
            $oldTopics = $db->query("SELECT id FROM risk_topics WHERE template_id = ? ORDER BY sort_order", [$id])->getResultArray();
            $newTopics = $db->query("SELECT id FROM risk_topics WHERE template_id = ? ORDER BY sort_order", [$newTemplateId])->getResultArray();

            $topicMapping = [];
            for ($i = 0; $i < count($oldTopics); $i++) {
                if (isset($newTopics[$i])) {
                    $topicMapping[$oldTopics[$i]['id']] = $newTopics[$i]['id'];
                }
            }

            // Copy risk factors with updated topic_id
            foreach ($topicMapping as $oldTopicId => $newTopicId) {
                $db->query("
                    INSERT INTO risk_factors (template_id, topic_id, category_id, factor_name, sort_order, created_at, updated_at)
                    SELECT ?, ?, category_id, factor_name, sort_order, NOW(), NOW()
                    FROM risk_factors
                    WHERE template_id = ? AND topic_id = ?
                ", [$newTemplateId, $newTopicId, $id, $oldTopicId]);
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