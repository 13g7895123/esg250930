<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use CodeIgniter\Controller;

class SimpleCategoryController extends Controller
{
    /**
     * Get categories list for template
     * GET /api/v1/risk-assessment/templates/{templateId}/categories
     */
    public function index($templateId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $categories = [
                [
                    'id' => 1,
                    'template_id' => (int)$templateId,
                    'category_name' => 'Financial Risk',
                    'category_code' => 'FIN',
                    'description' => 'Financial related risks',
                    'sort_order' => 1,
                    'created_at' => '2025-09-17 14:00:00',
                    'updated_at' => '2025-09-17 14:00:00'
                ],
                [
                    'id' => 2,
                    'template_id' => (int)$templateId,
                    'category_name' => 'Operational Risk',
                    'category_code' => 'OPS',
                    'description' => 'Operational related risks',
                    'sort_order' => 2,
                    'created_at' => '2025-09-17 14:00:00',
                    'updated_at' => '2025-09-17 14:00:00'
                ]
            ];

            $response = [
                'success' => true,
                'data' => [
                    'categories' => $categories
                ],
                'message' => 'Categories retrieved successfully'
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
     * Create new category
     * POST /api/v1/risk-assessment/templates/{templateId}/categories
     */
    public function create($templateId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $newCategory = [
                'id' => rand(100, 999),
                'template_id' => (int)$templateId,
                'category_name' => $input['category_name'] ?? 'New Category',
                'category_code' => $input['category_code'] ?? 'NEW',
                'description' => $input['description'] ?? '',
                'sort_order' => $input['sort_order'] ?? 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $response = [
                'success' => true,
                'data' => $newCategory,
                'message' => 'Category created successfully'
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
     * Update category
     * PUT /api/v1/risk-assessment/templates/{templateId}/categories/{categoryId}
     */
    public function update($templateId = null, $categoryId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $updatedCategory = [
                'id' => (int)$categoryId,
                'template_id' => (int)$templateId,
                'category_name' => $input['category_name'] ?? 'Updated Category',
                'category_code' => $input['category_code'] ?? 'UPD',
                'description' => $input['description'] ?? '',
                'sort_order' => $input['sort_order'] ?? 1,
                'created_at' => '2025-09-17 14:00:00',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $response = [
                'success' => true,
                'data' => $updatedCategory,
                'message' => 'Category updated successfully'
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
     * Delete category
     * DELETE /api/v1/risk-assessment/templates/{templateId}/categories/{categoryId}
     */
    public function delete($templateId = null, $categoryId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $response = [
                'success' => true,
                'message' => 'Category deleted successfully',
                'data' => [
                    'id' => (int)$categoryId,
                    'template_id' => (int)$templateId
                ]
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
}