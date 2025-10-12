<?php

namespace App\Models\RiskAssessment;

use CodeIgniter\Model;

class TemplateContentModel extends Model
{
    protected $table = 'template_contents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false; // Hard deletion only
    protected $protectFields = true;
    protected $allowedFields = [
        'template_id',
        'category_id',
        'topic_id',
        'risk_factor_id',
        'topic',
        'description',
        'scoring_method',
        'weight',
        'sort_order',
        'is_required',
        // 'a_content', // REMOVED: Now using risk_factors.description instead
        'b_content',
        'c_placeholder',
        'd_placeholder_1',
        'd_placeholder_2',
        'e1_placeholder_1',
        'e2_select_1',
        'e2_select_2',
        'e2_placeholder',
        'f1_placeholder_1',
        'f2_select_1',
        'f2_select_2',
        'f2_placeholder',
        'g1_select',         // G 對外負面衝擊評分
        'g1_placeholder_1',
        'h1_select',         // H 對外正面影響評分
        'h1_placeholder_1',
        // 資訊圖示懸浮文字欄位
        'e1_info',
        'f1_info',
        'g1_info',
        'h1_info'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'template_id' => 'required|integer',
        'topic_id' => 'permit_empty|integer',
        'risk_factor_id' => 'permit_empty|integer',
        'sort_order' => 'permit_empty|integer',
        'is_required' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'template_id' => [
            'required' => 'Template ID is required',
            'integer' => 'Template ID must be an integer'
        ],
        'topic_id' => [
            'integer' => 'Topic ID must be an integer'
        ],
        'risk_factor_id' => [
            'integer' => 'Risk Factor ID must be an integer'
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
                risk_categories.category_name,
                risk_topics.topic_name,
                risk_factors.factor_name,
                risk_factors.description as factor_description
            ')
            ->join('risk_categories', 'risk_categories.id = template_contents.category_id', 'left')
            ->join('risk_topics', 'risk_topics.id = template_contents.topic_id', 'left')
            ->join('risk_factors', 'risk_factors.id = template_contents.risk_factor_id', 'left')
            ->where('template_contents.template_id', $templateId);

        // Search condition
        if (!empty($search)) {
            $builder->groupStart()
                ->like('template_contents.description', $search)
                ->groupEnd();
        }

        // Category filter
        if (!empty($categoryId)) {
            $builder->where('template_contents.category_id', $categoryId);
        }

        // Sorting
        $allowedSorts = ['id', 'sort_order', 'created_at'];
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
            ->get()
            ->getRow()
            ->sort_order ?? 0;

        return $maxOrder + 1;
    }

    /**
     * Get content with category, topic, and factor names and descriptions
     */
    public function getContentWithCategory($id)
    {
        return $this->select('
                template_contents.*,
                risk_categories.category_name,
                risk_categories.description as category_description,
                risk_topics.topic_name,
                risk_topics.description as topic_description,
                risk_factors.factor_name,
                risk_factors.description as factor_description
            ')
            ->join('risk_categories', 'risk_categories.id = template_contents.category_id', 'left')
            ->join('risk_topics', 'risk_topics.id = template_contents.topic_id', 'left')
            ->join('risk_factors', 'risk_factors.id = template_contents.risk_factor_id', 'left')
            ->where('template_contents.id', $id)
            ->first();
    }
}