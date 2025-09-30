<?php

namespace App\Models\RiskAssessment;

use CodeIgniter\Model;

class ImpactScaleColumnModel extends Model
{
    protected $table = 'impact_scale_columns';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'scale_id',
        'column_id',
        'name',
        'amount_note',
        'removable',
        'sort_order'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'scale_id' => 'required|integer',
        'column_id' => 'required|integer',
        'name' => 'required|max_length[255]',
        'amount_note' => 'permit_empty|max_length[255]',
        'removable' => 'permit_empty|in_list[0,1]',
        'sort_order' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'scale_id' => [
            'required' => '量表ID為必填',
            'integer' => '量表ID必須為整數'
        ],
        'column_id' => [
            'required' => '欄位ID為必填',
            'integer' => '欄位ID必須為整數'
        ],
        'name' => [
            'required' => '欄位名稱為必填',
            'max_length' => '欄位名稱不能超過255個字元'
        ],
        'amount_note' => [
            'max_length' => '金額說明不能超過255個字元'
        ],
        'removable' => [
            'in_list' => '可移除標記必須為0或1'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;
}