<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RiskAssessmentTemplateModel;

class TemplateController extends ResourceController
{
    protected $modelName = 'App\Models\RiskAssessmentTemplateModel';
    protected $format = 'json';

    public function __construct()
    {
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * Return the editable properties of a resource object
     * GET /api/v1/risk-assessment/templates
     */
    public function index()
    {
        try {
            // Use direct database query instead of model
            $db = \Config\Database::connect();
            $query = $db->query('SELECT id, version_name, description, status, created_at, updated_at FROM risk_assessment_templates');
            $templates = $query->getResultArray();

            // Manual JSON response bypassing CodeIgniter's formatter
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');

            $response = [
                'success' => true,
                'data' => $templates
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;

        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            http_response_code(500);

            $response = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * Return the properties of a resource object
     * GET /api/v1/risk-assessment/templates/{id}
     */
    public function show($id = null)
    {
        try {
            $template = $this->model->find($id);

            if (!$template) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            return $this->respond([
                'success' => true,
                'data' => $template
            ]);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new resource object
     * POST /api/v1/risk-assessment/templates
     */
    public function create()
    {
        try {
            $input = $this->request->getJSON(true) ?: $this->request->getPost();

            $rules = [
                'version_name' => 'required|max_length[255]',
                'description' => 'permit_empty|string',
                'status' => 'permit_empty|in_list[active,inactive,draft]'
            ];

            if (!$this->validate($rules, $input)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }

            // Check if version_name already exists
            $existingTemplate = $this->model->where('version_name', $input['version_name'])->first();
            if ($existingTemplate) {
                return $this->respond([
                    'success' => false,
                    'message' => '版本名稱已存在，請使用不同的名稱'
                ], 400);
            }

            $data = [
                'version_name' => $input['version_name'],
                'description' => $input['description'] ?? null,
                'status' => $input['status'] ?? 'active'
            ];

            $templateId = $this->model->insert($data);

            if (!$templateId) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Failed to create template',
                    'errors' => $this->model->errors()
                ], 500);
            }

            $template = $this->model->find($templateId);

            return $this->respond([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template
            ], 201);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add or update a model resource
     * PUT /api/v1/risk-assessment/templates/{id}
     */
    public function update($id = null)
    {
        try {
            $template = $this->model->find($id);
            if (!$template) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $input = $this->request->getJSON(true);
            if (empty($input)) {
                $input = $this->request->getRawInput();
            }

            $rules = [
                'version_name' => "required|max_length[255]",
                'description' => 'permit_empty|string',
                'status' => 'permit_empty|in_list[active,inactive,draft]'
            ];

            if (!$this->validate($rules, $input)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }

            $data = [
                'version_name' => $input['version_name'],
                'description' => $input['description'] ?? $template['description'],
                'status' => $input['status'] ?? $template['status']
            ];

            $success = $this->model->update($id, $data);

            if (!$success) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Failed to update template',
                    'errors' => $this->model->errors()
                ], 500);
            }

            $updatedTemplate = $this->model->find($id);

            return $this->respond([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => $updatedTemplate
            ]);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the designated resource object
     * DELETE /api/v1/risk-assessment/templates/{id}
     */
    public function delete($id = null)
    {
        try {
            $template = $this->model->find($id);
            if (!$template) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $success = $this->model->delete($id);

            if (!$success) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Failed to delete template'
                ], 500);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copy a template
     * POST /api/v1/risk-assessment/templates/{id}/copy
     */
    public function copy($id = null)
    {
        try {
            // Find the original template
            $originalTemplate = $this->model->find($id);
            if (!$originalTemplate) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            // Get input data
            $input = $this->request->getJSON(true) ?: $this->request->getPost();

            // Validation rules
            $rules = [
                'version_name' => 'required|max_length[255]',
                'description' => 'permit_empty|string'
            ];

            if (!$this->validate($rules, $input)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }

            // Check if version_name already exists
            $existingTemplate = $this->model->where('version_name', $input['version_name'])->first();
            if ($existingTemplate) {
                return $this->respond([
                    'success' => false,
                    'message' => '版本名稱已存在，請使用不同的名稱'
                ], 400);
            }

            // Prepare data for the new template
            $data = [
                'version_name' => $input['version_name'],
                'description' => $input['description'] ?? $originalTemplate['description'],
                'status' => 'draft', // New copy starts as draft
                'copied_from' => $id
            ];

            // Create the new template
            $newTemplateId = $this->model->insert($data);

            if (!$newTemplateId) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Failed to copy template',
                    'errors' => $this->model->errors()
                ], 500);
            }

            // Copy template structure and contents
            $db = \Config\Database::connect();

            // 1. Copy risk categories with all fields
            $db->query("
                INSERT INTO risk_categories (template_id, category_name, description, sort_order, created_at, updated_at)
                SELECT ?, category_name, description, sort_order, NOW(), NOW()
                FROM risk_categories
                WHERE template_id = ?
            ", [$newTemplateId, $id]);

            // Get mapping of old category IDs to new category IDs
            $oldCategories = $db->query("SELECT id FROM risk_categories WHERE template_id = ? ORDER BY sort_order", [$id])->getResultArray();
            $newCategories = $db->query("SELECT id FROM risk_categories WHERE template_id = ? ORDER BY sort_order", [$newTemplateId])->getResultArray();

            $categoryMapping = [];
            for ($i = 0; $i < count($oldCategories); $i++) {
                if (isset($newCategories[$i])) {
                    $categoryMapping[$oldCategories[$i]['id']] = $newCategories[$i]['id'];
                }
            }

            // 3. Copy risk topics with updated category_id mapping
            foreach ($categoryMapping as $oldCategoryId => $newCategoryId) {
                $db->query("
                    INSERT INTO risk_topics (template_id, category_id, topic_name, description, sort_order, status, created_at, updated_at)
                    SELECT ?, ?, topic_name, description, sort_order, status, NOW(), NOW()
                    FROM risk_topics
                    WHERE template_id = ? AND category_id = ?
                ", [$newTemplateId, $newCategoryId, $id, $oldCategoryId]);
            }

            // 4. Get mapping of old topic IDs to new topic IDs
            $oldTopics = $db->query("SELECT id, category_id FROM risk_topics WHERE template_id = ? ORDER BY category_id, sort_order", [$id])->getResultArray();
            $newTopics = $db->query("SELECT id, category_id FROM risk_topics WHERE template_id = ? ORDER BY category_id, sort_order", [$newTemplateId])->getResultArray();

            $topicMapping = [];
            for ($i = 0; $i < count($oldTopics); $i++) {
                if (isset($newTopics[$i])) {
                    $topicMapping[$oldTopics[$i]['id']] = $newTopics[$i]['id'];
                }
            }

            // 5. Copy risk factors with updated topic_id and category_id mapping
            foreach ($topicMapping as $oldTopicId => $newTopicId) {
                // Get the old and new category_id for this topic
                $oldTopic = array_values(array_filter($oldTopics, function($t) use ($oldTopicId) {
                    return $t['id'] == $oldTopicId;
                }))[0] ?? null;

                $newTopic = array_values(array_filter($newTopics, function($t) use ($newTopicId) {
                    return $t['id'] == $newTopicId;
                }))[0] ?? null;

                if ($oldTopic && $newTopic) {
                    $db->query("
                        INSERT INTO risk_factors (template_id, topic_id, category_id, factor_name, description, status, created_at, updated_at)
                        SELECT ?, ?, ?, factor_name, description, status, NOW(), NOW()
                        FROM risk_factors
                        WHERE template_id = ? AND topic_id = ? AND category_id = ?
                    ", [$newTemplateId, $newTopicId, $newTopic['category_id'], $id, $oldTopicId, $oldTopic['category_id']]);
                }
            }

            // 6. Get mapping of old risk_factor IDs to new risk_factor IDs
            $oldFactors = $db->query("SELECT id, topic_id, category_id FROM risk_factors WHERE template_id = ? ORDER BY category_id, topic_id, id", [$id])->getResultArray();
            $newFactors = $db->query("SELECT id, topic_id, category_id FROM risk_factors WHERE template_id = ? ORDER BY category_id, topic_id, id", [$newTemplateId])->getResultArray();

            $factorMapping = [];
            for ($i = 0; $i < count($oldFactors); $i++) {
                if (isset($newFactors[$i])) {
                    $factorMapping[$oldFactors[$i]['id']] = $newFactors[$i]['id'];
                }
            }

            // 7. Copy template_contents with updated IDs
            $contents = $db->query("SELECT * FROM template_contents WHERE template_id = ?", [$id])->getResultArray();

            foreach ($contents as $content) {
                $newCategoryId = $categoryMapping[$content['category_id']] ?? null;
                $newTopicId = isset($content['topic_id']) ? ($topicMapping[$content['topic_id']] ?? null) : null;
                $newFactorId = isset($content['risk_factor_id']) ? ($factorMapping[$content['risk_factor_id']] ?? null) : null;

                // Only insert if we have valid mapped IDs (at least category_id should exist)
                if ($newCategoryId) {
                    $db->query("
                        INSERT INTO template_contents (
                            template_id, category_id, topic_id, risk_factor_id, description, sort_order, is_required,
                            a_content, b_content, c_placeholder, d_placeholder_1, d_placeholder_2,
                            e1_placeholder_1, e2_select_1, e2_select_2, e2_placeholder,
                            f2_select_1, f2_select_2, f2_placeholder,
                            e1_info, f1_info, g1_info, h1_info,
                            created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ", [
                        $newTemplateId,
                        $newCategoryId,
                        $newTopicId,
                        $newFactorId,
                        $content['description'],
                        $content['sort_order'],
                        $content['is_required'],
                        $content['a_content'],
                        $content['b_content'],
                        $content['c_placeholder'],
                        $content['d_placeholder_1'],
                        $content['d_placeholder_2'],
                        $content['e1_placeholder_1'],
                        $content['e2_select_1'],
                        $content['e2_select_2'],
                        $content['e2_placeholder'],
                        $content['f2_select_1'],
                        $content['f2_select_2'],
                        $content['f2_placeholder'],
                        $content['e1_info'],
                        $content['f1_info'],
                        $content['g1_info'],
                        $content['h1_info']
                    ]);
                }
            }

            // Get the newly created template
            $newTemplate = $this->model->find($newTemplateId);

            return $this->respond([
                'success' => true,
                'message' => 'Template copied successfully',
                'data' => $newTemplate
            ], 201);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export template structure to Excel with RichText support
     * POST /api/v1/risk-assessment/templates/{templateId}/export-structure
     */
    public function exportStructure()
    {
        try {
            $templateId = $this->request->uri->getSegment(5);

            // Check if template exists
            $template = $this->model->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
                ]);
            }

            // Load models
            $categoryModel = new \App\Models\RiskAssessment\RiskCategoryModel();
            $topicModel = new \App\Models\RiskAssessment\RiskTopicModel();
            $factorModel = new \App\Models\RiskAssessment\RiskFactorModel();

            // Get template data
            $hasTopicLayer = $template['risk_topics_enabled'] ?? true;

            // Fetch structure data
            $categories = $categoryModel->where('template_id', $templateId)->findAll();
            $topics = $hasTopicLayer ? $topicModel->where('template_id', $templateId)->findAll() : [];
            $factors = $factorModel->where('template_id', $templateId)->findAll();

            // Create Excel file
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('架構資料');

            // Initialize converters
            $htmlToRichText = new \App\Libraries\HtmlToRichTextConverter();

            // Set headers
            $headers = ['風險類別名稱', '風險類別描述'];
            if ($hasTopicLayer) {
                $headers[] = '風險主題名稱';
                $headers[] = '風險主題描述';
            }
            $headers[] = '風險因子名稱';
            $headers[] = '風險因子描述';

            $col = 'A';
            foreach ($headers as $header) {
                $cell = $sheet->getCell($col . '1');
                $cell->setValue($header);
                $cell->getStyle()->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F46E5']
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ]
                ]);
                $col++;
            }

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(20); // 類別名稱
            $sheet->getColumnDimension('B')->setWidth(40); // 類別描述
            if ($hasTopicLayer) {
                $sheet->getColumnDimension('C')->setWidth(20); // 主題名稱
                $sheet->getColumnDimension('D')->setWidth(40); // 主題描述
                $sheet->getColumnDimension('E')->setWidth(20); // 因子名稱
                $sheet->getColumnDimension('F')->setWidth(40); // 因子描述
            } else {
                $sheet->getColumnDimension('C')->setWidth(20); // 因子名稱
                $sheet->getColumnDimension('D')->setWidth(40); // 因子描述
            }

            // Fill data rows
            $row = 2;
            foreach ($factors as $factor) {
                // Find category
                $category = null;
                foreach ($categories as $cat) {
                    if ($cat['id'] == $factor['category_id']) {
                        $category = $cat;
                        break;
                    }
                }

                $col = 'A';

                // Category name
                $sheet->setCellValue($col++ . $row, $category['category_name'] ?? '');

                // Category description (RichText)
                if (!empty($category['description'])) {
                    $richText = $htmlToRichText->convert($category['description']);
                    $sheet->setCellValue($col . $row, $richText);
                }
                $col++;

                if ($hasTopicLayer) {
                    // Find topic
                    $topic = null;
                    foreach ($topics as $t) {
                        if ($t['id'] == $factor['topic_id']) {
                            $topic = $t;
                            break;
                        }
                    }

                    // Topic name
                    $sheet->setCellValue($col++ . $row, $topic['topic_name'] ?? '');

                    // Topic description (RichText)
                    if (!empty($topic['description'])) {
                        $richText = $htmlToRichText->convert($topic['description']);
                        $sheet->setCellValue($col . $row, $richText);
                    }
                    $col++;
                }

                // Factor name
                $sheet->setCellValue($col++ . $row, $factor['factor_name'] ?? '');

                // Factor description (RichText)
                if (!empty($factor['description'])) {
                    $richText = $htmlToRichText->convert($factor['description']);
                    $sheet->setCellValue($col . $row, $richText);
                }

                $row++;
            }

            // Output Excel file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $timestamp = date('Y-m-d');
            $filename = "{$template['version_name']}_架構_{$timestamp}.xlsx";

            // Save to temporary file
            $tempFile = WRITEPATH . 'uploads/' . $filename;
            $writer->save($tempFile);

            // Read file content
            $fileContent = file_get_contents($tempFile);

            // Delete temporary file
            unlink($tempFile);

            // Return file
            return $this->response
                ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->setHeader('Content-Disposition', 'attachment;filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'max-age=0')
                ->setBody($fileContent);

        } catch (\Exception $e) {
            log_message('error', 'Template structure export error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '匯出失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Import template structure from Excel with RichText support
     * POST /api/v1/risk-assessment/templates/{templateId}/import-structure
     */
    public function importStructure()
    {
        try {
            $templateId = $this->request->uri->getSegment(5);

            // Check if template exists
            $template = $this->model->find($templateId);
            if (!$template) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => '範本不存在'
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

            // Get data (skip first row which is header)
            $rows = $sheet->toArray();
            $highestRow = $sheet->getHighestRow();

            if ($highestRow <= 1) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Excel 檔案中沒有資料'
                ]);
            }

            // Load models
            $categoryModel = new \App\Models\RiskAssessment\RiskCategoryModel();
            $topicModel = new \App\Models\RiskAssessment\RiskTopicModel();
            $factorModel = new \App\Models\RiskAssessment\RiskFactorModel();

            // Check if template has topic layer enabled
            $hasTopicLayer = $template['risk_topics_enabled'] ?? true;

            $imported = 0;
            $errors = [];

            // Process each row (skip header row 1)
            for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
                try {
                    $row = $rows[$rowIndex - 1]; // Array is 0-indexed

                    if ($hasTopicLayer) {
                        // Three-layer structure: Category, Topic, Factor
                        $categoryName = trim($row[0] ?? '');
                        $categoryDescHtml = '';
                        $topicName = trim($row[2] ?? '');
                        $topicDescHtml = '';
                        $factorName = trim($row[4] ?? '');
                        $factorDescHtml = '';

                        // Convert RichText descriptions to HTML
                        // Category Description (Column B)
                        $cellB = $sheet->getCell('B' . $rowIndex);
                        $categoryDescHtml = $this->convertCellToHtml($cellB, $richTextToHtml);

                        // Topic Description (Column D)
                        $cellD = $sheet->getCell('D' . $rowIndex);
                        $topicDescHtml = $this->convertCellToHtml($cellD, $richTextToHtml);

                        // Factor Description (Column F)
                        $cellF = $sheet->getCell('F' . $rowIndex);
                        $factorDescHtml = $this->convertCellToHtml($cellF, $richTextToHtml);

                        // Validate required fields
                        if (empty($categoryName) || empty($factorName)) {
                            $errors[] = "第 {$rowIndex} 行：缺少必填欄位（風險類別名稱、風險因子名稱）";
                            continue;
                        }

                        // Find or create category
                        $category = $categoryModel->where('template_id', $templateId)
                            ->where('category_name', $categoryName)
                            ->first();

                        if (!$category) {
                            $maxSort = $categoryModel->where('template_id', $templateId)
                                ->selectMax('sort_order')
                                ->first();
                            $nextSort = ($maxSort['sort_order'] ?? 0) + 1;

                            $categoryId = $categoryModel->insert([
                                'template_id' => $templateId,
                                'category_name' => $categoryName,
                                'description' => !empty($categoryDescHtml) ? $categoryDescHtml : null,
                                'sort_order' => $nextSort
                            ]);
                        } else {
                            $categoryId = $category['id'];
                            // Update description if provided
                            if (!empty($categoryDescHtml)) {
                                $categoryModel->update($categoryId, [
                                    'description' => $categoryDescHtml
                                ]);
                            }
                        }

                        // Find or create topic (if provided)
                        $topicId = null;
                        if (!empty($topicName)) {
                            $topic = $topicModel->where('template_id', $templateId)
                                ->where('category_id', $categoryId)
                                ->where('topic_name', $topicName)
                                ->first();

                            if (!$topic) {
                                $maxSort = $topicModel->where('template_id', $templateId)
                                    ->where('category_id', $categoryId)
                                    ->selectMax('sort_order')
                                    ->first();
                                $nextSort = ($maxSort['sort_order'] ?? 0) + 1;

                                $topicId = $topicModel->insert([
                                    'template_id' => $templateId,
                                    'category_id' => $categoryId,
                                    'topic_name' => $topicName,
                                    'description' => !empty($topicDescHtml) ? $topicDescHtml : null,
                                    'sort_order' => $nextSort
                                ]);
                            } else {
                                $topicId = $topic['id'];
                                // Update description if provided
                                if (!empty($topicDescHtml)) {
                                    $topicModel->update($topicId, [
                                        'description' => $topicDescHtml
                                    ]);
                                }
                            }
                        }

                        // Find or create factor
                        $factor = $factorModel->where('template_id', $templateId)
                            ->where('category_id', $categoryId)
                            ->where('factor_name', $factorName);

                        if ($topicId) {
                            $factor = $factor->where('topic_id', $topicId);
                        }

                        $factor = $factor->first();

                        if (!$factor) {
                            $factorId = $factorModel->insert([
                                'template_id' => $templateId,
                                'category_id' => $categoryId,
                                'topic_id' => $topicId,
                                'factor_name' => $factorName,
                                'description' => !empty($factorDescHtml) ? $factorDescHtml : null
                            ]);
                        } else {
                            // Update description if provided
                            if (!empty($factorDescHtml)) {
                                $factorModel->update($factor['id'], [
                                    'description' => $factorDescHtml
                                ]);
                            }
                        }

                    } else {
                        // Two-layer structure: Category, Factor (no Topic)
                        $categoryName = trim($row[0] ?? '');
                        $categoryDescHtml = '';
                        $factorName = trim($row[2] ?? '');
                        $factorDescHtml = '';

                        // Convert RichText descriptions to HTML
                        // Category Description (Column B)
                        $cellB = $sheet->getCell('B' . $rowIndex);
                        $categoryDescHtml = $this->convertCellToHtml($cellB, $richTextToHtml);

                        // Factor Description (Column D)
                        $cellD = $sheet->getCell('D' . $rowIndex);
                        $factorDescHtml = $this->convertCellToHtml($cellD, $richTextToHtml);

                        // Validate required fields
                        if (empty($categoryName) || empty($factorName)) {
                            $errors[] = "第 {$rowIndex} 行：缺少必填欄位（風險類別名稱、風險因子名稱）";
                            continue;
                        }

                        // Find or create category
                        $category = $categoryModel->where('template_id', $templateId)
                            ->where('category_name', $categoryName)
                            ->first();

                        if (!$category) {
                            $maxSort = $categoryModel->where('template_id', $templateId)
                                ->selectMax('sort_order')
                                ->first();
                            $nextSort = ($maxSort['sort_order'] ?? 0) + 1;

                            $categoryId = $categoryModel->insert([
                                'template_id' => $templateId,
                                'category_name' => $categoryName,
                                'description' => !empty($categoryDescHtml) ? $categoryDescHtml : null,
                                'sort_order' => $nextSort
                            ]);
                        } else {
                            $categoryId = $category['id'];
                            // Update description if provided
                            if (!empty($categoryDescHtml)) {
                                $categoryModel->update($categoryId, [
                                    'description' => $categoryDescHtml
                                ]);
                            }
                        }

                        // Find or create factor
                        $factor = $factorModel->where('template_id', $templateId)
                            ->where('category_id', $categoryId)
                            ->where('factor_name', $factorName)
                            ->first();

                        if (!$factor) {
                            $factorId = $factorModel->insert([
                                'template_id' => $templateId,
                                'category_id' => $categoryId,
                                'topic_id' => null,
                                'factor_name' => $factorName,
                                'description' => !empty($factorDescHtml) ? $factorDescHtml : null
                            ]);
                        } else {
                            // Update description if provided
                            if (!empty($factorDescHtml)) {
                                $factorModel->update($factor['id'], [
                                    'description' => $factorDescHtml
                                ]);
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
            log_message('error', 'Template structure import error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '匯入失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Convert Excel cell to HTML with RichText support
     * Helper method for importStructure
     */
    private function convertCellToHtml($cell, $richTextToHtml)
    {
        try {
            $cellValue = $cell->getValue();

            if (empty($cellValue)) {
                return '';
            }

            // If it's a RichText object, convert it
            if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                return $richTextToHtml->convert($cellValue);
            }

            // Check if the cell has formatting applied
            $cellStyle = $cell->getStyle();
            $font = $cellStyle->getFont();

            $hasFormatting = $font->getBold() ||
                           $font->getItalic() ||
                           $font->getUnderline() ||
                           $font->getStrikethrough() ||
                           ($font->getColor() && $font->getColor()->getRGB());

            if ($hasFormatting) {
                // Apply cell-level formatting to plain text
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

                // Apply color
                if ($font->getColor() && $font->getColor()->getRGB()) {
                    $rgb = $font->getColor()->getRGB();
                    if ($rgb && strtoupper($rgb) !== '000000' && strtoupper($rgb) !== 'FF000000') {
                        $color = '#' . $rgb;
                        $text = '<span style="color: ' . $color . '">' . $text . '</span>';
                    }
                }

                return '<p>' . $text . '</p>';
            } else {
                // Plain text - wrap in paragraph
                return '<p>' . htmlspecialchars($cellValue) . '</p>';
            }

        } catch (\Exception $e) {
            log_message('warning', 'Cell conversion error: ' . $e->getMessage());
            // Fallback to plain text
            $cellValue = $cell->getValue();
            if (is_string($cellValue)) {
                return '<p>' . htmlspecialchars($cellValue) . '</p>';
            }
            return '';
        }
    }
}