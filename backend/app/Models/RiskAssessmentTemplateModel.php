<?php

namespace App\Models;

use CodeIgniter\Model;

class RiskAssessmentTemplateModel extends Model
{
    protected $table = 'risk_assessment_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'version_name',
        'description',
        'status',
        'copied_from'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'version_name' => 'required|max_length[255]',
        'status' => 'permit_empty|in_list[active,inactive,draft]'
    ];
    
    protected $validationMessages = [
        'version_name' => [
            'required' => 'Version name is required',
            'max_length' => 'Version name cannot exceed 255 characters'
        ],
        'status' => [
            'in_list' => 'Status must be active, inactive or draft'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get template with additional statistics
     */
    public function getTemplateWithStats($templateId)
    {
        return $this->select('
                risk_assessment_templates.*,
                (SELECT COUNT(*) FROM template_contents WHERE template_id = risk_assessment_templates.id AND deleted_at IS NULL) as content_count,
                (SELECT COUNT(*) FROM risk_categories WHERE template_id = risk_assessment_templates.id AND deleted_at IS NULL) as category_count
            ')
            ->where('id', $templateId)
            ->where('deleted_at', null)
            ->first();
    }

    /**
     * Get templates list with statistics
     */
    public function getTemplatesWithStats($search = null, $status = null, $page = 1, $limit = 20, $sort = 'created_at', $order = 'desc')
    {
        $builder = $this->select('
                risk_assessment_templates.*,
                (SELECT COUNT(*) FROM template_contents WHERE template_id = risk_assessment_templates.id AND deleted_at IS NULL) as content_count,
                (SELECT COUNT(*) FROM risk_categories WHERE template_id = risk_assessment_templates.id AND deleted_at IS NULL) as category_count
            ')
            ->where('deleted_at', null);

        // Search condition
        if (!empty($search)) {
            $builder->like('version_name', $search);
        }

        // Status filter
        if (!empty($status)) {
            $builder->where('status', $status);
        }

        // Sorting
        $allowedSorts = ['id', 'version_name', 'created_at', 'updated_at'];
        if (in_array($sort, $allowedSorts)) {
            $builder->orderBy($sort, $order);
        }

        return $builder;
    }
}