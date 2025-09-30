<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ExternalPersonnelModel;
use App\Models\PersonnelAssignmentModel;
use App\Models\LocalCompanyModel;
use GuzzleHttp\Client;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 人員指派API控制器
 *
 * 處理人員指派相關的API請求，包括：
 * - 人員資料同步
 * - 指派關係管理
 * - 指派統計查詢
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */
class PersonnelAssignmentController extends ResourceController
{
    protected $format = 'json';

    /**
     * 確保字串資料為有效UTF-8編碼（加強版）
     */
    private function ensureUtf8($data, $depth = 0)
    {
        // 防止無限遞歸
        if ($depth > 10) {
            return $data;
        }

        if (is_string($data)) {
            // 先移除所有控制字符和非UTF-8字符
            $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/', '', $data);

            // 移除無效的UTF-8序列
            $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');

            // 再次檢查並清理
            if (!mb_check_encoding($data, 'UTF-8')) {
                // 如果仍然無效，嘗試從其他編碼轉換
                $encodings = ['UTF-8', 'Big5', 'GB2312', 'ISO-8859-1', 'Windows-1252'];
                foreach ($encodings as $encoding) {
                    $converted = @mb_convert_encoding($data, 'UTF-8', $encoding);
                    if ($converted && mb_check_encoding($converted, 'UTF-8')) {
                        return $converted;
                    }
                }

                // 最後手段：移除所有非ASCII字符，保留基本信息
                return preg_replace('/[^\x20-\x7E]/', '', $data);
            }

            return $data;
        }

        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? $this->ensureUtf8($key, $depth + 1) : $key;
                $cleanValue = $this->ensureUtf8($value, $depth + 1);
                $result[$cleanKey] = $cleanValue;
            }
            return $result;
        }

        if (is_object($data)) {
            // 轉換為陣列處理，再轉換回物件
            $arrayData = (array) $data;
            $cleanArray = $this->ensureUtf8($arrayData, $depth + 1);
            return (object) $cleanArray;
        }

        return $data;
    }


    protected ExternalPersonnelModel $personnelModel;
    protected PersonnelAssignmentModel $assignmentModel;
    protected LocalCompanyModel $companyModel;

    public function __construct()
    {
        $this->personnelModel = new ExternalPersonnelModel();
        $this->assignmentModel = new PersonnelAssignmentModel();
        $this->companyModel = new LocalCompanyModel();
    }

    /**
     * 取得公司的人員列表
     * GET /api/v1/personnel/companies/{companyId}/personnel
     */
    public function getPersonnelByCompany($companyId = null)
    {
        try {
            if (empty($companyId)) {
                return $this->fail('公司ID為必填參數', 400);
            }

            // 驗證公司是否存在
            $company = $this->companyModel->find($companyId);
            if (!$company) {
                return $this->failNotFound('找不到指定的公司');
            }

            // 從資料庫取得真實的人員列表（已同步的164筆資料）
            $personnelList = $this->personnelModel->getActivePersonnelByCompany($companyId);

            log_message('info', "Retrieved " . count($personnelList) . " personnel records from database");

            // 如果沒有資料，返回空列表
            if (empty($personnelList)) {
                return $this->respond([
                    'success' => true,
                    'message' => '未找到人員資料',
                    'data' => [],
                    'meta' => [
                        'company_id' => (int) $companyId,
                        'company_name' => 'Company ' . $companyId,
                        'personnel_count' => 0,
                        'data_source' => 'database_empty'
                    ]
                ]);
            }

            // 過濾並清理有效的人員資料
            $validPersonnelList = [];
            $skippedCount = 0;

            foreach ($personnelList as $person) {
                // 檢查這筆記錄是否可以安全地轉換為JSON
                $testRecord = json_encode($person);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // 這筆記錄有編碼問題，嘗試清理
                    $cleanPerson = [];
                    foreach ($person as $key => $value) {
                        if (is_string($value)) {
                            // 使用mb_convert_encoding清理字符串
                            $cleanValue = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                            // 如果仍然無法處理，使用安全替換
                            if (!mb_check_encoding($cleanValue, 'UTF-8')) {
                                $cleanValue = 'User_' . $person['id'];
                            }
                        } else {
                            $cleanValue = $value;
                        }
                        $cleanPerson[$key] = $cleanValue;
                    }

                    // 再次測試清理後的記錄
                    $testCleanRecord = json_encode($cleanPerson);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $validPersonnelList[] = $cleanPerson;
                    } else {
                        $skippedCount++;
                        log_message('warning', "Skipped personnel record with ID {$person['id']} due to encoding issues");
                    }
                } else {
                    // 記錄本身就是有效的
                    $validPersonnelList[] = $person;
                }
            }

            log_message('info', "Filtered personnel list: " . count($validPersonnelList) . " valid, {$skippedCount} skipped");

            return $this->respond([
                'success' => true,
                'message' => '成功取得人員列表',
                'data' => $validPersonnelList,
                'meta' => [
                    'company_id' => (int) $companyId,
                    'company_name' => 'Company ' . $companyId,
                    'personnel_count' => count($validPersonnelList),
                    'total_in_db' => count($personnelList),
                    'skipped_records' => $skippedCount,
                    'data_source' => 'external_api_sync'
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in getPersonnelByCompany: ' . $e->getMessage());
            return $this->failServerError('取得人員列表時發生錯誤');
        }
    }

    /**
     * 強制同步公司人員資料
     * POST /api/v1/personnel/companies/{companyId}/sync
     */
    public function syncPersonnel($companyId = null)
    {
        try {
            if (empty($companyId)) {
                return $this->fail('公司ID為必填參數', 400);
            }

            // 驗證公司是否存在
            $company = $this->companyModel->find($companyId);
            if (!$company) {
                return $this->failNotFound('找不到指定的公司');
            }

            // 執行同步並取得詳細資訊
            $syncResult = $this->syncPersonnelFromExternalAPIWithDetails($companyId, $company['external_id']);

            return $this->respond([
                'success' => $syncResult['success'],
                'message' => $syncResult['success'] ? '人員資料同步完成' : '人員資料同步失敗',
                'data' => [
                    'company_id' => $companyId,
                    'company_name' => $company['company_name'],
                    'external_id' => $company['external_id'],
                    'synced_count' => $syncResult['synced_count'],
                    'synced_at' => date('Y-m-d H:i:s'),
                    // 外部API詳細資訊用於除錯
                    'external_api_debug' => [
                        'request_url' => $syncResult['request_url'],
                        'request_method' => $syncResult['request_method'],
                        'request_params' => $syncResult['request_params'],
                        'request_headers' => $syncResult['request_headers'],
                        'response_status' => $syncResult['response_status'],
                        'response_time_ms' => $syncResult['response_time_ms'],
                        'response_headers' => $syncResult['response_headers'] ?? [],
                        'response_body' => $syncResult['response_body'],
                        'parsed_response' => $syncResult['parsed_response'],
                        'error_message' => $syncResult['error_message'] ?? null,
                        'processed_users' => $syncResult['processed_users'] ?? []
                    ]
                ]
            ], $syncResult['success'] ? 200 : 500);

        } catch (\Exception $e) {
            log_message('error', 'Error in syncPersonnel: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => '同步人員資料時發生系統錯誤',
                'data' => [
                    'company_id' => $companyId,
                    'error' => $e->getMessage(),
                    'exception_trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }

    /**
     * 取得評估的指派摘要
     * GET /api/v1/personnel/companies/{companyId}/assessments/{assessmentId}/assignments
     */
    public function getAssignmentSummary($companyId = null, $assessmentId = null)
    {
        try {
            if (empty($companyId) || empty($assessmentId)) {
                return $this->fail('公司ID和評估ID為必填參數', 400);
            }

            // 取得指派關係
            $assignments = $this->assignmentModel->getAssignmentsByAssessment($companyId, $assessmentId);

            // 取得人員摘要
            $personnelSummary = $this->assignmentModel->getPersonnelAssignmentSummary($companyId, $assessmentId);

            // 取得統計資料
            $statistics = $this->assignmentModel->getAssignmentStatistics($companyId, $assessmentId);

            return $this->respond([
                'success' => true,
                'message' => '成功取得指派摘要',
                'data' => [
                    'assignments' => $assignments,
                    'personnel_summary' => $personnelSummary,
                    'statistics' => $statistics
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in getAssignmentSummary: ' . $e->getMessage());
            return $this->failServerError('取得指派摘要時發生錯誤');
        }
    }

    /**
     * 指派人員到題項內容
     * POST /api/v1/personnel/assignments
     */
    public function createAssignment()
    {
        try {
            $data = $this->request->getJSON(true);

            // 驗證必填欄位
            $required = ['company_id', 'assessment_id', 'question_content_id', 'personnel_id'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->fail("缺少必填欄位: {$field}", 400);
                }
            }

            // 取得人員資料
            $personnel = $this->personnelModel->find($data['personnel_id']);
            if (!$personnel) {
                return $this->failNotFound('找不到指定的人員');
            }

            // 檢查是否已經指派
            if ($this->assignmentModel->isPersonnelAssigned(
                $data['company_id'],
                $data['assessment_id'],
                $data['question_content_id'],
                $data['personnel_id']
            )) {
                return $this->fail('該人員已經指派到此題項內容', 409);
            }

            // 建立指派關係
            $assignmentId = $this->assignmentModel->assignPersonnelToContent(
                $data['company_id'],
                $data['assessment_id'],
                $data['question_content_id'],
                $personnel,
                $data['assigned_by'] ?? null
            );

            if ($assignmentId) {
                return $this->respondCreated([
                    'success' => true,
                    'message' => '成功建立人員指派',
                    'data' => [
                        'assignment_id' => $assignmentId,
                        'personnel_name' => $personnel['name'] ?? ''
                    ]
                ]);
            } else {
                return $this->failServerError('建立指派關係失敗');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in createAssignment: ' . $e->getMessage());
            return $this->failServerError('建立人員指派時發生錯誤');
        }
    }

    /**
     * 批量指派人員
     * POST /api/v1/personnel/assignments/batch
     */
    public function batchCreateAssignments()
    {
        try {
            $data = $this->request->getJSON(true);

            // 驗證必填欄位
            if (empty($data['company_id']) || empty($data['assessment_id']) ||
                empty($data['personnel_id']) || empty($data['question_content_ids'])) {
                return $this->fail('缺少必填欄位', 400);
            }

            // 取得人員資料
            $personnel = $this->personnelModel->find($data['personnel_id']);
            if (!$personnel) {
                return $this->failNotFound('找不到指定的人員');
            }

            // 驗證並清理 question_content_ids
            $validContentIds = array_filter($data['question_content_ids'], function($id) {
                return !is_null($id) && is_numeric($id) && intval($id) > 0;
            });
            $validContentIds = array_map('intval', $validContentIds);

            if (empty($validContentIds)) {
                return $this->fail('沒有有效的題項內容ID', 400);
            }

            // 執行批量指派
            $assignedCount = $this->assignmentModel->batchAssignPersonnel(
                $data['company_id'],
                $data['assessment_id'],
                $validContentIds,
                $personnel,
                $data['assigned_by'] ?? null
            );

            return $this->respond([
                'success' => true,
                'message' => '批量指派完成',
                'data' => [
                    'personnel_name' => $personnel['name'] ?? '',
                    'total_contents' => count($validContentIds),
                    'assigned_count' => $assignedCount,
                    'skipped_count' => count($validContentIds) - $assignedCount
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in batchCreateAssignments: ' . $e->getMessage());
            return $this->failServerError('批量指派時發生錯誤');
        }
    }

    /**
     * 移除人員指派
     * DELETE /api/v1/personnel/assignments
     */
    public function removeAssignment()
    {
        try {
            $data = $this->request->getJSON(true);

            // 驗證必填欄位
            $required = ['company_id', 'assessment_id', 'question_content_id', 'personnel_id'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->fail("缺少必填欄位: {$field}", 400);
                }
            }

            // 移除指派關係
            $removed = $this->assignmentModel->removeAssignment(
                $data['company_id'],
                $data['assessment_id'],
                $data['question_content_id'],
                $data['personnel_id']
            );

            if ($removed) {
                return $this->respond([
                    'success' => true,
                    'message' => '成功移除人員指派'
                ]);
            } else {
                return $this->failNotFound('找不到指定的指派關係');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in removeAssignment: ' . $e->getMessage());
            return $this->failServerError('移除人員指派時發生錯誤');
        }
    }

    /**
     * 移除人員在整個評估中的所有指派
     * DELETE /api/v1/personnel/companies/{companyId}/assessments/{assessmentId}/personnel/{personnelId}
     */
    public function removePersonnelFromAssessment($companyId = null, $assessmentId = null, $personnelId = null)
    {
        try {
            if (empty($companyId) || empty($assessmentId) || empty($personnelId)) {
                return $this->fail('公司ID、評估ID和人員ID為必填參數', 400);
            }

            // 移除人員在評估中的所有指派
            $removed = $this->assignmentModel->removePersonnelFromAssessment(
                $companyId,
                $assessmentId,
                $personnelId
            );

            if ($removed) {
                return $this->respond([
                    'success' => true,
                    'message' => '成功移除人員在評估中的所有指派'
                ]);
            } else {
                return $this->failNotFound('找不到該人員在評估中的指派關係');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in removePersonnelFromAssessment: ' . $e->getMessage());
            return $this->failServerError('移除人員指派時發生錯誤');
        }
    }

    /**
     * 更新指派狀態
     * PUT /api/v1/personnel/assignments/{assignmentId}/status
     */
    public function updateAssignmentStatus($assignmentId = null)
    {
        try {
            if (empty($assignmentId)) {
                return $this->fail('指派ID為必填參數', 400);
            }

            $data = $this->request->getJSON(true);
            if (empty($data['status'])) {
                return $this->fail('缺少狀態參數', 400);
            }

            $validStatuses = ['assigned', 'accepted', 'declined', 'completed'];
            if (!in_array($data['status'], $validStatuses)) {
                return $this->fail('無效的狀態值', 400);
            }

            // 更新狀態
            $updated = $this->assignmentModel->updateAssignmentStatus($assignmentId, $data['status']);

            if ($updated) {
                return $this->respond([
                    'success' => true,
                    'message' => '成功更新指派狀態',
                    'data' => [
                        'assignment_id' => $assignmentId,
                        'new_status' => $data['status']
                    ]
                ]);
            } else {
                return $this->failNotFound('找不到指定的指派記錄');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in updateAssignmentStatus: ' . $e->getMessage());
            return $this->failServerError('更新指派狀態時發生錯誤');
        }
    }

    /**
     * 從外部API同步人員資料並回傳詳細除錯資訊（用於API除錯）
     */
    private function syncPersonnelFromExternalAPIWithDetails($companyId, $externalCompanyId): array
    {
        $startTime = microtime(true);
        $apiUrl = 'https://csr.cc-sustain.com/admin/api/user/get_user';
        $requestParams = ['com_id' => $externalCompanyId];
        $requestHeaders = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ];

        $result = [
            'success' => false,
            'synced_count' => 0,
            'request_url' => $apiUrl,
            'request_method' => 'POST',
            'request_params' => $requestParams,
            'request_headers' => $requestHeaders,
            'response_status' => null,
            'response_time_ms' => 0,
            'response_headers' => [],
            'response_body' => null,
            'parsed_response' => null,
            'error_message' => null,
            'processed_users' => []
        ];

        try {
            log_message('debug', "開始同步人員資料 - 公司ID: {$companyId}, 外部ID: {$externalCompanyId}");

            // 建立HTTP客戶端
            $client = new Client([
                'timeout' => 30,
                'verify' => false,
                'http_errors' => false  // 不讓4xx/5xx狀態碼直接拋出異常
            ]);

            // 發送請求
            $response = $client->post($apiUrl, [
                'form_params' => $requestParams,
                'headers' => $requestHeaders
            ]);

            // 記錄回應時間
            $result['response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);

            // 記錄回應資訊
            $result['response_status'] = $response->getStatusCode();
            $result['response_headers'] = $response->getHeaders();
            $responseBody = $response->getBody()->getContents();
            $result['response_body'] = $responseBody;

            log_message('debug', "外部API回應狀態: {$result['response_status']}");
            log_message('debug', "外部API回應內容: " . substr($responseBody, 0, 500));

            // 檢查HTTP狀態碼
            if ($result['response_status'] !== 200) {
                $result['error_message'] = "外部API回應非200狀態碼: {$result['response_status']}";
                return $result;
            }

            // 解析JSON回應
            $responseData = json_decode($responseBody, true);
            $result['parsed_response'] = $responseData;

            if (json_last_error() !== JSON_ERROR_NONE) {
                $result['error_message'] = '外部API回應JSON格式錯誤: ' . json_last_error_msg();
                return $result;
            }

            // 檢查API回應成功狀態
            if (!isset($responseData['success']) || !$responseData['success']) {
                $result['error_message'] = '外部API回應失敗: ' . ($responseData['message'] ?? '未知錯誤');
                return $result;
            }

            // 處理用戶資料
            $userData = $responseData['user_data'] ?? [];
            if (empty($userData)) {
                $result['success'] = true;
                $result['error_message'] = '外部API回應成功但無用戶資料';
                return $result;
            }

            // 轉換資料格式
            $personnelData = [];
            foreach ($userData as $index => $user) {
                // 從 groups 中取得第一筆作為職級
                $position = '';
                if (!empty($user['groups']) && is_array($user['groups'])) {
                    $firstGroup = reset($user['groups']);
                    $position = $firstGroup['description'] ?? $firstGroup['name'] ?? '';
                }

                // 如果沒有群組資料，則使用原始的職位資料
                if (empty($position)) {
                    $position = $user['com_title'] ?? $user['position'] ?? '';
                }

                $processedUser = [
                    'original_data' => $user,
                    'transformed_data' => [
                        'external_id' => $user['id'] ?? $user['user_id'] ?? "unknown_{$index}",
                        'external_company_id' => $user['com_id'] ?? $externalCompanyId,
                        'name' => $user['user_name'] ?? $user['name'] ?? $user['full_name'] ?? '未知用戶',
                        'email' => $user['email'] ?? '',
                        'department' => $user['dept_title'] ?? $user['department'] ?? '',
                        'position' => $position,
                        'phone' => $user['phone'] ?? $user['mobile'] ?? '',
                        'avatar' => $user['avatar'] ?? $user['photo'] ?? null,
                        'status' => 'active',
                        'groups' => $user['groups'] ?? null
                    ]
                ];

                $personnelData[] = $processedUser['transformed_data'];
                $result['processed_users'][] = $processedUser;
            }

            log_message('debug', "準備同步 " . count($personnelData) . " 位人員到資料庫");

            // 同步到資料庫
            $syncedCount = $this->personnelModel->syncPersonnelData($companyId, $personnelData);

            $result['success'] = true;
            $result['synced_count'] = $syncedCount;

            log_message('info', "人員資料同步完成 - 處理: " . count($personnelData) . " 位，成功同步: {$syncedCount} 位");

            return $result;

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $result['response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $result['error_message'] = '無法連接外部API: ' . $e->getMessage();
            log_message('error', 'Connection error to external API: ' . $e->getMessage());
            return $result;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $result['response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $result['error_message'] = '外部API請求錯誤: ' . $e->getMessage();
            if ($e->hasResponse()) {
                $result['response_status'] = $e->getResponse()->getStatusCode();
                $result['response_body'] = $e->getResponse()->getBody()->getContents();
            }
            log_message('error', 'Request error to external API: ' . $e->getMessage());
            return $result;

        } catch (\Exception $e) {
            $result['response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $result['error_message'] = '同步過程發生錯誤: ' . $e->getMessage();
            log_message('error', 'Error syncing personnel from external API: ' . $e->getMessage());
            return $result;
        }
    }

    /**
     * 從外部API同步人員資料（私有方法）
     */
    private function syncPersonnelFromExternalAPI($companyId, $externalCompanyId): int
    {
        try {
            // 呼叫外部API
            $apiUrl = 'https://csr.cc-sustain.com/admin/api/user/get_user';
            $client = new Client(['timeout' => 30, 'verify' => false]);

            $response = $client->post($apiUrl, [
                'form_params' => ['com_id' => $externalCompanyId],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                ]
            ]);

            $responseData = json_decode($response->getBody(), true);

            // 處理API回應
            if (!isset($responseData['success']) || !$responseData['success']) {
                throw new \Exception('外部API回應失敗');
            }

            $userData = $responseData['user_data'] ?? [];
            if (empty($userData)) {
                return 0;
            }

            // 轉換資料格式
            $personnelData = [];
            foreach ($userData as $user) {
                // 從 groups 中取得第一筆作為職級
                $position = '';
                if (!empty($user['groups']) && is_array($user['groups'])) {
                    $firstGroup = reset($user['groups']);
                    $position = $firstGroup['description'] ?? $firstGroup['name'] ?? '';
                }

                // 如果沒有群組資料，則使用原始的職位資料
                if (empty($position)) {
                    $position = $user['com_title'] ?? '';
                }

                $personnelData[] = [
                    'id' => $user['id'] ?? '',
                    'com_id' => $user['com_id'] ?? $externalCompanyId,
                    'name' => $user['user_name'] ?? '',
                    'email' => $user['email'] ?? '',
                    'department' => $user['dept_title'] ?? '',
                    'position' => $position,
                    'phone' => $user['phone'] ?? '',
                    'status' => 'active',
                    'groups' => $user['groups'] ?? null
                ];
            }

            // 同步到資料庫
            return $this->personnelModel->syncPersonnelData($companyId, $personnelData);

        } catch (\Exception $e) {
            log_message('error', 'Error syncing personnel from external API: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 檢查並同步人員資料（如果需要）
     */
    private function syncPersonnelIfNeeded($companyId, $hoursThreshold = 24): void
    {
        try {
            $needingSync = $this->personnelModel->getPersonnelNeedingSync($companyId, $hoursThreshold);

            if (!empty($needingSync)) {
                $company = $this->companyModel->find($companyId);
                if ($company && !empty($company['external_id'])) {
                    $this->syncPersonnelFromExternalAPI($companyId, $company['external_id']);
                }
            }
        } catch (\Exception $e) {
            // 同步失敗不影響主要功能，僅記錄錯誤
            log_message('warning', 'Auto sync personnel failed: ' . $e->getMessage());
        }
    }

    /**
     * 除錯端點：取得評估記錄的指派詳細資訊
     * GET /api/v1/personnel-assignments/debug/assessment/{assessmentId}
     */
    public function debugAssignmentInfo($assessmentId = null)
    {
        try {
            if (empty($assessmentId)) {
                return $this->fail('評估ID為必填參數', 400);
            }

            // 取得查詢參數
            $userId = $this->request->getGet('user_id');

            log_message('info', "=== Personnel Assignment Debug Request ===");
            log_message('info', "Assessment ID: {$assessmentId}");
            log_message('info', "User ID: " . ($userId ?? 'null'));

            // 使用模型的除錯方法
            $debugInfo = $this->assignmentModel->getAssignmentDebugInfo(
                (int)$assessmentId,
                $userId ? (int)$userId : null
            );

            // 如果有指定用戶ID，也檢查該用戶的驗證資訊
            if ($userId) {
                $validationInfo = $this->assignmentModel->validateAssignmentExpected(
                    0, // 公司ID暫時設為0，在除錯中不是關鍵
                    (int)$assessmentId,
                    (int)$userId
                );

                $debugInfo['validation'] = $validationInfo;
            }

            return $this->respond([
                'success' => true,
                'data' => $debugInfo,
                'debug_message' => '指派記錄除錯資訊已生成',
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in debugAssignmentInfo: ' . $e->getMessage());
            return $this->failServerError('取得除錯資訊時發生錯誤: ' . $e->getMessage());
        }
    }

    /**
     * 除錯端點：驗證特定指派記錄
     * GET /api/v1/personnel-assignments/debug/validate
     */
    public function debugValidateAssignment()
    {
        try {
            // 取得查詢參數
            $companyId = $this->request->getGet('company_id');
            $assessmentId = $this->request->getGet('assessment_id');
            $personnelId = $this->request->getGet('personnel_id');

            if (empty($companyId) || empty($assessmentId) || empty($personnelId)) {
                return $this->fail('公司ID、評估ID和人員ID為必填參數', 400);
            }

            log_message('info', "=== Assignment Validation Debug ===");
            log_message('info', "Company: {$companyId}, Assessment: {$assessmentId}, Personnel: {$personnelId}");

            // 使用模型的驗證方法
            $validationResult = $this->assignmentModel->validateAssignmentExpected(
                (int)$companyId,
                (int)$assessmentId,
                (int)$personnelId
            );

            return $this->respond([
                'success' => true,
                'data' => $validationResult,
                'debug_message' => '指派記錄驗證完成',
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in debugValidateAssignment: ' . $e->getMessage());
            return $this->failServerError('驗證指派記錄時發生錯誤: ' . $e->getMessage());
        }
    }
}