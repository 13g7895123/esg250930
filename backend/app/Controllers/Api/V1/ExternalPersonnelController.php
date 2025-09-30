<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Models\ExternalPersonnelModel;
use CodeIgniter\API\ResponseTrait;

/**
 * 外部人員控制器
 *
 * 處理外部人員相關的API請求
 * 主要用於用戶ID映射和查詢
 */
class ExternalPersonnelController extends BaseController
{
    use ResponseTrait;

    protected $personnelModel;

    public function __construct()
    {
        $this->personnelModel = new ExternalPersonnelModel();
    }

    /**
     * 根據外部ID查找人員（用於用戶ID映射）
     * POST /api/v1/external-personnel/find-by-external-id
     */
    public function findByExternalId()
    {
        try {
            $json = $this->request->getJSON(true);

            if (!$json || empty($json['external_id'])) {
                return $this->failValidationError('external_id is required');
            }

            $externalId = $json['external_id'];
            log_message('info', "ExternalPersonnelController::findByExternalId - 查詢外部ID: {$externalId}");

            // 根據外部ID查找人員
            $personnel = $this->personnelModel->findByExternalIdOnly($externalId);

            if (!$personnel) {
                log_message('info', "External personnel not found for external_id: {$externalId}");
                return $this->failNotFound('找不到對應的外部人員');
            }

            log_message('info', "External personnel found - ID: {$personnel['id']}, Name: {$personnel['name']}");

            return $this->respond([
                'success' => true,
                'data' => [
                    'id' => $personnel['id'],
                    'external_id' => $personnel['external_id'],
                    'name' => $personnel['name'],
                    'email' => $personnel['email'],
                    'company_id' => $personnel['company_id']
                ],
                'message' => '成功找到外部人員'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'ExternalPersonnelController::findByExternalId - ' . $e->getMessage());
            return $this->fail('查詢外部人員失敗: ' . $e->getMessage());
        }
    }

    /**
     * 獲取外部人員列表
     * GET /api/v1/external-personnel
     */
    public function index()
    {
        try {
            $companyId = $this->request->getGet('company_id');

            if (!$companyId) {
                return $this->failValidationError('company_id is required');
            }

            $personnel = $this->personnelModel->getActivePersonnelByCompany($companyId);

            return $this->respond([
                'success' => true,
                'data' => $personnel,
                'message' => '成功獲取外部人員列表'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'ExternalPersonnelController::index - ' . $e->getMessage());
            return $this->fail('獲取外部人員列表失敗: ' . $e->getMessage());
        }
    }
}