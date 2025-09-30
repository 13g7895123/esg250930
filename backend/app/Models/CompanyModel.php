<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDelete = false;
    protected $protectFields = true;
    protected $allowedFields = ['name', 'email', 'phone', 'address', 'created_at', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|string|max_length[255]',
        'email' => 'required|valid_email|max_length[255]',
        'phone' => 'permit_empty|string|max_length[50]',
        'address' => 'permit_empty|string'
    ];
    protected $validationMessages = [
        'name' => [
            'required' => 'Company name is required',
            'max_length' => 'Company name cannot exceed 255 characters'
        ],
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please provide a valid email address',
            'max_length' => 'Email cannot exceed 255 characters'
        ]
    ];
    protected $skipValidation = false;
}