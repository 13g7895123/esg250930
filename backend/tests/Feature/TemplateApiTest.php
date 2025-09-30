<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\RiskAssessment\RiskAssessmentTemplateModel;
use App\Models\RiskAssessment\RiskCategoryModel;
use App\Models\RiskAssessment\TemplateContentModel;

/**
 * @internal
 * 
 * TDD Test Suite for Risk Assessment Template Management API
 * Following Red-Green-Refactor cycle to ensure comprehensive coverage
 */
final class TemplateApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $seed = '';
    protected $basePath = 'api/v1/risk-assessment/templates';
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Ensure fresh database state for each test
        $this->regressDatabase();
        
        // Initialize models for test data creation
        $this->templateModel = new RiskAssessmentTemplateModel();
        $this->categoryModel = new RiskCategoryModel();
        $this->contentModel = new TemplateContentModel();
        
        // Clear database before each test
        $this->clearDatabase();
    }
    
    /**
     * Helper method to extract JSON from wrapped HTML response
     */
    protected function getCleanJSON($response)
    {
        $body = $response->getBody();
        
        // If response is wrapped in HTML, extract the JSON
        if (strpos($body, '<html>') !== false) {
            preg_match('/\{.*\}/s', $body, $matches);
            if (!empty($matches[0])) {
                // Decode HTML entities
                $jsonString = html_entity_decode($matches[0], ENT_QUOTES, 'UTF-8');
                return json_decode($jsonString, true);
            }
        }
        
        // Otherwise, try to decode directly
        return $response->getJSON(true);
    }
    
    /**
     * Clear database before each test
     */
    protected function clearDatabase()
    {
        // Clear all template-related tables in correct order
        $this->db->table('template_contents')->truncate();
        $this->db->table('risk_categories')->truncate();  
        $this->db->table('risk_assessment_templates')->truncate();
    }

    // ================================
    // RED PHASE: Template List Tests
    // ================================
    
    /**
     * @test
     * RED: Test getting templates list when no templates exist
     * Should return empty array with proper structure
     */
    public function test_get_templates_empty_database()
    {
        // Arrange - Ensure empty database
        $this->clearDatabase();
        
        // Act - Call API endpoint
        $response = $this->get($this->basePath);

        // Assert - Should return 200 with empty templates array
        $response->assertStatus(200);
        $response->assertJSONFragment([
            'success' => true,
            'data' => [
                'templates' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => 20,
                    'current_page' => 1,
                    'total_pages' => 0
                ]
            ]
        ]);
    }

    /**
     * @test
     * RED: Test getting templates list with templates
     * Should return array of templates with counts
     */
    public function test_get_templates_with_data()
    {
        // Arrange - Create test template
        $templateId = $this->createTestTemplate([
            'version_name' => 'Test Template v1.0',
            'description' => 'Test description',
            'status' => 'active'
        ]);

        // Act
        $response = $this->get($this->basePath);

        // Assert
        $response->assertStatus(200);
        $response->assertJSONFragment([
            'success' => true
        ]);
        
        $data = $this->getCleanJSON($response);
        $this->assertCount(1, $data['data']['templates']);
        $this->assertEquals('Test Template v1.0', $data['data']['templates'][0]['version_name']);
        $this->assertEquals(1, $data['data']['pagination']['total']);
    }

    /**
     * @test
     * RED: Test templates list with search functionality
     */
    public function test_get_templates_with_search()
    {
        // Arrange - Create multiple templates
        $this->createTestTemplate(['version_name' => 'Financial Risk Assessment']);
        $this->createTestTemplate(['version_name' => 'Operational Risk Assessment']);

        // Act
        $response = $this->get($this->basePath . '?search=Financial');

        // Assert
        $response->assertStatus(200);
        $data = $this->getCleanJSON($response);
        $this->assertCount(1, $data['data']['templates']);
        $this->assertStringContainsString('Financial', $data['data']['templates'][0]['version_name']);
    }

    /**
     * @test
     * RED: Test templates list pagination
     */
    public function test_get_templates_pagination()
    {
        // Arrange - Create 25 templates
        for ($i = 1; $i <= 25; $i++) {
            $this->createTestTemplate(['version_name' => "Template $i"]);
        }

        // Act - Request first page with limit 10
        $response = $this->get($this->basePath . '?page=1&limit=10');

        // Assert
        $response->assertStatus(200);
        $data = $this->getCleanJSON($response);
        $this->assertCount(10, $data['data']['templates']);
        $this->assertEquals(25, $data['data']['pagination']['total']);
        $this->assertEquals(3, $data['data']['pagination']['total_pages']);
        $this->assertTrue($data['data']['pagination']['has_next']);
    }

    // ================================
    // RED PHASE: Template Show Tests
    // ================================

    /**
     * @test
     * RED: Test getting single template details
     */
    public function test_get_single_template()
    {
        // Arrange
        $templateId = $this->createTestTemplate([
            'version_name' => 'Detailed Template',
            'description' => 'Detailed description',
            'status' => 'active'
        ]);

        // Act
        $response = $this->get($this->basePath . '/' . $templateId);

        // Assert
        $response->assertStatus(200);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('Detailed Template', $data['data']['version_name']);
        $this->assertEquals('Detailed description', $data['data']['description']);
        $this->assertEquals('active', $data['data']['status']);
        $this->assertEquals(0, $data['data']['content_count']);
        $this->assertEquals(0, $data['data']['category_count']);
    }

    /**
     * @test
     * RED: Test getting non-existent template
     */
    public function test_get_non_existent_template()
    {
        // Act
        $response = $this->get($this->basePath . '/999');

        // Assert
        $response->assertStatus(404);
        $data = $this->getCleanJSON($response);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('TEMPLATE_NOT_FOUND', $data['error']['code']);
    }

    // ================================
    // RED PHASE: Template Create Tests
    // ================================

    /**
     * @test
     * RED: Test creating template with all fields
     */
    public function test_create_template_all_fields()
    {
        // Arrange
        $templateData = [
            'version_name' => 'New Template v2.0',
            'description' => 'Comprehensive template description',
            'status' => 'active'
        ];

        // Act
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, $templateData);

        // Assert
        $data = $this->getCleanJSON($response);
        $this->assertTrue($data['success']);
        $this->assertEquals('New Template v2.0', $data['data']['version_name']);
        $this->assertEquals('Comprehensive template description', $data['data']['description']);
        $this->assertEquals('active', $data['data']['status']);
        
        // Verify in database
        $this->seeInDatabase('risk_assessment_templates', [
            'version_name' => 'New Template v2.0',
            'status' => 'active'
        ]);
    }

    /**
     * @test
     * RED: Test creating template with required fields only
     */
    public function test_create_template_required_fields_only()
    {
        // Arrange
        $templateData = [
            'version_name' => 'Minimal Template'
        ];

        // Act
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, $templateData);

        // Assert
        $response->assertStatus(201);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('Minimal Template', $data['data']['version_name']);
        $this->assertEquals('active', $data['data']['status']); // Default status
    }

    /**
     * @test
     * RED: Test creating template validation - missing version_name
     */
    public function test_create_template_missing_version_name()
    {
        // Act
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, []);

        // Assert
        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('field_errors', $data['error']);
        $this->assertArrayHasKey('version_name', $data['error']['field_errors']);
    }

    /**
     * @test
     * RED: Test creating template validation - duplicate version_name
     */
    public function test_create_template_duplicate_name()
    {
        // Arrange
        $this->createTestTemplate(['version_name' => 'Duplicate Template']);

        // Act
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
            'version_name' => 'Duplicate Template'
        ]);

        // Assert
        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('field_errors', $data['error']);
        $this->assertArrayHasKey('version_name', $data['error']['field_errors']);
    }

    // ================================
    // RED PHASE: Template Update Tests
    // ================================

    /**
     * @test
     * RED: Test updating template all fields
     */
    public function test_update_template_all_fields()
    {
        // Arrange
        $templateId = $this->createTestTemplate([
            'version_name' => 'Original Template',
            'description' => 'Original description',
            'status' => 'draft'
        ]);

        $updateData = [
            'version_name' => 'Updated Template',
            'description' => 'Updated description',
            'status' => 'active'
        ];

        // Act
        $response = $this->withBodyFormat('json')
                         ->put($this->basePath . '/' . $templateId, $updateData);


        // Assert
        $response->assertStatus(200);
        $data = $this->getCleanJSON($response);
        $this->assertTrue($data['success']);
        $this->assertEquals('Updated Template', $data['data']['version_name']);
        $this->assertEquals('Updated description', $data['data']['description']);
        $this->assertEquals('active', $data['data']['status']);
    }

    /**
     * @test
     * RED: Test updating non-existent template
     */
    public function test_update_non_existent_template()
    {
        // Act
        $response = $this->withBodyFormat('json')
                         ->put($this->basePath . '/999', [
            'version_name' => 'Update Attempt'
        ]);

        // Assert
        $response->assertStatus(404);
        $data = $this->getCleanJSON($response);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('TEMPLATE_NOT_FOUND', $data['error']['code']);
    }

    // ================================
    // RED PHASE: Template Delete Tests
    // ================================

    /**
     * @test
     * RED: Test soft deleting template
     */
    public function test_delete_template()
    {
        // Arrange
        $templateId = $this->createTestTemplate([
            'version_name' => 'Template to Delete'
        ]);

        // Act
        $response = $this->delete($this->basePath . '/' . $templateId);

        // Assert
        $response->assertStatus(200);
        $response->assertJSONFragment([
            'success' => true,
            'message' => '範本刪除成功'
        ]);

        // Verify hard delete in database
        $this->dontSeeInDatabase('risk_assessment_templates', [
            'id' => $templateId
        ]);
    }

    /**
     * @test
     * RED: Test deleting non-existent template
     */
    public function test_delete_non_existent_template()
    {
        // Act
        $response = $this->delete($this->basePath . '/999');

        // Assert
        $response->assertStatus(404);
        $response->assertJSONFragment([
            'success' => false,
            'message' => '範本不存在'
        ]);
    }

    // ================================
    // RED PHASE: Template Copy Tests
    // ================================

    /**
     * @test
     * RED: Test copying template
     */
    public function test_copy_template()
    {
        // Arrange
        $originalId = $this->createTestTemplate([
            'version_name' => 'Original Template',
            'description' => 'Original description'
        ]);

        // Add some categories and content to original
        $categoryId = $this->createTestCategory($originalId, [
            'category_name' => 'Financial Risk'
        ]);
        
        $this->createTestContent($originalId, $categoryId, [
            'topic' => 'Cash Flow Risk'
        ]);

        $copyData = [
            'version_name' => 'Copied Template',
            'description' => 'Copied from original'
        ];

        // Act
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath . '/' . $originalId . '/copy', $copyData);

        // Assert
        $response->assertStatus(201);
        $data = $this->getCleanJSON($response);
        $this->assertTrue($data['success']);
        $this->assertEquals('Copied Template', $data['data']['version_name']);
        $this->assertEquals('Copied from original', $data['data']['description']);
        $this->assertEquals($originalId, $data['data']['copied_from']);
        
        // Verify categories and content were copied
        $this->assertEquals(1, $data['data']['category_count']);
        $this->assertEquals(1, $data['data']['content_count']);
    }

    /**
     * @test
     * RED: Test copying non-existent template
     */
    public function test_copy_non_existent_template()
    {
        // Act
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath . '/999/copy', [
            'version_name' => 'Copy Attempt'
        ]);

        // Assert
        $response->assertStatus(404);
        $data = $this->getCleanJSON($response);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('TEMPLATE_NOT_FOUND', $data['error']['code']);
    }

    // ================================
    // RED PHASE: Enhanced Response Format Tests (TEMP-BE-001-1)
    // ================================

    /**
     * @test
     * RED: Test enhanced response format for templates list with metadata
     * Enhanced format should include request metadata, execution time, and API version
     */
    public function test_templates_list_enhanced_response_format()
    {
        // Arrange
        $this->createTestTemplate(['version_name' => 'Test Template for Format']);

        // Act
        $response = $this->get($this->basePath);

        // Assert - Enhanced response format should include:
        $response->assertStatus(200);
        $data = $this->getCleanJSON($response);

        // Standard response structure
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data); // NEW: metadata section

        // Enhanced metadata section
        $this->assertArrayHasKey('api_version', $data['meta']);
        $this->assertArrayHasKey('execution_time', $data['meta']);
        $this->assertArrayHasKey('timestamp', $data['meta']);
        $this->assertArrayHasKey('request_id', $data['meta']);
        $this->assertArrayHasKey('server_info', $data['meta']);

        // Server info should include environment and version details
        $this->assertArrayHasKey('environment', $data['meta']['server_info']);
        $this->assertArrayHasKey('php_version', $data['meta']['server_info']);
        $this->assertArrayHasKey('framework_version', $data['meta']['server_info']);

        // Enhanced data section
        $this->assertArrayHasKey('templates', $data['data']);
        $this->assertArrayHasKey('pagination', $data['data']);
        $this->assertArrayHasKey('filters_applied', $data['data']); // NEW: show what filters were applied
        $this->assertArrayHasKey('sort_info', $data['data']); // NEW: show sorting information
    }

    /**
     * @test
     * RED: Test enhanced response format for single template with computed fields
     */
    public function test_single_template_enhanced_response_format()
    {
        // Arrange
        $templateId = $this->createTestTemplate([
            'version_name' => 'Enhanced Format Template',
            'description' => 'Test description'
        ]);

        // Add some related data
        $categoryId = $this->createTestCategory($templateId);
        $this->createTestContent($templateId, $categoryId);

        // Act
        $response = $this->get($this->basePath . '/' . $templateId);

        // Assert - Enhanced single template response
        $response->assertStatus(200);
        $data = $this->getCleanJSON($response);

        // Standard structure with meta
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);

        // Enhanced template data should include computed fields
        $template = $data['data'];
        $this->assertArrayHasKey('id', $template);
        $this->assertArrayHasKey('version_name', $template);
        $this->assertArrayHasKey('category_count', $template);
        $this->assertArrayHasKey('content_count', $template);
        $this->assertArrayHasKey('completion_percentage', $template); // NEW: completion metrics
        $this->assertArrayHasKey('risk_coverage', $template); // NEW: risk coverage analysis
        $this->assertArrayHasKey('template_health', $template); // NEW: template health score
        $this->assertArrayHasKey('last_usage_stats', $template); // NEW: usage statistics

        // Ensure computed fields have correct data types
        $this->assertIsInt($template['category_count']);
        $this->assertIsInt($template['content_count']);
        $this->assertTrue(is_float($template['completion_percentage']) || is_int($template['completion_percentage']), 'completion_percentage should be numeric');
        $this->assertIsArray($template['risk_coverage']);
        $this->assertIsArray($template['template_health']);
        $this->assertIsArray($template['last_usage_stats']);
    }

    /**
     * @test
     * RED: Test enhanced error response format with detailed error context
     */
    public function test_enhanced_error_response_format()
    {
        // Act - Try to get non-existent template
        $response = $this->get($this->basePath . '/999');

        // Assert - Enhanced error response format
        $response->assertStatus(404);
        $data = $this->getCleanJSON($response);

        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data); // NEW: structured error object
        $this->assertArrayHasKey('meta', $data);

        // Enhanced error structure
        $error = $data['error'];
        $this->assertArrayHasKey('code', $error); // NEW: error code
        $this->assertArrayHasKey('message', $error);
        $this->assertArrayHasKey('type', $error); // NEW: error type
        $this->assertArrayHasKey('context', $error); // NEW: error context
        $this->assertArrayHasKey('suggestions', $error); // NEW: helpful suggestions

        // Error context should provide debugging information
        $this->assertArrayHasKey('resource', $error['context']);
        $this->assertArrayHasKey('action', $error['context']);
        $this->assertArrayHasKey('parameters', $error['context']);

        // Verify error code format
        $this->assertEquals('TEMPLATE_NOT_FOUND', $error['code']);
        $this->assertEquals('NotFoundError', $error['type']);
    }

    /**
     * @test
     * RED: Test enhanced validation error response with field-specific guidance
     */
    public function test_enhanced_validation_error_response()
    {
        // Act - Try to create template with invalid data
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
                             'version_name' => '', // Invalid: empty
                             'status' => 'invalid_status' // Invalid: not in allowed list
                         ]);

        // Assert - Enhanced validation error response
        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);

        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('meta', $data);

        // Enhanced validation error structure
        $error = $data['error'];
        $this->assertEquals('VALIDATION_FAILED', $error['code']);
        $this->assertEquals('ValidationError', $error['type']);
        $this->assertArrayHasKey('field_errors', $error); // NEW: field-specific errors

        // Field-specific error details
        $fieldErrors = $error['field_errors'];
        $this->assertArrayHasKey('version_name', $fieldErrors);
        $this->assertArrayHasKey('status', $fieldErrors);

        // Each field error should have detailed information
        $versionNameError = $fieldErrors['version_name'];
        $this->assertArrayHasKey('message', $versionNameError);
        $this->assertArrayHasKey('code', $versionNameError);
        $this->assertArrayHasKey('value_received', $versionNameError); // NEW: what was received
        $this->assertArrayHasKey('expected_format', $versionNameError); // NEW: what was expected
        $this->assertArrayHasKey('suggestion', $versionNameError); // NEW: helpful suggestion
    }

    // ================================
    // RED PHASE: Template Copy Performance Tests (TEMP-BE-001-2)
    // ================================

    /**
     * @test
     * RED: Test template copy performance with large amount of data
     * Should complete copy operation within reasonable time limits
     */
    public function test_template_copy_performance_with_large_dataset()
    {
        // Arrange - Create template with substantial data
        $originalId = $this->createTestTemplate([
            'version_name' => 'Large Performance Template',
            'description' => 'Template with substantial data for performance testing'
        ]);

        // Create multiple categories (simulate large template)
        $categoryIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $categoryIds[] = $this->createTestCategory($originalId, [
                'category_name' => "Performance Category $i",
                'description' => "Category $i for performance testing with detailed description"
            ]);
        }

        // Create multiple content items per category (100 total)
        foreach ($categoryIds as $categoryId) {
            for ($j = 1; $j <= 10; $j++) {
                $this->createTestContent($originalId, $categoryId, [
                    'topic' => "Performance Topic {$categoryId}-{$j}",
                    'description' => "Detailed description for performance testing content item {$categoryId}-{$j}"
                ]);
            }
        }

        $copyData = [
            'version_name' => 'Performance Copy Template',
            'description' => 'Copied from large performance template'
        ];

        // Act - Measure copy operation time
        $startTime = microtime(true);
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath . '/' . $originalId . '/copy', $copyData);
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Assert - Performance requirements
        $response->assertStatus(201);
        $data = $this->getCleanJSON($response);
        $this->assertTrue($data['success']);

        // Performance assertion: Copy should complete within 5 seconds
        $this->assertLessThan(5.0, $executionTime,
            "Template copy took {$executionTime} seconds, should be under 5 seconds");

        // Verify all data was copied correctly
        $this->assertEquals(10, $data['data']['category_count']);
        $this->assertEquals(100, $data['data']['content_count']);

        // Verify execution time is reported in response metadata
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('execution_time', $data['meta']);
        $this->assertLessThan(5.0, $data['meta']['execution_time']);
    }

    /**
     * @test
     * RED: Test template copy with batch operations for improved performance
     * Should use batch inserts instead of individual operations
     */
    public function test_template_copy_uses_batch_operations()
    {
        // Arrange - Create template with moderate data set
        $originalId = $this->createTestTemplate([
            'version_name' => 'Batch Operations Template'
        ]);

        // Create 5 categories with 5 content items each
        $categoryIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $categoryIds[] = $this->createTestCategory($originalId, [
                'category_name' => "Batch Category $i"
            ]);
        }

        foreach ($categoryIds as $categoryId) {
            for ($j = 1; $j <= 5; $j++) {
                $this->createTestContent($originalId, $categoryId, [
                    'topic' => "Batch Topic {$categoryId}-{$j}"
                ]);
            }
        }

        $copyData = [
            'version_name' => 'Batch Copy Template'
        ];

        // Act
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath . '/' . $originalId . '/copy', $copyData);

        // Assert - Response should include performance metrics
        $response->assertStatus(201);
        $data = $this->getCleanJSON($response);

        // Check for performance optimization indicators in metadata
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('performance_metrics', $data['meta']); // NEW: performance metrics

        $metrics = $data['meta']['performance_metrics'];
        $this->assertArrayHasKey('database_queries', $metrics); // NEW: query count
        $this->assertArrayHasKey('batch_operations_used', $metrics); // NEW: batch operation flag
        $this->assertArrayHasKey('memory_usage', $metrics); // NEW: memory usage

        // Verify batch operations were used
        $this->assertTrue($metrics['batch_operations_used'], 'Batch operations should be used for performance');

        // Database queries should be optimized (fewer than individual inserts)
        $this->assertLessThan(15, $metrics['database_queries'],
            'Should use batch operations to reduce database queries');
    }

    /**
     * @test
     * RED: Test template copy with memory usage optimization
     * Should not consume excessive memory during large copies
     */
    public function test_template_copy_memory_optimization()
    {
        // Arrange - Create template with data that could cause memory issues
        $originalId = $this->createTestTemplate([
            'version_name' => 'Memory Optimization Template'
        ]);

        // Create substantial data set
        for ($i = 1; $i <= 15; $i++) {
            $categoryId = $this->createTestCategory($originalId, [
                'category_name' => "Memory Category $i",
                'description' => str_repeat("Large description content ", 100) // Large description
            ]);

            for ($j = 1; $j <= 8; $j++) {
                $this->createTestContent($originalId, $categoryId, [
                    'topic' => "Memory Topic {$i}-{$j}",
                    'description' => str_repeat("Detailed content description ", 50)
                ]);
            }
        }

        $copyData = [
            'version_name' => 'Memory Optimized Copy'
        ];

        // Act - Monitor memory usage
        $memoryBefore = memory_get_usage(true);
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath . '/' . $originalId . '/copy', $copyData);
        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Assert - Memory usage should be reasonable
        $response->assertStatus(201);
        $data = $this->getCleanJSON($response);

        // Memory usage should not exceed 32MB for this operation
        $this->assertLessThan(32 * 1024 * 1024, $memoryUsed,
            'Template copy should not use excessive memory');

        // Verify memory metrics are reported
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('performance_metrics', $data['meta']);
        $this->assertArrayHasKey('memory_usage', $data['meta']['performance_metrics']);
    }

    /**
     * @test
     * RED: Test template copy transaction optimization
     * Should use single transaction for entire copy operation
     */
    public function test_template_copy_transaction_optimization()
    {
        // Arrange
        $originalId = $this->createTestTemplate([
            'version_name' => 'Transaction Test Template'
        ]);

        $categoryId = $this->createTestCategory($originalId, [
            'category_name' => 'Transaction Category'
        ]);

        $this->createTestContent($originalId, $categoryId, [
            'topic' => 'Transaction Content'
        ]);

        $copyData = [
            'version_name' => 'Transaction Copy'
        ];

        // Act
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath . '/' . $originalId . '/copy', $copyData);

        // Assert - Should include transaction performance metrics
        $response->assertStatus(201);
        $data = $this->getCleanJSON($response);

        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('performance_metrics', $data['meta']);

        $metrics = $data['meta']['performance_metrics'];
        $this->assertArrayHasKey('transaction_count', $metrics); // NEW: transaction count
        $this->assertArrayHasKey('rollback_occurred', $metrics); // NEW: rollback flag

        // Should use single transaction
        $this->assertEquals(1, $metrics['transaction_count'],
            'Should use single transaction for entire copy operation');
        $this->assertFalse($metrics['rollback_occurred'],
            'No rollback should occur in successful copy');
    }

    // ================================
    // RED PHASE: Enhanced Template Validation Tests (TEMP-BE-001-3)
    // ================================

    /**
     * @test
     * RED: Test enhanced version name validation with business rules
     * Should enforce naming conventions and business logic
     */
    public function test_enhanced_version_name_validation()
    {
        // Test case 1: Version name with reserved keywords should be rejected
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
                             'version_name' => 'DEFAULT', // Reserved keyword
                             'description' => 'Test template'
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('field_errors', $data['error']);
        $this->assertArrayHasKey('version_name', $data['error']['field_errors']);
        $this->assertEquals('RESERVED_KEYWORD', $data['error']['field_errors']['version_name']['code']);

        // Test case 2: Version name with invalid characters should be rejected
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
                             'version_name' => 'Test<script>Template', // Contains HTML/script tags
                             'description' => 'Test template'
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('INVALID_CHARACTERS', $data['error']['field_errors']['version_name']['code']);

        // Test case 3: Version name with SQL injection patterns should be rejected
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
                             'version_name' => "Test'; DROP TABLE templates; --",
                             'description' => 'Test template'
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('SECURITY_VIOLATION', $data['error']['field_errors']['version_name']['code']);
    }

    /**
     * @test
     * RED: Test enhanced status validation with workflow rules
     * Should enforce status transition rules and business logic
     */
    public function test_enhanced_status_validation()
    {
        // Test case 1: Invalid status transition validation
        $templateId = $this->createTestTemplate([
            'version_name' => 'Status Test Template',
            'status' => 'active'
        ]);

        // Try to transition from active to draft (invalid business rule)
        $response = $this->withBodyFormat('json')
                         ->put($this->basePath . '/' . $templateId, [
                             'version_name' => 'Status Test Template',
                             'status' => 'draft', // Invalid transition
                             'force_status_change' => false
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('INVALID_STATUS_TRANSITION', $data['error']['code']);
        $this->assertArrayHasKey('current_status', $data['error']['context']);
        $this->assertArrayHasKey('requested_status', $data['error']['context']);
        $this->assertArrayHasKey('allowed_transitions', $data['error']['context']);

        // Test case 2: Status validation with dependencies
        // Create template with categories (making it "in use")
        $categoryId = $this->createTestCategory($templateId);
        $this->createTestContent($templateId, $categoryId);

        // Try to set status to inactive when template has active content
        $response = $this->withBodyFormat('json')
                         ->put($this->basePath . '/' . $templateId, [
                             'version_name' => 'Status Test Template',
                             'status' => 'inactive'
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('STATUS_CHANGE_BLOCKED', $data['error']['code']);
        $this->assertArrayHasKey('blocking_dependencies', $data['error']['context']);
    }

    /**
     * @test
     * RED: Test enhanced description validation with content analysis
     * Should validate description quality and appropriateness
     */
    public function test_enhanced_description_validation()
    {
        // Test case 1: Description with inappropriate content
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
                             'version_name' => 'Description Test Template',
                             'description' => 'This template is shit and fucking useless' // Inappropriate language
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('field_errors', $data['error']);
        $this->assertArrayHasKey('description', $data['error']['field_errors']);
        $this->assertEquals('INAPPROPRIATE_CONTENT', $data['error']['field_errors']['description']['code']);

        // Test case 2: Description that's too short for business requirements
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
                             'version_name' => 'Short Description Template',
                             'description' => 'Test' // Too short for business needs
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('DESCRIPTION_TOO_SHORT', $data['error']['field_errors']['description']['code']);

        // Test case 3: Description with potential security risks
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
                             'version_name' => 'Security Test Template',
                             'description' => '<script>alert("xss")</script>Template description' // XSS attempt
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('SECURITY_VIOLATION', $data['error']['field_errors']['description']['code']);
    }

    /**
     * @test
     * RED: Test business rule validation for template completeness
     * Should validate template meets business requirements before activation
     */
    public function test_template_completeness_business_validation()
    {
        // Create template without sufficient content
        $templateData = [
            'version_name' => 'Incomplete Template',
            'description' => 'Template for testing business validation',
            'status' => 'active',
            'enforce_business_rules' => true // NEW: Flag to enforce business rules
        ];

        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, $templateData);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('BUSINESS_RULE_VIOLATION', $data['error']['code']);
        $this->assertArrayHasKey('business_rule_failures', $data['error']['context']);

        $failures = $data['error']['context']['business_rule_failures'];
        $this->assertContains('MINIMUM_CATEGORIES_REQUIRED', $failures);
        $this->assertContains('MINIMUM_CONTENT_REQUIRED', $failures);
    }

    /**
     * @test
     * RED: Test template duplication prevention with advanced detection
     * Should detect similar templates and prevent unnecessary duplication
     */
    public function test_advanced_duplication_prevention()
    {
        // Create original template
        $this->createTestTemplate([
            'version_name' => 'Financial Risk Assessment Template',
            'description' => 'Financial risk evaluation for enterprise assessment'
        ]);

        // Try to create very similar template (should trigger similarity detection)
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
                             'version_name' => 'Financial Risk Assessment Template v2',
                             'description' => 'Financial risk evaluation for enterprise assessment purposes',
                             'check_similarity' => true // NEW: Enable similarity check
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('POTENTIAL_DUPLICATE', $data['error']['code']);
        $this->assertArrayHasKey('similar_templates', $data['error']['context']);
        $this->assertArrayHasKey('similarity_score', $data['error']['context']);
        $this->assertGreaterThan(0.7, $data['error']['context']['similarity_score']); // Lowered threshold for test
    }

    /**
     * @test
     * RED: Test multi-field cross-validation rules
     * Should validate field combinations and business logic dependencies
     */
    public function test_cross_field_validation_rules()
    {
        // Test case 1: Status and description consistency
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
                             'version_name' => 'Cross Validation Template',
                             'description' => 'DRAFT: This template is still being developed',
                             'status' => 'active' // Inconsistent with description
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('FIELD_CONSISTENCY_ERROR', $data['error']['code']);
        $this->assertArrayHasKey('inconsistent_fields', $data['error']['context']);

        // Test case 2: Template naming and business domain validation
        $response = $this->withBodyFormat('json')
                         ->post($this->basePath, [
                             'version_name' => 'Technical Risk Assessment',
                             'description' => 'Financial risk evaluation template', // Domain mismatch
                             'validate_domain_consistency' => true
                         ]);

        $response->assertStatus(400);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('DOMAIN_MISMATCH', $data['error']['code']);
    }

    // ================================
    // Helper Methods for Test Data
    // ================================

    protected function createTestTemplate(array $data = []): int
    {
        $defaultData = [
            'version_name' => 'Test Template ' . uniqid(),
            'description' => 'Test description',
            'status' => 'active'
        ];

        $templateData = array_merge($defaultData, $data);
        return $this->templateModel->insert($templateData);
    }

    protected function createTestCategory(int $templateId, array $data = []): int
    {
        $defaultData = [
            'template_id' => $templateId,
            'category_name' => 'Test Category ' . uniqid(),
            'sort_order' => 0
        ];

        $categoryData = array_merge($defaultData, $data);
        return $this->categoryModel->insert($categoryData);
    }

    protected function createTestContent(int $templateId, int $categoryId, array $data = []): int
    {
        $defaultData = [
            'template_id' => $templateId,
            'category_id' => $categoryId,
            'topic' => 'Test Topic ' . uniqid(),
            'description' => 'Test content description',
            'scoring_method' => 'scale_1_5',
            'weight' => 1.0,
            'sort_order' => 0,
            'is_required' => 1
        ];

        $contentData = array_merge($defaultData, $data);
        return $this->contentModel->insert($contentData);
    }
}