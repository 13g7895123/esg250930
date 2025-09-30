<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

class NasIntegration extends ResourceController
{
    protected $format = 'json';
    
    // NAS Configuration
    private $nasHost;
    private $nasPort;
    private $nasUsername;
    private $nasPassword;
    private $nasTargetPath;
    private $nasDownloadPath;
    private $nasSession;
    private $useQuickConnect;
    private $quickConnectId;
    private $quickConnectProtocol;
    private $quickConnectPort;
    private $nasBaseUrl;
    
    // External API Configuration  
    private $uploadJsonApiUrl;
    private $uploadFormApiUrl;
    
    // API Key Configuration
    private $validApiKeys;
    
    // Models
    private $nasSyncLogModel;
    
    public function __construct()
    {
        $this->initializeConfig();
        $this->initializeNasConnection();
        $this->nasSyncLogModel = new \App\Models\NasSyncLogModel();
    }
    
    /**
     * Initialize all configuration parameters
     */
    private function initializeConfig()
    {
        // NAS Configuration
        $this->nasUsername = 'IT';
        $this->nasPassword = '80vQm%UnvA';
        $this->nasTargetPath = '/CSR/知識區(成創)/9.歷年永續報告書/20250814_平台自動化上傳測試(提供IT)';
        $this->nasDownloadPath = WRITEPATH . 'uploads/nas_downloads';
        
        // QuickConnect configuration (currently disabled, using direct connection)
        $this->useQuickConnect = false;
        $this->quickConnectId = 'ChengChuang';
        $this->quickConnectProtocol = 'https';
        $this->quickConnectPort = '443';
        
        // Standard connection configuration (direct IP connection)
        $this->nasHost = '59.127.174.232';
        $this->nasPort = 5002;
        
        // External API URLs (moved to top as requested)
        $this->uploadJsonApiUrl = 'https://csr.cc-sustain.com/admin/api/template/upload_and_process_json';
        $this->uploadFormApiUrl = 'https://csr.cc-sustain.com/admin/api/template/upload_and_process';
        
        // API Key Configuration
        $this->initializeApiKeys();
    }
    
    /**
     * Initialize NAS connection URL based on configuration
     */
    private function initializeNasConnection()
    {
        if ($this->useQuickConnect && !empty($this->quickConnectId)) {
            $this->nasBaseUrl = $this->resolveQuickConnectUrl();
        } else {
            $protocol = ($this->nasPort == 5001 || $this->quickConnectProtocol == 'https') ? 'https' : 'http';
            $this->nasBaseUrl = "{$protocol}://{$this->nasHost}:{$this->nasPort}";
        }
    }
    
    /**
     * Initialize API Keys for authentication
     */
    private function initializeApiKeys()
    {
        // Default API Keys - should be moved to environment variables in production
        $this->validApiKeys = [
            'BxTGa5KrTDmrmfhr',
        ];
        
        // Load API Keys from environment variable if available
        $envApiKeys = getenv('NAS_SYNC_API_KEYS');
        if ($envApiKeys) {
            $this->validApiKeys = array_merge($this->validApiKeys, explode(',', $envApiKeys));
        }
    }
    
    /**
     * Validate API Key from request header
     */
    private function validateApiKey()
    {
        $request = service('request');
        $apiKey = $request->getHeaderLine('X-API-Key');
        
        if (empty($apiKey)) {
            return [
                'valid' => false,
                'message' => 'Missing X-API-Key header',
                'error_code' => 'MISSING_API_KEY'
            ];
        }
        
        if (!in_array($apiKey, $this->validApiKeys)) {
            return [
                'valid' => false,
                'message' => 'Invalid API Key',
                'error_code' => 'INVALID_API_KEY'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'API Key validated successfully',
            'api_key' => substr($apiKey, 0, 8) . '...' // Only log partial key for security
        ];
    }
    
    /**
     * Log process information with timestamp
     */
    private function logProcess($step, $message, $data = null)
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'step' => $step,
            'message' => $message
        ];
        
        if ($data !== null) {
            $logEntry['data'] = $data;
        }
        
