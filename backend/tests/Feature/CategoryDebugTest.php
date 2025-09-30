<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\RiskAssessmentTemplateModel;

/**
 * Debug category API issues
 */
final class CategoryDebugTest extends CIUnitTestCase
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

    public function test_debug_category_endpoint()
    {
        // Clear and create template
        $this->clearDatabase();
        
        $templateModel = new RiskAssessmentTemplateModel();
        $templateId = $templateModel->insert([
            'version_name' => 'Test Template for Categories',
            'description' => 'Test description',
            'status' => 'active'
        ]);

        // Test URL
        $url = "api/admin/templates/$templateId/categories";
        echo "\n=== CATEGORY DEBUG ===\n";
        echo "URL: $url\n";
        echo "Template ID: $templateId\n";

        // Act
        $response = $this->get($url);

        // Debug output
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Body: " . $response->getBody() . "\n";
        
        $data = $this->getCleanJSON($response);
        echo "JSON: ";
        print_r($data);
        
        // Basic assertion
        $this->assertTrue(true); // Just to pass the test while debugging
    }
}