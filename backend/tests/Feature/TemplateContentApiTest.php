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
 * TDD Test Suite for Template Content Management API
 * Following Red-Green-Refactor cycle
 */
final class TemplateContentApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $seed = '';
    protected $templateId;
    protected $categoryId;
    protected $basePath;
    
    public function setUp(): void
    {
        parent::setUp();
        
        $this->regressDatabase();
        
        $this->templateModel = new RiskAssessmentTemplateModel();
        $this->categoryModel = new RiskCategoryModel();
        $this->contentModel = new TemplateContentModel();
        
        // Create template and category for testing
        $this->templateId = $this->createTestTemplate();
        $this->categoryId = $this->createTestCategory($this->templateId);
        $this->basePath = "api/v1/risk-assessment/templates/{$this->templateId}/contents";
    }

    // ================================
    // RED PHASE: Content List Tests
    // ================================

    /**
     * @test
     * RED: Test getting content for template with no content
     */
    public function test_get_contents_empty()
    {
        // Act
        $response = $this->get($this->basePath);

        // Assert
        $response->assertStatus(200);
        $response->assertJSONFragment([
            'success' => true,
            'data' => [
                'contents' => []
            ]
        ]);
    }

    /**
     * @test
     * RED: Test getting contents with data
     */
    public function test_get_contents_with_data()
    {
        // Arrange
        $this->createTestContent($this->templateId, $this->categoryId, [
            'topic' => 'Cash Flow Management',
            'description' => 'Assess cash flow risks',
            'scoring_method' => 'scale_1_5',
            'sort_order' => 1
        ]);
        
        $this->createTestContent($this->templateId, $this->categoryId, [
            'topic' => 'Credit Risk Assessment',
            'description' => 'Evaluate credit exposure',
            'scoring_method' => 'scale_1_10',
            'sort_order' => 2
        ]);

        // Act
        $response = $this->get($this->basePath);

        // Assert
        $response->assertStatus(200);
        $data = $response->getJSON(true);
        $this->assertTrue($data['success']);
        $this->assertCount(2, $data['data']['contents']);
        
        // Should be ordered by sort_order
        $this->assertEquals('Cash Flow Management', $data['data']['contents'][0]['topic']);
        $this->assertEquals('Credit Risk Assessment', $data['data']['contents'][1]['topic']);
    }

    /**
     * @test
     * RED: Test getting single content item
     */
    public function test_get_single_content()
    {
        // Arrange
        $contentId = $this->createTestContent($this->templateId, $this->categoryId, [
            'topic' => 'Detailed Content',
            'description' => 'Detailed description',
            'scoring_method' => 'binary',
            'weight' => 2.5,
            'is_required' => true
        ]);

        // Act
        $response = $this->get($this->basePath . '/' . $contentId);

        // Assert
        $response->assertStatus(200);
        $data = $response->getJSON(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Detailed Content', $data['data']['topic']);
        $this->assertEquals('binary', $data['data']['scoring_method']);
        $this->assertEquals(2.5, $data['data']['weight']);
        $this->assertTrue($data['data']['is_required']);
    }

    /**
     * @test
     * RED: Test getting content for non-existent template
     */
    public function test_get_contents_non_existent_template()
    {
        // Act
        $response = $this->get('api/v1/risk-assessment/templates/999/contents');

        // Assert
        $response->assertStatus(404);
        $response->assertJSONFragment([
            'success' => false,
            'message' => '範本不存在'
        ]);
    }

    // ================================
    // RED PHASE: Content Create Tests
    // ================================

    /**
     * @test
     * RED: Test creating content with all fields
     */
    public function test_create_content_all_fields()
    {
        // Arrange
        $contentData = [
            'category_id' => $this->categoryId,
            'topic' => 'Market Risk Analysis',
            'description' => 'Comprehensive market risk evaluation',
            'scoring_method' => 'percentage',
            'weight' => 3.0,
            'sort_order' => 10,
            'is_required' => false
        ];

        // Act
        $response = $this->post($this->basePath, $contentData);

        // Assert
        $response->assertStatus(201);
        $data = $response->getJSON(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Market Risk Analysis', $data['data']['topic']);
        $this->assertEquals($this->categoryId, $data['data']['category_id']);
        $this->assertEquals('percentage', $data['data']['scoring_method']);
        $this->assertEquals(3.0, $data['data']['weight']);
        $this->assertEquals(10, $data['data']['sort_order']);
        $this->assertFalse($data['data']['is_required']);
        
        // Verify in database
        $this->seeInDatabase('template_contents', [
            'topic' => 'Market Risk Analysis',
            'template_id' => $this->templateId
        ]);
    }

    /**
     * @test
     * RED: Test creating content with required fields only
     */
    public function test_create_content_required_fields_only()
    {
        // Arrange
        $contentData = [
            'topic' => 'Basic Risk Item',
            'description' => 'Basic description'
        ];

        // Act
        $response = $this->post($this->basePath, $contentData);

        // Assert
        $response->assertStatus(201);
        $data = $response->getJSON(true);
        $this->assertEquals('Basic Risk Item', $data['data']['topic']);
        $this->assertEquals('scale_1_5', $data['data']['scoring_method']); // Default
        $this->assertEquals(1.0, $data['data']['weight']); // Default
        $this->assertTrue($data['data']['is_required']); // Default
    }

    /**
     * @test
     * RED: Test creating content validation - missing topic
     */
    public function test_create_content_missing_topic()
    {
        // Act
        $response = $this->post($this->basePath, [
            'description' => 'Missing topic test'
        ]);

        // Assert
        $response->assertStatus(422);
        $data = $response->getJSON(true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('topic', $data['errors']);
    }

    /**
     * @test
     * RED: Test creating content validation - invalid scoring method
     */
    public function test_create_content_invalid_scoring_method()
    {
        // Act
        $response = $this->post($this->basePath, [
            'topic' => 'Test Topic',
            'description' => 'Test description',
            'scoring_method' => 'invalid_method'
        ]);

        // Assert
        $response->assertStatus(422);
        $data = $response->getJSON(true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('scoring_method', $data['errors']);
    }

    /**
     * @test
     * RED: Test creating content with invalid category_id
     */
    public function test_create_content_invalid_category()
    {
        // Act
        $response = $this->post($this->basePath, [
            'category_id' => 999,
            'topic' => 'Test Topic',
            'description' => 'Test description'
        ]);

        // Assert
        $response->assertStatus(422);
        $data = $response->getJSON(true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('category_id', $data['errors']);
    }

    // ================================
    // RED PHASE: Content Update Tests
    // ================================

    /**
     * @test
     * RED: Test updating content
     */
    public function test_update_content()
    {
        // Arrange
        $contentId = $this->createTestContent($this->templateId, $this->categoryId, [
            'topic' => 'Original Topic',
            'description' => 'Original description',
            'scoring_method' => 'scale_1_5'
        ]);

        $updateData = [
            'topic' => 'Updated Topic',
            'description' => 'Updated description',
            'scoring_method' => 'scale_1_10',
            'weight' => 2.0
        ];

        // Act
        $response = $this->put($this->basePath . '/' . $contentId, $updateData);

        // Assert
        $response->assertStatus(200);
        $data = $response->getJSON(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Updated Topic', $data['data']['topic']);
        $this->assertEquals('Updated description', $data['data']['description']);
        $this->assertEquals('scale_1_10', $data['data']['scoring_method']);
        $this->assertEquals(2.0, $data['data']['weight']);
    }

    /**
     * @test
     * RED: Test updating non-existent content
     */
    public function test_update_non_existent_content()
    {
        // Act
        $response = $this->put($this->basePath . '/999', [
            'topic' => 'Update Attempt'
        ]);

        // Assert
        $response->assertStatus(404);
        $response->assertJSONFragment([
            'success' => false,
            'message' => '內容不存在'
        ]);
    }

    // ================================
    // RED PHASE: Content Delete Tests
    // ================================

    /**
     * @test
     * RED: Test deleting content
     */
    public function test_delete_content()
    {
        // Arrange
        $contentId = $this->createTestContent($this->templateId, $this->categoryId, [
            'topic' => 'Content to Delete'
        ]);

        // Act
        $response = $this->delete($this->basePath . '/' . $contentId);

        // Assert
        $response->assertStatus(200);
        $response->assertJSONFragment([
            'success' => true,
            'message' => '內容已刪除'
        ]);

        // Verify hard delete
        $this->dontSeeInDatabase('template_contents', [
            'id' => $contentId
        ]);
    }

    // ================================
    // RED PHASE: Content Reorder Tests
    // ================================

    /**
     * @test
     * RED: Test reordering content items
     */
    public function test_reorder_contents()
    {
        // Arrange - Create 3 content items
        $content1Id = $this->createTestContent($this->templateId, $this->categoryId, [
            'topic' => 'Content 1',
            'sort_order' => 1
        ]);
        
        $content2Id = $this->createTestContent($this->templateId, $this->categoryId, [
            'topic' => 'Content 2',
            'sort_order' => 2
        ]);
        
        $content3Id = $this->createTestContent($this->templateId, $this->categoryId, [
            'topic' => 'Content 3',
            'sort_order' => 3
        ]);

        // Reorder data - reverse the order
        $reorderData = [
            'content_orders' => [
                ['id' => $content3Id, 'sort_order' => 1],
                ['id' => $content2Id, 'sort_order' => 2],
                ['id' => $content1Id, 'sort_order' => 3]
            ]
        ];

        // Act
        $response = $this->put($this->basePath . '/reorder', $reorderData);

        // Assert
        $response->assertStatus(200);
        $response->assertJSONFragment([
            'success' => true,
            'message' => '內容順序已更新'
        ]);

        // Verify new order in database
        $this->seeInDatabase('template_contents', [
            'id' => $content3Id,
            'sort_order' => 1
        ]);
        
        $this->seeInDatabase('template_contents', [
            'id' => $content1Id,
            'sort_order' => 3
        ]);
    }

    /**
     * @test
     * RED: Test reordering with invalid content IDs
     */
    public function test_reorder_contents_invalid_ids()
    {
        // Act
        $response = $this->put($this->basePath . '/reorder', [
            'content_orders' => [
                ['id' => 999, 'sort_order' => 1]
            ]
        ]);

        // Assert
        $response->assertStatus(422);
        $data = $response->getJSON(true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('無效的內容項目', $data['message']);
    }

    // ================================
    // Helper Methods
    // ================================

    protected function createTestTemplate(): int
    {
        return $this->templateModel->insert([
            'version_name' => 'Test Template for Content ' . uniqid(),
            'description' => 'Test description',
            'status' => 'active'
        ]);
    }

    protected function createTestCategory(int $templateId): int
    {
        return $this->categoryModel->insert([
            'template_id' => $templateId,
            'category_name' => 'Test Category ' . uniqid(),
            'sort_order' => 0
        ]);
    }

    protected function createTestContent(int $templateId, int $categoryId, array $data = []): int
    {
        $defaultData = [
            'template_id' => $templateId,
            'category_id' => $categoryId,
            'topic' => 'Test Topic ' . uniqid(),
            'description' => 'Test description',
            'scoring_method' => 'scale_1_5',
            'weight' => 1.0,
            'sort_order' => 0,
            'is_required' => true
        ];

        $contentData = array_merge($defaultData, $data);
        return $this->contentModel->insert($contentData);
    }
}