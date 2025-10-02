<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API Routes
$routes->group('api', function($routes) {
    $routes->resource('companies', ['controller' => 'Api\Companies']);
    
    // NAS Integration Routes (No authentication required)
    $routes->group('nas', function($routes) {
        $routes->get('sync', 'Api\NasIntegration::sync');
        $routes->get('folders', 'Api\NasIntegration::folders');
        $routes->get('files', 'Api\NasIntegration::files');
        $routes->get('test', 'Api\NasIntegration::test');
        $routes->get('test-batch', 'Api\NasIntegration::testBatch');
        $routes->get('clear-db', 'Api\NasIntegration::clearDatabase');
        $routes->get('list-downloaded', 'Api\NasIntegration::listDownloaded');
        $routes->get('test-upload', 'Api\NasIntegration::testUpload');
    });
});

// Admin Template API Routes  
$routes->group('admin/api/template', function($routes) {
    $routes->post('upload_file', 'Api\\TemplateController::uploadFile');
    $routes->post('process_indicator_data', 'Api\\TemplateController::processIndicatorData');
});

// Admin Risk Assessment Template Management API Routes (Legacy - keep for backward compatibility)
$routes->group('api/admin', function($routes) {
    // Template Categories routes - Put these BEFORE resource routes to avoid conflicts
    $routes->get('templates/(:num)/categories', 'Admin\CategoryController::index/$1');
    $routes->post('templates/(:num)/categories', 'Admin\CategoryController::create/$1');
    $routes->put('templates/(:num)/categories/(:num)', 'Admin\CategoryController::update/$1/$2');
    $routes->delete('templates/(:num)/categories/(:num)', 'Admin\CategoryController::delete/$1/$2');
    
    // Template Contents routes - Put these BEFORE resource routes to avoid conflicts
    $routes->get('templates/(:num)/contents', 'Admin\TemplateContentController::index/$1');
    $routes->get('templates/(:num)/contents/(:num)', 'Admin\TemplateContentController::show/$1/$2');
    $routes->post('templates/(:num)/contents', 'Admin\TemplateContentController::create/$1');
    $routes->put('templates/(:num)/contents/(:num)', 'Admin\TemplateContentController::update/$1/$2');
    $routes->delete('templates/(:num)/contents/(:num)', 'Admin\TemplateContentController::delete/$1/$2');
    $routes->put('templates/(:num)/contents/reorder', 'Admin\TemplateContentController::reorder/$1');
    
    // Template copy route - Put this before resource routes 
    $routes->post('templates/(:num)/copy', 'Admin\TemplateController::copy/$1');
    
    // Template CRUD routes - Put resource routes LAST to avoid conflicts
    $routes->resource('templates', ['controller' => 'Admin\TemplateController']);
});

