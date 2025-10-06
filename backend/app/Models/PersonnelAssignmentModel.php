<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 人員指派模型
 *
 * 管理人員對題項內容的指派關係
 * 替代目前 localStorage 的指派資料存儲方式
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class PersonnelAssignmentModel extends Model
{
    protected $table = 'personnel_assignments';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    // 允許插入/更新的欄位
    protected $allowedFields = [
        'company_id',
        'assessment_id',
        'question_content_id',
        'personnel_id',
        'personnel_name',
        'personnel_department',
        'personnel_position',
        'assignment_status',
        'assignment_note',
        'assigned_by',
        'assigned_at',
        'accepted_at',
        'completed_at'
    ];

    // 自動處理時間戳記
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // 欄位格式轉換
    protected $casts = [
        'id' => 'integer',
        'company_id' => 'integer',
        'assessment_id' => 'integer',
        'question_content_id' => 'string',  // 支援UUID字串格式
        'personnel_id' => 'integer',
        'assigned_by' => '?integer',
        'assigned_at' => 'datetime',
        'accepted_at' => '?datetime',
        'completed_at' => '?datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // 驗證規則
    protected $validationRules = [
        'company_id' => 'required|integer',
        'assessment_id' => 'required|integer',
        'question_content_id' => 'required',  // 支援UUID字串格式
        'personnel_id' => 'required|integer',
        'personnel_name' => 'required|string|max_length[255]',
        'personnel_department' => 'permit_empty|string|max_length[255]',
        'personnel_position' => 'permit_empty|string|max_length[255]',
        'assignment_status' => 'permit_empty|in_list[assigned,accepted,declined,completed]'
    ];

    protected $validationMessages = [
        'company_id' => [
            'required' => '公司ID為必填項目',
            'integer' => '公司ID必須為整數'
        ],
        'assessment_id' => [
            'required' => '評估記錄ID為必填項目',
            'integer' => '評估記錄ID必須為整數'
        ],
        'question_content_id' => [
            'required' => '題項內容ID為必填項目'
        ],
        'personnel_id' => [
            'required' => '人員ID為必填項目',
            'integer' => '人員ID必須為整數'
        ],
        'personnel_name' => [
            'required' => '人員姓名為必填項目',
            'max_length' => '人員姓名長度不可超過255字元'
        ]
    ];

    /**
     * 為題項內容指派人員
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @param string|int $questionContentId 題項內容ID（支援UUID字串格式）
     * @param array $personnelData 人員資料
     * @param int|null $assignedBy 指派者ID
     * @return bool|int 成功返回指派ID，失敗返回false
     */
    public function assignPersonnelToContent(
        int $companyId,
        int $assessmentId,
        string|int $questionContentId,
        array $personnelData,
        ?int $assignedBy = null
    ) {
        // 檢查是否已經指派
        if ($this->isPersonnelAssigned($companyId, $assessmentId, $questionContentId, $personnelData['id'])) {
            return false; // 已存在指派關係
        }

        $data = [
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
            'question_content_id' => $questionContentId,
            'personnel_id' => $personnelData['id'],
            'personnel_name' => $personnelData['name'],
            'personnel_department' => $personnelData['department'] ?? null,
            'personnel_position' => $personnelData['position'] ?? null,
            'assignment_status' => 'assigned',
            'assigned_by' => $assignedBy,
            'assigned_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    /**
     * 檢查人員是否已被指派到特定題項內容
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @param string|int $questionContentId 題項內容ID（支援UUID字串格式）
     * @param int $personnelId 人員ID
     * @return bool 是否已指派
     */
    public function isPersonnelAssigned(
        int $companyId,
        int $assessmentId,
        string|int $questionContentId,
        int $personnelId
    ): bool {
        return $this->where([
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
            'question_content_id' => $questionContentId,
            'personnel_id' => $personnelId
        ])->first() !== null;
    }

    /**
     * 取得評估記錄的所有指派關係
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @return array 指派關係陣列
     */
    public function getAssignmentsByAssessment(int $companyId, int $assessmentId): array
    {
        return $this->where([
            'company_id' => $companyId,
            'assessment_id' => $assessmentId
        ])->orderBy('assigned_at', 'DESC')->findAll();
    }

    /**
     * 取得特定題項內容的指派人員
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @param string|int $questionContentId 題項內容ID（支援UUID字串格式）
     * @return array 指派人員陣列
     */
    public function getAssignmentsByContent(
        int $companyId,
        int $assessmentId,
        string|int $questionContentId
    ): array {
        return $this->where([
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
            'question_content_id' => $questionContentId
        ])->findAll();
    }

    /**
     * 取得人員的所有指派工作
     *
     * @param int $companyId 公司ID
     * @param int $personnelId 人員ID
     * @param array $filters 額外篩選條件
     * @return array 指派工作陣列
     */
    public function getAssignmentsByPersonnel(
        int $companyId,
        int $personnelId,
        array $filters = []
    ): array {
        $builder = $this->where([
            'company_id' => $companyId,
            'personnel_id' => $personnelId
        ]);

        // 狀態篩選
        if (isset($filters['status']) && !empty($filters['status'])) {
            $builder->where('assignment_status', $filters['status']);
        }

        // 評估記錄篩選
        if (isset($filters['assessment_id']) && !empty($filters['assessment_id'])) {
            $builder->where('assessment_id', $filters['assessment_id']);
        }

        return $builder->orderBy('assigned_at', 'DESC')->findAll();
    }

    /**
     * 移除人員與題項內容的指派關係
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @param string|int $questionContentId 題項內容ID（支援UUID字串格式）
     * @param int $personnelId 人員ID
     * @return bool 是否成功移除
     */
    public function removeAssignment(
        int $companyId,
        int $assessmentId,
        string|int $questionContentId,
        int $personnelId
    ): bool {
        return $this->where([
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
            'question_content_id' => $questionContentId,
            'personnel_id' => $personnelId
        ])->delete();
    }

    /**
     * 移除人員在整個評估記錄中的所有指派
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @param int $personnelId 人員ID
     * @return bool 是否成功移除
     */
    public function removePersonnelFromAssessment(
        int $companyId,
        int $assessmentId,
        int $personnelId
    ): bool {
        return $this->where([
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
            'personnel_id' => $personnelId
        ])->delete();
    }

    /**
     * 批量指派人員到多個題項內容
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @param array $questionContentIds 題項內容ID陣列
     * @param array $personnelData 人員資料
     * @param int|null $assignedBy 指派者ID
     * @return int 成功指派的數量
     */
    public function batchAssignPersonnel(
        int $companyId,
        int $assessmentId,
        array $questionContentIds,
        array $personnelData,
        ?int $assignedBy = null
    ): int {
        $assignedCount = 0;

        foreach ($questionContentIds as $contentId) {
            if ($this->assignPersonnelToContent(
                $companyId,
                $assessmentId,
                $contentId,
                $personnelData,
                $assignedBy
            )) {
                $assignedCount++;
            }
        }

        return $assignedCount;
    }

    /**
     * 更新指派狀態
     *
     * @param int $assignmentId 指派記錄ID
     * @param string $status 新狀態
     * @return bool 是否成功更新
     */
    public function updateAssignmentStatus(int $assignmentId, string $status): bool
    {
        $updateData = ['assignment_status' => $status];

        // 根據狀態設置對應的時間戳記
        switch ($status) {
            case 'accepted':
                $updateData['accepted_at'] = date('Y-m-d H:i:s');
                break;
            case 'completed':
                $updateData['completed_at'] = date('Y-m-d H:i:s');
                break;
        }

        return $this->update($assignmentId, $updateData);
    }

    /**
     * 取得評估記錄的指派統計
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @return array 統計資料
     */
    public function getAssignmentStatistics(int $companyId, int $assessmentId): array
    {
        $assignments = $this->getAssignmentsByAssessment($companyId, $assessmentId);

        $stats = [
            'total_assignments' => count($assignments),
            'unique_personnel' => 0,
            'unique_contents' => 0,
            'status_counts' => [
                'assigned' => 0,
                'accepted' => 0,
                'declined' => 0,
                'completed' => 0
            ]
        ];

        $uniquePersonnel = [];
        $uniqueContents = [];

        foreach ($assignments as $assignment) {
            // 統計狀態
            $status = $assignment['assignment_status'];
            if (isset($stats['status_counts'][$status])) {
                $stats['status_counts'][$status]++;
            }

            // 統計唯一人員
            $uniquePersonnel[$assignment['personnel_id']] = true;

            // 統計唯一內容
            $uniqueContents[$assignment['question_content_id']] = true;
        }

        $stats['unique_personnel'] = count($uniquePersonnel);
        $stats['unique_contents'] = count($uniqueContents);

        return $stats;
    }

    /**
     * 取得人員的指派摘要（包含詳細資訊）
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @return array 人員指派摘要
     */
    public function getPersonnelAssignmentSummary(int $companyId, int $assessmentId): array
    {
        $sql = "
            SELECT
                personnel_id,
                personnel_name,
                personnel_department,
                personnel_position,
                COUNT(*) as assignment_count,
                SUM(CASE WHEN assignment_status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                MIN(assigned_at) as first_assigned_at,
                MAX(assigned_at) as last_assigned_at
            FROM {$this->table}
            WHERE company_id = ? AND assessment_id = ?
            GROUP BY personnel_id, personnel_name, personnel_department, personnel_position
            ORDER BY personnel_name
        ";

        return $this->db->query($sql, [$companyId, $assessmentId])->getResultArray();
    }

    /**
     * 除錯方法：取得評估記錄的詳細指派資訊
     *
     * @param int $assessmentId 評估記錄ID
     * @param int|null $userId 特定用戶ID (可選)
     * @return array 除錯資訊
     */
    public function getAssignmentDebugInfo(int $assessmentId, ?int $userId = null): array
    {
        // 取得該評估記錄的所有指派記錄
        $allAssignments = $this->where('assessment_id', $assessmentId)->findAll();

        // 取得特定用戶的指派記錄
        $userAssignments = [];
        if ($userId !== null) {
            $userAssignments = $this->where([
                'assessment_id' => $assessmentId,
                'personnel_id' => $userId
            ])->findAll();
        }

        // 統計狀態分佈
        $statusDistribution = [];
        foreach ($allAssignments as $assignment) {
            $status = $assignment['assignment_status'];
            $statusDistribution[$status] = ($statusDistribution[$status] ?? 0) + 1;
        }

        // 準備除錯資訊
        $debugInfo = [
            'assessment_id' => $assessmentId,
            'user_id' => $userId,
            'total_assignments' => count($allAssignments),
            'user_assignments' => count($userAssignments),
            'assessment_assignments' => count($allAssignments),
            'matching_assignments' => count($userAssignments),
            'status_distribution' => $statusDistribution,
            'user_assignment_details' => $userAssignments,
            'all_assignments_sample' => array_slice($allAssignments, 0, 5), // 前5筆作為樣本
        ];

        // 記錄除錯資訊到日誌
        log_message('info', '=== Personnel Assignment Debug Info ===');
        log_message('info', "Assessment ID: {$assessmentId}");
        log_message('info', "User ID: " . ($userId ?? 'null'));
        log_message('info', "Total assignments for assessment: " . count($allAssignments));
        log_message('info', "User assignments: " . count($userAssignments));
        log_message('info', "Status distribution: " . json_encode($statusDistribution));

        if ($userId !== null && count($userAssignments) === 0) {
            log_message('warning', "⚠️ No assignments found for user {$userId} in assessment {$assessmentId}");

            // 檢查該用戶是否在其他評估中有指派記錄
            $otherAssignments = $this->where('personnel_id', $userId)->findAll();
            log_message('info', "User {$userId} has " . count($otherAssignments) . " assignments in total across all assessments");

            if (count($otherAssignments) > 0) {
                $otherAssessmentIds = array_unique(array_column($otherAssignments, 'assessment_id'));
                log_message('info', "User {$userId} has assignments in assessment IDs: " . implode(', ', $otherAssessmentIds));
            }
        }

        return $debugInfo;
    }

    /**
     * 除錯方法：檢查指派記錄是否符合預期
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @param int $personnelId 人員ID
     * @return array 檢查結果
     */
    public function validateAssignmentExpected(
        int $companyId,
        int $assessmentId,
        int $personnelId
    ): array {
        // 檢查該人員在該評估中的指派記錄
        $assignments = $this->where([
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
            'personnel_id' => $personnelId
        ])->findAll();

        // 檢查該人員是否存在於 external_personnel 表
        $db = \Config\Database::connect();
        $personnelQuery = "SELECT * FROM external_personnel WHERE id = ?";
        $personnelExists = $db->query($personnelQuery, [$personnelId])->getResultArray();

        $result = [
            'personnel_id' => $personnelId,
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
            'assignments_found' => count($assignments),
            'personnel_exists' => count($personnelExists) > 0,
            'personnel_info' => $personnelExists[0] ?? null,
            'assignment_details' => $assignments,
            'status_summary' => []
        ];

        // 統計狀態分佈
        foreach ($assignments as $assignment) {
            $status = $assignment['assignment_status'];
            $result['status_summary'][$status] = ($result['status_summary'][$status] ?? 0) + 1;
        }

        log_message('info', "=== Assignment Validation ===");
        log_message('info', "Company: {$companyId}, Assessment: {$assessmentId}, Personnel: {$personnelId}");
        log_message('info', "Assignments found: " . count($assignments));
        log_message('info', "Personnel exists: " . ($result['personnel_exists'] ? 'Yes' : 'No'));

        return $result;
    }
}