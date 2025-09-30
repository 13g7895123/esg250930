<?php

namespace App\Models;

use CodeIgniter\Model;

class RiskCategoryModel extends Model
{
    protected $table = 'risk_categories';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
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
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'template_id' => 'required|integer',
        'category_name' => 'required|max_length[255]',
        'category_code' => 'permit_empty|max_length[50]',
        'sort_order' => 'permit_empty|integer'
    ];
    
    protected $validationMessages = [
        'template_id' => [
            'required' => '範本 ID 為必填項目',
            'integer' => '範本 ID 必須為整數'
        ],
        'category_name' => [
            'required' => '分類名稱為必填項目',
            'max_length' => '分類名稱長度不能超過 255 個字元'
        ],
        'category_code' => [
            'max_length' => '分類代碼長度不能超過 50 個字元'
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
                (SELECT COUNT(*) FROM template_contents WHERE category_id = risk_categories.id AND deleted_at IS NULL) as content_count
            ')
            ->where('template_id', $templateId)
            ->where('deleted_at', null)
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
            ->where('category_code', $categoryCode)
            ->where('deleted_at', null);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first() === null;
    }
}