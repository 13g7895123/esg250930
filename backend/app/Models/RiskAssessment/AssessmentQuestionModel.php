<?php

namespace App\Models\RiskAssessment;

use CodeIgniter\Model;

class AssessmentQuestionModel extends Model
{
    protected $table = 'assessment_questions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'template_id',
        'category_id',
        'topic_id',
        'risk_factor_id',
        'question_title',
        'question_description',
        'question_type',
        'scoring_method',
        'scoring_options',
        'weight',
        'sort_order',
        'status'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'integer',
        'template_id' => 'integer',
        'category_id' => 'integer',
        'topic_id' => '?integer',
        'risk_factor_id' => 'integer',
        'weight' => 'float',
        'sort_order' => 'integer'
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'template_id' => 'required|integer',
        'category_id' => 'required|integer',
        'risk_factor_id' => 'required|integer',
        'question_title' => 'permit_empty|max_length[255]',
        'question_description' => 'required',
        'question_type' => 'required|in_list[single_choice,multiple_choice,scale_rating,text_input,file_upload]',
        'scoring_method' => 'required|in_list[binary,scale_1_5,scale_1_10,percentage,custom]',
        'weight' => 'permit_empty|decimal',
        'sort_order' => 'permit_empty|integer',
        'status' => 'permit_empty|in_list[active,inactive,draft]'
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
     * Get assessment questions for a template with related data
     *
     * @param int $templateId Template ID
     * @param array $params Query parameters
     * @return array
     */
    public function getQuestionsWithRelations($templateId, $params = [])
    {
        $builder = $this->db->table($this->table . ' aq')
            ->select('
                aq.*,
                rc.category_name,
                rt.topic_name,
                rf.factor_name
            ')
            ->join('risk_categories rc', 'rc.id = aq.category_id', 'left')
            ->join('risk_topics rt', 'rt.id = aq.topic_id', 'left')
            ->join('risk_factors rf', 'rf.id = aq.risk_factor_id', 'left')
            ->where('aq.template_id', $templateId);

        // Add search functionality
        if (!empty($params['search'])) {
            $search = $params['search'];
            $builder->groupStart()
                ->like('aq.question_title', $search)
                ->orLike('aq.question_description', $search)
                ->orLike('rf.factor_name', $search)
                ->groupEnd();
        }

        // Add category filter
        if (!empty($params['category_id'])) {
            $builder->where('aq.category_id', $params['category_id']);
        }

        // Add topic filter
        if (!empty($params['topic_id'])) {
            $builder->where('aq.topic_id', $params['topic_id']);
        }

        // Add status filter
        if (!empty($params['status'])) {
            $builder->where('aq.status', $params['status']);
        } else {
            $builder->where('aq.status', 'active');
        }

        // Add sorting - 最新建立的題目放在最上面
        $sort = $params['sort'] ?? 'created_at';
        $order = $params['order'] ?? 'DESC';

        if (in_array($sort, $this->allowedFields) || in_array($sort, ['category_name', 'topic_name', 'factor_name', 'created_at', 'updated_at'])) {
            $builder->orderBy("aq.$sort", $order);
        }

        return $builder;
    }

    /**
     * Get single question with relations
     *
     * @param int $templateId Template ID
     * @param int $questionId Question ID
     * @return array|null
     */
    public function getQuestionWithRelations($templateId, $questionId)
    {
        $result = $this->getQuestionsWithRelations($templateId)
            ->where('aq.id', $questionId)
            ->get()
            ->getRowArray();

        return $result;
    }

    /**
     * Reorder questions for a template
     *
     * @param int $templateId Template ID
     * @param array $questionIds Array of question IDs in new order
     * @return bool
     */
    public function reorderQuestions($templateId, $questionIds)
    {
        $this->db->transStart();

        foreach ($questionIds as $index => $questionId) {
            $this->where('template_id', $templateId)
                 ->where('id', $questionId)
                 ->set('sort_order', $index + 1)
                 ->update();
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Get questions count by template
     *
     * @param int $templateId Template ID
     * @return int
     */
    public function getQuestionsCount($templateId)
    {
        return $this->where('template_id', $templateId)
                   ->where('status', 'active')
                   ->countAllResults();
    }

    /**
     * Duplicate questions from one template to another
     *
     * @param int $sourceTemplateId Source template ID
     * @param int $targetTemplateId Target template ID
     * @return bool
     */
    public function duplicateQuestions($sourceTemplateId, $targetTemplateId)
    {
        $sourceQuestions = $this->where('template_id', $sourceTemplateId)
                               ->where('status', 'active')
                               ->orderBy('sort_order', 'ASC')
                               ->findAll();

        if (empty($sourceQuestions)) {
            return true;
        }

        $this->db->transStart();

        foreach ($sourceQuestions as $question) {
            unset($question['id']);
            $question['template_id'] = $targetTemplateId;
            $question['created_at'] = date('Y-m-d H:i:s');
            $question['updated_at'] = date('Y-m-d H:i:s');

            $this->insert($question);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}