// V1 Risk Assessment API Routes (New Structure)
$routes->group('api/v1/risk-assessment', function($routes) {
    // Handle CORS preflight OPTIONS requests
    $routes->options('templates', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/categories', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/categories/(:num)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/contents', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/contents/(:num)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/contents/reorder', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/copy', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/topics', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/topics/(:num)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/topics/reorder', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/factors', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/factors/(:num)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/factors/reorder', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('templates/(:num)/factors/stats', function() {
        return service('response')->setStatusCode(200);
    });
    
    // Company Assessments CORS preflight options
    $routes->options('company-assessments', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('company-assessments/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('company-assessments/company/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('company-assessments/company/(:any)/stats', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('company-assessments/(:num)/copy', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('company-assessments/(:num)/status', function() {
        return service('response')->setStatusCode(200);
    });
    
    // Local Companies CORS preflight options
    $routes->options('local-companies', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('local-companies/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('local-companies/external/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('local-companies/stats', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('local-companies/resolve', function() {
        return service('response')->setStatusCode(200);
    });
    
    // Template Categories routes - Put these BEFORE resource routes to avoid conflicts
    $routes->get('templates/(:num)/categories', 'Api\V1\RiskAssessment\CategoryController::index/$1');
    $routes->post('templates/(:num)/categories', 'Api\V1\RiskAssessment\CategoryController::create/$1');
    $routes->put('templates/(:num)/categories/(:num)', 'Api\V1\RiskAssessment\CategoryController::update/$1/$2');
    $routes->delete('templates/(:num)/categories/(:num)', 'Api\V1\RiskAssessment\CategoryController::delete/$1/$2');

    // Template Topics routes - Put these BEFORE resource routes to avoid conflicts
    $routes->get('templates/(:num)/topics', 'Api\V1\RiskAssessment\RiskTopicController::index/$1');
    $routes->post('templates/(:num)/topics', 'Api\V1\RiskAssessment\RiskTopicController::create/$1');
    $routes->put('templates/(:num)/topics/(:num)', 'Api\V1\RiskAssessment\RiskTopicController::update/$1/$2');
    $routes->delete('templates/(:num)/topics/(:num)', 'Api\V1\RiskAssessment\RiskTopicController::delete/$1/$2');
    $routes->put('templates/(:num)/topics/reorder', 'Api\V1\RiskAssessment\RiskTopicController::reorder/$1');

    // Template Factors routes - Put these BEFORE resource routes to avoid conflicts
    $routes->get('templates/(:num)/factors', 'Api\V1\RiskAssessment\RiskFactorController::index/$1');
    $routes->post('templates/(:num)/factors', 'Api\V1\RiskAssessment\RiskFactorController::create/$1');
    $routes->put('templates/(:num)/factors/(:num)', 'Api\V1\RiskAssessment\RiskFactorController::update/$1/$2');
    $routes->delete('templates/(:num)/factors/(:num)', 'Api\V1\RiskAssessment\RiskFactorController::delete/$1/$2');
    $routes->put('templates/(:num)/factors/reorder', 'Api\V1\RiskAssessment\RiskFactorController::reorder/$1');
    $routes->get('templates/(:num)/factors/stats', 'Api\V1\RiskAssessment\RiskFactorController::stats/$1');
    
    // Template Contents routes - Now using correct TemplateContentController
    $routes->get('templates/(:num)/contents', 'Api\V1\RiskAssessment\TemplateContentController::index/$1');
    $routes->get('templates/(:num)/contents/(:num)', 'Api\V1\RiskAssessment\TemplateContentController::show/$1/$2');
    $routes->post('templates/(:num)/contents', 'Api\V1\RiskAssessment\TemplateContentController::create/$1');
    $routes->put('templates/(:num)/contents/(:num)', 'Api\V1\RiskAssessment\TemplateContentController::update/$1/$2');
    $routes->delete('templates/(:num)/contents/(:num)', 'Api\V1\RiskAssessment\TemplateContentController::delete/$1/$2');
    $routes->put('templates/(:num)/contents/reorder', 'Api\V1\RiskAssessment\TemplateContentController::reorder/$1');

    // Scale Management routes - Probability and Impact Scales
    $routes->post('templates/(:num)/scales/probability', 'Api\V1\RiskAssessment\ScaleController::saveProbabilityScale/$1');
    $routes->get('templates/(:num)/scales/probability', 'Api\V1\RiskAssessment\ScaleController::getProbabilityScale/$1');
    $routes->post('templates/(:num)/scales/impact', 'Api\V1\RiskAssessment\ScaleController::saveImpactScale/$1');
    $routes->get('templates/(:num)/scales/impact', 'Api\V1\RiskAssessment\ScaleController::getImpactScale/$1');

    // Template copy route - Put this before resource routes
    $routes->post('templates/(:num)/copy', 'Api\V1\RiskAssessment\TemplateController::copy/$1');
    
    // Company Assessments routes - Put these before template resource routes
    $routes->get('company-assessments/company/(:segment)', 'Api\V1\RiskAssessment\CompanyAssessmentController::getByCompany/$1');
    $routes->get('company-assessments/company/(:segment)/stats', 'Api\V1\RiskAssessment\CompanyAssessmentController::getCompanyStats/$1');
    $routes->post('company-assessments/(:num)/copy', 'Api\V1\RiskAssessment\CompanyAssessmentController::copy/$1');
    $routes->patch('company-assessments/(:num)/status', 'Api\V1\RiskAssessment\CompanyAssessmentController::updateStatus/$1');
    
    // Company Assessments CRUD routes
    $routes->resource('company-assessments', ['controller' => 'Api\V1\RiskAssessment\CompanyAssessmentController']);
    
    // Local Companies routes - Put specific routes before resource routes
    $routes->get('local-companies/stats', 'Api\V1\LocalCompaniesController::stats');
    $routes->get('local-companies/external/(:segment)', 'Api\V1\LocalCompaniesController::findByExternalId/$1');
    $routes->post('local-companies/resolve', 'Api\V1\LocalCompaniesController::resolveCompany');
    
    // Local Companies CRUD routes
    $routes->resource('local-companies', ['controller' => 'Api\V1\LocalCompaniesController']);
    
    // Template CRUD routes - Temporarily redirect to working endpoint
    $routes->get('templates', 'Api\V1\RiskAssessment\TemplateRedirectController::index');
    $routes->post('templates', 'Api\V1\RiskAssessment\TemplateRedirectController::create');
    $routes->get('templates/(:num)', 'Api\V1\RiskAssessment\TemplateRedirectController::show/$1');
    $routes->put('templates/(:num)', 'Api\V1\RiskAssessment\TemplateRedirectController::update/$1');
    $routes->delete('templates/(:num)', 'Api\V1\RiskAssessment\TemplateRedirectController::delete/$1');
    $routes->options('templates', 'Api\V1\RiskAssessment\TemplateRedirectController::options');

    // Test route
    $routes->resource('test', ['controller' => 'Api\V1\RiskAssessment\TestController']);
});

// V1 Question Management API Routes (Independent Question Management System)
$routes->group('api/v1/question-management', function($routes) {
    // Assessment Structure Management
    $routes->get('assessment/(:num)/structure', 'Api\V1\QuestionManagement\QuestionManagementController::getAssessmentStructure/$1');
    $routes->post('assessment/(:num)/sync-from-template', 'Api\V1\QuestionManagement\QuestionManagementController::syncFromTemplate/$1');
    $routes->get('assessment/(:num)/scales', 'Api\V1\QuestionManagement\QuestionManagementController::getAssessmentScales/$1');
    $routes->get('assessment/(:num)/stats', 'Api\V1\QuestionManagement\QuestionManagementController::getAssessmentStats/$1');
    $routes->get('assessment/(:num)/assignment-status', 'Api\V1\QuestionManagement\QuestionManagementController::getAssignmentStatus/$1');
    $routes->get('assessment/(:num)/assignments', 'Api\V1\QuestionManagement\QuestionManagementController::getAssignments/$1');
    $routes->get('assessment/(:num)/user/(:num)/results', 'Api\V1\QuestionManagement\QuestionManagementController::getUserResults/$1/$2');
    $routes->get('assessment/(:num)/user/(:num)/responses/(:num)', 'Api\V1\QuestionManagement\QuestionManagementController::getUserContentResponse/$1/$2/$3');
    $routes->delete('assessment/(:num)/clear', 'Api\V1\QuestionManagement\QuestionManagementController::clearAssessmentData/$1');

    // Categories Management
    $routes->get('assessment/(:num)/categories', 'Api\V1\QuestionManagement\QuestionStructureController::getCategories/$1');
    $routes->post('assessment/(:num)/categories', 'Api\V1\QuestionManagement\QuestionStructureController::createCategory/$1');
    $routes->put('categories/(:num)', 'Api\V1\QuestionManagement\QuestionStructureController::updateCategory/$1');
    $routes->delete('categories/(:num)', 'Api\V1\QuestionManagement\QuestionStructureController::deleteCategory/$1');
    $routes->put('assessment/(:num)/categories/reorder', 'Api\V1\QuestionManagement\QuestionStructureController::reorderCategories/$1');

    // Topics Management
    $routes->get('assessment/(:num)/topics', 'Api\V1\QuestionManagement\QuestionStructureController::getTopics/$1');
    $routes->post('assessment/(:num)/topics', 'Api\V1\QuestionManagement\QuestionStructureController::createTopic/$1');
    $routes->put('topics/(:num)', 'Api\V1\QuestionManagement\QuestionStructureController::updateTopic/$1');
    $routes->delete('topics/(:num)', 'Api\V1\QuestionManagement\QuestionStructureController::deleteTopic/$1');
    $routes->put('assessment/(:num)/topics/reorder', 'Api\V1\QuestionManagement\QuestionStructureController::reorderTopics/$1');

    // Factors Management
    $routes->get('assessment/(:num)/factors', 'Api\V1\QuestionManagement\QuestionStructureController::getFactors/$1');
    $routes->post('assessment/(:num)/factors', 'Api\V1\QuestionManagement\QuestionStructureController::createFactor/$1');
    $routes->put('factors/(:num)', 'Api\V1\QuestionManagement\QuestionStructureController::updateFactor/$1');
    $routes->delete('factors/(:num)', 'Api\V1\QuestionManagement\QuestionStructureController::deleteFactor/$1');
    $routes->put('assessment/(:num)/factors/reorder', 'Api\V1\QuestionManagement\QuestionStructureController::reorderFactors/$1');

    // Contents Management
    $routes->get('assessment/(:num)/contents', 'Api\V1\QuestionManagement\QuestionManagementController::getContents/$1');
    $routes->get('contents/(:num)', 'Api\V1\QuestionManagement\QuestionManagementController::getContent/$1');
    $routes->post('assessment/(:num)/contents', 'Api\V1\QuestionManagement\QuestionManagementController::createContent/$1');
    $routes->put('contents/(:num)', 'Api\V1\QuestionManagement\QuestionManagementController::updateContent/$1');
    $routes->delete('contents/(:num)', 'Api\V1\QuestionManagement\QuestionManagementController::deleteContent/$1');

    // Statistics Results
    $routes->get('assessment/(:num)/statistics-results', 'Api\V1\QuestionManagement\QuestionManagementController::getStatisticsResults/$1');

    // Export Statistics
    $routes->get('assessment/(:num)/export', 'Api\V1\QuestionManagement\QuestionManagementController::exportStatistics/$1');

    // Responses Management
    $routes->get('assessment/(:num)/responses', 'Api\V1\QuestionManagement\QuestionManagementController::getResponses/$1');
    $routes->post('assessment/(:num)/responses', 'Api\V1\QuestionManagement\QuestionManagementController::saveResponses/$1');
    $routes->put('responses/(:num)', 'Api\V1\QuestionManagement\QuestionManagementController::updateResponse/$1');
    $routes->delete('responses/(:num)', 'Api\V1\QuestionManagement\QuestionManagementController::deleteResponse/$1');

    // CORS Support for Question Management APIs
    $routes->options('assessment/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('categories/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('topics/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('factors/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('contents/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('responses/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
});

// V1 Personnel Management API Routes
$routes->group('api/v1/personnel', function($routes) {
    // CORS preflight options
    $routes->options('companies', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('companies/(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('companies/(:num)/external-id', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('companies/(:num)/personnel', function() {
        return service('response')->setStatusCode(200);
    });

    // Personnel API routes
    $routes->get('companies', 'Api\V1\PersonnelController::getCompanies');
    $routes->get('companies/(:num)/external-id', 'Api\V1\PersonnelController::getCompanyExternalId/$1');
    $routes->get('companies/(:num)/personnel', 'Api\V1\PersonnelController::getPersonnel/$1');

    // Personnel Assignment API routes - Add CORS options first
    $routes->options('companies/(:num)/personnel-assignments', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('companies/(:num)/sync', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('companies/(:num)/assessments/(:num)/assignments', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('companies/(:num)/assessments/(:num)/personnel/(:num)', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('assignments', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('assignments/batch', function() {
        return service('response')->setStatusCode(200);
    });
    $routes->options('assignments/(:num)/status', function() {
        return service('response')->setStatusCode(200);
    });

    // Personnel Assignment Management routes
    $routes->get('companies/(:num)/personnel-assignments', 'Api\V1\PersonnelAssignmentController::getPersonnelByCompany/$1');
    $routes->post('companies/(:num)/sync', 'Api\V1\PersonnelAssignmentController::syncPersonnel/$1');
    $routes->get('companies/(:num)/assessments/(:num)/assignments', 'Api\V1\PersonnelAssignmentController::getAssignmentSummary/$1/$2');
    $routes->delete('companies/(:num)/assessments/(:num)/personnel/(:num)', 'Api\V1\PersonnelAssignmentController::removePersonnelFromAssessment/$1/$2/$3');

    // Assignment CRUD operations
    $routes->post('assignments', 'Api\V1\PersonnelAssignmentController::createAssignment');
    $routes->post('assignments/batch', 'Api\V1\PersonnelAssignmentController::batchCreateAssignments');
    $routes->delete('assignments', 'Api\V1\PersonnelAssignmentController::removeAssignment');
    $routes->put('assignments/(:num)/status', 'Api\V1\PersonnelAssignmentController::updateAssignmentStatus/$1');
});

// V1 External Personnel API Routes
$routes->group('api/v1/external-personnel', function($routes) {
    // CORS preflight options
    $routes->options('find-by-external-id', function() {
        return service('response')->setStatusCode(200);
    });

    // External Personnel API routes
    $routes->post('find-by-external-id', 'Api\V1\ExternalPersonnelController::findByExternalId');
    $routes->get('/', 'Api\V1\ExternalPersonnelController::index');
});
