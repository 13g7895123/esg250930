<?php

namespace App\Services;

use App\Models\RiskAssessment\RiskAssessmentTemplateModel;
use App\Models\RiskAssessment\RiskCategoryModel;
use App\Models\RiskAssessment\TemplateContentModel;

/**
 * Enhanced Template Validation Service
 * Provides advanced validation logic for template operations
 */
class EnhancedTemplateValidationService
{
    protected $templateModel;
    protected $categoryModel;
    protected $contentModel;

    // Reserved keywords that cannot be used in template names
    protected $reservedKeywords = [
        'DEFAULT', 'SYSTEM', 'ADMIN', 'ROOT', 'NULL', 'UNDEFINED',
        'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE',
        'TEMPLATE', 'TEST', 'SAMPLE', 'DEMO'
    ];

    // Inappropriate content patterns
    protected $inappropriatePatterns = [
        '/shit/i', '/fuck/i', '/damn/i', '/crap/i', '/stupid/i',
        '/idiot/i', '/moron/i', '/dumb/i', '/suck/i', '/hate/i'
    ];

    // Security violation patterns
    protected $securityPatterns = [
        '/<script/i', '/<\/script>/i', '/javascript:/i', '/onclick/i',
        '/onerror/i', '/onload/i', '/eval\(/i', '/exec\(/i',
        '/drop\s+table/i', '/delete\s+from/i', '/insert\s+into/i',
        '/update\s+set/i', '/union\s+select/i', '/\';/i', '/--/i'
    ];

    // Business domain keywords for consistency checking
    protected $domainKeywords = [
        'financial' => ['financial', 'finance', 'money', 'revenue', 'profit', 'cost', 'budget'],
        'technical' => ['technical', 'technology', 'system', 'software', 'hardware', 'IT'],
        'operational' => ['operational', 'operations', 'process', 'workflow', 'procedure'],
        'compliance' => ['compliance', 'regulatory', 'legal', 'audit', 'governance'],
        'strategic' => ['strategic', 'strategy', 'planning', 'vision', 'mission']
    ];

    public function __construct()
    {
        $this->templateModel = new RiskAssessmentTemplateModel();
        $this->categoryModel = new RiskCategoryModel();
        $this->contentModel = new TemplateContentModel();
    }

    /**
     * Validate version name with enhanced business rules
     */
    public function validateVersionName(string $versionName, array $context = []): array
    {
        $errors = [];

        // Check for reserved keywords
        if (in_array(strtoupper($versionName), $this->reservedKeywords)) {
            $errors[] = [
                'code' => 'RESERVED_KEYWORD',
                'message' => '版本名稱不能使用系統保留字',
                'value_received' => $versionName,
                'expected_format' => '請使用描述性的版本名稱，避免系統保留字',
                'suggestion' => '例如: "2024年度財務風險評估範本 v1.0"'
            ];
        }

        // Check for SQL injection patterns first (higher priority)
        if (strpos($versionName, "';") !== false || strpos($versionName, '--') !== false ||
            preg_match('/drop\s+table/i', $versionName) || preg_match('/delete\s+from/i', $versionName)) {
            $errors[] = [
                'code' => 'SECURITY_VIOLATION',
                'message' => '版本名稱包含潛在安全風險字符',
                'value_received' => $versionName,
                'expected_format' => '安全的版本名稱格式',
                'suggestion' => '請使用安全的命名方式，避免特殊字符組合'
            ];
        }
        // Check for other invalid characters (HTML/script tags)
        elseif (preg_match('/<script/i', $versionName) || preg_match('/<\/script>/i', $versionName)) {
            $errors[] = [
                'code' => 'INVALID_CHARACTERS',
                'message' => '版本名稱包含無效字符',
                'value_received' => $versionName,
                'expected_format' => '只允許字母、數字、空格和基本標點符號',
                'suggestion' => '請移除HTML標籤、腳本或特殊符號'
            ];
        }

        return $errors;
    }

    /**
     * Validate description with content analysis
     */
    public function validateDescription(string $description, array $context = []): array
    {
        $errors = [];

        // Check minimum length for business requirements
        if (strlen(trim($description)) < 10) {
            $errors[] = [
                'code' => 'DESCRIPTION_TOO_SHORT',
                'message' => '描述內容過短，不符合業務要求',
                'value_received' => $description,
                'expected_format' => '至少10個字符的有意義描述',
                'suggestion' => '請提供詳細說明範本的用途、適用範圍和特點'
            ];
        }

        // Check for inappropriate content
        foreach ($this->inappropriatePatterns as $pattern) {
            if (preg_match($pattern, $description)) {
                $errors[] = [
                    'code' => 'INAPPROPRIATE_CONTENT',
                    'message' => '描述包含不當內容',
                    'value_received' => $description,
                    'expected_format' => '專業、正面的描述內容',
                    'suggestion' => '請使用專業術語和正面表達方式'
                ];
                break;
            }
        }

        // Check for security violations
        foreach ($this->securityPatterns as $pattern) {
            if (preg_match($pattern, $description)) {
                $errors[] = [
                    'code' => 'SECURITY_VIOLATION',
                    'message' => '描述包含潛在安全風險內容',
                    'value_received' => $description,
                    'expected_format' => '安全的描述格式',
                    'suggestion' => '請移除腳本標籤或危險代碼'
                ];
                break;
            }
        }

        return $errors;
    }

