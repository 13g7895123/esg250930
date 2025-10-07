<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 人員指派歷史記錄模型
 *
 * 管理人員指派的所有歷史操作記錄（新增、移除、更新）
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-10-07
 */
class PersonnelAssignmentHistoryModel extends Model
{
    protected $table = 'personnel_assignment_history';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    // 允許插入的欄位
    protected $allowedFields = [
        'assignment_id',
        'company_id',
        'assessment_id',
        'question_content_id',
        'personnel_id',
        'personnel_name',
        'personnel_department',
        'personnel_position',
        'assignment_status',
        'assignment_note',
        'action_type',
        'action_by',
        'action_by_name',
        'action_reason',
        'action_at',
        'original_assigned_at',
    ];

    // 自動處理時間戳記
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    // 驗證規則
    protected $validationRules = [
        'company_id' => 'required|integer',
        'assessment_id' => 'required|integer',
        'question_content_id' => 'required',
        'personnel_id' => 'required|integer',
        'action_type' => 'required|in_list[created,removed,updated]',
        'action_at' => 'required',
    ];

    /**
     * 記錄指派建立操作
     *
     * @param array $assignmentData 指派資料
     * @param int|null $actionBy 操作人員ID
     * @param string|null $actionByName 操作人員姓名
     * @return bool|int 成功返回插入ID，失敗返回false
     */
    public function logCreated(array $assignmentData, ?int $actionBy = null, ?string $actionByName = null)
    {
        $historyData = [
            'assignment_id' => $assignmentData['id'] ?? null,
            'company_id' => $assignmentData['company_id'],
            'assessment_id' => $assignmentData['assessment_id'],
            'question_content_id' => $assignmentData['question_content_id'],
            'personnel_id' => $assignmentData['personnel_id'],
            'personnel_name' => $assignmentData['personnel_name'] ?? null,
            'personnel_department' => $assignmentData['personnel_department'] ?? null,
            'personnel_position' => $assignmentData['personnel_position'] ?? null,
            'assignment_status' => $assignmentData['assignment_status'] ?? 'pending',
            'assignment_note' => $assignmentData['assignment_note'] ?? null,
            'action_type' => 'created',
            'action_by' => $actionBy,
            'action_by_name' => $actionByName,
            'action_at' => date('Y-m-d H:i:s'),
            'original_assigned_at' => $assignmentData['assigned_at'] ?? date('Y-m-d H:i:s'),
        ];

        return $this->insert($historyData);
    }

    /**
     * 記錄指派移除操作
     *
     * @param array $assignmentData 被移除的指派資料
     * @param int|null $actionBy 操作人員ID
     * @param string|null $actionByName 操作人員姓名
     * @param string|null $reason 移除原因
     * @return bool|int 成功返回插入ID，失敗返回false
     */
    public function logRemoved(array $assignmentData, ?int $actionBy = null, ?string $actionByName = null, ?string $reason = null)
    {
        $historyData = [
            'assignment_id' => $assignmentData['id'] ?? null,
            'company_id' => $assignmentData['company_id'],
            'assessment_id' => $assignmentData['assessment_id'],
            'question_content_id' => $assignmentData['question_content_id'],
            'personnel_id' => $assignmentData['personnel_id'],
            'personnel_name' => $assignmentData['personnel_name'] ?? null,
            'personnel_department' => $assignmentData['personnel_department'] ?? null,
            'personnel_position' => $assignmentData['personnel_position'] ?? null,
            'assignment_status' => $assignmentData['assignment_status'] ?? null,
            'assignment_note' => $assignmentData['assignment_note'] ?? null,
            'action_type' => 'removed',
            'action_by' => $actionBy,
            'action_by_name' => $actionByName,
            'action_reason' => $reason,
            'action_at' => date('Y-m-d H:i:s'),
            'original_assigned_at' => $assignmentData['assigned_at'] ?? null,
        ];

        return $this->insert($historyData);
    }

