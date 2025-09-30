<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use CodeIgniter\Controller;

class SimpleFactorController extends Controller
{
    /**
     * Get factors list for template
     */
    public function index($templateId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $factors = [
                [
                    'id' => 1,
                    'template_id' => (int)$templateId,
                    'topic_id' => 1,
                    'factor_name' => 'Credit Score Analysis',
                    'factor_code' => 'CSA001',
                    'description' => 'Assessment of credit scoring methodologies',
                    'weight' => 25.0,
                    'scoring_method' => 'scale_1_5',
                    'sort_order' => 1,
                    'status' => 'active',
                    'created_at' => '2025-09-17 14:00:00',
                    'updated_at' => '2025-09-17 14:00:00'
                ],
                [
                    'id' => 2,
                    'template_id' => (int)$templateId,
                    'topic_id' => 1,
                    'factor_name' => 'Debt-to-Income Ratio',
                    'factor_code' => 'DTI001',
                    'description' => 'Assessment of borrower debt-to-income ratio',
                    'weight' => 20.0,
                    'scoring_method' => 'scale_1_5',
                    'sort_order' => 2,
                    'status' => 'active',
                    'created_at' => '2025-09-17 14:00:00',
                    'updated_at' => '2025-09-17 14:00:00'
                ],
                [
                    'id' => 3,
                    'template_id' => (int)$templateId,
                    'topic_id' => 2,
                    'factor_name' => 'Operational Efficiency',
                    'factor_code' => 'OE001',
                    'description' => 'Assessment of operational process efficiency',
                    'weight' => 30.0,
                    'scoring_method' => 'scale_1_10',
                    'sort_order' => 1,
                    'status' => 'active',
                    'created_at' => '2025-09-17 14:00:00',
                    'updated_at' => '2025-09-17 14:00:00'
                ]
            ];

            $response = [
                'success' => true,
                'data' => ['factors' => $factors],
                'message' => 'Factors retrieved successfully'
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

            $newFactor = [
                'id' => rand(100, 999),
                'template_id' => (int)$templateId,
                'topic_id' => $input['topic_id'] ?? 1,
                'factor_name' => $input['factor_name'] ?? 'New Factor',
                'factor_code' => $input['factor_code'] ?? 'NEW',
                'description' => $input['description'] ?? '',
                'weight' => $input['weight'] ?? 1.0,
                'scoring_method' => $input['scoring_method'] ?? 'scale_1_5',
                'sort_order' => $input['sort_order'] ?? 1,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $response = [
                'success' => true,
                'data' => $newFactor,
                'message' => 'Factor created successfully'
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

    public function update($templateId = null, $factorId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $updatedFactor = [
                'id' => (int)$factorId,
                'template_id' => (int)$templateId,
                'topic_id' => $input['topic_id'] ?? 1,
                'factor_name' => $input['factor_name'] ?? 'Updated Factor',
                'factor_code' => $input['factor_code'] ?? 'UPD',
                'description' => $input['description'] ?? '',
                'weight' => $input['weight'] ?? 1.0,
                'scoring_method' => $input['scoring_method'] ?? 'scale_1_5',
                'sort_order' => $input['sort_order'] ?? 1,
                'status' => 'active',
                'created_at' => '2025-09-17 14:00:00',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $response = [
                'success' => true,
                'data' => $updatedFactor,
                'message' => 'Factor updated successfully'
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

    public function delete($templateId = null, $factorId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $response = [
                'success' => true,
                'message' => 'Factor deleted successfully',
                'data' => [
                    'id' => (int)$factorId,
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
                'message' => 'Factors reordered successfully',
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

    public function stats($templateId = null)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $stats = [
                'template_id' => (int)$templateId,
                'total_factors' => 15,
                'active_factors' => 13,
                'inactive_factors' => 2,
                'avg_weight' => 6.67,
                'total_weight' => 100.0,
                'factors_by_scoring_method' => [
                    'scale_1_5' => 8,
                    'scale_1_10' => 5,
                    'percentage' => 2
                ],
                'factors_by_topic' => [
                    ['topic_id' => 1, 'topic_name' => 'Credit Risk Assessment', 'factor_count' => 8],
                    ['topic_id' => 2, 'topic_name' => 'Process Risk', 'factor_count' => 7]
                ]
            ];

            $response = [
                'success' => true,
                'data' => $stats,
                'message' => 'Factor statistics retrieved successfully'
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