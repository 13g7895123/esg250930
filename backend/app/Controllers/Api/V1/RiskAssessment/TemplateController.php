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
}