    /**
     * Validate status transitions based on business rules
     */
    public function validateStatusTransition(string $currentStatus, string $newStatus, int $templateId = null): array
    {
        $errors = [];

        // Define allowed status transitions
        $allowedTransitions = [
            'draft' => ['active', 'inactive'],
            'active' => ['inactive'],
            'inactive' => ['active']
        ];

        // Check if transition is allowed
        if (!isset($allowedTransitions[$currentStatus]) ||
            !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            $errors[] = [
                'code' => 'INVALID_STATUS_TRANSITION',
                'message' => "無法從 {$currentStatus} 狀態轉換到 {$newStatus} 狀態",
                'context' => [
                    'current_status' => $currentStatus,
                    'requested_status' => $newStatus,
                    'allowed_transitions' => $allowedTransitions[$currentStatus] ?? []
                ]
            ];
        }

        // Check for dependencies when changing to inactive
        if ($newStatus === 'inactive' && $templateId) {
            $dependencies = $this->checkTemplateDependencies($templateId);
            if (!empty($dependencies)) {
                $errors[] = [
                    'code' => 'STATUS_CHANGE_BLOCKED',
                    'message' => '範本有相關依賴項目，無法設為停用狀態',
                    'context' => [
                        'blocking_dependencies' => $dependencies
                    ]
                ];
            }
        }

        return $errors;
    }

    /**
     * Validate business rules for template completeness
     */
    public function validateBusinessRules(array $templateData, int $templateId = null): array
    {
        $errors = [];
        $failures = [];

        // Only enforce business rules if explicitly requested
        if (!isset($templateData['enforce_business_rules']) || !$templateData['enforce_business_rules']) {
            return $errors;
        }

        // Check if trying to activate template without minimum requirements
        if (isset($templateData['status']) && $templateData['status'] === 'active') {

            // Check minimum categories requirement
            if ($templateId) {
                $categoryCount = $this->categoryModel->where('template_id', $templateId)->countAllResults();
                if ($categoryCount < 3) {
                    $failures[] = 'MINIMUM_CATEGORIES_REQUIRED';
                }

                // Check minimum content requirement
                $contentCount = $this->contentModel->where('template_id', $templateId)->countAllResults();
                if ($contentCount < 5) {
                    $failures[] = 'MINIMUM_CONTENT_REQUIRED';
                }
            } else {
                // New template - will not have categories/content yet
                $failures[] = 'MINIMUM_CATEGORIES_REQUIRED';
                $failures[] = 'MINIMUM_CONTENT_REQUIRED';
            }
        }

        if (!empty($failures)) {
            $errors[] = [
                'code' => 'BUSINESS_RULE_VIOLATION',
                'message' => '範本不符合業務規則要求',
                'context' => [
                    'business_rule_failures' => $failures
                ]
            ];
        }

        return $errors;
    }

    /**
     * Check for similar templates to prevent duplication
     */
    public function checkTemplateSimiliarity(string $versionName, string $description, array $context = []): array
    {
        $errors = [];

        // Only check similarity if explicitly requested
        if (!isset($context['check_similarity']) || !$context['check_similarity']) {
            return $errors;
        }

        // Get existing templates for comparison
        $existingTemplates = $this->templateModel->findAll();
        $maxSimilarity = 0;
        $similarTemplates = [];

        foreach ($existingTemplates as $template) {
            $similarity = $this->calculateSimilarity($versionName, $description, $template);
            if ($similarity > 0.7) {
                $similarTemplates[] = [
                    'id' => $template['id'],
                    'version_name' => $template['version_name'],
                    'similarity_score' => $similarity
                ];
                $maxSimilarity = max($maxSimilarity, $similarity);
            }
        }

        if (!empty($similarTemplates)) {
            $errors[] = [
                'code' => 'POTENTIAL_DUPLICATE',
                'message' => '檢測到類似的範本，可能造成重複',
                'context' => [
                    'similar_templates' => $similarTemplates,
                    'similarity_score' => $maxSimilarity
                ]
            ];
        }

        return $errors;
    }

