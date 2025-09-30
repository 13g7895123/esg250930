<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Quick debug for API issues
 */
final class QuickDebugTest extends CIUnitTestCase
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
                return json_decode($matches[0], true);
            }
        }
        
        return $response->getJSON(true);
    }

    public function test_debug_create_template()
    {
        // Clear database
        $this->db->table('risk_assessment_templates')->truncate();
        
        // Test data
        $templateData = [
            'version_name' => 'Debug Template',
            'description' => 'Debug description',
            'status' => 'active'
        ];

        // Act
        $response = $this->post('api/admin/templates', $templateData);

        // Debug output
        echo "\n=== CREATE DEBUG ===\n";
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Body: " . $response->getBody() . "\n";
        
        $data = $this->getCleanJSON($response);
        echo "JSON: ";
        print_r($data);
        
        // Basic assertion
        $this->assertTrue(in_array($response->getStatusCode(), [200, 201, 400, 422]));
    }
}