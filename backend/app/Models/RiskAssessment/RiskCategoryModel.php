<?php

namespace App\Models\RiskAssessment;

use CodeIgniter\Model;

class RiskCategoryModel extends Model
{
    protected $table = 'risk_categories';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false; // Hard deletion only
    protected $protectFields = true;
    protected $allowedFields = [
        'template_id',
        'category_name',
        'category_code',
        'description',
        'sort_order'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'template_id' => 'required|integer',
        'category_name' => 'required|max_length[255]',
        'category_code' => 'permit_empty|max_length[50]',
        'sort_order' => 'permit_empty|integer'
    ];
    
    protected $validationMessages = [
        'template_id' => [
            'required' => 'Template ID is required',
            'integer' => 'Template ID must be an integer'
        ],
        'category_name' => [
            'required' => 'Category name is required',
            'max_length' => 'Category name cannot exceed 255 characters'
        ],
        'category_code' => [
            'max_length' => 'Category code cannot exceed 50 characters'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get categories for a specific template with content count
     */
    public function getCategoriesWithStats($templateId, $search = null)
    {
        $builder = $this->select('
                risk_categories.*,
                (SELECT COUNT(*) FROM template_contents WHERE category_id = risk_categories.id) as content_count
            ')
            ->where('template_id', $templateId)
            ->orderBy('sort_order', 'ASC');

        // Search condition
        if (!empty($search)) {
            $builder->like('category_name', $search);
        }

        return $builder->findAll();
    }

    /**
     * Check if category code is unique within template
     */
    public function isCategoryCodeUniqueInTemplate($templateId, $categoryCode, $excludeId = null)
    {
        $builder = $this->where('template_id', $templateId)
            ->where('category_code', $categoryCode);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first() === null;
    }
}