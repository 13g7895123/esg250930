<?php

namespace App\Models;

use CodeIgniter\Model;

class NasSyncLogModel extends Model
{
    protected $table = 'nas_sync_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'file_path',
        'industry',
        'indicator',
        'company',
        'year',
        'file_name',
        'status',
        'local_file_path',
        'upload_response',
        'error_message',
        'created_at',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'file_path' => 'required',
        'status' => 'required|in_list[pending,downloaded,uploaded,failed]'
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
     * Get all existing file paths from database
     */
    public function getExistingFilePaths()
    {
        return $this->select('file_path')
                   ->findAll();
    }

    /**
     * Check if file path exists in database
     */
    public function filePathExists($filePath)
    {
        return $this->where('file_path', $filePath)->countAllResults() > 0;
    }
    
    /**
     * Check if file path exists and is successfully uploaded
     */
    public function isFileSuccessfullyProcessed($filePath)
    {
        return $this->where('file_path', $filePath)
                   ->where('status', 'uploaded')
                   ->countAllResults() > 0;
    }
    
    /**
     * Get failed files that can be retried
     */
    public function getFailedFiles()
    {
        return $this->where('status', 'failed')
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get file by path
     */
    public function getFileByPath($filePath)
    {
        return $this->where('file_path', $filePath)->first();
    }

    /**
     * Insert new file record
     */
    public function insertFileRecord($fileData)
    {
        return $this->insert([
            'file_path' => $fileData['file_path'],
            'industry' => $fileData['industry'],
            'indicator' => $fileData['indicator'],
            'company' => $fileData['company'],
            'year' => $fileData['year'],
            'file_name' => $fileData['file_name'],
            'status' => 'pending'
        ]);
    }

    /**
     * Update file status and local path after download
     */
    public function updateFileDownloaded($id, $localFilePath)
    {
        return $this->update($id, [
            'status' => 'downloaded',
            'local_file_path' => $localFilePath
        ]);
    }

    /**
     * Update file status after successful upload
     */
    public function updateFileUploaded($id, $uploadResponse)
    {
        // Ensure uploadResponse is properly formatted for JSON storage
        $jsonResponse = is_string($uploadResponse) ? $uploadResponse : json_encode($uploadResponse);
        
        return $this->update($id, [
            'status' => 'uploaded',
            'upload_response' => $jsonResponse
        ]);
    }

    /**
     * Update file status on failure
     */
    public function updateFileFailure($id, $errorMessage)
    {
        return $this->update($id, [
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Get pending files for processing
     */
    public function getPendingFiles()
    {
        return $this->where('status', 'pending')
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
    }

    /**
     * Get downloaded files ready for upload
     */
    public function getDownloadedFiles()
    {
        return $this->where('status', 'downloaded')
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
    }

    /**
     * Reset file status to pending for retry
     */
    public function resetFileForRetry($id)
    {
        return $this->update($id, [
            'status' => 'pending',
            'local_file_path' => null,
            'upload_response' => null,
            'error_message' => null
        ]);
    }
    
    /**
     * Get file statistics
     */
    public function getFileStats()
    {
        return [
            'total_files' => $this->countAllResults(),
            'pending_files' => $this->where('status', 'pending')->countAllResults(),
            'downloaded_files' => $this->where('status', 'downloaded')->countAllResults(),
            'uploaded_files' => $this->where('status', 'uploaded')->countAllResults(),
            'failed_files' => $this->where('status', 'failed')->countAllResults()
        ];
    }
}