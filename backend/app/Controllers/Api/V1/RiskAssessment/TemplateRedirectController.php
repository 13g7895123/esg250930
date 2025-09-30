<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use CodeIgniter\Controller;
use App\Models\RiskAssessment\RiskAssessmentTemplateModel;

class TemplateRedirectController extends Controller
{
    protected $templateModel;

    public function __construct()
    {
        $this->templateModel = new RiskAssessmentTemplateModel();
    }

    /**
     * Return templates data from database
     * GET /api/v1/risk-assessment/templates
     */
    public function index()
    {
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Get templates from database with statistics
            $templates = $this->templateModel->getTemplatesWithStats()
                ->orderBy('created_at', 'DESC')
                ->findAll();

            // Clean and sanitize data to prevent UTF-8 encoding issues
            $cleanedTemplates = $this->sanitizeDataForJson($templates);

            $response = [
                'success' => true,
                'data' => $cleanedTemplates,
                'message' => 'Templates retrieved successfully',
                'count' => count($cleanedTemplates)
            ];

            // Direct JSON output with maximum compatibility
            $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            if ($json === false) {
                $response = [
                    'success' => false,
                    'message' => 'JSON encoding failed: ' . json_last_error_msg(),
                    'data' => []
                ];
                echo json_encode($response);
            } else {
                echo $json;
            }
            exit;

        } catch (\Exception $e) {
            // Bypass CodeIgniter's error handling completely
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'data' => []
            ]);
            exit;
        } catch (\Throwable $e) {
            // Catch any other throwable
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Fatal error: ' . $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'data' => []
            ]);
            exit;
        }
    }

    /**
     * Return specific template details
     * GET /api/v1/risk-assessment/templates/{id}
     */
    public function show($id = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $template = $this->templateModel->getTemplateWithStats($id);

            if (!$template) {
                http_response_code(404);
                $errorResponse = [
                    'success' => false,
                    'message' => 'Template not found',
                    'data' => null
                ];
                echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }

            // Clean and sanitize data to prevent UTF-8 encoding issues
            $cleanedTemplate = $this->sanitizeDataForJson($template);

            $response = [
                'success' => true,
                'data' => $cleanedTemplate,
                'message' => 'Template retrieved successfully'
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            $errorResponse = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
            echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    }

    /**
     * Create new template
     * POST /api/v1/risk-assessment/templates
     */
    public function create()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $data = [
                'version_name' => $input['version_name'] ?? 'New Template',
                'description' => $input['description'] ?? '',
                'status' => $input['status'] ?? 'active'
            ];

            $templateId = $this->templateModel->insert($data);

            if (!$templateId) {
                http_response_code(400);
                $errorResponse = [
                    'success' => false,
                    'message' => 'Failed to create template: ' . implode(', ', $this->templateModel->errors()),
                    'data' => null
                ];
                echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }

            $newTemplate = $this->templateModel->find($templateId);

            // Clean and sanitize data to prevent UTF-8 encoding issues
            $cleanedTemplate = $this->sanitizeDataForJson($newTemplate);

            $response = [
                'success' => true,
                'data' => $cleanedTemplate,
                'message' => 'Template created successfully'
            ];

            http_response_code(201);
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            $errorResponse = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
            echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    }

    /**
     * Update template
     * PUT /api/v1/risk-assessment/templates/{id}
     */
    public function update($id = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $existingTemplate = $this->templateModel->find($id);
            if (!$existingTemplate) {
                http_response_code(404);
                $errorResponse = [
                    'success' => false,
                    'message' => 'Template not found',
                    'data' => null
                ];
                echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $data = [
                'version_name' => $input['version_name'] ?? $existingTemplate['version_name'],
                'description' => $input['description'] ?? $existingTemplate['description'],
                'status' => $input['status'] ?? $existingTemplate['status']
            ];

            $updateResult = $this->templateModel->update($id, $data);

            if (!$updateResult) {
                http_response_code(400);
                $errorResponse = [
                    'success' => false,
                    'message' => 'Failed to update template: ' . implode(', ', $this->templateModel->errors()),
                    'data' => null
                ];
                echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }

            $updatedTemplate = $this->templateModel->find($id);

            // Clean and sanitize data to prevent UTF-8 encoding issues
            $cleanedTemplate = $this->sanitizeDataForJson($updatedTemplate);

            $response = [
                'success' => true,
                'data' => $cleanedTemplate,
                'message' => 'Template updated successfully'
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            $errorResponse = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
            echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    }

    /**
     * Delete template
     * DELETE /api/v1/risk-assessment/templates/{id}
     */
    public function delete($id = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $existingTemplate = $this->templateModel->find($id);
            if (!$existingTemplate) {
                http_response_code(404);
                $errorResponse = [
                    'success' => false,
                    'message' => 'Template not found',
                    'data' => null
                ];
                echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }

            $deleteResult = $this->templateModel->delete($id);

            if (!$deleteResult) {
                http_response_code(400);
                $errorResponse = [
                    'success' => false,
                    'message' => 'Failed to delete template',
                    'data' => null
                ];
                echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }

            $response = [
                'success' => true,
                'message' => 'Template deleted successfully',
                'data' => ['id' => (int)$id]
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            $errorResponse = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
            echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    }

    /**
     * Handle OPTIONS requests for CORS
     */
    public function options()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        http_response_code(200);
        exit;
    }

    /**
     * Sanitize data to prevent UTF-8 encoding issues in JSON
     */
    private function sanitizeDataForJson($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeDataForJson'], $data);
        }

        if (is_string($data)) {
            // Remove or replace invalid UTF-8 characters
            $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            // Remove control characters that might cause JSON issues
            $data = preg_replace('/[\x00-\x1F\x7F]/', '', $data);
            return $data;
        }

        return $data;
    }
}