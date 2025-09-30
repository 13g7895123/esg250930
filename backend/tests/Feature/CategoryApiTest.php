<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\RiskAssessment\RiskAssessmentTemplateModel;
use App\Models\RiskAssessment\RiskCategoryModel;

/**
 * @internal
 * 
 * TDD Test Suite for Risk Category Management API
 * Following Red-Green-Refactor cycle
 */
final class CategoryApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $seed = '';
    protected $templateId;
    protected $basePath;
    
    public function setUp(): void
    {
        parent::setUp();
        
        $this->regressDatabase();
        
        $this->templateModel = new RiskAssessmentTemplateModel();
        $this->categoryModel = new RiskCategoryModel();
        
        // Clear database and create a template for testing categories
        $this->clearDatabase();
        $this->templateId = $this->createTestTemplate();
        $this->basePath = "api/v1/risk-assessment/templates/{$this->templateId}/categories";
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
     * Clear database before tests
     */
    protected function clearDatabase()
    {
        // Clear all template-related tables in correct order
        $this->db->table('template_contents')->truncate();
        $this->db->table('risk_categories')->truncate();  
        $this->db->table('risk_assessment_templates')->truncate();
    }

    // ================================
    // RED PHASE: Category List Tests
    // ================================

    /**
     * @test
     * RED: Test getting categories for template with no categories
     */
    public function test_get_categories_empty()
    {
        // Act
        $response = $this->get($this->basePath);

        // Assert
        $response->assertStatus(200);
        $response->assertJSONFragment([
            'success' => true,
            'data' => [
                'categories' => []
            ]
        ]);
    }

    /**
     * @test
     * RED: Test getting categories with data
     */
    public function test_get_categories_with_data()
    {
        // Arrange
        $this->createTestCategory($this->templateId, [
            'category_name' => 'Financial Risk',
            'category_code' => 'FIN',
            'sort_order' => 1
        ]);
        
        $this->createTestCategory($this->templateId, [
            'category_name' => 'Operational Risk', 
            'category_code' => 'OPS',
            'sort_order' => 2
        ]);

        // Act
        $response = $this->get($this->basePath);

        // Assert
        $response->assertStatus(200);
        $data = $this->getCleanJSON($response);
        $this->assertTrue($data['success']);
        $this->assertCount(2, $data['data']['categories']);
        
        // Should be ordered by sort_order
        $this->assertEquals('Financial Risk', $data['data']['categories'][0]['category_name']);
        $this->assertEquals('Operational Risk', $data['data']['categories'][1]['category_name']);
    }

    /**
     * @test
     * RED: Test getting categories for non-existent template
     */
    public function test_get_categories_non_existent_template()
    {
        // Act
        $response = $this->get('api/v1/risk-assessment/templates/999/categories');

        // Assert
        $response->assertStatus(404);
        $response->assertJSONFragment([
            'success' => false,
            'message' => '範本不存在'
        ]);
    }

    // ================================
    // RED PHASE: Category Create Tests
    // ================================

    /**
     * @test
     * RED: Test creating category with all fields
     */
    public function test_create_category_all_fields()
    {
        // Arrange
        $categoryData = [
            'category_name' => 'Market Risk',
            'category_code' => 'MKT', 
            'description' => 'Market-related risk factors',
            'sort_order' => 5
        ];

        // Act
        $response = $this->post($this->basePath, $categoryData);

        // Assert
        $response->assertStatus(201);
        $data = $this->getCleanJSON($response);
        $this->assertTrue($data['success']);
        $this->assertEquals('Market Risk', $data['data']['category_name']);
        $this->assertEquals('MKT', $data['data']['category_code']);
        $this->assertEquals('Market-related risk factors', $data['data']['description']);
        $this->assertEquals(5, $data['data']['sort_order']);
        $this->assertEquals($this->templateId, $data['data']['template_id']);
        
        // Verify in database
        $this->seeInDatabase('risk_categories', [
            'category_name' => 'Market Risk',
            'template_id' => $this->templateId
        ]);
    }

    /**
     * @test
     * RED: Test creating category with required fields only
     */
    public function test_create_category_required_fields_only()
    {
        // Arrange
        $categoryData = [
            'category_name' => 'Legal Risk'
        ];

        // Act
        $response = $this->post($this->basePath, $categoryData);

        // Assert
        $response->assertStatus(201);
        $data = $this->getCleanJSON($response);
        $this->assertEquals('Legal Risk', $data['data']['category_name']);
        $this->assertEquals(0, $data['data']['sort_order']); // Default
    }

    /**
     * @test
     * RED: Test creating category validation - missing category_name
     */
    public function test_create_category_missing_name()
    {
        // Act
        $response = $this->post($this->basePath, []);

        // Assert
        $response->assertStatus(422);
        $data = $this->getCleanJSON($response);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('category_name', $data['errors']);
    }

    /**
     * @test
     * RED: Test creating category for non-existent template
     */
    public function test_create_category_non_existent_template()
    {
        // Act
        $response = $this->post('api/v1/risk-assessment/templates/999/categories', [
            'category_name' => 'Test Category'
        ]);

        // Assert
        $response->assertStatus(404);
        $response->assertJSONFragment([
            'success' => false,
            'message' => '範本不存在'
        ]);
    }

    // ================================
    // RED PHASE: Category Update Tests
    // ================================

    /**
     * @test
     * RED: Test updating category
     */
    public function test_update_category()
    {
        // Arrange
        $categoryId = $this->createTestCategory($this->templateId, [
            'category_name' => 'Original Category',
            'category_code' => 'ORIG'
        ]);

        $updateData = [
            'category_name' => 'Updated Category',
            'category_code' => 'UPD',
            'description' => 'Updated description'
        ];

        // Act
        $response = $this->put($this->basePath . '/' . $categoryId, $updateData);

        // Assert
        $data = $this->getCleanJSON($response);
        $this->assertTrue($data['success']);
        $this->assertEquals('Updated Category', $data['data']['category_name']);
        $this->assertEquals('UPD', $data['data']['category_code']);
        $this->assertEquals('Updated description', $data['data']['description']);
    }

    /**
     * @test
     * RED: Test updating non-existent category
     */
    public function test_update_non_existent_category()
    {
        // Act
        $response = $this->put($this->basePath . '/999', [
            'category_name' => 'Update Attempt'
        ]);

        // Assert
        $response->assertStatus(404);
        $response->assertJSONFragment([
            'success' => false,
            'message' => '分類不存在'
        ]);
    }

    // ================================
    // RED PHASE: Category Delete Tests
    // ================================

    /**
     * @test
     * RED: Test deleting category
     */
    public function test_delete_category()
    {
        // Arrange
        $categoryId = $this->createTestCategory($this->templateId, [
            'category_name' => 'Category to Delete'
        ]);

        // Act
        $response = $this->delete($this->basePath . '/' . $categoryId);

        // Assert
        $response->assertStatus(200);
        $response->assertJSONFragment([
            'success' => true,
            'message' => '風險分類刪除成功'
        ]);

        // Verify hard delete - row should not exist
        $this->dontSeeInDatabase('risk_categories', [
            'id' => $categoryId
        ]);
    }

    /**
     * @test
     * RED: Test deleting non-existent category
     */
    public function test_delete_non_existent_category()
    {
        // Act
        $response = $this->delete($this->basePath . '/999');

        // Assert
        $response->assertStatus(404);
        $response->assertJSONFragment([
            'success' => false,
            'message' => '分類不存在'
        ]);
    }

    // ================================
    // Helper Methods
    // ================================

    protected function createTestTemplate(): int
    {
        return $this->templateModel->insert([
            'version_name' => 'Test Template for Categories ' . uniqid(),
            'description' => 'Test description',
            'status' => 'active'
        ]);
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
}