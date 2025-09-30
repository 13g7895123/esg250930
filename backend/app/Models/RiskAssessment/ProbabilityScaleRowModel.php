<?php

namespace App\Models\RiskAssessment;

use CodeIgniter\Model;

class ProbabilityScaleRowModel extends Model
{
    protected $table = 'probability_scale_rows';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'scale_id',
        'probability',
        'score_range',
        'dynamic_fields',
        'sort_order'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'scale_id' => 'required|integer',
        'probability' => 'permit_empty|max_length[255]',
        'score_range' => 'permit_empty|max_length[50]',
        'sort_order' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'scale_id' => [
            'required' => '量表ID為必填',
            'integer' => '量表ID必須為整數'
        ],
        'probability' => [
            'max_length' => '發生可能性程度不能超過255個字元'
        ],
        'score_range' => [
            'max_length' => '分數級距不能超過50個字元'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;
}