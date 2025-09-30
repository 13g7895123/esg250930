<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateContentModel extends Model
{
    protected $table = 'template_contents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'template_id',
        'category_id',
        'topic',
        'description',
        'sort_order',
        'is_required'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'template_id' => 'required|integer',
        'topic' => 'required|max_length[255]',
        'description' => 'required',
        'sort_order' => 'permit_empty|integer',
        'is_required' => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'template_id' => [
            'required' => '範本 ID 為必填項目',
            'integer' => '範本 ID 必須為整數'
        ],
        'topic' => [
            'required' => '主題為必填項目',
            'max_length' => '主題長度不能超過 255 個字元'
        ],
        'description' => [
            'required' => '風險因子描述為必填項目'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get template contents with category information
     */
    public function getContentsWithCategory($templateId, $search = null, $categoryId = null, $page = 1, $limit = 20, $sort = 'sort_order', $order = 'asc')
    {
        $builder = $this->select('
                template_contents.*,
                risk_categories.category_name
            ')
            ->join('risk_categories', 'risk_categories.id = template_contents.category_id', 'left')
            ->where('template_contents.template_id', $templateId)
            ->where('template_contents.deleted_at', null);

        // Search condition
        if (!empty($search)) {
            $builder->groupStart()
                ->like('template_contents.topic', $search)
                ->orLike('template_contents.description', $search)
                ->groupEnd();
        }

        // Category filter
        if (!empty($categoryId)) {
            $builder->where('template_contents.category_id', $categoryId);
        }

        // Sorting
        $allowedSorts = ['id', 'topic', 'sort_order', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $builder->orderBy("template_contents.$sort", $order);
        }

        return $builder;
    }

    /**
     * Get next sort order for template
     */
    public function getNextSortOrder($templateId)
    {
        $maxOrder = $this->selectMax('sort_order')
            ->where('template_id', $templateId)
            ->where('deleted_at', null)
            ->get()
            ->getRow()
            ->sort_order ?? 0;

        return $maxOrder + 1;
    }

    /**
     * Get content with category name
     */
    public function getContentWithCategory($id)
    {
        return $this->select('
                template_contents.*,
                risk_categories.category_name
            ')
            ->join('risk_categories', 'risk_categories.id = template_contents.category_id', 'left')
            ->where('template_contents.id', $id)
            ->first();
    }
}