<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Debug test to inspect actual API responses
 */
final class DebugApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $seed = '';
    
    public function setUp(): void
    {
        parent::setUp();
        $this->regressDatabase();
    }

    public function test_debug_empty_templates_response()
    {
        // Act
        $response = $this->get('api/admin/templates');

        // Debug output
        echo "\n=== DEBUG: Response Status ===\n";
        echo "Status: " . $response->getStatusCode() . "\n";
        
        echo "\n=== DEBUG: Raw Response ===\n";
        echo $response->getBody() . "\n";
        
        echo "\n=== DEBUG: JSON Decoded ===\n";
        $data = $response->getJSON(true);
        print_r($data);
        
        // Simple assertion
        $this->assertEquals(200, $response->getStatusCode());
    }
}