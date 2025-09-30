<?php

namespace App\Models\RiskAssessment;

use CodeIgniter\Model;

class ProbabilityScaleModel extends Model
{
    protected $table = 'probability_scales';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'template_id',
        'description_text',
        'show_description',
        'selected_display_column'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'template_id' => 'required|integer',
        'show_description' => 'permit_empty|in_list[0,1]',
        'selected_display_column' => 'permit_empty|max_length[50]'
    ];

    protected $validationMessages = [
        'template_id' => [
            'required' => '範本ID為必填',
            'integer' => '範本ID必須為整數'
        ],
        'show_description' => [
            'in_list' => '顯示說明文字必須為0或1'
        ],
        'selected_display_column' => [
            'max_length' => '預設顯示欄位不能超過50個字元'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;
}