<?php

namespace App\Controllers\Api\V1\QuestionManagement;

use App\Controllers\Api\BaseController;
use App\Models\CompanyAssessmentModel;
use App\Models\QuestionManagement\QuestionCategoryModel;
use App\Models\QuestionManagement\QuestionTopicModel;
use App\Models\QuestionManagement\QuestionFactorModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 題項架構管理控制器
 *
 * 專門處理題項管理中的架構管理功能，包括：
 * - 風險分類的 CRUD 操作
 * - 風險主題的 CRUD 操作
 * - 風險因子的 CRUD 操作
 * - 架構層級的排序管理
 *
 * 與範本管理的架構控制器完全獨立
 *
 * @package App\Controllers\Api\V1\QuestionManagement
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class QuestionStructureController extends BaseController
{
    protected $assessmentModel;
    protected $categoryModel;
    protected $topicModel;
    protected $factorModel;


    public function __construct()
    {
        $this->assessmentModel = new CompanyAssessmentModel();
        $this->categoryModel = new QuestionCategoryModel();
        $this->topicModel = new QuestionTopicModel();
        $this->factorModel = new QuestionFactorModel();
    }

    // =============================================
    // 風險分類管理
    // =============================================

    /**
     * 取得評估記錄的所有風險分類
     * GET /api/v1/question-management/assessment/{assessmentId}/categories
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getCategories($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Assessment ID is required'
                ]);
            }

            // 使用資料庫模型取得分類資料
            $categories = $this->categoryModel->getCategoriesByAssessment($assessmentId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::getCategories - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error getting risk categories'
            ]);
        }
    }

    /**
     * 新增風險分類
     * POST /api/v1/question-management/assessment/{assessmentId}/categories
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function createCategory($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            $input = $this->request->getJSON(true);
            $input['assessment_id'] = $assessmentId;

            // 如果沒有提供排序，自動取得下一個排序號
            if (!isset($input['sort_order'])) {
                $input['sort_order'] = $this->categoryModel->getNextSortOrder($assessmentId);
            }

            $categoryId = $this->categoryModel->insert($input);

            if (!$categoryId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '新增風險分類失敗',
                    'errors' => $this->categoryModel->errors()
                ]);
            }

            $category = $this->categoryModel->getCategoryWithStats($categoryId);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '成功新增風險分類',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::createCategory - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '新增風險分類時發生錯誤'
            ]);
        }
    }

    /**
     * 更新風險分類
     * PUT /api/v1/question-management/categories/{categoryId}
     *
     * @param int|null $categoryId 分類ID
     * @return ResponseInterface
     */
    public function updateCategory($categoryId = null)
    {
        try {
            if (!$categoryId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '風險分類ID為必填項目'
                ]);
            }

            $input = $this->request->getJSON(true);

            $result = $this->categoryModel->update($categoryId, $input);

            if (!$result) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '更新風險分類失敗',
                    'errors' => $this->categoryModel->errors()
                ]);
            }

            $category = $this->categoryModel->getCategoryWithStats($categoryId);

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功更新風險分類',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::updateCategory - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新風險分類時發生錯誤'
            ]);
        }
    }

    /**
     * 刪除風險分類
     * DELETE /api/v1/question-management/categories/{categoryId}
     *
     * @param int|null $categoryId 分類ID
     * @return ResponseInterface
     */
    public function deleteCategory($categoryId = null)
    {
        try {
            if (!$categoryId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '風險分類ID為必填項目'
                ]);
            }

            // 檢查分類是否存在
            $category = $this->categoryModel->getCategoryWithStats($categoryId);
            if (!$category) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的風險分類'
                ]);
            }

            // 檢查是否有相關的內容
            if ($category['content_count'] > 0 || $category['topic_count'] > 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '無法刪除：此分類下還有題項內容或風險主題'
                ]);
            }

            // 使用資料庫模型刪除分類
            $result = $this->categoryModel->delete($categoryId);

            if (!$result) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '刪除風險分類失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功刪除風險分類'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::deleteCategory - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除風險分類時發生錯誤'
            ]);
        }
    }

    // =============================================
    // 風險主題管理
    // =============================================

    /**
     * 取得評估記錄的所有風險主題
     * GET /api/v1/question-management/assessment/{assessmentId}/topics
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getTopics($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Assessment ID is required'
                ]);
            }

            // 使用資料庫模型取得主題資料
            $categoryId = $this->request->getGet('category_id');
            $search = $this->request->getGet('search');

            $topics = $this->topicModel->getTopicsByAssessment($assessmentId, $categoryId, $search);

            return $this->response->setJSON([
                'success' => true,
                'data' => $topics
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::getTopics - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error getting risk topics'
            ]);
        }
    }

    /**
     * 新增風險主題
     * POST /api/v1/question-management/assessment/{assessmentId}/topics
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function createTopic($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            $input = $this->request->getJSON(true);
            $input['assessment_id'] = $assessmentId;

            // 如果沒有提供排序，自動取得下一個排序號
            if (!isset($input['sort_order'])) {
                $categoryId = $input['category_id'] ?? null;
                $input['sort_order'] = $this->topicModel->getNextSortOrder($assessmentId, $categoryId);
            }

            $topicId = $this->topicModel->insert($input);

            if (!$topicId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '新增風險主題失敗',
                    'errors' => $this->topicModel->errors()
                ]);
            }

            $topic = $this->topicModel->getTopicWithStats($topicId);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '成功新增風險主題',
                'data' => $topic
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::createTopic - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '新增風險主題時發生錯誤'
            ]);
        }
    }

    /**
     * 更新風險主題
     * PUT /api/v1/question-management/topics/{topicId}
     *
     * @param int|null $topicId 主題ID
     * @return ResponseInterface
     */
    public function updateTopic($topicId = null)
    {
        try {
            if (!$topicId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '風險主題ID為必填項目'
                ]);
            }

            $input = $this->request->getJSON(true);

            $result = $this->topicModel->update($topicId, $input);

            if (!$result) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '更新風險主題失敗',
                    'errors' => $this->topicModel->errors()
                ]);
            }

            $topic = $this->topicModel->getTopicWithStats($topicId);

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功更新風險主題',
                'data' => $topic
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::updateTopic - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新風險主題時發生錯誤'
            ]);
        }
    }

    /**
     * 刪除風險主題
     * DELETE /api/v1/question-management/topics/{topicId}
     *
     * @param int|null $topicId 主題ID
     * @return ResponseInterface
     */
    public function deleteTopic($topicId = null)
    {
        try {
            if (!$topicId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '風險主題ID為必填項目'
                ]);
            }

            // 檢查主題是否存在
            $topic = $this->topicModel->getTopicWithStats($topicId);
            if (!$topic) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的風險主題'
                ]);
            }

            // 檢查是否有相關的內容
            if ($topic['content_count'] > 0 || $topic['factor_count'] > 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '無法刪除：此主題下還有題項內容或風險因子'
                ]);
            }

            // 使用資料庫模型刪除主題
            $result = $this->topicModel->delete($topicId);

            if (!$result) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '刪除風險主題失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功刪除風險主題'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::deleteTopic - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除風險主題時發生錯誤'
            ]);
        }
    }

    // =============================================
    // 風險因子管理
    // =============================================

    /**
     * 測試端點 - 返回簡單的JSON響應
     * GET /api/v1/question-management/test
     *
     * @return ResponseInterface
     */
    public function testEndpoint()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Test endpoint working',
            'data' => []
        ]);
    }

    /**
     * 取得評估記錄的所有風險因子
     * GET /api/v1/question-management/assessment/{assessmentId}/factors
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function getFactors($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Assessment ID is required'
                ]);
            }

            // 使用資料庫模型取得因子資料
            $topicId = $this->request->getGet('topic_id');
            $categoryId = $this->request->getGet('category_id');
            $search = $this->request->getGet('search');

            $factors = $this->factorModel->getFactorsByAssessment($assessmentId, $topicId, $categoryId, $search);

            return $this->response->setJSON([
                'success' => true,
                'data' => $factors
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::getFactors - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error getting risk factors'
            ]);
        }
    }

    /**
     * 新增風險因子
     * POST /api/v1/question-management/assessment/{assessmentId}/factors
     *
     * @param int|null $assessmentId 評估記錄ID
     * @return ResponseInterface
     */
    public function createFactor($assessmentId = null)
    {
        try {
            if (!$assessmentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '評估記錄ID為必填項目'
                ]);
            }

            $input = $this->request->getJSON(true);
            $input['assessment_id'] = $assessmentId;

            // 如果沒有提供排序，自動取得下一個排序號
            if (!isset($input['sort_order'])) {
                $topicId = $input['topic_id'] ?? null;
                $categoryId = $input['category_id'] ?? null;
                $input['sort_order'] = $this->factorModel->getNextSortOrder($assessmentId, $topicId, $categoryId);
            }

            $factorId = $this->factorModel->insert($input);

            if (!$factorId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '新增風險因子失敗',
                    'errors' => $this->factorModel->errors()
                ]);
            }

            $factor = $this->factorModel->getFactorWithStats($factorId);

            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'message' => '成功新增風險因子',
                'data' => $factor
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::createFactor - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '新增風險因子時發生錯誤'
            ]);
        }
    }

    /**
     * 更新風險因子
     * PUT /api/v1/question-management/factors/{factorId}
     *
     * @param int|null $factorId 因子ID
     * @return ResponseInterface
     */
    public function updateFactor($factorId = null)
    {
        try {
            if (!$factorId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '風險因子ID為必填項目'
                ]);
            }

            $input = $this->request->getJSON(true);

            $result = $this->factorModel->update($factorId, $input);

            if (!$result) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '更新風險因子失敗',
                    'errors' => $this->factorModel->errors()
                ]);
            }

            $factor = $this->factorModel->getFactorWithStats($factorId);

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功更新風險因子',
                'data' => $factor
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::updateFactor - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新風險因子時發生錯誤'
            ]);
        }
    }

    /**
     * 刪除風險因子
     * DELETE /api/v1/question-management/factors/{factorId}
     *
     * @param int|null $factorId 因子ID
     * @return ResponseInterface
     */
    public function deleteFactor($factorId = null)
    {
        try {
            if (!$factorId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '風險因子ID為必填項目'
                ]);
            }

            // 檢查因子是否存在
            $factor = $this->factorModel->getFactorWithStats($factorId);
            if (!$factor) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '找不到指定的風險因子'
                ]);
            }

            // 檢查是否有相關的內容
            if ($factor['content_count'] > 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '無法刪除：此因子下還有題項內容'
                ]);
            }

            // 使用資料庫模型刪除因子
            $result = $this->factorModel->delete($factorId);

            if (!$result) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '刪除風險因子失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功刪除風險因子'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::deleteFactor - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '刪除風險因子時發生錯誤'
            ]);
        }
    }

    // =============================================
    // 排序管理
    // =============================================

    /**
     * 更新分類排序
     * PUT /api/v1/question-management/categories/sort
     *
     * @return ResponseInterface
     */
    public function updateCategoriesSort()
    {
        try {
            $input = $this->request->getJSON(true);

            if (empty($input['sort_data']) || !is_array($input['sort_data'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '排序資料格式錯誤'
                ]);
            }

            $result = $this->categoryModel->updateSortOrder($input['sort_data']);

            if (!$result) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新分類排序失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功更新分類排序'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::updateCategoriesSort - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新分類排序時發生錯誤'
            ]);
        }
    }

    /**
     * 更新主題排序
     * PUT /api/v1/question-management/topics/sort
     *
     * @return ResponseInterface
     */
    public function updateTopicsSort()
    {
        try {
            $input = $this->request->getJSON(true);

            if (empty($input['sort_data']) || !is_array($input['sort_data'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '排序資料格式錯誤'
                ]);
            }

            $result = $this->topicModel->updateSortOrder($input['sort_data']);

            if (!$result) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新主題排序失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功更新主題排序'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::updateTopicsSort - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新主題排序時發生錯誤'
            ]);
        }
    }

    /**
     * 更新因子排序
     * PUT /api/v1/question-management/factors/sort
     *
     * @return ResponseInterface
     */
    public function updateFactorsSort()
    {
        try {
            $input = $this->request->getJSON(true);

            if (empty($input['sort_data']) || !is_array($input['sort_data'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '排序資料格式錯誤'
                ]);
            }

            $result = $this->factorModel->updateSortOrder($input['sort_data']);

            if (!$result) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '更新因子排序失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '成功更新因子排序'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'QuestionStructureController::updateFactorsSort - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '更新因子排序時發生錯誤'
            ]);
        }
    }

    /**
     * Import structure from Excel with RichText support
     * POST /api/v1/question-management/assessment/{assessmentId}/import-structure
     */
    public function importStructure()
    {
        try {
            $assessmentId = $this->request->uri->getSegment(4);

            // Get the assessment
            $assessmentModel = new \App\Models\RiskAssessment\CompanyAssessmentModel();
            $assessment = $assessmentModel->find($assessmentId);

            if (!$assessment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '評估不存在'
                ]);
            }

            // Get uploaded file
            $file = $this->request->getFile('file');

            if (!$file || !$file->isValid()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => '請上傳有效的 Excel 檔案'
                ]);
            }

            // Load Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();

            // Initialize converters
            $richTextToHtml = new \App\Libraries\RichTextToHtmlConverter();

            // Get data
            $rows = $sheet->toArray();
            $highestRow = $sheet->getHighestRow();

            if ($highestRow <= 1) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Excel 檔案中沒有資料'
                ]);
            }

            // Check if assessment has topic layer enabled
            $hasTopicLayer = $assessment['enable_topic_layer'] ?? true;

            $imported = 0;
            $errors = [];

            // Process each row (skip header row 1)
            for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
                try {
                    $row = $rows[$rowIndex - 1];

                    if ($hasTopicLayer) {
                        // Three-layer structure
                        $categoryName = trim($row[0] ?? '');
                        $topicName = trim($row[2] ?? '');
                        $factorName = trim($row[4] ?? '');

                        // Convert RichText to HTML
                        $cellB = $sheet->getCell('B' . $rowIndex);
                        $categoryDescHtml = $this->convertCellToHtml($cellB, $richTextToHtml);

                        $cellD = $sheet->getCell('D' . $rowIndex);
                        $topicDescHtml = $this->convertCellToHtml($cellD, $richTextToHtml);

                        $cellF = $sheet->getCell('F' . $rowIndex);
                        $factorDescHtml = $this->convertCellToHtml($cellF, $richTextToHtml);

                        if (empty($categoryName) || empty($factorName)) {
                            $errors[] = "第 {$rowIndex} 行：缺少必填欄位";
                            continue;
                        }

                        // Find or create category
                        $category = $this->categoryModel->where('assessment_id', $assessmentId)
                            ->where('category_name', $categoryName)
                            ->first();

                        if (!$category) {
                            $maxSort = $this->categoryModel->where('assessment_id', $assessmentId)
                                ->selectMax('sort_order')
                                ->first();
                            $nextSort = ($maxSort['sort_order'] ?? 0) + 1;

                            $categoryId = $this->categoryModel->insert([
                                'assessment_id' => $assessmentId,
                                'category_name' => $categoryName,
                                'description' => !empty($categoryDescHtml) ? $categoryDescHtml : null,
                                'sort_order' => $nextSort
                            ]);
                        } else {
                            $categoryId = $category['id'];
                            if (!empty($categoryDescHtml)) {
                                $this->categoryModel->update($categoryId, ['description' => $categoryDescHtml]);
                            }
                        }

                        // Find or create topic
                        $topicId = null;
                        if (!empty($topicName)) {
                            $topic = $this->topicModel->where('assessment_id', $assessmentId)
                                ->where('category_id', $categoryId)
                                ->where('topic_name', $topicName)
                                ->first();

                            if (!$topic) {
                                $maxSort = $this->topicModel->where('assessment_id', $assessmentId)
                                    ->where('category_id', $categoryId)
                                    ->selectMax('sort_order')
                                    ->first();
                                $nextSort = ($maxSort['sort_order'] ?? 0) + 1;

                                $topicId = $this->topicModel->insert([
                                    'assessment_id' => $assessmentId,
                                    'category_id' => $categoryId,
                                    'topic_name' => $topicName,
                                    'description' => !empty($topicDescHtml) ? $topicDescHtml : null,
                                    'sort_order' => $nextSort
                                ]);
                            } else {
                                $topicId = $topic['id'];
                                if (!empty($topicDescHtml)) {
                                    $this->topicModel->update($topicId, ['description' => $topicDescHtml]);
                                }
                            }
                        }

                        // Find or create factor
                        $factor = $this->factorModel->where('assessment_id', $assessmentId)
                            ->where('category_id', $categoryId)
                            ->where('factor_name', $factorName);

                        if ($topicId) {
                            $factor = $factor->where('topic_id', $topicId);
                        }

                        $factor = $factor->first();

                        if (!$factor) {
                            $this->factorModel->insert([
                                'assessment_id' => $assessmentId,
                                'category_id' => $categoryId,
                                'topic_id' => $topicId,
                                'factor_name' => $factorName,
                                'description' => !empty($factorDescHtml) ? $factorDescHtml : null
                            ]);
                        } else {
                            if (!empty($factorDescHtml)) {
                                $this->factorModel->update($factor['id'], ['description' => $factorDescHtml]);
                            }
                        }

                    } else {
                        // Two-layer structure
                        $categoryName = trim($row[0] ?? '');
                        $factorName = trim($row[2] ?? '');

                        $cellB = $sheet->getCell('B' . $rowIndex);
                        $categoryDescHtml = $this->convertCellToHtml($cellB, $richTextToHtml);

                        $cellD = $sheet->getCell('D' . $rowIndex);
                        $factorDescHtml = $this->convertCellToHtml($cellD, $richTextToHtml);

                        if (empty($categoryName) || empty($factorName)) {
                            $errors[] = "第 {$rowIndex} 行：缺少必填欄位";
                            continue;
                        }

                        // Find or create category
                        $category = $this->categoryModel->where('assessment_id', $assessmentId)
                            ->where('category_name', $categoryName)
                            ->first();

                        if (!$category) {
                            $maxSort = $this->categoryModel->where('assessment_id', $assessmentId)
                                ->selectMax('sort_order')
                                ->first();
                            $nextSort = ($maxSort['sort_order'] ?? 0) + 1;

                            $categoryId = $this->categoryModel->insert([
                                'assessment_id' => $assessmentId,
                                'category_name' => $categoryName,
                                'description' => !empty($categoryDescHtml) ? $categoryDescHtml : null,
                                'sort_order' => $nextSort
                            ]);
                        } else {
                            $categoryId = $category['id'];
                            if (!empty($categoryDescHtml)) {
                                $this->categoryModel->update($categoryId, ['description' => $categoryDescHtml]);
                            }
                        }

                        // Find or create factor
                        $factor = $this->factorModel->where('assessment_id', $assessmentId)
                            ->where('category_id', $categoryId)
                            ->where('factor_name', $factorName)
                            ->first();

                        if (!$factor) {
                            $this->factorModel->insert([
                                'assessment_id' => $assessmentId,
                                'category_id' => $categoryId,
                                'topic_id' => null,
                                'factor_name' => $factorName,
                                'description' => !empty($factorDescHtml) ? $factorDescHtml : null
                            ]);
                        } else {
                            if (!empty($factorDescHtml)) {
                                $this->factorModel->update($factor['id'], ['description' => $factorDescHtml]);
                            }
                        }
                    }

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "第 {$rowIndex} 行：" . $e->getMessage();
                    log_message('error', "Row {$rowIndex} import error: " . $e->getMessage());
                }
            }

            $message = "成功匯入 {$imported} 筆資料";
            if (!empty($errors)) {
                $message .= "，" . count($errors) . " 筆失敗";
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'data' => [
                    'imported' => $imported,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Structure import error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '匯入失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Convert Excel cell to HTML with RichText support
     */
    private function convertCellToHtml($cell, $richTextToHtml)
    {
        try {
            $cellValue = $cell->getValue();

            if (empty($cellValue)) {
                return '';
            }

            if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                return $richTextToHtml->convert($cellValue);
            }

            $cellStyle = $cell->getStyle();
            $font = $cellStyle->getFont();

            $hasFormatting = $font->getBold() ||
                           $font->getItalic() ||
                           $font->getUnderline() ||
                           $font->getStrikethrough() ||
                           ($font->getColor() && $font->getColor()->getRGB());

            if ($hasFormatting) {
                $text = htmlspecialchars($cellValue);

                if ($font->getBold()) {
                    $text = '<strong>' . $text . '</strong>';
                }
                if ($font->getItalic()) {
                    $text = '<em>' . $text . '</em>';
                }
                if ($font->getUnderline() && $font->getUnderline() !== \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_NONE) {
                    $text = '<u>' . $text . '</u>';
                }
                if ($font->getStrikethrough()) {
                    $text = '<s>' . $text . '</s>';
                }

                if ($font->getColor() && $font->getColor()->getRGB()) {
                    $rgb = $font->getColor()->getRGB();
                    if ($rgb && strtoupper($rgb) !== '000000' && strtoupper($rgb) !== 'FF000000') {
                        $color = '#' . $rgb;
                        $text = '<span style="color: ' . $color . '">' . $text . '</span>';
                    }
                }

                return '<p>' . $text . '</p>';
            } else {
                return '<p>' . htmlspecialchars($cellValue) . '</p>';
            }

        } catch (\Exception $e) {
            log_message('warning', 'Cell conversion error: ' . $e->getMessage());
            $cellValue = $cell->getValue();
            if (is_string($cellValue)) {
                return '<p>' . htmlspecialchars($cellValue) . '</p>';
            }
            return '';
        }
    }

}