    /**
     * 記錄指派更新操作
     *
     * @param array $assignmentData 更新後的指派資料
     * @param int|null $actionBy 操作人員ID
     * @param string|null $actionByName 操作人員姓名
     * @param string|null $reason 更新原因
     * @return bool|int 成功返回插入ID，失敗返回false
     */
    public function logUpdated(array $assignmentData, ?int $actionBy = null, ?string $actionByName = null, ?string $reason = null)
    {
        $historyData = [
            'assignment_id' => $assignmentData['id'] ?? null,
            'company_id' => $assignmentData['company_id'],
            'assessment_id' => $assignmentData['assessment_id'],
            'question_content_id' => $assignmentData['question_content_id'],
            'personnel_id' => $assignmentData['personnel_id'],
            'personnel_name' => $assignmentData['personnel_name'] ?? null,
            'personnel_department' => $assignmentData['personnel_department'] ?? null,
            'personnel_position' => $assignmentData['personnel_position'] ?? null,
            'assignment_status' => $assignmentData['assignment_status'] ?? null,
            'assignment_note' => $assignmentData['assignment_note'] ?? null,
            'action_type' => 'updated',
            'action_by' => $actionBy,
            'action_by_name' => $actionByName,
            'action_reason' => $reason,
            'action_at' => date('Y-m-d H:i:s'),
            'original_assigned_at' => $assignmentData['assigned_at'] ?? null,
        ];

        return $this->insert($historyData);
    }

    /**
     * 取得指定評估的歷史記錄
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估ID
     * @param array $filters 篩選條件
     * @return array 歷史記錄列表
     */
    public function getAssessmentHistory(int $companyId, int $assessmentId, array $filters = []): array
    {
        $builder = $this->where([
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
        ]);

        // 人員篩選
        if (isset($filters['personnel_id']) && !empty($filters['personnel_id'])) {
            $builder->where('personnel_id', $filters['personnel_id']);
        }

        // 題項內容篩選
        if (isset($filters['question_content_id']) && !empty($filters['question_content_id'])) {
            $builder->where('question_content_id', $filters['question_content_id']);
        }

        // 操作類型篩選
        if (isset($filters['action_type']) && !empty($filters['action_type'])) {
            $builder->where('action_type', $filters['action_type']);
        }

        // 時間範圍篩選
        if (isset($filters['start_date']) && !empty($filters['start_date'])) {
            $builder->where('action_at >=', $filters['start_date']);
        }
        if (isset($filters['end_date']) && !empty($filters['end_date'])) {
            $builder->where('action_at <=', $filters['end_date']);
        }

        return $builder->orderBy('action_at', 'DESC')->findAll();
    }

    /**
     * 取得指定題項內容的歷史記錄
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估ID
     * @param string|int $questionContentId 題項內容ID
     * @return array 歷史記錄列表
     */
    public function getContentHistory(int $companyId, int $assessmentId, $questionContentId): array
    {
        return $this->where([
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
            'question_content_id' => $questionContentId,
        ])->orderBy('action_at', 'DESC')->findAll();
    }

    /**
     * 取得指定人員的歷史記錄
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估ID
     * @param int $personnelId 人員ID
     * @return array 歷史記錄列表
     */
    public function getPersonnelHistory(int $companyId, int $assessmentId, int $personnelId): array
    {
        return $this->where([
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
            'personnel_id' => $personnelId,
        ])->orderBy('action_at', 'DESC')->findAll();
    }

    /**
     * 取得歷史統計資訊
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估ID
     * @return array 統計資訊
     */
    public function getHistoryStatistics(int $companyId, int $assessmentId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        $result = $builder
            ->select('action_type, COUNT(*) as count')
            ->where('company_id', $companyId)
            ->where('assessment_id', $assessmentId)
            ->groupBy('action_type')
            ->get()
            ->getResultArray();

        $stats = [
            'created' => 0,
            'removed' => 0,
            'updated' => 0,
            'total' => 0,
        ];

        foreach ($result as $row) {
            $stats[$row['action_type']] = (int)$row['count'];
            $stats['total'] += (int)$row['count'];
        }

        return $stats;
    }
}
