<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use CodeIgniter\RESTful\ResourceController;

class TestController extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        // Simple test response without any models or database calls
        return $this->respond([
            'success' => true,
            'message' => 'Test endpoint working',
            'data' => []
        ]);
    }
}