    /**
     * Validate cross-field consistency rules
     */
    public function validateCrossFieldConsistency(array $templateData): array
    {
        $errors = [];

        // Check status and description consistency
        if (isset($templateData['status']) && isset($templateData['description'])) {
            $status = $templateData['status'];
            $description = strtolower($templateData['description']);

            if ($status === 'active' && strpos($description, 'draft') !== false) {
                $errors[] = [
                    'code' => 'FIELD_CONSISTENCY_ERROR',
                    'message' => '狀態與描述不一致',
                    'context' => [
                        'inconsistent_fields' => ['status', 'description']
                    ]
                ];
            }
        }

        // Check domain consistency if requested
        if (isset($templateData['validate_domain_consistency']) && $templateData['validate_domain_consistency']) {
            $domainMismatch = $this->checkDomainConsistency(
                $templateData['version_name'] ?? '',
                $templateData['description'] ?? ''
            );

            if ($domainMismatch) {
                $errors[] = [
                    'code' => 'DOMAIN_MISMATCH',
                    'message' => '範本名稱與描述的業務領域不一致',
                    'context' => $domainMismatch
                ];
            }
        }

        return $errors;
    }

    /**
     * Check template dependencies
     */
    protected function checkTemplateDependencies(int $templateId): array
    {
        $dependencies = [];

        // Check if template has categories
        $categoryCount = $this->categoryModel->where('template_id', $templateId)->countAllResults();
        if ($categoryCount > 0) {
            $dependencies[] = 'template_has_categories';
        }

        // Check if template has content
        $contentCount = $this->contentModel->where('template_id', $templateId)->countAllResults();
        if ($contentCount > 0) {
            $dependencies[] = 'template_has_content';
        }

        return $dependencies;
    }

    /**
     * Calculate similarity between templates
     */
    protected function calculateSimilarity(string $newName, string $newDescription, array $existingTemplate): float
    {
        $existingName = $existingTemplate['version_name'];
        $existingDescription = $existingTemplate['description'] ?? '';

        // Improved similarity calculation
        $nameWords = array_filter(explode(' ', strtolower($newName)));
        $existingNameWords = array_filter(explode(' ', strtolower($existingName)));

        // Calculate name similarity with better logic
        if (empty($nameWords) || empty($existingNameWords)) {
            $nameSimilarity = 0;
        } else {
            $nameCommonWords = count(array_intersect($nameWords, $existingNameWords));
            $nameUnionWords = count(array_unique(array_merge($nameWords, $existingNameWords)));
            $nameSimilarity = $nameCommonWords / $nameUnionWords;
        }

        // Calculate description similarity
        $descWords = array_filter(explode(' ', strtolower($newDescription)));
        $existingDescWords = array_filter(explode(' ', strtolower($existingDescription)));

        if (empty($descWords) || empty($existingDescWords)) {
            $descSimilarity = 0;
        } else {
            $descCommonWords = count(array_intersect($descWords, $existingDescWords));
            $descUnionWords = count(array_unique(array_merge($descWords, $existingDescWords)));
            $descSimilarity = $descCommonWords / $descUnionWords;
        }

        // Also check for substring matches to catch similar patterns
        $nameSubstringSimilarity = 0;
        $descSubstringSimilarity = 0;

        // Check if one name contains significant parts of the other
        if (strlen($newName) > 3 && strlen($existingName) > 3) {
            $nameSubstringSimilarity = max(
                substr_count(strtolower($existingName), strtolower($newName)) / strlen($newName),
                substr_count(strtolower($newName), strtolower($existingName)) / strlen($existingName)
            );
        }

        // Check description substring similarity
        if (strlen($newDescription) > 3 && strlen($existingDescription) > 3) {
            $descSubstringSimilarity = max(
                substr_count(strtolower($existingDescription), strtolower($newDescription)) / strlen($newDescription),
                substr_count(strtolower($newDescription), strtolower($existingDescription)) / strlen($existingDescription)
            );
        }

        // Take the maximum of word-based and substring-based similarity
        $finalNameSimilarity = max($nameSimilarity, $nameSubstringSimilarity);
        $finalDescSimilarity = max($descSimilarity, $descSubstringSimilarity);

        return ($finalNameSimilarity * 0.7) + ($finalDescSimilarity * 0.3);
    }

    /**
     * Check domain consistency between name and description
     */
    protected function checkDomainConsistency(string $name, string $description): ?array
    {
        $nameDomain = $this->identifyDomain($name);
        $descDomain = $this->identifyDomain($description);

        if ($nameDomain && $descDomain && $nameDomain !== $descDomain) {
            return [
                'name_domain' => $nameDomain,
                'description_domain' => $descDomain,
                'suggestion' => '請確保範本名稱與描述屬於同一業務領域'
            ];
        }

        return null;
    }

    /**
     * Identify business domain from text
     */
    protected function identifyDomain(string $text): ?string
    {
        $text = strtolower($text);

        foreach ($this->domainKeywords as $domain => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    return $domain;
                }
            }
        }

        return null;
    }
}