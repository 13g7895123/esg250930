<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Models\LocalCompanyModel;
use CodeIgniter\API\ResponseTrait;

class LocalCompaniesController extends BaseController
{
    use ResponseTrait;

    protected $localCompanyModel;
    protected $db;

    public function __construct()
    {
        $this->localCompanyModel = new LocalCompanyModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * 獲取所有本地公司列表
     * GET /api/v1/local-companies
     */
    public function index()
    {
        try {
            $search = $this->request->getGet('search');
            $page = (int) ($this->request->getGet('page') ?? 1);
            $limit = (int) ($this->request->getGet('limit') ?? 20);
            $sort = $this->request->getGet('sort') ?? 'created_at';
            $order = $this->request->getGet('order') ?? 'desc';

            log_message('info', "LocalCompanies index called - search: {$search}, page: {$page}, limit: {$limit}, sort: {$sort}, order: {$order}");

            $builder = $this->localCompanyModel->getAllCompanies($search, $page, $limit, $sort, $order);

            // Get total count
            $total = $builder->countAllResults(false);

            // Get paginated results using limit/offset
            $offset = ($page - 1) * $limit;
            $companies = $builder->limit($limit, $offset)->findAll();

            // Get the complete SQL query with bound parameters
            $rawSql = $this->db->getLastQuery()->getQuery();
            // Clean up SQL: remove newlines and extra spaces for direct use
            $sql = preg_replace('/\s+/', ' ', str_replace(["\n", "\r", "\t"], ' ', $rawSql));

            log_message('info', "LocalCompanies SQL: " . $sql);
            log_message('info', "LocalCompanies found {$total} total, returning " . count($companies) . " companies");

            $totalPages = ceil($total / $limit);

            return $this->respond([
                'success' => true,
                'data' => [
                    'companies' => $companies,
                    'pagination' => [
                        'currentPage' => $page,
                        'totalPages' => $totalPages,
                        'perPage' => $limit,
                        'total' => $total
                    ]
                ],
                'message' => '成功獲取公司列表',
                'debug' => [
                    'sql' => $sql,
                    'params' => [
                        'search' => $search,
                        'page' => $page,
                        'limit' => $limit,
                        'sort' => $sort,
                        'order' => $order
                    ],
                    'total_found' => $total,
                    'returned_count' => count($companies)
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'LocalCompaniesController::index - ' . $e->getMessage());
            return $this->fail('獲取公司列表失敗: ' . $e->getMessage());
        }
    }

    /**
     * 獲取單一公司詳情
     * GET /api/v1/local-companies/{id}
     */
    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->failValidationError('公司ID為必需參數');
            }

            $company = $this->localCompanyModel->find($id);

            if (!$company) {
                return $this->failNotFound('找不到指定的公司');
            }

            return $this->respond([
                'success' => true,
                'data' => $company,
                'message' => '成功獲取公司詳情'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'LocalCompaniesController::show - ' . $e->getMessage());
            return $this->fail('獲取公司詳情失敗: ' . $e->getMessage());
        }
    }

    /**
     * 新增本地公司
     * POST /api/v1/local-companies
     */
    public function create()
    {
        try {
            $json = $this->request->getJSON(true);
            
            if (!$json) {
                return $this->failValidationError('請提供有效的JSON資料');
            }

            // 驗證必要欄位
            $requiredFields = ['company_name', 'external_id'];
            foreach ($requiredFields as $field) {
                if (empty($json[$field])) {
                    return $this->failValidationError("欄位 {$field} 為必填項目");
                }
            }

            // 準備資料
            $companyData = [
                'company_name' => $json['company_name'],
                'external_id' => $json['external_id'],
                'abbreviation' => $json['abbreviation'] ?? null
            ];

            // 使用 addCompanyIfNotExists 方法防止重複
            $company = $this->localCompanyModel->addCompanyIfNotExists($companyData);

            if (!$company) {
                return $this->fail('新增公司失敗');
            }

            return $this->respondCreated([
                'success' => true,
                'data' => $company,
                'message' => '公司新增成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'LocalCompaniesController::create - ' . $e->getMessage());
            return $this->fail('新增公司失敗: ' . $e->getMessage());
        }
    }

    /**
     * 更新本地公司
     * PUT /api/v1/local-companies/{id}
     */
    public function update($id = null)
    {
        try {
            if (!$id) {
                return $this->failValidationError('公司ID為必需參數');
            }

            $company = $this->localCompanyModel->find($id);
            if (!$company) {
                return $this->failNotFound('找不到指定的公司');
            }

            $json = $this->request->getJSON(true);
            if (!$json) {
                return $this->failValidationError('請提供有效的JSON資料');
            }

            // 準備更新資料
            $updateData = [];
            $allowedFields = ['company_name', 'abbreviation'];
            
            foreach ($allowedFields as $field) {
                if (isset($json[$field])) {
                    $updateData[$field] = $json[$field];
                }
            }

            if (empty($updateData)) {
                return $this->failValidationError('沒有提供可更新的資料');
            }

            // 更新公司資料
            if (!$this->localCompanyModel->update($id, $updateData)) {
                return $this->fail('更新公司失敗');
            }

            // 獲取更新後的資料
            $updatedCompany = $this->localCompanyModel->find($id);

            return $this->respond([
                'success' => true,
                'data' => $updatedCompany,
                'message' => '公司更新成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'LocalCompaniesController::update - ' . $e->getMessage());
            return $this->fail('更新公司失敗: ' . $e->getMessage());
        }
    }

    /**
     * 刪除本地公司
     * DELETE /api/v1/local-companies/{id}
     */
    public function delete($id = null)
    {
        try {
            if (!$id) {
                return $this->failValidationError('公司ID為必需參數');
            }

            $company = $this->localCompanyModel->find($id);
            if (!$company) {
                return $this->failNotFound('找不到指定的公司');
            }

            // 刪除公司
            if (!$this->localCompanyModel->delete($id)) {
                return $this->fail('刪除公司失敗');
            }

            return $this->respondDeleted([
                'success' => true,
                'message' => '公司刪除成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'LocalCompaniesController::delete - ' . $e->getMessage());
            return $this->fail('刪除公司失敗: ' . $e->getMessage());
        }
    }

    /**
     * 根據外部ID查找公司
     * GET /api/v1/local-companies/external/{external_id}
     */
    public function findByExternalId($externalId = null)
    {
        try {
            if (!$externalId) {
                return $this->failValidationError('外部公司ID為必需參數');
            }

            $company = $this->localCompanyModel->findByExternalId($externalId);

            if (!$company) {
                return $this->failNotFound('找不到指定的公司');
            }

            return $this->respond([
                'success' => true,
                'data' => $company,
                'message' => '成功找到公司'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'LocalCompaniesController::findByExternalId - ' . $e->getMessage());
            return $this->fail('查找公司失敗: ' . $e->getMessage());
        }
    }

    /**
     * 根據外部ID查找公司 (POST方法)
     * POST /api/v1/local-companies/find-by-external-id
     */
    public function findByExternalIdPost()
    {
        try {
            $json = $this->request->getJSON(true);

            if (!$json || !isset($json['external_company_id'])) {
                return $this->failValidationError('external_company_id 為必需參數');
            }

            $externalCompanyId = $json['external_company_id'];

            log_message('info', "Finding company by external_id: {$externalCompanyId}");

            $company = $this->localCompanyModel->findByExternalId($externalCompanyId);

            if (!$company) {
                log_message('info', "Company with external_id {$externalCompanyId} not found");
                return $this->respond([
                    'success' => false,
                    'data' => null,
                    'message' => '找不到指定的公司'
                ]);
            }

            log_message('info', "Company found: {$company['company_name']} (ID: {$company['id']})");

            return $this->respond([
                'success' => true,
                'data' => $company,
                'message' => '成功找到公司'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'LocalCompaniesController::findByExternalIdPost - ' . $e->getMessage());
            return $this->fail('查找公司失敗: ' . $e->getMessage());
        }
    }

    /**
     * 獲取公司統計資訊
     * GET /api/v1/local-companies/stats
     */
    public function stats()
    {
        try {
            $stats = $this->localCompanyModel->getCompanyStats();

            return $this->respond([
                'success' => true,
                'data' => $stats,
                'message' => '成功獲取統計資訊'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'LocalCompaniesController::stats - ' . $e->getMessage());
            return $this->fail('獲取統計資訊失敗: ' . $e->getMessage());
        }
    }

    /**
     * 根據公司ID自動查詢或建立公司
     * POST /api/v1/local-companies/resolve
     */
    public function resolveCompany()
    {
        try {
            $json = $this->request->getJSON(true);

            if (!$json || empty($json['company_id'])) {
                return $this->failValidationError('company_id is required');
            }

            $companyId = $json['company_id'];
            log_message('info', "ResolveCompany: Processing company_id: {$companyId}");

            // 步驟1：查詢本地資料庫是否已存在此 external_id
            $existingCompany = $this->localCompanyModel->findByExternalId($companyId);

            if ($existingCompany) {
                log_message('info', "Company found in local database: {$existingCompany['company_name']} (ID: {$existingCompany['id']})");

                return $this->respond([
                    'success' => true,
                    'data' => [
                        'id' => $existingCompany['id'],
                        'company_name' => $existingCompany['company_name'],
                        'external_id' => $existingCompany['external_id'],
                        'abbreviation' => $existingCompany['abbreviation'] ?? null,
                        'source' => 'existing'
                    ],
                    'message' => '公司已存在於本地資料庫'
                ]);
            }

            // 步驟2：從外部API獲取公司資料
            log_message('info', "Company not found locally, querying external API for company_id: {$companyId}");
            $externalCompany = $this->fetchCompanyFromExternalAPI($companyId);

            if (!$externalCompany) {
                log_message('warning', "Company not found in external API: {$companyId}");
                return $this->failNotFound('指定的公司ID在外部系統中不存在');
            }

            // 步驟3：建立本地公司紀錄
            $companyData = [
                'company_name' => $externalCompany['com_title'] ?? "公司 {$companyId}",
                'external_id' => $companyId,
                'abbreviation' => $externalCompany['com_abbreviation'] ?? null
            ];

            $newCompany = $this->localCompanyModel->addCompanyIfNotExists($companyData);

            if (!$newCompany) {
                log_message('error', "Failed to create local company for external_id: {$companyId}");
                return $this->fail('建立本地公司紀錄失敗');
            }

            log_message('info', "Successfully created local company: {$newCompany['company_name']} (ID: {$newCompany['id']})");

            // 步驟4：記錄操作日誌
            $this->logCompanyOperation('AUTO_CREATE', $newCompany, $externalCompany);

            return $this->respondCreated([
                'success' => true,
                'data' => [
                    'id' => $newCompany['id'],
                    'company_name' => $newCompany['company_name'],
                    'external_id' => $newCompany['external_id'],
                    'abbreviation' => $newCompany['abbreviation'] ?? null,
                    'source' => 'created'
                ],
                'message' => '公司已成功建立於本地資料庫'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'LocalCompaniesController::resolveCompany - ' . $e->getMessage());
            return $this->fail('處理公司查詢失敗: ' . $e->getMessage());
        }
    }

    /**
     * 從外部API獲取公司資料
     */
    private function fetchCompanyFromExternalAPI($companyId)
    {
        try {
            $client = \Config\Services::curlrequest();

            $apiUrl = 'https://csr.cc-sustain.com/admin/api/risk/get_all_companies';

            $options = [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 30,
                'verify' => false,
            ];

            log_message('debug', "Calling external API: {$apiUrl}");
            $response = $client->get($apiUrl, $options);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            log_message('debug', "External API response status: {$statusCode}");

            if ($statusCode !== 200) {
                log_message('error', "External API returned non-200 status: {$statusCode}");
                return null;
            }

            $responseData = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Failed to decode JSON response: ' . json_last_error_msg());
                return null;
            }

            // 檢查回應格式
            $companies = [];
            if (isset($responseData['success']) && $responseData['success'] && isset($responseData['data'])) {
                $companies = $responseData['data'];
            } elseif (isset($responseData['data'])) {
                $companies = $responseData['data'];
            } elseif (is_array($responseData)) {
                $companies = $responseData;
            }

            // 尋找指定的公司ID
            foreach ($companies as $company) {
                if (isset($company['com_id']) && $company['com_id'] == $companyId) {
                    log_message('info', "Found company in external API: {$company['com_title']}");
                    return $company;
                }
            }

            log_message('info', "Company with ID {$companyId} not found in external API");
            return null;

        } catch (\Exception $e) {
            log_message('error', 'Exception when calling external API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 記錄公司操作日誌
     */
    private function logCompanyOperation($action, $localCompany, $externalCompany = null)
    {
        try {
            // 檢查是否有 activity_logs 表
            if (!$this->db->tableExists('activity_logs')) {
                return;
            }

            $logData = [
                'action' => $action,
                'resource_type' => 'LOCAL_COMPANY',
                'resource_id' => $localCompany['id'],
                'description' => sprintf(
                    '自動建立公司: %s (ID: %s, External ID: %s)',
                    $localCompany['company_name'],
                    $localCompany['id'],
                    $localCompany['external_id']
                ),
                'metadata' => json_encode([
                    'local_company' => $localCompany,
                    'external_company' => $externalCompany,
                    'trigger' => 'resolve_company_api'
                ]),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('activity_logs')->insert($logData);
            log_message('info', "Operation logged: {$action} for company {$localCompany['id']}");

        } catch (\Exception $e) {
            log_message('error', 'Failed to log company operation: ' . $e->getMessage());
        }
    }
}