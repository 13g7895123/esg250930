<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyAssessmentModel extends Model
{
    protected $table = 'company_assessments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'company_id',
        'template_id',
        'template_version',
        'assessment_year',
        'status',
        'copied_from',
        'include_questions',
        'include_results',
        'total_score',
        'percentage_score',
        'risk_level',
        'notes'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'company_id' => 'required|max_length[50]',
        'template_id' => 'required|integer|is_not_unique[risk_assessment_templates.id]',
        'assessment_year' => 'required|integer|greater_than[1900]|less_than_equal_to[2040]',
        'status' => 'permit_empty|in_list[pending,in_progress,completed,archived]',
        'copied_from' => 'permit_empty|integer',
        'include_questions' => 'permit_empty|in_list[0,1]',
        'include_results' => 'permit_empty|in_list[0,1]',
        'total_score' => 'permit_empty|decimal',
        'percentage_score' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'risk_level' => 'permit_empty|in_list[low,medium,high,critical]',
        'notes' => 'permit_empty'
    ];

    protected $validationMessages = [
        'company_id' => [
            'required' => '公司ID為必填項目',
            'max_length' => '公司ID不能超過50個字符'
        ],
        'template_id' => [
            'required' => '風險評估範本ID為必填項目',
            'integer' => '風險評估範本ID必須為整數',
            'is_not_unique' => '指定的風險評估範本不存在'
        ],
        'assessment_year' => [
            'required' => '評估年度為必填項目',
            'integer' => '評估年度必須為整數',
            'greater_than' => '評估年度不能小於1900',
            'less_than_equal_to' => '評估年度不能超過未來10年'
        ],
        'status' => [
            'in_list' => '狀態必須為：pending、in_progress、completed、archived 之一'
        ],
        'copied_from' => [
            'integer' => '複製來源ID必須為整數'
        ],
        'include_questions' => [
            'in_list' => '是否包含題目必須為0或1'
        ],
        'include_results' => [
            'in_list' => '是否包含結果必須為0或1'
        ],
        'total_score' => [
            'decimal' => '總分必須為數字'
        ],
        'percentage_score' => [
            'decimal' => '百分比分數必須為數字',
            'greater_than_equal_to' => '百分比分數不能小於0',
            'less_than_equal_to' => '百分比分數不能大於100'
        ],
        'risk_level' => [
            'in_list' => '風險等級必須為：low、medium、high、critical 之一'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;

    /**
     * 取得指定公司的評估項目列表
     */
    public function getAssessmentsByCompany($companyId, $search = null, $year = null, $status = null, $page = 1, $limit = 20, $sort = 'created_at', $order = 'desc')
    {
        $builder = $this->select('
                company_assessments.*,
                risk_assessment_templates.version_name as template_version_name
            ')
            ->join('risk_assessment_templates', 'risk_assessment_templates.id = company_assessments.template_id', 'left')
            ->where('company_assessments.company_id', $companyId);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('risk_assessment_templates.version_name', $search)
                ->orLike('company_assessments.notes', $search)
                ->groupEnd();
        }

        if (!empty($year)) {
            $builder->where('company_assessments.assessment_year', $year);
        }

        if (!empty($status)) {
            $builder->where('company_assessments.status', $status);
        }

        $allowedSorts = ['id', 'assessment_year', 'status', 'created_at', 'updated_at'];
        if (in_array($sort, $allowedSorts)) {
            $builder->orderBy("company_assessments.$sort", $order);
        }

        return $builder;
    }

    /**
     * 取得評估項目詳細資訊（包含範本資訊）
     */
    public function getAssessmentWithTemplate($id)
    {
        return $this->select('
                company_assessments.*,
                risk_assessment_templates.version_name,
                risk_assessment_templates.description as template_description
            ')
            ->join('risk_assessment_templates', 'risk_assessment_templates.id = company_assessments.template_id', 'left')
            ->where('company_assessments.id', $id)
            ->first();
    }

    /**
     * 複製評估項目
     */
    public function copyAssessment($originalId, $companyId, $copyOptions = [])
    {
        $original = $this->find($originalId);
        if (!$original) {
            return false;
        }

        $newAssessment = [
            'company_id' => $companyId,
            'template_id' => $original['template_id'],
            'template_version' => $original['template_version'],
            'assessment_year' => $original['assessment_year'],
            'status' => 'pending',
            'copied_from' => $originalId,
            'include_questions' => $copyOptions['include_questions'] ?? true,
            'include_results' => $copyOptions['include_results'] ?? false,
            'notes' => $original['notes'] ?? null
        ];

        return $this->insert($newAssessment);
    }

    /**
     * 檢查公司是否存在指定年度的評估項目
     */
    public function hasAssessmentForYear($companyId, $year, $templateId = null)
    {
        $builder = $this->where('company_id', $companyId)
            ->where('assessment_year', $year);

        if ($templateId) {
            $builder->where('template_id', $templateId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * 取得公司評估統計資訊
     */
    public function getAssessmentStats($companyId)
    {
        $total = $this->where('company_id', $companyId)->countAllResults();
        $pending = $this->where('company_id', $companyId)->where('status', 'pending')->countAllResults();
        $inProgress = $this->where('company_id', $companyId)->where('status', 'in_progress')->countAllResults();
        $completed = $this->where('company_id', $companyId)->where('status', 'completed')->countAllResults();
        $archived = $this->where('company_id', $companyId)->where('status', 'archived')->countAllResults();

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'archived' => $archived
        ];
    }

    /**
     * 更新評估狀態
     */
    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * 更新評估分數和風險等級
     */
    public function updateScoreAndRisk($id, $totalScore, $percentageScore, $riskLevel)
    {
        return $this->update($id, [
            'total_score' => $totalScore,
            'percentage_score' => $percentageScore,
            'risk_level' => $riskLevel
        ]);
    }
}