<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use CodeIgniter\Controller;

class SimpleContentController extends Controller
{
    /**
     * Get contents list for template
     */
    public function index($templateId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $contents = [
                [
                    'id' => 1,
                    'template_id' => (int)$templateId,
                    'category_id' => 1,
                    'topic' => 'Credit Risk Assessment Framework',
                    'description' => 'Comprehensive framework for assessing credit risks',
                    'scoring_method' => 'scale_1_5',
                    'weight' => 25.0,
                    'sort_order' => 1,
                    'created_at' => '2025-09-17 14:00:00',
                    'updated_at' => '2025-09-17 14:00:00'
                ],
                [
                    'id' => 2,
                    'template_id' => (int)$templateId,
                    'category_id' => 1,
                    'topic' => 'Financial Stability Analysis',
                    'description' => 'Analysis of financial stability indicators',
                    'scoring_method' => 'scale_1_10',
                    'weight' => 20.0,
                    'sort_order' => 2,
                    'created_at' => '2025-09-17 14:00:00',
                    'updated_at' => '2025-09-17 14:00:00'
                ],
                [
                    'id' => 3,
                    'template_id' => (int)$templateId,
                    'category_id' => 2,
                    'topic' => 'Operational Risk Management',
                    'description' => 'Assessment of operational risk management practices',
                    'scoring_method' => 'percentage',
                    'weight' => 30.0,
                    'sort_order' => 1,
                    'created_at' => '2025-09-17 14:00:00',
                    'updated_at' => '2025-09-17 14:00:00'
                ]
            ];

            $response = [
                'success' => true,
                'data' => ['contents' => $contents],
                'message' => 'Template contents retrieved successfully'
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

    public function show($templateId = null, $contentId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $content = [
                'id' => (int)$contentId,
                'template_id' => (int)$templateId,
                'category_id' => 1,
                'topic' => 'Content Item ' . $contentId,
                'description' => 'Detailed description for content item ' . $contentId,
                'scoring_method' => 'scale_1_5',
                'weight' => 25.0,
                'sort_order' => 1,
                'created_at' => '2025-09-17 14:00:00',
                'updated_at' => '2025-09-17 14:00:00'
            ];

            $response = [
                'success' => true,
                'data' => $content,
                'message' => 'Content retrieved successfully'
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

    public function create($templateId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $newContent = [
                'id' => rand(100, 999),
                'template_id' => (int)$templateId,
                'category_id' => $input['category_id'] ?? 1,
                'topic' => $input['topic'] ?? 'New Content',
                'description' => $input['description'] ?? '',
                'scoring_method' => $input['scoring_method'] ?? 'scale_1_5',
                'weight' => $input['weight'] ?? 1.0,
                'sort_order' => $input['sort_order'] ?? 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $response = [
                'success' => true,
                'data' => $newContent,
                'message' => 'Content created successfully'
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

    public function update($templateId = null, $contentId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $updatedContent = [
                'id' => (int)$contentId,
                'template_id' => (int)$templateId,
                'category_id' => $input['category_id'] ?? 1,
                'topic' => $input['topic'] ?? 'Updated Content',
                'description' => $input['description'] ?? '',
                'scoring_method' => $input['scoring_method'] ?? 'scale_1_5',
                'weight' => $input['weight'] ?? 1.0,
                'sort_order' => $input['sort_order'] ?? 1,
                'created_at' => '2025-09-17 14:00:00',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $response = [
                'success' => true,
                'data' => $updatedContent,
                'message' => 'Content updated successfully'
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

    public function delete($templateId = null, $contentId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $response = [
                'success' => true,
                'message' => 'Content deleted successfully',
                'data' => [
                    'id' => (int)$contentId,
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

    public function reorder($templateId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $response = [
                'success' => true,
                'message' => 'Contents reordered successfully',
                'data' => ['template_id' => (int)$templateId]
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