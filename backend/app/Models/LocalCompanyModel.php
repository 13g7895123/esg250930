<?php

namespace App\Models;

use CodeIgniter\Model;

class LocalCompanyModel extends Model
{
    protected $table = 'local_companies';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'company_name',
        'external_id',
        'abbreviation'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'company_name' => 'required|max_length[255]',
        'external_id' => 'required|max_length[50]|is_unique[local_companies.external_id,id,{id}]',
        'abbreviation' => 'permit_empty|max_length[100]'
    ];

    protected $validationMessages = [
        'company_name' => [
            'required' => '公司名稱為必填項目',
            'max_length' => '公司名稱不能超過255個字符'
        ],
        'external_id' => [
            'required' => '外部公司ID為必填項目',
            'max_length' => '外部公司ID不能超過50個字符',
            'is_unique' => '此公司已經存在'
        ],
        'abbreviation' => [
            'max_length' => '公司簡稱不能超過100個字符'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;

    /**
     * 根據外部ID查找公司
     */
    public function findByExternalId($externalId)
    {
        return $this->where('external_id', $externalId)->first();
    }

    /**
     * 檢查外部ID是否已存在
     */
    public function existsByExternalId($externalId, $excludeId = null)
    {
        $builder = $this->where('external_id', $externalId);
        
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * 獲取所有公司並依建立時間排序
     */
    public function getAllCompanies($search = null, $page = 1, $limit = 20, $sort = 'created_at', $order = 'desc')
    {
        $builder = $this->select('*');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('company_name', $search)
                ->orLike('abbreviation', $search)
                ->groupEnd();
        }

        $allowedSorts = ['id', 'company_name', 'external_id', 'abbreviation', 'created_at', 'updated_at'];
        if (in_array($sort, $allowedSorts)) {
            $builder->orderBy($sort, $order);
        }

        return $builder;
    }

    /**
     * 新增公司（防止重複）
     */
    public function addCompanyIfNotExists($companyData)
    {
        // 檢查是否已存在
        $existing = $this->findByExternalId($companyData['external_id']);
        if ($existing) {
            return $existing; // 返回已存在的紀錄
        }

        // 新增公司
        $insertId = $this->insert($companyData);
        if ($insertId) {
            return $this->find($insertId);
        }

        return false;
    }

    /**
     * 獲取公司統計資訊
     */
    public function getCompanyStats()
    {
        return [
            'total' => $this->countAllResults(),
            'added_today' => $this->where('DATE(created_at)', date('Y-m-d'))->countAllResults(),
            'added_this_week' => $this->where('created_at >=', date('Y-m-d', strtotime('-7 days')))->countAllResults(),
            'added_this_month' => $this->where('created_at >=', date('Y-m-01'))->countAllResults()
        ];
    }
}