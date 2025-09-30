<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\RiskAssessmentTemplateModel;
use App\Models\RiskCategoryModel;

final class CategoryUpdateDeleteDebugTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $seed = '';
    
    public function setUp(): void
    {
        parent::setUp();
        $this->regressDatabase();
    }

    protected function getCleanJSON($response)
    {
        $body = $response->getBody();
        
        if (strpos($body, '<html>') !== false) {
            preg_match('/\{.*\}/s', $body, $matches);
            if (!empty($matches[0])) {
                $jsonString = html_entity_decode($matches[0], ENT_QUOTES, 'UTF-8');
                return json_decode($jsonString, true);
            }
        }
        
        return $response->getJSON(true);
    }
    
    protected function clearDatabase()
    {
        $this->db->table('template_contents')->truncate();
        $this->db->table('risk_categories')->truncate();  
        $this->db->table('risk_assessment_templates')->truncate();
    }

    public function test_debug_category_update()
    {
        // Setup
        $this->clearDatabase();
        
        $templateModel = new RiskAssessmentTemplateModel();
        $templateId = $templateModel->insert([
            'version_name' => 'Test Template',
            'status' => 'active'
        ]);
        
        $categoryModel = new RiskCategoryModel();
        $categoryId = $categoryModel->insert([
            'template_id' => $templateId,
            'category_name' => 'Original Category',
            'category_code' => 'ORIG'
        ]);

        $updateData = [
            'category_name' => 'Updated Category',
            'category_code' => 'UPD',
            'description' => 'Updated description'
        ];

        echo "\n=== UPDATE DEBUG ===\n";
        echo "Category ID: $categoryId\n";
        echo "Template ID: $templateId\n";

        // Act
        $response = $this->put("api/admin/templates/$templateId/categories/$categoryId", $updateData);

        // Debug output
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Body: " . $response->getBody() . "\n";
        
        $data = $this->getCleanJSON($response);
        echo "JSON: ";
        print_r($data);
        
        $this->assertTrue(true); // Just to pass
    }

    public function test_debug_category_delete()
    {
        // Setup
        $this->clearDatabase();
        
        $templateModel = new RiskAssessmentTemplateModel();
        $templateId = $templateModel->insert([
            'version_name' => 'Test Template',
            'status' => 'active'
        ]);
        
        $categoryModel = new RiskCategoryModel();
        $categoryId = $categoryModel->insert([
            'template_id' => $templateId,
            'category_name' => 'Category to Delete'
        ]);

        echo "\n=== DELETE DEBUG ===\n";
        echo "Category ID: $categoryId\n";
        echo "Template ID: $templateId\n";

        // Act
        $response = $this->delete("api/admin/templates/$templateId/categories/$categoryId");

        // Debug output
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Body: " . $response->getBody() . "\n";
        
        $data = $this->getCleanJSON($response);
        echo "JSON: ";
        print_r($data);
        
        $this->assertTrue(true); // Just to pass
    }
}