        // Log to CodeIgniter log
        log_message('info', '[NAS Sync] ' . $step . ': ' . $message . ($data ? ' - Data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) : ''));
        
        return $logEntry;
    }
    
    /**
     * Main endpoint to sync NAS folders and upload to external API
     * GET /api/nas/sync
     */
    public function sync()
    {
        try {
            $processLogs = [];
            
            // Get test batch size parameter
            $request = service('request');
            $testBatchSize = $request->getGet('test_batch_size') ?: $request->getPost('test_batch_size');
            $testBatchSize = $testBatchSize ? (int)$testBatchSize : null;
            
            if ($testBatchSize && $testBatchSize > 0) {
                $processLogs[] = $this->logProcess('BATCH_MODE_ENABLED', 'Test batch mode enabled', ['batch_size' => $testBatchSize]);
            }
            
            $processLogs[] = $this->logProcess('START', 'Beginning NAS sync process');
            
            // Step 0: Validate API Key
            $processLogs[] = $this->logProcess('AUTH_START', 'Starting API key validation');
            $apiKeyValidation = $this->validateApiKey();
            if (!$apiKeyValidation['valid']) {
                $processLogs[] = $this->logProcess('AUTH_FAILED', 'API key validation failed', $apiKeyValidation);
                return $this->respond([
                    'success' => false,
                    'message' => 'Authentication required',
                    'error' => $apiKeyValidation['message'],
                    'error_code' => $apiKeyValidation['error_code'],
                    'hint' => 'Please include X-API-Key header with a valid API key'
                ], 401);
            }
            $processLogs[] = $this->logProcess('AUTH_SUCCESS', 'API key validation successful', ['api_key_partial' => $apiKeyValidation['api_key']]);
            
            // Step 1: Authenticate with Synology NAS
            $processLogs[] = $this->logProcess('NAS_AUTH_START', 'Starting NAS authentication', ['host' => $this->nasHost, 'port' => $this->nasPort, 'username' => $this->nasUsername]);
            $authResult = $this->authenticateNas();
            
            if (!$authResult['success']) {
                $processLogs[] = $this->logProcess('NAS_AUTH_FAILED', 'NAS authentication failed', $authResult);
                return $this->respond([
                    'success' => false,
                    'message' => 'NAS authentication failed',
                    'error' => $authResult['message'],
                    'connection_info' => $this->getConnectionInfo(),
                    'debug_info' => $authResult['debug_info'] ?? null
                ], 401);
            }
            $processLogs[] = $this->logProcess('NAS_AUTH_SUCCESS', 'NAS authentication successful', ['session_exists' => !empty($this->nasSession)]);
            
            // Step 2: Get all folders from specified path
            $processLogs[] = $this->logProcess('FOLDER_LIST_START', 'Starting to retrieve NAS folders', ['target_path' => $this->nasTargetPath]);
            $foldersResult = $this->getNasFolders();
            if (!$foldersResult['success']) {
                $processLogs[] = $this->logProcess('FOLDER_LIST_FAILED', 'Failed to retrieve NAS folders', $foldersResult);
                return $this->respond([
                    'success' => false,
                    'message' => 'Failed to retrieve NAS folders',
                    'error' => $foldersResult['message'],
                    'connection_info' => $this->getConnectionInfo(),
                    'debug_info' => $foldersResult['debug_info'] ?? null
                ], 500);
            }
            $processLogs[] = $this->logProcess('FOLDER_LIST_SUCCESS', 'Successfully retrieved NAS folders', ['folder_count' => count($foldersResult['folders'])]);
            
            $allFiles = [];
            $validFiles = [];
            $invalidFiles = [];
            $newFiles = [];
            
            // Step 3: Process each folder and collect file paths with extracted data
            $processLogs[] = $this->logProcess('FILE_PROCESSING_START', 'Starting to process folder files', ['folder_count' => count($foldersResult['folders'])]);
            foreach ($foldersResult['folders'] as $folder) {
                $processLogs[] = $this->logProcess('FOLDER_PROCESSING', 'Processing folder', ['folder_path' => $folder['path']]);
                $folderResult = $this->processFolderFiles($folder);
                if ($folderResult['success']) {
                    $processLogs[] = $this->logProcess('FOLDER_FILES_FOUND', 'Files found in folder', ['folder_path' => $folder['path'], 'file_count' => count($folderResult['data']['files'])]);
                    foreach ($folderResult['data']['files'] as $file) {
                        $extractedData = $this->extractDataFromPath($file['path']);
                        
                        if ($extractedData['is_valid']) {
                            $processLogs[] = $this->logProcess('FILE_DATA_EXTRACTED', 'Valid file data extracted', $extractedData);
                            $validFiles[] = $extractedData;
                            $allFiles[] = $extractedData;
                        } else {
                            $processLogs[] = $this->logProcess('FILE_VALIDATION_FAILED', 'File validation failed - skipped', [
                                'file_path' => $extractedData['file_path'],
                                'validation_errors' => $extractedData['validation_errors'],
                                'extracted_data' => $extractedData
                            ]);
                            $invalidFiles[] = $extractedData;
                        }
                    }
                } else {
                    $processLogs[] = $this->logProcess('FOLDER_PROCESSING_FAILED', 'Failed to process folder', ['folder_path' => $folder['path'], 'error' => $folderResult['message']]);
                }
            }
            $processLogs[] = $this->logProcess('FILE_PROCESSING_COMPLETE', 'File processing completed', [
                'total_files_found' => count($allFiles) + count($invalidFiles),
                'valid_files' => count($validFiles),
                'invalid_files_skipped' => count($invalidFiles)
            ]);
            
            // Step 4: Check existing files in database and filter new ones + failed files for retry
            // Only process valid files for database operations
            $processLogs[] = $this->logProcess('DB_CHECK_START', 'Starting database check for valid files only', [
                'valid_files_to_check' => count($allFiles),
                'invalid_files_skipped' => count($invalidFiles)
            ]);
            $retryFiles = [];
            
            foreach ($allFiles as $fileData) {
                if (!$this->nasSyncLogModel->filePathExists($fileData['file_path'])) {
                    // New file - add to database
                    $insertId = $this->nasSyncLogModel->insertFileRecord($fileData);
                    if ($insertId) {
                        $fileData['db_id'] = $insertId;
                        $newFiles[] = $fileData;
                        $processLogs[] = $this->logProcess('NEW_FILE_ADDED', 'New file added to database', ['file_path' => $fileData['file_path'], 'db_id' => $insertId]);
                    }
                } else {
                    // File exists - check if it's successfully uploaded or failed
                    if ($this->nasSyncLogModel->isFileSuccessfullyProcessed($fileData['file_path'])) {
                        $processLogs[] = $this->logProcess('FILE_SKIPPED', 'File already successfully processed', ['file_path' => $fileData['file_path']]);
                    } else {
                        // File exists but not successfully uploaded - check if it needs processing (failed or pending)
                        $existingFile = $this->nasSyncLogModel->getFileByPath($fileData['file_path']);
                        if ($existingFile && ($existingFile['status'] === 'failed' || $existingFile['status'] === 'pending')) {
                            $fileData['db_id'] = $existingFile['id'];
                            $retryFiles[] = $fileData;
                            $logMessage = $existingFile['status'] === 'failed' ? 'Failed file marked for retry' : 'Pending file marked for processing';
                            $processLogs[] = $this->logProcess('RETRY_FILE_FOUND', $logMessage, ['file_path' => $fileData['file_path'], 'db_id' => $existingFile['id'], 'status' => $existingFile['status'], 'previous_error' => $existingFile['error_message'] ?? null]);
                        } else {
                            $processLogs[] = $this->logProcess('FILE_SKIPPED', 'File in non-processable state', ['file_path' => $fileData['file_path'], 'status' => $existingFile['status'] ?? 'unknown']);
                        }
                    }
                }
            }
            
            // Combine new files and retry files for processing
            $allFilesToProcess = array_merge($newFiles, $retryFiles);
            
            // Apply test batch size limit if specified
            $originalFileCount = count($allFilesToProcess);
            $batchLimited = false;
            
            if ($testBatchSize && $testBatchSize > 0 && count($allFilesToProcess) > $testBatchSize) {
                $filesToProcess = array_slice($allFilesToProcess, 0, $testBatchSize);
                $batchLimited = true;
                $processLogs[] = $this->logProcess('BATCH_LIMIT_APPLIED', 'Test batch size limit applied', [
                    'original_file_count' => $originalFileCount,
                    'batch_size' => $testBatchSize,
                    'files_to_process' => count($filesToProcess),
                    'files_excluded' => $originalFileCount - $testBatchSize
                ]);
            } else {
                $filesToProcess = $allFilesToProcess;
            }
            
            $processLogs[] = $this->logProcess('DB_CHECK_COMPLETE', 'Database check completed', [
                'new_files_count' => count($newFiles), 
                'retry_files_count' => count($retryFiles),
                'total_available_files' => $originalFileCount,
                'files_to_process_count' => count($filesToProcess),
                'batch_limited' => $batchLimited,
                'existing_successful_files' => count($allFiles) - count($newFiles) - count($retryFiles)
            ]);
            
            // Step 5: Process all files with retry mechanism for failed records
            $processResult = [];
            $processedFiles = [];
            $failedFiles = [];
            
            if (!empty($filesToProcess)) {
                $processLogs[] = $this->logProcess('PROCESSING_START', 'Starting file processing', [
                    'total_files_to_process' => count($filesToProcess),
                    'new_files' => count($newFiles),
                    'retry_files' => count($retryFiles)
                ]);
                
                // Process all files in the list
                $processedSuccessfully = false;
                $attemptedFiles = [];
                $allProcessResults = [];
                
                foreach ($filesToProcess as $fileIndex => $fileData) {
                    $isRetry = in_array($fileData, $retryFiles);
                    $processLogs[] = $this->logProcess('FILE_PROCESSING_START', 'Starting to process file', [
                        'file_index' => $fileIndex, 
                        'file_data' => $fileData,
                        'is_retry' => $isRetry
                    ]);
                    
                    // If this is a retry, reset the file status
                    if ($isRetry) {
                        $this->nasSyncLogModel->resetFileForRetry($fileData['db_id']);
                        $processLogs[] = $this->logProcess('FILE_RETRY_RESET', 'Reset failed file for retry', ['db_id' => $fileData['db_id']]);
                    }
                    
                    // Download file from NAS
                    $processLogs[] = $this->logProcess('DOWNLOAD_START', 'Starting file download', ['file_path' => $fileData['file_path']]);
                    $downloadResult = $this->downloadAndProcessFile($fileData);
                    $processLogs[] = $this->logProcess('DOWNLOAD_RESULT', 'File download result', $downloadResult);
                    $currentProcessResult = ['download' => $downloadResult];
                    
                    if ($downloadResult['success']) {
                        $this->nasSyncLogModel->updateFileDownloaded($fileData['db_id'], $downloadResult['local_path']);
                        
                        // Upload file to external API
                        $processLogs[] = $this->logProcess('UPLOAD_START', 'Starting file upload', ['file_path' => $fileData['file_path'], 'original_filename' => $downloadResult['original_filename']]);
                        $uploadResult = $this->uploadFileToExternalApi($fileData, $downloadResult['local_path'], $downloadResult['original_filename']);
                        $processLogs[] = $this->logProcess('UPLOAD_RESULT', 'File upload result', $uploadResult);
                        $currentProcessResult['upload'] = $uploadResult;
                        
                        if ($uploadResult['success']) {
                            $this->nasSyncLogModel->updateFileUploaded($fileData['db_id'], $uploadResult['response_data']);
                            $processLogs[] = $this->logProcess('FILE_SUCCESS', 'File processed successfully', ['file_path' => $fileData['file_path'], 'db_id' => $fileData['db_id']]);
                            $processedSuccessfully = true;
                            $processedFiles[] = $fileData;
                            $allProcessResults[] = $currentProcessResult;
                        } else {
                            $this->nasSyncLogModel->updateFileFailure($fileData['db_id'], $uploadResult['message']);
                            $processLogs[] = $this->logProcess('FILE_UPLOAD_FAILED', 'File upload failed', ['file_path' => $fileData['file_path'], 'error' => $uploadResult['message']]);
                            $failedFiles[] = $fileData;
                        }
                    } else {
                        $this->nasSyncLogModel->updateFileFailure($fileData['db_id'], $downloadResult['message']);
                        $processLogs[] = $this->logProcess('FILE_DOWNLOAD_FAILED', 'File download failed', ['file_path' => $fileData['file_path'], 'error' => $downloadResult['message']]);
                        $failedFiles[] = $fileData;
                    }
                    
                    $attemptedFiles[] = $fileData;
                }
                
                // Set process result to first successful result for backward compatibility
                if (!empty($allProcessResults)) {
                    $processResult = $allProcessResults[0];
                }
                
                $processLogs[] = $this->logProcess('PROCESSING_COMPLETE', 'File processing completed', [
                    'files_processed_successfully' => count($processedFiles),
                    'files_failed' => count($failedFiles), 
                    'attempted_files' => count($attemptedFiles),
                    'total_files_to_process' => count($filesToProcess)
                ]);
            } else {
                $processLogs[] = $this->logProcess('NO_FILES_TO_PROCESS', 'No files to process (no new files or failed files for retry)');
            }
            
            // Step 6: Logout from NAS
            $this->logoutNas();
            
            $processLogs[] = $this->logProcess('SYNC_COMPLETE', 'NAS sync process completed');
            
            $successCount = isset($processedFiles) ? count($processedFiles) : 0;
            $failedCount = isset($failedFiles) ? count($failedFiles) : 0;
            $attemptedCount = isset($attemptedFiles) ? count($attemptedFiles) : 0;
            
            return $this->respond([
                'success' => !empty($filesToProcess) ? ($successCount > 0) : true,
                'message' => empty($filesToProcess) ? 'No files to process (all files already successfully uploaded)' : 
                           ($successCount > 0 ? "NAS bulk sync completed: {$successCount} files processed successfully, {$failedCount} failed" : 
                           'NAS sync completed but no files were successfully processed'),
                'summary' => [
                    'total_files_found' => count($allFiles) + count($invalidFiles ?? []),
                    'valid_files_found' => count($allFiles),
                    'invalid_files_skipped' => count($invalidFiles ?? []),
                    'new_files_added' => count($newFiles),
                    'retry_files_found' => count($retryFiles ?? []),
                    'files_to_process' => count($filesToProcess),
                    'existing_successful_files' => count($allFiles) - count($newFiles) - count($retryFiles ?? []),
                ],
                'processing_results' => [
                    'files_processed_successfully' => $successCount,
                    'files_failed' => $failedCount,
                    'files_attempted' => $attemptedCount,
                    'success_rate' => $attemptedCount > 0 ? round(($successCount / $attemptedCount) * 100, 2) . '%' : '0%'
                ],
                'bulk_processing' => [
                    'enabled' => true,
                    'mode' => $batchLimited ? 'test_batch_mode' : 'process_all_files',
                    'test_batch_size' => $testBatchSize,
                    'batch_limited' => $batchLimited,
                    'original_file_count' => $originalFileCount ?? count($filesToProcess),
                    'processed_file_count' => count($filesToProcess),
                    'files_excluded_by_batch' => $batchLimited ? ($originalFileCount - count($filesToProcess)) : 0,
                    'note' => $batchLimited ? 
                              "Test batch mode: Processing {$testBatchSize} files out of {$originalFileCount} available files" : 
                              'All available files in the queue are processed in a single API call'
                ],
                'process_result' => $processResult,
                'api_endpoint_info' => [
                    'upload_api' => $processResult['upload']['api_url'] ?? 'Not available',
                    'upload_method' => $processResult['upload']['upload_method'] ?? 'Not available',
                    'note' => $processResult['upload']['note'] ?? 'Standard upload process'
                ],
                'retry_mechanism' => [
                    'enabled' => true,
                    'retry_files_processed' => count($retryFiles ?? []),
                    'new_files_processed' => count($newFiles)
                ],
                'file_validation' => [
                    'enabled' => true,
                    'invalid_files_count' => count($invalidFiles ?? []),
                    'invalid_files' => isset($invalidFiles) && !empty($invalidFiles) ? array_map(function($file) {
                        return [
                            'file_path' => $file['file_path'],
                            'file_name' => $file['file_name'],
                            'validation_errors' => $file['validation_errors'],
                            'extracted_data' => [
                                'industry' => $file['industry'],
                                'indicator' => $file['indicator'],
                                'company' => $file['company'],
                                'year' => $file['year']
                            ]
                        ];
                    }, $invalidFiles) : [],
                    'note' => count($invalidFiles ?? []) > 0 ? 'Some files were skipped due to validation errors. Check validation_errors for details.' : 'All files passed validation checks.'
                ],
                'note' => $successCount > 0 ? "Bulk processing completed: {$successCount} files uploaded successfully. Check CI4 logs for detailed information." : 'Bulk processing completed but no files were successfully processed. Check CI4 logs for detailed error information.'
            ]);
            
        } catch (\Exception $e) {
            $errorLog = $this->logProcess('SYNC_ERROR', 'Sync process failed with exception', ['exception' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            
            return $this->respond([
                'success' => false,
                'message' => 'Sync process failed',
                'error' => $e->getMessage(),
                'connection_info' => $this->getConnectionInfo(),
                'debug_info' => [
                    'exception_type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ],
                'note' => 'Check CI4 logs for detailed error information'
            ], 500);
        }
    }
    
    /**
     * Get connection information for debugging
     */
    private function getConnectionInfo()
    {
        return [
            'nas_base_url' => $this->nasBaseUrl,
            'nas_host' => $this->nasHost,
            'nas_port' => $this->nasPort,
            'use_quick_connect' => $this->useQuickConnect,
            'quick_connect_id' => $this->quickConnectId,
            'target_path' => $this->nasTargetPath,
            'session_exists' => !empty($this->nasSession)
        ];
    }
    
    /**
     * Resolve QuickConnect URL for Synology NAS
     */
    private function resolveQuickConnectUrl()
    {
        $quickConnectApiBase = 'https://global.quickconnect.to/Serv.php';
        $resolveData = [
            'version' => 1,
            'command' => 'get_server_info',
            'stop_when_error' => 'false',
            'stop_when_success' => 'false',
            'id' => 'dsm',
            'serverID' => $this->quickConnectId,
            'is_gofile' => 'false'
        ];
        
        $quickConnectUrl = $quickConnectApiBase . '?' . http_build_query($resolveData);
        $response = $this->makeHttpRequest($quickConnectUrl, 'GET', $resolveData);
        
        if ($response && isset($response['server'])) {
            $server = $response['server'];
            if (isset($server['external']) && $server['external']['host']) {
                $host = $server['external']['host'];
                $port = $server['external']['port'] ?? $this->quickConnectPort;
                return "{$this->quickConnectProtocol}://{$host}:{$port}";
            }
        }
        
        return "{$this->quickConnectProtocol}://{$this->quickConnectId}.quickconnect.to:{$this->quickConnectPort}";
    }
    
    /**
     * Authenticate with Synology NAS
     */
    private function authenticateNas()
    {
        $loginUrl = $this->nasBaseUrl . '/webapi/auth.cgi';
        $loginData = [
            'api' => 'SYNO.API.Auth',
            'version' => '3',
            'method' => 'login',
            'account' => $this->nasUsername,
            'passwd' => $this->nasPassword,
            'session' => 'FileStation',
            'format' => 'cookie'
        ];
        
        $response = $this->makeHttpRequest($loginUrl, 'POST', $loginData);
        
        if ($response && isset($response['success']) && $response['success']) {
            $this->nasSession = $response['data']['sid'] ?? null;
            return [
                'success' => true,
                'message' => 'Authentication successful',
                'session' => $this->nasSession
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Authentication failed: ' . ($response['error']['code'] ?? 'Unknown error'),
            'debug_info' => $response
        ];
    }
    
    /**
     * Get folders from NAS path
     */
    private function getNasFolders()
    {
        $infoUrl = $this->nasBaseUrl . '/webapi/entry.cgi';
        $infoData = [
            'api' => 'SYNO.FileStation.Info',
            'version' => '2',
            'method' => 'get',
            'path' => [$this->nasTargetPath],
            '_sid' => $this->nasSession
        ];
        
        $infoResponse = $this->makeHttpRequest($infoUrl, 'GET', $infoData);
        
        if (!$infoResponse || !$infoResponse['success']) {
            return [
                'success' => false,
                'message' => 'Failed to get path info',
                'debug_info' => $infoResponse
            ];
        }
        
        $listUrl = $this->nasBaseUrl . '/webapi/entry.cgi';
        $listData = [
            'api' => 'SYNO.FileStation.List',
            'version' => '2',
            'method' => 'list',
            'folder_path' => $this->nasTargetPath,
            'filetype' => 'dir',
            '_sid' => $this->nasSession
        ];
        
        $response = $this->makeHttpRequest($listUrl, 'GET', $listData);
        
        if ($response && isset($response['success']) && $response['success']) {
            return [
                'success' => true,
                'folders' => $response['data']['files'] ?? []
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to list folders: ' . ($response['error']['code'] ?? 'Unknown error'),
            'debug_info' => $response
        ];
    }
    
    /**
     * Process files in a specific folder
     */
    private function processFolderFiles($folder)
    {
        $filesUrl = $this->nasBaseUrl . '/webapi/entry.cgi';
        $filesData = [
            'api' => 'SYNO.FileStation.List',
            'version' => '2',
            'method' => 'list',
            'folder_path' => $folder['path'],
            'filetype' => 'file',
            'additional' => 'size,time',
            '_sid' => $this->nasSession
        ];
        
        try {
            $response = $this->makeHttpRequest($filesUrl, 'GET', $filesData);
            
            if ($response && isset($response['success']) && $response['success']) {
                return [
                    'success' => true,
                    'data' => [
                        'folder' => $folder,
                        'files' => $response['data']['files'] ?? []
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to get files for folder: ' . $folder['path'],
                'debug_info' => $response
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception while processing folder: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Extract data from file path according to specified rules
     * Also validates the file against naming conventions
     */
    private function extractDataFromPath($filePath)
    {
        $pathParts = explode('/', $filePath);
        $secondLastPart = $pathParts[count($pathParts) - 2] ?? '';
        $fileName = basename($filePath);
        
        $validationErrors = [];
        
        // Validate file extension
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($fileExtension !== 'pdf') {
            $validationErrors[] = 'File is not a PDF (extension: .' . $fileExtension . ')';
        }
        
        // Extract industry from folder path
        $underscoreParts = explode('_', $secondLastPart);
        
        if (count($underscoreParts) == 4) {
            $industry = $underscoreParts[1];
        } elseif (count($underscoreParts) == 3) {
            $industry = $underscoreParts[0];
        } else {
            $industry = '';
            $validationErrors[] = 'Unable to extract industry from folder path: ' . $secondLastPart;
        }
        
        // Extract indicator, company, year from filename
        // Pattern: {indicator}_{company}_{year}.pdf
        $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
        $fileNameParts = explode('_', $fileNameWithoutExt);
        
        $indicator = '';
        $company = '';
        $year = '';
        
        if (count($fileNameParts) < 3) {
            $validationErrors[] = 'Filename does not follow pattern {indicator}_{company}_{year}.pdf (parts: ' . count($fileNameParts) . ')';
        } else {
            $indicator = trim($fileNameParts[0]);
            $year = trim(end($fileNameParts)); // Last part is year
            
            // Company is everything between indicator and year
            $companyParts = array_slice($fileNameParts, 1, -1);
            $company = trim(implode('_', $companyParts));
            
            // Validate extracted data
            if (empty($indicator)) {
                $validationErrors[] = 'Indicator is empty';
            }
            
            if (empty($company)) {
                $validationErrors[] = 'Company is empty';
            }
            
            if (empty($year)) {
                $validationErrors[] = 'Year is empty';
            } elseif (!preg_match('/^\d{4}$/', $year)) {
                $validationErrors[] = 'Year is not a 4-digit number: ' . $year;
            } elseif ((int)$year < 2000 || (int)$year > (date('Y') + 1)) {
                $validationErrors[] = 'Year is out of reasonable range: ' . $year;
            }
        }
        
        // Additional validation for industry
        if (empty($industry)) {
            $validationErrors[] = 'Industry is empty';
        }
        
        $isValid = empty($validationErrors);
        
        return [
            'file_path' => $filePath,
            'industry' => $industry,
            'indicator' => $indicator,
            'company' => $company,
            'year' => $year,
            'file_name' => $fileName,
            'is_valid' => $isValid,
            'validation_errors' => $validationErrors,
            'extracted_parts' => [
                'folder_path' => $secondLastPart,
                'filename_parts' => $fileNameParts,
                'file_extension' => $fileExtension
            ]
        ];
    }
    
    /**
     * Generate UUID v4
     */
    private function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Download and process file from NAS
     */
    private function downloadAndProcessFile($fileData)
    {
        try {
            // Get original filename and extension
            $originalFileName = basename($fileData['file_path']);
            $pathInfo = pathinfo($originalFileName);
            $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
            
            // Generate UUID as local filename to avoid Chinese encoding issues
            $uuid = $this->generateUuid();
            $localFilePath = $this->nasDownloadPath . '/sync_' . $uuid . $extension;
            
            $localDir = dirname($localFilePath);
            if (!is_dir($localDir)) {
                if (!mkdir($localDir, 0755, true)) {
                    return [
                        'success' => false,
                        'message' => 'Failed to create download directory: ' . $localDir
                    ];
                }
            }
            
            $downloadResult = $this->safeDownloadFile($fileData['file_path'], $localFilePath);
            
            if ($downloadResult['success']) {
                return [
                    'success' => true,
                    'message' => 'File downloaded successfully',
                    'local_path' => $localFilePath,
                    'original_filename' => $originalFileName,
                    'file_size' => filesize($localFilePath)
                ];
            }
            
            return $downloadResult;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Download process failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Safely download file from NAS
     */
    private function safeDownloadFile($filePath, $localPath)
    {
        try {
            $downloadUrl = $this->nasBaseUrl . '/webapi/entry.cgi';
            $downloadData = [
                'api' => 'SYNO.FileStation.Download',
                'version' => '2',
                'method' => 'download',
                'path' => $filePath,
                'mode' => 'download',
                '_sid' => $this->nasSession
            ];
            
            $downloadUrlFull = $downloadUrl . '?' . http_build_query($downloadData);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $downloadUrlFull,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_USERAGENT => 'NAS Integration Client',
                CURLOPT_HEADER => false
            ]);
            
            $fileContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return [
                    'success' => false,
                    'message' => 'Curl error: ' . $curlError
                ];
            }
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'message' => 'HTTP error: ' . $httpCode
                ];
            }
            
            if (file_put_contents($localPath, $fileContent) === false) {
                return [
                    'success' => false,
                    'message' => 'Failed to save file to: ' . $localPath
                ];
            }
            
            return [
                'success' => true,
                'message' => 'File downloaded successfully',
                'local_path' => $localPath,
                'file_size' => strlen($fileContent)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Safe download failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload file to external API
     */
    private function uploadFileToExternalApi($fileData, $localFilePath, $originalFileName = null)
    {
        try {
            $jsonResult = $this->uploadAndProcessViaJson($fileData, $localFilePath, $originalFileName);
            if ($jsonResult['success']) {
                $jsonResult['upload_method'] = 'json';
                return $jsonResult;
            }
            
            $formResult = $this->uploadAndProcessViaFormData($fileData, $localFilePath, $originalFileName);
            $formResult['upload_method'] = 'form-data';
            $formResult['json_error'] = $jsonResult['message'] ?? 'JSON upload failed';
            
            return $formResult;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Upload process failed: ' . $e->getMessage(),
                'upload_method' => 'error'
            ];
        }
    }
    
    /**
     * Upload via JSON format
     */
    private function uploadAndProcessViaJson($fileData, $localFilePath, $originalFileName = null)
    {
        try {
            $apiUrl = $this->uploadJsonApiUrl;
            
            if (!file_exists($localFilePath)) {
                return [
                    'success' => false,
                    'message' => 'Local file not found: ' . $localFilePath,
                    'api_url' => $apiUrl
                ];
            }
            
            // Use passed original filename, fallback to extracted filename from path
            if ($originalFileName) {
                $sourceFileName = $originalFileName;
            } else {
                $sourceFileName = explode('/', $fileData['file_path']);
                $sourceFileName = end($sourceFileName);
            }
            
            $sendData = [
                'industry' => $fileData['industry'],
                'indicator' => $fileData['indicator'],
                'company' => $fileData['company'],
                'year' => $fileData['year'],
                'file_name' => $sourceFileName,
                'uploaded_file' => base64_encode(file_get_contents($localFilePath))
            ];
            
            $postData = json_encode($sendData);
            $headers = [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            ];
            
            $response = $this->makeHttpRequest($apiUrl, 'POST', $postData, $headers);
            
            return [
                'success' => isset($response['success']) && $response['success'],
                'message' => $response['message'] ?? 'JSON upload completed',
                'response_data' => $response,
                'api_url' => $apiUrl,
                'send_data_preview' => [
                    'industry' => $sendData['industry'],
                    'indicator' => $sendData['indicator'],
                    'company' => $sendData['company'],
                    'year' => $sendData['year'],
                    'file_name' => $sendData['file_name'],
                    'file_size' => strlen($sendData['uploaded_file'])
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'JSON upload failed: ' . $e->getMessage(),
                'api_url' => $apiUrl ?? 'Not available'
            ];
        }
    }
    
    /**
     * Upload via form data format
     */
    private function uploadAndProcessViaFormData($fileData, $localFilePath, $originalFileName = null)
    {
        try {
            $apiUrl = $this->uploadFormApiUrl;
            
            if (!file_exists($localFilePath)) {
                return [
                    'success' => false,
                    'message' => 'Local file not found: ' . $localFilePath,
                    'api_url' => $apiUrl
                ];
            }
            
            // Use passed original filename, fallback to extracted filename from path
            if ($originalFileName) {
                $sourceFileName = $originalFileName;
            } else {
                $sourceFileName = explode('/', $fileData['file_path']);
                $sourceFileName = end($sourceFileName);
            }
            
            $postFields = [
                'industry' => $fileData['industry'],
                'indicator' => $fileData['indicator'],
                'company' => $fileData['company'],
                'year' => $fileData['year'],
                'file_name' => $sourceFileName,
                'uploaded_file' => new \CURLFile($localFilePath, mime_content_type($localFilePath), $sourceFileName)
            ];
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 60
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return [
                    'success' => false,
                    'message' => 'Form data upload curl error: ' . $curlError,
                    'api_url' => $apiUrl
                ];
            }
            
            $responseData = json_decode($response, true);
            
            return [
                'success' => $httpCode == 200,
                'message' => $responseData['message'] ?? 'Form data upload completed',
                'response_data' => $responseData,
                'http_code' => $httpCode,
                'api_url' => $apiUrl
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Form data upload failed: ' . $e->getMessage(),
                'api_url' => $apiUrl ?? 'Not available'
            ];
        }
    }
    
    /**
     * Test batch functionality without NAS connection
     * GET /api/nas/test-batch?test_batch_size=N
     */
    public function testBatch()
    {
        try {
            $processLogs = [];
            
            // Get test batch size parameter
            $request = service('request');
            $testBatchSize = $request->getGet('test_batch_size') ?: $request->getPost('test_batch_size');
            $testBatchSize = $testBatchSize ? (int)$testBatchSize : null;
            
            if ($testBatchSize && $testBatchSize > 0) {
                $processLogs[] = $this->logProcess('BATCH_MODE_ENABLED', 'Test batch mode enabled', ['batch_size' => $testBatchSize]);
            }
            
            $processLogs[] = $this->logProcess('START', 'Beginning batch test process');
            
            // Step 0: Validate API Key
            $processLogs[] = $this->logProcess('AUTH_START', 'Starting API key validation');
            $apiKeyValidation = $this->validateApiKey();
            if (!$apiKeyValidation['valid']) {
                $processLogs[] = $this->logProcess('AUTH_FAILED', 'API key validation failed', $apiKeyValidation);
                return $this->respond([
                    'success' => false,
                    'message' => 'Authentication required',
                    'error' => $apiKeyValidation['message'],
                    'error_code' => $apiKeyValidation['error_code'],
                    'hint' => 'Please include X-API-Key header with a valid API key'
                ], 401);
            }
            $processLogs[] = $this->logProcess('AUTH_SUCCESS', 'API key validation successful', ['api_key_partial' => $apiKeyValidation['api_key']]);
            
            // Step 1: Get files from database for simulation
            $allDatabaseFiles = $this->nasSyncLogModel->findAll();
            $processLogs[] = $this->logProcess('DATABASE_FILES_LOADED', 'Loaded files from database', ['total_files' => count($allDatabaseFiles)]);
            
            // Step 2: Filter files that need processing (failed or pending)
            $filesToProcess = [];
            $failedFiles = [];
            $pendingFiles = [];
            $successfulFiles = [];
            
            foreach ($allDatabaseFiles as $file) {
                if ($file['status'] === 'failed') {
                    $failedFiles[] = $file;
                    $filesToProcess[] = $file;
                } elseif ($file['status'] === 'pending') {
                    $pendingFiles[] = $file;
                    $filesToProcess[] = $file;
                } elseif ($file['status'] === 'uploaded') {
                    $successfulFiles[] = $file;
                }
            }
            
            $processLogs[] = $this->logProcess('FILE_FILTERING_COMPLETE', 'File filtering completed', [
                'failed_files' => count($failedFiles),
                'pending_files' => count($pendingFiles),
                'successful_files' => count($successfulFiles),
                'total_to_process' => count($filesToProcess)
            ]);
            
            // Step 3: Apply batch size limit
            $originalFileCount = count($filesToProcess);
            $batchLimited = false;
            
            if ($testBatchSize && $testBatchSize > 0 && count($filesToProcess) > $testBatchSize) {
                $filesToProcess = array_slice($filesToProcess, 0, $testBatchSize);
                $batchLimited = true;
                $processLogs[] = $this->logProcess('BATCH_LIMIT_APPLIED', 'Test batch size limit applied', [
                    'original_file_count' => $originalFileCount,
                    'batch_size' => $testBatchSize,
                    'files_to_process' => count($filesToProcess),
                    'files_excluded' => $originalFileCount - $testBatchSize
                ]);
            }
            
            $processLogs[] = $this->logProcess('BATCH_TEST_COMPLETE', 'Batch test process completed');
            
            return $this->respond([
                'success' => true,
                'message' => 'Batch test completed successfully',
                'test_mode' => true,
                'summary' => [
                    'total_files_in_database' => count($allDatabaseFiles),
                    'failed_files' => count($failedFiles),
                    'pending_files' => count($pendingFiles),
                    'successful_files' => count($successfulFiles),
                    'total_available_for_processing' => $originalFileCount,
                    'files_selected_for_processing' => count($filesToProcess)
                ],
                'batch_processing' => [
                    'enabled' => true,
                    'mode' => $batchLimited ? 'test_batch_mode' : 'process_all_files',
                    'test_batch_size' => $testBatchSize,
                    'batch_limited' => $batchLimited,
                    'original_file_count' => $originalFileCount,
                    'processed_file_count' => count($filesToProcess),
                    'files_excluded_by_batch' => $batchLimited ? ($originalFileCount - count($filesToProcess)) : 0,
                    'note' => $batchLimited ? 
                              "Test batch mode: Selected {$testBatchSize} files out of {$originalFileCount} available files for processing" : 
                              'All available files would be processed'
                ],
                'selected_files_preview' => array_map(function($file) {
                    return [
                        'id' => $file['id'],
                        'file_name' => $file['file_name'],
                        'status' => $file['status'],
                        'industry' => $file['industry'],
                        'company' => $file['company'],
                        'year' => $file['year']
                    ];
                }, array_slice($filesToProcess, 0, 5)), // Show first 5 files
                'note' => 'This is a test mode that demonstrates batch functionality without connecting to NAS or processing files.',
                'process_logs' => $processLogs
            ]);
            
        } catch (\Exception $e) {
            $errorLog = $this->logProcess('BATCH_TEST_ERROR', 'Batch test process failed with exception', ['exception' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            
            return $this->respond([
                'success' => false,
                'message' => 'Batch test process failed',
                'error' => $e->getMessage(),
                'debug_info' => [
                    'exception_type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }
    
    /**
     * Logout from NAS
     */
    private function logoutNas()
    {
        if (empty($this->nasSession)) {
            return;
        }
        
        try {
            $logoutUrl = $this->nasBaseUrl . '/webapi/auth.cgi';
            $logoutData = [
                'api' => 'SYNO.API.Auth',
                'version' => '3',
                'method' => 'logout',
                'session' => 'FileStation',
                '_sid' => $this->nasSession
            ];
            
            $this->makeHttpRequest($logoutUrl, 'POST', $logoutData);
            $this->nasSession = null;
            
        } catch (\Exception $e) {
        }
    }
    
    /**
     * Make HTTP request with error handling
     */
    private function makeHttpRequest($url, $method = 'GET', $data = [], $headers = [])
    {
        $ch = curl_init();
        
        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'NAS Integration Client'
        ];
        
        if (strtoupper($method) === 'POST') {
            $curlOptions[CURLOPT_POST] = true;
            
            if (is_string($data)) {
                $curlOptions[CURLOPT_POSTFIELDS] = $data;
            } else {
                $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
            }
        } elseif (!empty($data) && is_array($data)) {
            $separator = (strpos($url, '?') !== false) ? '&' : '?';
            $curlOptions[CURLOPT_URL] = $url . $separator . http_build_query($data);
        }
        
        if (!empty($headers)) {
            $curlOptions[CURLOPT_HTTPHEADER] = $headers;
        }
        
        curl_setopt_array($ch, $curlOptions);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new \Exception('HTTP request failed: ' . $curlError);
        }
        
        if ($httpCode >= 400) {
            throw new \Exception('HTTP request failed with status: ' . $httpCode);
        }
        
        $decodedResponse = json_decode($response, true);
        return $decodedResponse !== null ? $decodedResponse : $response;
    }
}