<?php

namespace App\Models\QuestionManagement;

use CodeIgniter\Model;

/**
 * 題項財務衝擊量表欄位定義模型
 */
class QuestionImpactScaleColumnModel extends Model
{
    protected $table = 'question_impact_scale_columns';
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

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'scale_id' => 'required|integer',
        'column_id' => 'required|integer',
        'name' => 'required|max_length[255]',
        'amount_note' => 'permit_empty|max_length[255]',
        'removable' => 'permit_empty|in_list[0,1]',
        'sort_order' => 'permit_empty|integer'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;

    /**
     * 複製範本量表欄位到題項量表欄位
     *
     * @param int $newScaleId 新量表ID
     * @param array $templateColumns 範本量表欄位陣列
     * @return bool 是否成功
     */
    public function copyFromTemplateColumns(int $newScaleId, array $templateColumns): bool
    {
        foreach ($templateColumns as $column) {
            $newColumnData = [
                'scale_id' => $newScaleId,
                'column_id' => $column['column_id'],
                'name' => $column['name'],
                'amount_note' => $column['amount_note'] ?? null,
                'removable' => $column['removable'] ?? 1,
                'sort_order' => $column['sort_order'] ?? 0
            ];

            if (!$this->insert($newColumnData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 取得量表的所有欄位定義
     *
     * @param int $scaleId 量表ID
     * @return array 欄位定義陣列
     */
    public function getByScale(int $scaleId): array
    {
        return $this->where('scale_id', $scaleId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }
}