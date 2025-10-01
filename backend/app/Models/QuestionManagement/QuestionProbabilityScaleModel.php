<?php

namespace App\Models\QuestionManagement;

use CodeIgniter\Model;

/**
 * 題項可能性量表模型
 *
 * 管理題項的可能性量表主資料
 */
class QuestionProbabilityScaleModel extends Model
{
    protected $table = 'question_probability_scales';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'assessment_id',
        'description_text',
        'show_description',
        'selected_display_column',
        'copied_from_template_scale'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'assessment_id' => 'required|integer',
        'show_description' => 'permit_empty|in_list[0,1]',
        'selected_display_column' => 'permit_empty|max_length[50]'
    ];

    protected $validationMessages = [
        'assessment_id' => [
            'required' => '評估記錄ID為必填',
            'integer' => '評估記錄ID必須為整數'
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

    /**
     * 複製範本量表到題項量表
     *
     * @param int $assessmentId 評估記錄ID
     * @param array $templateScale 範本量表資料
     * @return int|false 新建立的量表ID或失敗時返回false
     */
    public function copyFromTemplateScale(int $assessmentId, array $templateScale)
    {
        $newScaleData = [
            'assessment_id' => $assessmentId,
            'description_text' => $templateScale['description_text'] ?? null,
            'show_description' => $templateScale['show_description'] ?? 0,
            'selected_display_column' => $templateScale['selected_display_column'] ?? null,
            'copied_from_template_scale' => $templateScale['id'] ?? null
        ];

        return $this->insert($newScaleData);
    }

    /**
     * 取得評估記錄的量表資料
     *
     * @param int $assessmentId 評估記錄ID
     * @return array|null 量表資料
     */
    public function getByAssessment(int $assessmentId): ?array
    {
        return $this->where('assessment_id', $assessmentId)->first();
    }

    /**
     * 刪除評估記錄的量表資料（連帶刪除相關的欄位和列資料）
     *
     * @param int $assessmentId 評估記錄ID
     * @return bool 是否成功
     */
    public function deleteByAssessment(int $assessmentId): bool
    {
        return $this->where('assessment_id', $assessmentId)->delete();
    }
}