# Synology NAS Integration API

This API provides integration with Synology NAS to synchronize folders and upload file information to external systems.

## Features

- **No Authentication Required**: This API bypasses login authentication for automated processes
- **Synology NAS Integration**: Connects to Synology NAS using File Station API
- **Folder Discovery**: Lists all folders in specified NAS paths
- **File Analysis**: Extracts metadata from all files in each folder
- **External API Upload**: Automatically uploads processed data to external systems
- **Comprehensive Logging**: Tracks all sync operations with detailed logs

## Endpoints

### 1. Full Sync Operation
```
GET /api/nas/sync
```

Performs complete synchronization:
1. Authenticates with Synology NAS
2. Retrieves all folders from configured path
3. Analyzes files in each folder
4. Uploads processed data to external API
5. Logs all operations

**Response Example:**
```json
{
    "success": true,
    "message": "NAS sync completed successfully",
    "processed_folders": 5,
    "details": [
        {
            "success": true,
            "data": {
                "folder_name": "Documents",
                "folder_path": "/volume1/target_folder/Documents",
                "folder_size": 1048576,
                "file_count": 12,
                "files": [...],
                "processed_at": "2025-08-27 12:30:45"
            },
            "upload_status": {
                "success": true,
                "message": "Data uploaded successfully"
            }
        }
    ]
}
```

### 2. Get Folders Only
```
GET /api/nas/folders
```

Returns list of folders without processing files.

**Response Example:**
```json
{
    "success": true,
    "folders": [
        {
            "name": "Documents",
            "path": "/volume1/target_folder/Documents",
            "size": 1048576
        },
        {
            "name": "Images",
            "path": "/volume1/target_folder/Images",
            "size": 2097152
        }
    ]
}
```

### 3. Connection Test
```
GET /api/nas/test
```

Tests NAS connectivity and QuickConnect resolution (useful for troubleshooting).

**Response Example:**
```json
{
    "success": true,
    "message": "NAS connection test completed",
    "results": {
        "connection_method": "QuickConnect",
        "base_url": "https://mynas123.quickconnect.to",
        "quickconnect_id": "mynas123",
        "target_path": "/volume1/target_folder",
        "connectivity": {
            "success": true,
            "http_code": 200,
            "response_data": { ... }
        },
        "authentication": {
            "success": true
        }
    }
}
```

## Configuration

Update your `.env` file with the following settings:

### Method 1: Direct IP/Domain Connection
```env
# Synology NAS Configuration - Direct Connection
NAS_HOST=192.168.1.100
NAS_PORT=5000
NAS_USE_QUICKCONNECT=false

# Common settings
NAS_USERNAME=nas_admin_user
NAS_PASSWORD=nas_admin_password
NAS_TARGET_PATH=/volume1/target_folder
NAS_DOWNLOAD_PATH=/tmp/nas_downloads

# External API Configuration
EXTERNAL_API_URL=https://external-project-api.com/api
EXTERNAL_API_TOKEN=your_external_api_token
```

### Method 2: QuickConnect (Recommended for remote access)
```env
# Synology NAS Configuration - QuickConnect
NAS_USE_QUICKCONNECT=true
NAS_QUICKCONNECT_ID=your-quickconnect-id
NAS_QUICKCONNECT_PROTOCOL=https
NAS_QUICKCONNECT_PORT=

# Common settings
NAS_USERNAME=nas_admin_user
NAS_PASSWORD=nas_admin_password
NAS_TARGET_PATH=/volume1/target_folder
NAS_DOWNLOAD_PATH=/tmp/nas_downloads

# External API Configuration
EXTERNAL_API_URL=https://external-project-api.com/api
EXTERNAL_API_TOKEN=your_external_api_token
```

### Configuration Parameters

#### Direct Connection
- **NAS_HOST**: IP address or domain name of your Synology NAS
- **NAS_PORT**: Port number (usually 5000 for HTTP, 5001 for HTTPS)
- **NAS_USE_QUICKCONNECT**: Set to `false` for direct connection

#### QuickConnect Configuration
- **NAS_USE_QUICKCONNECT**: Set to `true` to enable QuickConnect
- **NAS_QUICKCONNECT_ID**: Your QuickConnect ID (e.g., `mynas123`)
- **NAS_QUICKCONNECT_PROTOCOL**: Protocol to use (`http` or `https`, recommended: `https`)
- **NAS_QUICKCONNECT_PORT**: Custom port (leave empty for default)

