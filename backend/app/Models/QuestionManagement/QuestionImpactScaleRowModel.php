<?php

namespace App\Models\QuestionManagement;

use CodeIgniter\Model;

/**
 * 題項財務衝擊量表資料列模型
 */
class QuestionImpactScaleRowModel extends Model
{
    protected $table = 'question_impact_scale_rows';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'scale_id',
        'impact_level',
        'score_range',
        'dynamic_fields',
        'sort_order'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'scale_id' => 'required|integer',
        'impact_level' => 'permit_empty|max_length[255]',
        'score_range' => 'permit_empty|max_length[50]',
        'sort_order' => 'permit_empty|integer'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;

    // Before insert/update callbacks to handle JSON encoding
    protected $beforeInsert = ['encodeJson'];
    protected $beforeUpdate = ['encodeJson'];
    protected $afterFind = ['decodeJson'];

    /**
     * Encode JSON fields before saving
     */
    protected function encodeJson(array $data): array
    {
        if (isset($data['data']['dynamic_fields']) && is_array($data['data']['dynamic_fields'])) {
            $data['data']['dynamic_fields'] = json_encode($data['data']['dynamic_fields']);
        }
        return $data;
    }

    /**
     * Decode JSON fields after fetching
     */
    protected function decodeJson(array $data): array
    {
        if (isset($data['data'])) {
            // Single row
            if (isset($data['data']['dynamic_fields']) && is_string($data['data']['dynamic_fields'])) {
                $data['data']['dynamic_fields'] = json_decode($data['data']['dynamic_fields'], true);
            }
        } elseif (isset($data['data']) === false) {
            // Multiple rows
            foreach ($data as &$row) {
                if (isset($row['dynamic_fields']) && is_string($row['dynamic_fields'])) {
                    $row['dynamic_fields'] = json_decode($row['dynamic_fields'], true);
                }
            }
        }
        return $data;
    }

    /**
     * 複製範本量表資料列到題項量表資料列
     *
     * @param int $newScaleId 新量表ID
     * @param array $templateRows 範本量表資料列陣列
     * @return bool 是否成功
     */
    public function copyFromTemplateRows(int $newScaleId, array $templateRows): bool
    {
        foreach ($templateRows as $row) {
            $newRowData = [
                'scale_id' => $newScaleId,
                'impact_level' => $row['impact_level'] ?? null,
                'score_range' => $row['score_range'] ?? null,
                'dynamic_fields' => $row['dynamic_fields'] ?? [],
                'sort_order' => $row['sort_order'] ?? 0
            ];

            if (!$this->insert($newRowData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 取得量表的所有資料列
     *
     * @param int $scaleId 量表ID
     * @return array 資料列陣列
     */
    public function getByScale(int $scaleId): array
    {
        return $this->where('scale_id', $scaleId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }
}