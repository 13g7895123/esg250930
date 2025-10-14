<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Models\LocalCompanyModel;
use App\Models\ExternalPersonnelModel;
use App\Models\CompanyAssessmentModel;
use App\Models\PersonnelAssignmentModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 外部系統存取驗證控制器
 *
 * 處理外部系統的存取驗證，包括：
 * - 驗證外部使用者是否有權限存取特定公司的評估記錄
 * - 根據權限產生對應的存取網址
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-01-08
 */
class ExternalAccessController extends ResourceController
{
    protected $format = 'json';

    protected LocalCompanyModel $companyModel;
    protected ExternalPersonnelModel $personnelModel;
    protected CompanyAssessmentModel $assessmentModel;
    protected PersonnelAssignmentModel $assignmentModel;

    public function __construct()
    {
        $this->companyModel = new LocalCompanyModel();
        $this->personnelModel = new ExternalPersonnelModel();
        $this->assessmentModel = new CompanyAssessmentModel();
        $this->assignmentModel = new PersonnelAssignmentModel();
    }

    /**
     * 驗證外部使用者的存取權限並產生存取網址
     * GET /api/v1/external-access/verify
     *
     * @return ResponseInterface
     */
    public function verifyAccess()
    {
        try {
            // 取得查詢參數
            $externalCompanyId = $this->request->getGet('company_id');
            $externalUserId = $this->request->getGet('user_id');

            // 驗證必填參數
            if (empty($externalCompanyId) || empty($externalUserId)) {
                return $this->respond([
                    'success' => false,
                    'is_authorized' => false,
                    'message' => '缺少必填參數：company_id 或 user_id',
                    'error' => 'MISSING_PARAMETERS'
                ], 400);
            }

            log_message('info', "=== External Access Verification ===");
            log_message('info', "External Company ID: {$externalCompanyId}");
            log_message('info', "External User ID: {$externalUserId}");

            // 步驟 1: 通過 external_id 查找本地公司
            $company = $this->companyModel->findByExternalId($externalCompanyId);
            if (!$company) {
                log_message('warning', "Company not found for external_id: {$externalCompanyId}");
                return $this->respond([
                    'success' => false,
                    'is_authorized' => false,
                    'message' => '找不到指定的公司',
                    'error' => 'COMPANY_NOT_FOUND'
                ], 404);
            }

            $systemCompanyId = $company['id'];
            log_message('info', "Found company - System ID: {$systemCompanyId}, Name: {$company['company_name']}");

            // 步驟 2: 通過 external_id 查找人員
            $personnel = $this->personnelModel->findByExternalIdOnly($externalUserId);
            if (!$personnel) {
                log_message('warning', "Personnel not found for external_id: {$externalUserId}");
                return $this->respond([
                    'success' => false,
                    'is_authorized' => false,
                    'message' => '找不到指定的使用者',
                    'error' => 'USER_NOT_FOUND'
                ], 404);
            }

            $systemPersonnelId = $personnel['id'];
            log_message('info', "Found personnel - System ID: {$systemPersonnelId}, Name: {$personnel['name']}");

            // 步驟 3: 查找該公司最新年度的評估記錄
            $latestAssessment = $this->getLatestAssessment($systemCompanyId);
            if (!$latestAssessment) {
                log_message('warning', "No assessment found for company: {$systemCompanyId}");
                return $this->respond([
                    'success' => true,
                    'is_authorized' => false,
                    'message' => '該公司沒有評估記錄',
                    'error' => 'NO_ASSESSMENT_FOUND',
                    'company_id' => $systemCompanyId,
                    'user_id' => $systemPersonnelId
                ], 404);
            }

            $assessmentId = $latestAssessment['id'];
            $assessmentYear = $latestAssessment['assessment_year'];
            log_message('info', "Found latest assessment - ID: {$assessmentId}, Year: {$assessmentYear}");

            // 步驟 4: 檢查該人員是否在該評估記錄中有指派
            $hasAssignment = $this->checkPersonnelAssignment(
                $systemCompanyId,
                $assessmentId,
                $systemPersonnelId
            );

            if (!$hasAssignment) {
                log_message('warning', "Personnel {$systemPersonnelId} has no assignment in assessment {$assessmentId}");
                return $this->respond([
                    'success' => true,
                    'is_authorized' => false,
                    'message' => '該使用者沒有此評估記錄的權限',
                    'error' => 'NO_PERMISSION',
                    'company_id' => $systemCompanyId,
                    'user_id' => $systemPersonnelId
                ], 403);
            }

            // 步驟 5: 產生存取網址
            $url = "/web/risk-assessment/questions/{$systemCompanyId}/management/{$assessmentId}/content";

            log_message('info', "Access authorized - Generating URL: {$url}");
            log_message('info', "=== External Access Verification Completed ===");

            // 步驟 6: 回傳結果
            return $this->respond([
                'success' => true,
                'is_authorized' => true,
                'message' => '驗證成功，使用者有權限存取',
                'url' => $url,
                'company_id' => $systemCompanyId,
                'user_id' => $systemPersonnelId
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error in verifyAccess: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return $this->respond([
                'success' => false,
                'is_authorized' => false,
                'message' => '驗證過程發生錯誤',
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * 取得公司的最新評估記錄
     *
     * @param int $companyId 公司ID（系統上的）
     * @return array|null 評估記錄資料，如果沒有則回傳 null
     */
    private function getLatestAssessment(int $companyId): ?array
    {
        // 查詢該公司最新年度的評估記錄
        // 優先順序：assessment_year DESC, created_at DESC
        $assessment = $this->assessmentModel
            ->where('company_id', $companyId)
            ->orderBy('assessment_year', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();

        return $assessment;
    }

    /**
     * 檢查人員是否在評估記錄中有指派
     *
     * @param int $companyId 公司ID
     * @param int $assessmentId 評估記錄ID
     * @param int $personnelId 人員ID
     * @return bool 是否有指派
     */
    private function checkPersonnelAssignment(
        int $companyId,
        int $assessmentId,
        int $personnelId
    ): bool {

        $test = array(
            'companyId' => $companyId,
            'assessmentId' => $assessmentId,
            'personnelId' => $personnelId);

        print_r($test);

        // 查詢該人員在該評估記錄中的指派
        $assignments = $this->assignmentModel
            ->where('company_id', $companyId)
            ->where('assessment_id', $assessmentId)
            ->where('personnel_id', $personnelId)
            ->findAll();

        $hasAssignment = !empty($assignments);

        log_message('debug', "Assignment check - Company: {$companyId}, Assessment: {$assessmentId}, Personnel: {$personnelId}, Has Assignment: " . ($hasAssignment ? 'YES' : 'NO'));

        if ($hasAssignment) {
            log_message('debug', "Found " . count($assignments) . " assignment(s) for this personnel");
        }

        return $hasAssignment;
    }
}
