<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Models\LocalCompanyModel;

class PersonnelController extends ResourceController
{
    protected $modelName = 'App\Models\LocalCompanyModel';
    protected $format = 'json';

    public function __construct()
    {
        // Initialize the local company model
        $this->localCompanyModel = new LocalCompanyModel();
    }

    /**
     * 根據公司ID獲取該公司的external_id
     *
     * @param int $companyId
     * @return mixed
     */
    public function getCompanyExternalId($companyId = null)
    {
        if (!$companyId) {
            return $this->fail('公司ID為必填項目', 400);
        }

        try {
            // 從local_companies表中根據id獲取external_id
            $company = $this->localCompanyModel->find($companyId);

            if (!$company) {
                return $this->failNotFound('找不到指定的公司');
            }

            return $this->respond([
                'success' => true,
                'data' => [
                    'company_id' => $companyId,
                    'external_id' => $company['external_id'],
                    'company_name' => $company['company_name']
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting company external ID: ' . $e->getMessage());
            return $this->failServerError('獲取公司資訊時發生錯誤');
        }
    }

    /**
     * 調用外部API獲取公司人員資料
     *
     * @param int $companyId
     * @return mixed
     */
    public function getPersonnel($companyId = null)
    {
        if (!$companyId) {
            return $this->fail('公司ID為必填項目', 400);
        }

        try {
            // 首先獲取公司的external_id
            $company = $this->localCompanyModel->find($companyId);

            if (!$company) {
                return $this->failNotFound('找不到指定的公司');
            }

            $externalId = $company['external_id'];

            // 調用外部API
            $userData = $this->fetchUserDataFromExternalAPI($externalId);

            if ($userData === false) {
                return $this->failServerError('無法獲取人員資料');
            }

            return $this->respond([
                'success' => true,
                'data' => [
                    'company_id' => $companyId,
                    'external_id' => $externalId,
                    'company_name' => $company['company_name'],
                    'personnel' => $userData
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting personnel data: ' . $e->getMessage());
            return $this->failServerError('獲取人員資料時發生錯誤');
        }
    }

    /**
     * 從外部API獲取人員資料
     *
     * @param string $externalId
     * @return array|false
     */
    private function fetchUserDataFromExternalAPI($externalId)
    {
        try {
            $client = \Config\Services::curlrequest();

            // 外部API設定
            $apiUrl = 'https://csr.cc-sustain.com/admin/api/user/get_user';

            $options = [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'com_id' => $externalId
                ],
                'timeout' => 30,
                'verify' => false, // 在開發環境中可能需要
            ];

            log_message('debug', 'Calling external API: ' . $apiUrl);
            log_message('debug', 'Request data: ' . json_encode(['com_id' => $externalId]));

            $response = $client->post($apiUrl, $options);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            log_message('debug', 'External API response status: ' . $statusCode);
            log_message('debug', 'External API response body: ' . $body);

            if ($statusCode !== 200) {
                log_message('error', 'External API returned non-200 status: ' . $statusCode);
                return false;
            }

            $responseData = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Failed to decode JSON response: ' . json_last_error_msg());
                return false;
            }

            // 檢查外部API的回應格式並轉換為我們需要的格式
            return $this->transformExternalUserData($responseData);

        } catch (\Exception $e) {
            log_message('error', 'Exception when calling external API: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 將外部API回傳的資料轉換為我們需要的格式
     *
     * @param array $externalData
     * @return array
     */
    private function transformExternalUserData($externalData)
    {
        // 假設外部API返回的資料結構，需要根據實際API調整
        $users = [];

        // 檢查外部API的回應結構
        if (isset($externalData['success']) && $externalData['success'] && isset($externalData['user_data'])) {
            $userData = $externalData['user_data'];
        } elseif (isset($externalData['data'])) {
            $userData = $externalData['data'];
        } elseif (isset($externalData['users'])) {
            $userData = $externalData['users'];
        } else {
            // 如果格式不符預期，記錄並返回空陣列
            log_message('warning', 'Unexpected external API response format: ' . json_encode($externalData));
            return [];
        }

        // 確保userData是陣列
        if (!is_array($userData)) {
            $userData = [$userData];
        }

        foreach ($userData as $user) {
            $transformedUser = [
                'id' => $user['id'] ?? uniqid(),
                'name' => $user['user_name'] ?? $user['name'] ?? $user['username'] ?? $user['full_name'] ?? '未知用戶',
                'email' => $user['email'] ?? '',
                'department' => $user['dept_title'] ?? $user['department'] ?? $user['dept'] ?? $user['division'] ?? '未分配',
                'position' => $user['com_title'] ?? $user['position'] ?? $user['title'] ?? $user['job_title'] ?? '未指定',
                'avatar' => $user['avatar'] ?? $user['photo'] ?? null,
                'phone' => $user['phone'] ?? $user['mobile'] ?? '',
                'status' => $user['status'] ?? 'active',
                'groups' => $user['groups'] ?? [] // 保留群組資訊
            ];

            $users[] = $transformedUser;
        }

        return $users;
    }

    /**
     * 獲取公司列表（用於測試）
     */
    public function getCompanies()
    {
        try {
            $companies = $this->localCompanyModel->findAll();

            return $this->respond([
                'success' => true,
                'data' => $companies
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting companies: ' . $e->getMessage());
            return $this->failServerError('獲取公司列表時發生錯誤');
        }
    }
}