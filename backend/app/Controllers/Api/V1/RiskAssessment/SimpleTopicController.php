<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use CodeIgniter\Controller;

class SimpleTopicController extends Controller
{
    /**
     * Get topics list for template
     */
    public function index($templateId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $topics = [
                [
                    'id' => 1,
                    'template_id' => (int)$templateId,
                    'category_id' => 1,
                    'topic_name' => 'Credit Risk Assessment',
                    'topic_code' => 'CR001',
                    'description' => 'Assessment of credit-related risks',
                    'sort_order' => 1,
                    'status' => 'active',
                    'created_at' => '2025-09-17 14:00:00',
                    'updated_at' => '2025-09-17 14:00:00'
                ],
                [
                    'id' => 2,
                    'template_id' => (int)$templateId,
                    'category_id' => 2,
                    'topic_name' => 'Process Risk',
                    'topic_code' => 'PR001',
                    'description' => 'Assessment of process-related risks',
                    'sort_order' => 2,
                    'status' => 'active',
                    'created_at' => '2025-09-17 14:00:00',
                    'updated_at' => '2025-09-17 14:00:00'
                ]
            ];

            $response = [
                'success' => true,
                'data' => ['topics' => $topics],
                'message' => 'Topics retrieved successfully'
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

            $newTopic = [
                'id' => rand(100, 999),
                'template_id' => (int)$templateId,
                'category_id' => $input['category_id'] ?? 1,
                'topic_name' => $input['topic_name'] ?? 'New Topic',
                'topic_code' => $input['topic_code'] ?? 'NEW',
                'description' => $input['description'] ?? '',
                'sort_order' => $input['sort_order'] ?? 1,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $response = [
                'success' => true,
                'data' => $newTopic,
                'message' => 'Topic created successfully'
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

    public function update($templateId = null, $topicId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $updatedTopic = [
                'id' => (int)$topicId,
                'template_id' => (int)$templateId,
                'category_id' => $input['category_id'] ?? 1,
                'topic_name' => $input['topic_name'] ?? 'Updated Topic',
                'topic_code' => $input['topic_code'] ?? 'UPD',
                'description' => $input['description'] ?? '',
                'sort_order' => $input['sort_order'] ?? 1,
                'status' => 'active',
                'created_at' => '2025-09-17 14:00:00',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $response = [
                'success' => true,
                'data' => $updatedTopic,
                'message' => 'Topic updated successfully'
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

    public function delete($templateId = null, $topicId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $response = [
                'success' => true,
                'message' => 'Topic deleted successfully',
                'data' => [
                    'id' => (int)$topicId,
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
                'message' => 'Topics reordered successfully',
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