<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 外部人員資料模型
 *
 * 管理從外部 API 取得的人員基本資料
 * 提供人員資料的本地快取和查詢功能
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class ExternalPersonnelModel extends Model
{
    protected $table = 'external_personnel';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    // 允許插入/更新的欄位
    protected $allowedFields = [
        'external_id',
        'company_id',
        'external_company_id',
        'name',
        'email',
        'department',
        'position',
        'phone',
        'avatar',
        'status',
        'groups',
        'last_synced_at'
    ];

    // 自動處理時間戳記
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // 欄位格式轉換
    protected $casts = [
        'id' => 'integer',
        'company_id' => 'integer',
        'groups' => 'json',
        'last_synced_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // 驗證規則
    protected $validationRules = [
        'external_id' => 'required|string|max_length[50]',
        'company_id' => 'required|integer',
        'external_company_id' => 'required|string|max_length[50]',
        'name' => 'required|string|max_length[255]',
        'email' => 'permit_empty|valid_email|max_length[255]',
        'department' => 'permit_empty|string|max_length[255]',
        'position' => 'permit_empty|string|max_length[255]',
        'phone' => 'permit_empty|string|max_length[50]',
        'avatar' => 'permit_empty|string|max_length[500]',
        'status' => 'permit_empty|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'external_id' => [
            'required' => '外部人員ID為必填項目',
            'max_length' => '外部人員ID長度不可超過50字元'
        ],
        'company_id' => [
            'required' => '公司ID為必填項目',
            'integer' => '公司ID必須為整數'
        ],
        'external_company_id' => [
            'required' => '外部公司ID為必填項目',
            'max_length' => '外部公司ID長度不可超過50字元'
        ],
        'name' => [
            'required' => '人員姓名為必填項目',
            'max_length' => '人員姓名長度不可超過255字元'
        ],
        'email' => [
            'valid_email' => '請輸入有效的電子郵件地址',
            'max_length' => '電子郵件地址長度不可超過255字元'
        ]
    ];

    /**
     * 根據公司ID取得所有啟用的人員
     *
     * @param int $companyId 公司ID
     * @return array 人員資料陣列
     */
    public function getActivePersonnelByCompany(int $companyId): array
    {
        return $this->where([
            'company_id' => $companyId,
            'status' => 'active'
        ])->orderBy('name', 'ASC')->findAll();
    }

    /**
     * 根據外部ID和公司ID查找人員
     *
     * @param string $externalId 外部人員ID
     * @param int $companyId 公司ID
     * @return array|null 人員資料
     */
    public function findByExternalId(string $externalId, int $companyId): ?array
    {
        return $this->where([
            'external_id' => $externalId,
            'company_id' => $companyId
        ])->first();
    }

    /**
     * 只根據外部ID查找人員（用於用戶ID映射）
     *
     * @param string $externalId 外部人員ID
     * @return array|null 人員資料
     */
    public function findByExternalIdOnly(string $externalId): ?array
    {
        return $this->where('external_id', $externalId)->first();
    }

    /**
     * 批量同步人員資料
     *
     * @param int $companyId 公司ID
     * @param array $personnelData 人員資料陣列（已轉換格式）
     * @return int 同步的人員數量
     */
    public function syncPersonnelData(int $companyId, array $personnelData): int
    {
        $syncedCount = 0;
        $currentTime = date('Y-m-d H:i:s');

        log_message('debug', "開始同步人員資料到資料庫 - 公司ID: {$companyId}, 人員數量: " . count($personnelData));

        foreach ($personnelData as $person) {
            try {
                // 處理已轉換格式的資料
                $data = [
                    'external_id' => $person['external_id'] ?? '',
                    'company_id' => $companyId,
                    'external_company_id' => $person['external_company_id'] ?? '',
                    'name' => $person['name'] ?? '',
                    'email' => $person['email'] ?? '',
                    'department' => $person['department'] ?? '',
                    'position' => $person['position'] ?? '',
                    'phone' => $person['phone'] ?? '',
                    'avatar' => $person['avatar'] ?? null,
                    'status' => $person['status'] ?? 'active',
                    'groups' => is_array($person['groups']) ? json_encode($person['groups']) : $person['groups'],
                    'last_synced_at' => $currentTime
                ];

                // 驗證必填欄位
                if (empty($data['external_id']) || empty($data['name'])) {
                    log_message('warning', "跳過人員同步 - 缺少必要資料: " . json_encode($person));
                    continue;
                }

                // 檢查是否已存在
                $existing = $this->findByExternalId($data['external_id'], $companyId);

                if ($existing) {
                    // 更新現有記錄
                    $result = $this->update($existing['id'], $data);
                    if ($result) {
                        log_message('debug', "更新人員資料: {$data['name']} (ID: {$data['external_id']})");
                        $syncedCount++;
                    } else {
                        log_message('error', "更新人員資料失敗: " . json_encode($this->errors()));
                    }
                } else {
                    // 新增記錄
                    $result = $this->insert($data);
                    if ($result) {
                        log_message('debug', "新增人員資料: {$data['name']} (ID: {$data['external_id']})");
                        $syncedCount++;
                    } else {
                        log_message('error', "新增人員資料失敗: " . json_encode($this->errors()));
                    }
                }

            } catch (\Exception $e) {
                log_message('error', "同步人員資料時發生錯誤: " . $e->getMessage() . " 資料: " . json_encode($person));
            }
        }

        log_message('info', "人員資料同步完成 - 處理: " . count($personnelData) . " 位，成功同步: {$syncedCount} 位");

        return $syncedCount;
    }

    /**
     * 取得需要同步的人員（超過指定時間未同步）
     *
     * @param int $companyId 公司ID
     * @param int $hoursAgo 幾小時前，預設為24小時
     * @return array 需要同步的人員資料
     */
    public function getPersonnelNeedingSync(int $companyId, int $hoursAgo = 24): array
    {
        $threshold = date('Y-m-d H:i:s', strtotime("-{$hoursAgo} hours"));

        return $this->where('company_id', $companyId)
                   ->groupStart()
                       ->where('last_synced_at IS NULL')
                       ->orWhere('last_synced_at <', $threshold)
                   ->groupEnd()
                   ->findAll();
    }

    /**
     * 根據部門統計人員數量
     *
     * @param int $companyId 公司ID
     * @return array 部門統計資料
     */
    public function getPersonnelCountByDepartment(int $companyId): array
    {
        return $this->select('department, COUNT(*) as count')
                   ->where([
                       'company_id' => $companyId,
                       'status' => 'active'
                   ])
                   ->groupBy('department')
                   ->orderBy('count', 'DESC')
                   ->findAll();
    }

    /**
     * 搜尋人員
     *
     * @param int $companyId 公司ID
     * @param string $keyword 搜尋關鍵字
     * @param array $filters 額外篩選條件
     * @return array 搜尋結果
     */
    public function searchPersonnel(int $companyId, string $keyword = '', array $filters = []): array
    {
        $builder = $this->where('company_id', $companyId);

        // 關鍵字搜尋
        if (!empty($keyword)) {
            $builder->groupStart()
                   ->like('name', $keyword)
                   ->orLike('email', $keyword)
                   ->orLike('department', $keyword)
                   ->orLike('position', $keyword)
                   ->groupEnd();
        }

        // 額外篩選條件
        if (isset($filters['department']) && !empty($filters['department'])) {
            $builder->where('department', $filters['department']);
        }

        if (isset($filters['position']) && !empty($filters['position'])) {
            $builder->where('position', $filters['position']);
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        } else {
            // 預設只顯示啟用的人員
            $builder->where('status', 'active');
        }

        return $builder->orderBy('name', 'ASC')->findAll();
    }
}