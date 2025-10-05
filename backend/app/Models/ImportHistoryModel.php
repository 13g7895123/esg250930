<?php

namespace App\Models;

use CodeIgniter\Model;

class ImportHistoryModel extends Model
{
    protected $table = 'import_history';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'template_id',
        'question_id',
        'import_type',
        'batch_id',
        'row_number',
        'status',
        'reason',
        'error_message',
        'inserted_id',
        'duplicate_id',
        'category_name',
        'topic_name',
        'factor_name',
        'factor_description',
        'data_json',
        'sql_preview',
        'created_at',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $deletedField = '';

    // Validation
    protected $validationRules = [
        'import_type' => 'required|in_list[template_content,question_content,template_structure,question_structure]',
        'batch_id' => 'required|max_length[50]',
        'row_number' => 'required|integer',
        'status' => 'required|in_list[success,skipped,error]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get import history by batch ID
     *
     * @param string $batchId
     * @return array
     */
    public function getByBatchId(string $batchId): array
    {
        return $this->where('batch_id', $batchId)
            ->orderBy('row_number', 'ASC')
            ->findAll();
    }

    /**
     * Get import history for a template with pagination
     *
     * @param int $templateId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTemplateHistory(int $templateId, int $limit = 20, int $offset = 0): array
    {
        return $this->where('template_id', $templateId)
            ->where('import_type', 'template_content')
            ->orderBy('created_at', 'DESC')
            ->orderBy('row_number', 'ASC')
            ->findAll($limit, $offset);
    }

    /**
     * Get unique batches for a template
     *
     * @param int $templateId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTemplateBatches(int $templateId, int $limit = 20, int $offset = 0): array
    {
        return $this->select('batch_id, MIN(created_at) as created_at, COUNT(*) as total_rows')
            ->select('SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count', false)
            ->select('SUM(CASE WHEN status = "skipped" THEN 1 ELSE 0 END) as skipped_count', false)
            ->select('SUM(CASE WHEN status = "error" THEN 1 ELSE 0 END) as error_count', false)
            ->where('template_id', $templateId)
            ->where('import_type', 'template_content')
            ->groupBy('batch_id')
            ->orderBy('MIN(created_at)', 'DESC')
            ->findAll($limit, $offset);
    }

    /**
     * Count total batches for a template
     *
     * @param int $templateId
     * @return int
     */
    public function countTemplateBatches(int $templateId): int
    {
        return $this->select('batch_id')
            ->where('template_id', $templateId)
            ->where('import_type', 'template_content')
            ->groupBy('batch_id')
            ->countAllResults();
    }

    /**
     * Get latest batch for a template
     *
     * @param int $templateId
     * @return array|null
     */
    public function getLatestBatch(int $templateId): ?array
    {
        $result = $this->select('batch_id')
            ->where('template_id', $templateId)
            ->where('import_type', 'template_content')
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->first();

        if (!$result) {
            return null;
        }

        // Get all records for this batch
        return $this->getByBatchId($result['batch_id']);
    }

    /**
     * Save import batch
     *
     * @param array $records Array of import records
     * @return bool
     */
    public function saveBatch(array $records): bool
    {
        if (empty($records)) {
            return false;
        }

        // Use batch insert for better performance
        return $this->insertBatch($records);
    }

    /**
     * Delete old import history (keep last N batches)
     *
     * @param int $templateId
     * @param int $keepBatches Number of batches to keep
     * @return int Number of deleted records
     */
    public function cleanOldHistory(int $templateId, int $keepBatches = 50): int
    {
        // Get batch IDs to keep
        $keepBatchIds = $this->select('batch_id')
            ->where('template_id', $templateId)
            ->where('import_type', 'template_content')
            ->groupBy('batch_id')
            ->orderBy('MIN(created_at)', 'DESC')
            ->limit($keepBatches)
            ->findColumn('batch_id');

        if (empty($keepBatchIds)) {
            return 0;
        }

        // Delete records not in the keep list
        return $this->where('template_id', $templateId)
            ->where('import_type', 'template_content')
            ->whereNotIn('batch_id', $keepBatchIds)
            ->delete();
    }
}
