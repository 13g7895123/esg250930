<?php

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Enhanced API Response Trait
 * Provides standardized response format with metadata and enhanced error handling
 */
trait EnhancedApiResponseTrait
{
    /**
     * @var float Request start time for execution tracking
     */
    protected $requestStartTime;

    /**
     * @var string Unique request ID
     */
    protected $requestId;

    /**
     * @var array Performance metrics tracking
     */
    protected $performanceMetrics = [];

    /**
     * Initialize enhanced response tracking
     */
    protected function initializeEnhancedResponse(): void
    {
        $this->requestStartTime = microtime(true);
        $this->requestId = uniqid('req_', true);
        $this->performanceMetrics = [
            'database_queries' => 0,
            'batch_operations_used' => false,
            'memory_usage' => memory_get_usage(true),
            'transaction_count' => 0,
            'rollback_occurred' => false
        ];
    }

    /**
     * Create enhanced success response with metadata
     */
    protected function enhancedSuccessResponse(array $data, int $statusCode = 200, string $message = null): ResponseInterface
    {
        $responseData = [
            'success' => true,
            'data' => $data,
            'meta' => $this->generateMetadata()
        ];

        if ($message) {
            $responseData['message'] = $message;
        }

        return $this->response->setStatusCode($statusCode)->setJSON($responseData);
    }

    /**
     * Create enhanced error response with detailed context
     */
    protected function enhancedErrorResponse(
        string $message,
        string $errorCode,
        string $errorType,
        int $statusCode = 400,
        array $context = [],
        array $suggestions = []
    ): ResponseInterface {
        $responseData = [
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'type' => $errorType,
                'message' => $message,
                'context' => array_merge([
                    'resource' => $this->getResourceName(),
                    'action' => $this->getActionName(),
                    'parameters' => $this->getRequestParameters()
                ], $context),
                'suggestions' => $suggestions
            ],
            'meta' => $this->generateMetadata()
        ];

        return $this->response->setStatusCode($statusCode)->setJSON($responseData);
    }

    /**
     * Create enhanced validation error response
     */
    protected function enhancedValidationErrorResponse(array $errors): ResponseInterface
    {
        $fieldErrors = [];

        foreach ($errors as $field => $messages) {
            // Handle both standard validation errors and enhanced validation errors
            if (is_array($messages) && isset($messages['code'])) {
                // Enhanced validation error format
                $fieldErrors[$field] = $messages;
            } else {
                // Standard validation error format
                $fieldErrors[$field] = [
                    'message' => is_array($messages) ? implode(', ', $messages) : $messages,
                    'code' => $this->getValidationErrorCode($field, $messages),
                    'value_received' => $this->request->getVar($field),
                    'expected_format' => $this->getExpectedFormat($field),
                    'suggestion' => $this->getFieldSuggestion($field)
                ];
            }
        }

        $responseData = [
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_FAILED',
                'type' => 'ValidationError',
                'message' => '輸入資料驗證失敗',
                'field_errors' => $fieldErrors,
                'context' => [
                    'resource' => $this->getResourceName(),
                    'action' => $this->getActionName(),
                    'parameters' => $this->getRequestParameters()
                ],
                'suggestions' => [
                    '請檢查所有必填欄位是否正確填寫',
                    '確認資料格式符合API規範',
                    '參考API文檔了解正確的欄位格式'
                ]
            ],
            'meta' => $this->generateMetadata()
        ];

        return $this->response->setStatusCode(400)->setJSON($responseData);
    }

    /**
     * Generate metadata for response
     */
    protected function generateMetadata(): array
    {
        $executionTime = microtime(true) - $this->requestStartTime;
        $currentMemory = memory_get_usage(true);
        $memoryUsed = $currentMemory - $this->performanceMetrics['memory_usage'];

        $metadata = [
            'api_version' => '1.0',
            'execution_time' => round($executionTime, 4),
            'timestamp' => date('c'),
            'request_id' => $this->requestId,
            'server_info' => [
                'environment' => ENVIRONMENT,
                'php_version' => PHP_VERSION,
                'framework_version' => \CodeIgniter\CodeIgniter::CI_VERSION
            ]
        ];

        // Add performance metrics if any were tracked
        if (!empty($this->performanceMetrics)) {
            $this->performanceMetrics['memory_usage'] = $memoryUsed;
            $metadata['performance_metrics'] = $this->performanceMetrics;
        }

        return $metadata;
    }

    /**
     * Get resource name for context
     */
    protected function getResourceName(): string
    {
        return 'risk_assessment_templates';
    }

    /**
     * Get current action name
     */
    protected function getActionName(): string
    {
        $method = $this->request->getMethod();
        $segments = $this->request->getUri()->getSegments();
        $lastSegment = end($segments);

        if ($method === 'GET' && is_numeric($lastSegment)) {
            return 'show';
        } elseif ($method === 'GET') {
            return 'index';
        } elseif ($method === 'POST' && $lastSegment === 'copy') {
            return 'copy';
        } elseif ($method === 'POST') {
            return 'create';
        } elseif ($method === 'PUT') {
            return 'update';
        } elseif ($method === 'DELETE') {
            return 'delete';
        }

        return strtolower($method);
    }

    /**
     * Get request parameters for context
     */
    protected function getRequestParameters(): array
    {
        $params = [];
        $params['query'] = $this->request->getGet();
        $params['route'] = $this->request->getUri()->getSegments();

        return $params;
    }

    /**
     * Get validation error code for specific field
     */
    protected function getValidationErrorCode(string $field, $messages): string
    {
        $message = is_array($messages) ? $messages[0] : $messages;

        if (strpos($message, 'required') !== false) {
            return 'FIELD_REQUIRED';
        } elseif (strpos($message, 'unique') !== false) {
            return 'FIELD_NOT_UNIQUE';
        } elseif (strpos($message, 'max_length') !== false) {
            return 'FIELD_TOO_LONG';
        } elseif (strpos($message, 'in_list') !== false) {
            return 'INVALID_FIELD_VALUE';
        }

        return 'VALIDATION_ERROR';
    }

    /**
     * Get expected format for field
     */
    protected function getExpectedFormat(string $field): string
    {
        switch ($field) {
            case 'version_name':
                return 'Non-empty string, max 255 characters, must be unique';
            case 'status':
                return 'One of: active, inactive, draft';
            case 'description':
                return 'Optional string';
            default:
                return 'See API documentation for field requirements';
        }
    }

    /**
     * Get helpful suggestion for field
     */
    protected function getFieldSuggestion(string $field): string
    {
        switch ($field) {
            case 'version_name':
                return '請提供有意義的版本名稱，例如: "2024年度風險評估範本 v1.0"';
            case 'status':
                return '使用 "active" 表示啟用，"draft" 表示草稿，"inactive" 表示停用';
            case 'description':
                return '提供範本的詳細說明有助於使用者理解用途';
            default:
                return '請參考API文檔了解正確格式';
        }
    }

    /**
     * Track database query for performance metrics
     */
    protected function trackDatabaseQuery(): void
    {
        $this->performanceMetrics['database_queries']++;
    }

    /**
     * Mark that batch operations are being used
     */
    protected function markBatchOperationsUsed(): void
    {
        $this->performanceMetrics['batch_operations_used'] = true;
    }

    /**
     * Track transaction usage
     */
    protected function trackTransaction(): void
    {
        $this->performanceMetrics['transaction_count']++;
    }

    /**
     * Mark that a rollback occurred
     */
    protected function markRollbackOccurred(): void
    {
        $this->performanceMetrics['rollback_occurred'] = true;
    }
}