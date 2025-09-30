<?php

namespace App\Controllers\Api\V1\RiskAssessment;

use App\Controllers\Api\BaseController;
use App\Models\RiskAssessment\ProbabilityScaleModel;
use App\Models\RiskAssessment\ProbabilityScaleColumnModel;
use App\Models\RiskAssessment\ProbabilityScaleRowModel;
use App\Models\RiskAssessment\ImpactScaleModel;
use App\Models\RiskAssessment\ImpactScaleColumnModel;
use App\Models\RiskAssessment\ImpactScaleRowModel;
use CodeIgniter\HTTP\ResponseInterface;

class ScaleController extends BaseController
{
    protected $probabilityScaleModel;
    protected $probabilityColumnModel;
    protected $probabilityRowModel;
    protected $impactScaleModel;
    protected $impactColumnModel;
    protected $impactRowModel;

    public function __construct()
    {
        $this->probabilityScaleModel = new ProbabilityScaleModel();
        $this->probabilityColumnModel = new ProbabilityScaleColumnModel();
        $this->probabilityRowModel = new ProbabilityScaleRowModel();
        $this->impactScaleModel = new ImpactScaleModel();
        $this->impactColumnModel = new ImpactScaleColumnModel();
        $this->impactRowModel = new ImpactScaleRowModel();

        // Set UTF-8 encoding for all responses
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * 儲存可能性量表
     * POST /api/v1/risk-assessment/templates/{templateId}/scales/probability
     */
    public function saveProbabilityScale($templateId)
    {
        try {
            $data = $this->request->getJSON(true);

            // 開始交易
            $db = \Config\Database::connect();
            $db->transStart();

            // 1. 儲存或更新主表
            $scaleData = [
                'template_id' => $templateId,
                'description_text' => $data['descriptionText'] ?? null,
                'show_description' => $data['showDescriptionText'] ?? 0,
                'selected_display_column' => $data['selectedDisplayColumn'] ?? 'probability'
            ];

            // 檢查是否已存在
            $existingScale = $this->probabilityScaleModel
                ->where('template_id', $templateId)
                ->first();

            if ($existingScale) {
                $scaleId = $existingScale['id'];
                $this->probabilityScaleModel->update($scaleId, $scaleData);

                // 刪除舊的欄位和資料列
                $this->probabilityColumnModel->where('scale_id', $scaleId)->delete();
                $this->probabilityRowModel->where('scale_id', $scaleId)->delete();
            } else {
                $scaleId = $this->probabilityScaleModel->insert($scaleData);
            }

            // 2. 儲存欄位定義
            $columns = $data['columns'] ?? [];
            foreach ($columns as $index => $column) {
                $columnData = [
                    'scale_id' => $scaleId,
                    'column_id' => $column['id'],
                    'name' => $column['name'],
                    'removable' => $column['removable'] ?? 1,
                    'sort_order' => $index
                ];
                $this->probabilityColumnModel->insert($columnData);
            }

            // 3. 儲存資料列
            $rows = $data['rows'] ?? [];
            foreach ($rows as $index => $row) {
                $rowData = [
                    'scale_id' => $scaleId,
                    'probability' => $row['probability'] ?? null,
                    'score_range' => $row['scoreRange'] ?? null,
                    'dynamic_fields' => json_encode($row['dynamicFields'] ?? []),
                    'sort_order' => $index
                ];
                $this->probabilityRowModel->insert($rowData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '儲存失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '可能性量表儲存成功',
                'data' => ['scale_id' => $scaleId]
            ]);

        } catch (\Exception $e) {
            log_message('error', '儲存可能性量表失敗: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '儲存失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 取得可能性量表
     * GET /api/v1/risk-assessment/templates/{templateId}/scales/probability
     */
    public function getProbabilityScale($templateId)
    {
        try {
            $scale = $this->probabilityScaleModel
                ->where('template_id', $templateId)
                ->first();

            if (!$scale) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => null
                ]);
            }

            // 取得欄位
            $columns = $this->probabilityColumnModel
                ->where('scale_id', $scale['id'])
                ->orderBy('sort_order', 'ASC')
                ->findAll();

            // 取得資料列
            $rows = $this->probabilityRowModel
                ->where('scale_id', $scale['id'])
                ->orderBy('sort_order', 'ASC')
                ->findAll();

            // 解析 JSON 欄位
            foreach ($rows as &$row) {
                $row['dynamicFields'] = json_decode($row['dynamic_fields'], true);
                unset($row['dynamic_fields']);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'scale' => $scale,
                    'columns' => $columns,
                    'rows' => $rows
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', '查詢可能性量表失敗: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '查詢失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 儲存財務衝擊量表
     * POST /api/v1/risk-assessment/templates/{templateId}/scales/impact
     */
    public function saveImpactScale($templateId)
    {
        try {
            $data = $this->request->getJSON(true);

            $db = \Config\Database::connect();
            $db->transStart();

            // 1. 儲存或更新主表
            $scaleData = [
                'template_id' => $templateId,
                'selected_display_column' => $data['selectedDisplayColumn'] ?? 'impactLevel'
            ];

            $existingScale = $this->impactScaleModel
                ->where('template_id', $templateId)
                ->first();

            if ($existingScale) {
                $scaleId = $existingScale['id'];
                $this->impactScaleModel->update($scaleId, $scaleData);

                $this->impactColumnModel->where('scale_id', $scaleId)->delete();
                $this->impactRowModel->where('scale_id', $scaleId)->delete();
            } else {
                $scaleId = $this->impactScaleModel->insert($scaleData);
            }

            // 2. 儲存欄位定義
            $columns = $data['columns'] ?? [];
            foreach ($columns as $index => $column) {
                $columnData = [
                    'scale_id' => $scaleId,
                    'column_id' => $column['id'],
                    'name' => $column['name'],
                    'amount_note' => $column['amountNote'] ?? null,
                    'removable' => $column['removable'] ?? 1,
                    'sort_order' => $index
                ];
                $this->impactColumnModel->insert($columnData);
            }

            // 3. 儲存資料列
            $rows = $data['rows'] ?? [];
            foreach ($rows as $index => $row) {
                $rowData = [
                    'scale_id' => $scaleId,
                    'impact_level' => $row['impactLevel'] ?? null,
                    'score_range' => $row['scoreRange'] ?? null,
                    'dynamic_fields' => json_encode($row['dynamicFields'] ?? []),
                    'sort_order' => $index
                ];
                $this->impactRowModel->insert($rowData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => '儲存失敗'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => '財務衝擊量表儲存成功',
                'data' => ['scale_id' => $scaleId]
            ]);

        } catch (\Exception $e) {
            log_message('error', '儲存財務衝擊量表失敗: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '儲存失敗: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 取得財務衝擊量表
     * GET /api/v1/risk-assessment/templates/{templateId}/scales/impact
     */
    public function getImpactScale($templateId)
    {
        try {
            $scale = $this->impactScaleModel
                ->where('template_id', $templateId)
                ->first();

            if (!$scale) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => null
                ]);
            }

            $columns = $this->impactColumnModel
                ->where('scale_id', $scale['id'])
                ->orderBy('sort_order', 'ASC')
                ->findAll();

            $rows = $this->impactRowModel
                ->where('scale_id', $scale['id'])
                ->orderBy('sort_order', 'ASC')
                ->findAll();

            foreach ($rows as &$row) {
                $row['dynamicFields'] = json_decode($row['dynamic_fields'], true);
                unset($row['dynamic_fields']);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'scale' => $scale,
                    'columns' => $columns,
                    'rows' => $rows
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', '查詢財務衝擊量表失敗: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '查詢失敗: ' . $e->getMessage()
            ]);
        }
    }
}