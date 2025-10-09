<?php

namespace App\Models\RiskAssessment;

use CodeIgniter\Model;

class RiskTopicModel extends Model
{
    protected $table = 'risk_topics';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'template_id',
        'category_id',
        'topic_name',
        'topic_code',
        'description',
        'sort_order',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'template_id' => 'required|integer',
        'category_id' => 'required|integer',
        'topic_name' => 'required|max_length[255]',
        'topic_code' => 'permit_empty|max_length[50]',
        'description' => 'permit_empty',
        'sort_order' => 'permit_empty|integer',
        'status' => 'permit_empty|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'template_id' => [
            'required' => 'Template ID is required',
            'integer' => 'Template ID must be an integer'
        ],
        'category_id' => [
            'required' => 'Category ID is required',
            'integer' => 'Category ID must be an integer'
        ],
        'topic_name' => [
            'required' => 'Topic name is required',
            'max_length' => 'Topic name cannot exceed 255 characters'
        ],
        'topic_code' => [
            'max_length' => 'Topic code cannot exceed 50 characters'
        ],
        'status' => [
            'in_list' => 'Status must be active or inactive'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get topics for a specific template with category information
     */
    public function getTopicsWithCategory($templateId, $search = null)
    {
        $builder = $this->select('
                risk_topics.*,
                risk_categories.category_name,
                (SELECT COUNT(*) FROM risk_factors WHERE topic_id = risk_topics.id) as factor_count
            ')
            ->join('risk_categories', 'risk_categories.id = risk_topics.category_id')
            ->where('risk_topics.template_id', $templateId)
            ->orderBy('risk_topics.sort_order', 'ASC')
            ->orderBy('risk_topics.id', 'ASC');

        // Search condition
        if (!empty($search)) {
            $builder->groupStart()
                ->like('risk_topics.topic_name', $search)
                ->orLike('risk_categories.category_name', $search)
                ->groupEnd();
        }

        return $builder->findAll();
    }

    /**
     * Get topics for a specific category
     */
    public function getTopicsByCategory($templateId, $categoryId)
    {
        return $this->where('template_id', $templateId)
            ->where('category_id', $categoryId)
            ->where('status', 'active')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Check if topic code is unique within template
     */
    public function isTopicCodeUniqueInTemplate($templateId, $topicCode, $excludeId = null)
    {
        if (empty($topicCode)) {
            return true; // Allow empty codes
        }

        $builder = $this->where('template_id', $templateId)
            ->where('topic_code', $topicCode);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first() === null;
    }

    /**
     * Get next sort order for a template
     */
    public function getNextSortOrder($templateId)
    {
        $result = $this->selectMax('sort_order')
            ->where('template_id', $templateId)
            ->first();

        return ($result['sort_order'] ?? 0) + 1;
    }

    /**
     * Reorder topics within a template
     * Expects $topicOrders to be an array of objects with 'id' and 'sort_order' keys
     */
    public function reorderTopics($templateId, $topicOrders)
    {
        $this->db->transStart();

        try {
            // First verify all topics belong to this template
            $topicIds = array_column($topicOrders, 'id');
            $existingTopics = $this->whereIn('id', $topicIds)
                ->where('template_id', $templateId)
                ->findAll();

            if (count($existingTopics) !== count($topicIds)) {
                throw new \Exception("部分主題不存在或不屬於此範本");
            }

            // Prepare batch update data
            $currentTime = date('Y-m-d H:i:s');
            $batchData = [];

            foreach ($topicOrders as $order) {
                $batchData[] = [
                    'id' => (int)$order['id'],
                    'sort_order' => (int)$order['sort_order'],
                    'updated_at' => $currentTime
                ];
            }

            // Use CI4's updateBatch for efficient batch update
            if (!empty($batchData)) {
                $this->db->table($this->table)->updateBatch($batchData, 'id');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('資料庫交易失敗');
            }

            return true;
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'RiskTopicModel::reorderTopics - ' . $e->getMessage());
            return false;
        }
    }
}