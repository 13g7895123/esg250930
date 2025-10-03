<?php

namespace App\Models\RiskAssessment;

use CodeIgniter\Model;

class RiskFactorModel extends Model
{
    protected $table = 'risk_factors';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'template_id',
        'category_id',
        'topic_id',
        'factor_name',
        'description',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'template_id' => 'required|integer',
        'category_id' => 'required|integer',
        'topic_id' => 'permit_empty|integer',
        'factor_name' => 'required|max_length[255]',
        'description' => 'permit_empty',
        'status' => 'permit_empty|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'template_id' => [
            'required' => 'Template ID is required',
            'integer' => 'Template ID must be an integer'
        ],
        'category_id' => [
            'required' => 'Category ID is required',
            'integer' => 'Category ID must be an integer'
        ],
        'topic_id' => [
            'integer' => 'Topic ID must be an integer'
        ],
        'factor_name' => [
            'required' => 'Factor name is required',
            'max_length' => 'Factor name cannot exceed 255 characters'
        ],
        'status' => [
            'in_list' => 'Status must be active or inactive'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get factors for a specific template with category and topic information
     */
    public function getFactorsWithRelations($templateId, $search = null)
    {
        $builder = $this->select('
                risk_factors.*,
                risk_categories.category_name,
                risk_topics.topic_name
            ')
            ->join('risk_categories', 'risk_categories.id = risk_factors.category_id')
            ->join('risk_topics', 'risk_topics.id = risk_factors.topic_id', 'left')
            ->where('risk_factors.template_id', $templateId)
            ->orderBy('risk_factors.id', 'ASC');

        // Search condition
        if (!empty($search)) {
            $builder->groupStart()
                ->like('risk_factors.factor_name', $search)
                ->orLike('risk_categories.category_name', $search)
                ->orLike('risk_topics.topic_name', $search)
                ->groupEnd();
        }

        return $builder->findAll();
    }

    /**
     * Get factors for a specific category
     */
    public function getFactorsByCategory($templateId, $categoryId, $topicId = null)
    {
        $builder = $this->where('template_id', $templateId)
            ->where('category_id', $categoryId)
            ->where('status', 'active')
            ->orderBy('id', 'ASC');

        if ($topicId !== null) {
            $builder->where('topic_id', $topicId);
        }

        return $builder->findAll();
    }

    /**
     * Get factors for a specific topic
     */
    public function getFactorsByTopic($templateId, $topicId)
    {
        return $this->where('template_id', $templateId)
            ->where('topic_id', $topicId)
            ->where('status', 'active')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Get factor statistics for a template
     */
    public function getFactorStats($templateId)
    {
        return [
            'total_factors' => $this->where('template_id', $templateId)->countAllResults(),
            'active_factors' => $this->where('template_id', $templateId)
                ->where('status', 'active')
                ->countAllResults(),
        ];
    }
}