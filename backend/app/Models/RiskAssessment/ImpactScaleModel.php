<?php

namespace App\Models\RiskAssessment;

use CodeIgniter\Model;

class ImpactScaleModel extends Model
{
    protected $table = 'impact_scales';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'template_id',
        'selected_display_column'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'template_id' => 'required|integer',
        'selected_display_column' => 'permit_empty|max_length[50]'
    ];

    protected $validationMessages = [
        'template_id' => [
            'required' => '範本ID為必填',
            'integer' => '範本ID必須為整數'
        ],
        'selected_display_column' => [
            'max_length' => '預設顯示欄位不能超過50個字元'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;
}