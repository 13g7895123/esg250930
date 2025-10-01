# Migration 執行指南

## 問題說明
量表編輯儲存時出現 `Call to a member function getFirstRow() on bool` 錯誤，原因是相關資料表尚未建立。

## 解決方案：執行 Migration

### 方法 1: 使用命令列 (推薦)

```bash
# 在 backend 目錄下執行
cd backend
php spark migrate

# 如果需要指定環境
CI_ENVIRONMENT=production php spark migrate

# 查看 migration 狀態
php spark migrate:status
```

### 方法 2: 使用 Windows 命令提示字元

```cmd
cd C:\path\to\esg-csr-new\backend
php spark migrate
```

### 方法 3: 使用 XAMPP/WAMP

```bash
# XAMPP
C:\xampp\php\php.exe C:\path\to\esg-csr-new\backend\spark migrate

# WAMP
C:\wamp64\bin\php\php8.x.x\php.exe C:\path\to\esg-csr-new\backend\spark migrate
```

## Migration 檔案資訊

**檔案位置**: `backend/app/Database/Migrations/2025-09-30-000001_CreateScaleTables.php`

**將建立的資料表**:
1. `probability_scales` - 可能性量表主表
2. `probability_scale_columns` - 可能性量表欄位定義
3. `probability_scale_rows` - 可能性量表資料列
4. `impact_scales` - 財務衝擊量表主表
5. `impact_scale_columns` - 財務衝擊量表欄位定義
6. `impact_scale_rows` - 財務衝擊量表資料列

## 驗證 Migration 成功

執行成功後，你應該會看到類似以下訊息：
```
Running: 2025-09-30-000001_App\Database\Migrations\CreateScaleTables
Migrated: 2025-09-30-000001_App\Database\Migrations\CreateScaleTables
```

## 檢查資料表是否建立

使用 MySQL 客戶端或 phpMyAdmin 確認：

```sql
SHOW TABLES LIKE '%scale%';

-- 應該顯示:
-- impact_scale_columns
-- impact_scale_rows
-- impact_scales
-- probability_scale_columns
-- probability_scale_rows
-- probability_scales
```

## 回滾 Migration (如需要)

如果需要重新執行：

```bash
# 回滾最後一次 migration
php spark migrate:rollback

# 重新執行
php spark migrate
```

## 疑難排解

### 錯誤: "Class 'mysqli' not found"
- 確保 PHP 已啟用 mysqli 擴展
- 檢查 php.ini 中 `extension=mysqli` 是否已取消註解

### 錯誤: "Unable to connect to the database"
- 檢查 `backend/.env` 檔案中的資料庫設定
- 確認資料庫服務已啟動

### 錯誤: "Table already exists"
- 資料表已存在，可以跳過或先執行 rollback

## 額外的錯誤處理改進

除了執行 migration，我也在 `ScaleController.php` 中加入了完整的錯誤處理，當儲存失敗時會返回詳細的錯誤訊息，而不是拋出 "getFirstRow() on bool" 錯誤。