#### Common Settings
- **NAS_USERNAME**: NAS admin username with File Station access
- **NAS_PASSWORD**: Password for the NAS user
- **NAS_TARGET_PATH**: Path to scan for folders (e.g., `/volume1/shared`)
- **NAS_DOWNLOAD_PATH**: Local temporary path for file downloads
- **EXTERNAL_API_URL**: Base URL of the external API to upload data
- **EXTERNAL_API_TOKEN**: Authentication token for external API

## File Information Structure

For each file, the following metadata is collected:

```json
{
    "filename": "document.pdf",
    "path": "/volume1/target_folder/Documents/document.pdf",
    "size": 245760,
    "modified_time": 1693123200,
    "file_type": "file",
    "extension": "pdf"
}
```

## Database Logging

All sync operations are logged in the `nas_sync_logs` table:

```sql
CREATE TABLE nas_sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sync_type ENUM('full', 'folder', 'test') DEFAULT 'full',
    folder_name VARCHAR(255) NULL,
    folder_path TEXT NULL,
    file_count INT UNSIGNED DEFAULT 0,
    total_size BIGINT UNSIGNED DEFAULT 0,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    external_api_response JSON NULL,
    sync_started_at DATETIME NULL,
    sync_completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Error Handling

The API provides comprehensive error handling:

- **Authentication Errors**: NAS login failures
- **Network Errors**: Connection timeouts or failures
- **Permission Errors**: Insufficient access to folders/files
- **External API Errors**: Upload failures to external systems

## Security Considerations

⚠️ **Important**: This API bypasses authentication for automation purposes. Ensure:

1. Network security is properly configured
2. API is only accessible from trusted sources
3. NAS credentials are properly secured
4. External API tokens are rotated regularly

## Usage Examples

### Basic Sync
```bash
curl -X GET "http://your-server:9217/api/nas/sync"
```

### Test Connection (Recommended First Step)
```bash
curl -X GET "http://your-server:9217/api/nas/test"
```

### Get Folders Only
```bash
curl -X GET "http://your-server:9217/api/nas/folders"
```

### QuickConnect Specific Usage
```bash
# Test QuickConnect resolution and connectivity
curl -X GET "http://localhost:9217/api/nas/test"

# Full sync using QuickConnect
curl -X GET "http://localhost:9217/api/nas/sync"
```

### Automated Sync (Cron Job)
```bash
# Add to crontab for daily sync at 2 AM
0 2 * * * curl -X GET "http://localhost:9217/api/nas/sync" >> /var/log/nas-sync.log 2>&1
```

## Troubleshooting

### Common Issues

1. **Authentication Failed**
   - Check NAS credentials in `.env`
   - Verify user has File Station access
   - Ensure NAS is accessible on specified port

2. **No Folders Found**
   - Check `NAS_TARGET_PATH` exists
   - Verify user has read permissions on target path
   - Ensure path format is correct (`/volume1/...`)

3. **External API Upload Failed**
   - Check `EXTERNAL_API_URL` and `EXTERNAL_API_TOKEN`
   - Verify external API is online and accepting requests
   - Check network connectivity to external API

4. **QuickConnect Issues**
   - Verify `NAS_QUICKCONNECT_ID` is correct
   - Ensure QuickConnect is enabled on your NAS
   - Try both `http` and `https` protocols
   - Test with `/api/nas/test` endpoint first
   - Check if custom port is required

5. **SSL/HTTPS Issues**
   - For self-signed certificates, the API ignores SSL verification
   - Try changing `NAS_QUICKCONNECT_PROTOCOL` to `http` if HTTPS fails
   - Check firewall settings on both server and NAS

### Debug Mode

Add debug logging by checking the `nas_sync_logs` table:

```sql
SELECT * FROM nas_sync_logs ORDER BY created_at DESC LIMIT 10;
```

## API Response Codes

- **200**: Success
- **401**: NAS Authentication failed
- **500**: Internal server error (check logs for details)

## Future Enhancements

- [ ] File download functionality
- [ ] Incremental sync (only changed files)
- [ ] Multiple NAS support
- [ ] Webhook notifications
- [ ] File content analysis
- [ ] Batch processing optimization