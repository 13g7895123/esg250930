# Migration 執行步驟說明

## 問題描述

在 `/api/v1/risk-assessment/templates/8/factors/reorder` 這個 API 端點執行時，出現以下錯誤：

```
ERROR - mysqli_sql_exception: Unknown column 'risk_factors.sort_order' in 'field list'
ERROR - RiskFactorModel::reorderFactors - 資料庫交易失敗
```

### 錯誤發生位置
- **檔案**: `app/Models/RiskAssessment/RiskFactorModel.php`
- **行數**: 172
- **方法**: `reorderFactors()`

### 根本原因

在 `2024-09-17-110001_RemoveUnnecessaryFieldsFromRiskFactors.php` 這個 migration 中，`sort_order` 欄位被移除了（第18行），導致拖曳排序功能無法正常運作。

---

## 解決方案

建立新的 migration 檔案重新添加 `sort_order` 欄位。

### Migration 檔案資訊
- **檔案名稱**: `2025-10-11-160000_AddSortOrderBackToRiskFactors.php`
- **檔案路徑**: `app/Database/Migrations/2025-10-11-160000_AddSortOrderBackToRiskFactors.php`
- **功能**: 重新添加 `sort_order` 欄位到 `risk_factors` 表

---

## 詳細執行步驟

### 步驟 1: 確認當前 Migration 狀態

首先，檢查目前資料庫的 migration 執行狀態：

```bash
cd /home/jarvis/project/job/twnict/esg-csr-new/backend
/usr/bin/php spark migrate:status
```

**預期輸出**：
```
+-----+--------------------------+---------------------+----------+
| ... | Filename                 | Migrated On         | Group    |
+-----+--------------------------+---------------------+----------+
| ... | 2024-09-17-110001...     | 2024-XX-XX XX:XX:XX | default  |
| ... | 2025-10-11-160000...     | (not migrated)      | default  |
+-----+--------------------------+---------------------+----------+
```

### 步驟 2: 備份資料庫（重要！）

在執行 migration 之前，**強烈建議**先備份資料庫：

```bash
# 方法 1: 使用 mysqldump（推薦）
mysqldump -u [username] -p [database_name] > backup_before_migration_$(date +%Y%m%d_%H%M%S).sql

# 方法 2: 如果使用 Docker
docker exec [container_name] mysqldump -u [username] -p[password] [database_name] > backup_before_migration_$(date +%Y%m%d_%H%M%S).sql
```

### 步驟 3: 執行 Migration

執行新建立的 migration：

```bash
cd /home/jarvis/project/job/twnict/esg-csr-new/backend
/usr/bin/php spark migrate
```

**預期輸出**：
```
Running: 2025-10-11-160000_AddSortOrderBackToRiskFactors
App\Database\Migrations\AddSortOrderBackToRiskFactors
Migrated: 2025-10-11-160000_AddSortOrderBackToRiskFactors
```

**檢查 log 訊息**：
```bash
tail -f writable/logs/log-$(date +%Y-%m-%d).log
```

應該會看到：
```
INFO - 開始執行：重新添加 sort_order 欄位到 risk_factors 表
INFO - 成功添加 sort_order 欄位到 risk_factors 表
INFO - 已為 X 筆風險因子記錄設定預設排序值 0
INFO - 完成執行：重新添加 sort_order 欄位到 risk_factors 表
```

### 步驟 4: 驗證欄位已添加

確認 `sort_order` 欄位已成功添加：

```bash
# 方法 1: 使用 MySQL CLI
mysql -u [username] -p -e "DESCRIBE [database_name].risk_factors;"

# 方法 2: 使用 Docker
docker exec -it [container_name] mysql -u [username] -p[password] -e "DESCRIBE [database_name].risk_factors;"
```

**預期輸出**：
```
+-------------+--------------+------+-----+---------+----------------+
| Field       | Type         | Null | Key | Default | Extra          |
+-------------+--------------+------+-----+---------+----------------+
| id          | bigint(20)   | NO   | PRI | NULL    | auto_increment |
| ...         | ...          | ...  | ... | ...     | ...            |
| description | text         | YES  |     | NULL    |                |
| sort_order  | int(11)      | NO   |     | 0       |                |
| status      | enum(...)    | NO   |     | active  |                |
| created_at  | timestamp    | NO   |     | ...     |                |
| updated_at  | timestamp    | NO   |     | ...     |                |
+-------------+--------------+------+-----+---------+----------------+
```

### 步驟 5: 初始化排序值（可選）

所有現有記錄的 `sort_order` 預設為 0。您可以選擇以下方式初始化排序值：

**方法 1: 使用 SQL 依據 ID 順序初始化**
```sql
-- 為每個 template 的 factors 依據 id 設定遞增的排序值
UPDATE risk_factors rf
JOIN (
    SELECT id,
           ROW_NUMBER() OVER (PARTITION BY template_id ORDER BY id) as new_order
    FROM risk_factors
) t ON rf.id = t.id
SET rf.sort_order = t.new_order;
```

**方法 2: 使用前端拖曳排序功能手動設定**
- 登入系統
- 前往「風險評估範本管理」頁面
- 開啟「管理風險因子」模態框
- 使用拖曳功能調整順序
- 系統會自動呼叫 API 更新 `sort_order` 值

### 步驟 6: 測試 API 功能

測試拖曳排序 API 是否正常運作：

```bash
# 使用 curl 測試 API（請替換 templateId 和實際的 factor IDs）
curl -X POST http://localhost/api/v1/risk-assessment/templates/8/factors/reorder \
  -H "Content-Type: application/json" \
  -d '{
    "orders": [
      {"id": 1, "sort_order": 1},
      {"id": 2, "sort_order": 2},
      {"id": 3, "sort_order": 3}
    ]
  }'
```

**預期成功回應**：
```json
{
  "success": true,
  "message": "重新排序成功"
}
```

### 步驟 7: 檢查錯誤日誌

確認沒有新的錯誤訊息：

```bash
tail -f /home/jarvis/project/job/twnict/esg-csr-new/backend/writable/logs/log-$(date +%Y-%m-%d).log
```

應該**不再**看到以下錯誤：
```
ERROR - mysqli_sql_exception: Unknown column 'risk_factors.sort_order' in 'field list'
ERROR - RiskFactorModel::reorderFactors - 資料庫交易失敗
```

---

## 回滾步驟（如果需要）

如果需要回滾此 migration（不建議，除非有特殊原因）：

```bash
cd /home/jarvis/project/job/twnict/esg-csr-new/backend
/usr/bin/php spark migrate:rollback
```

這將移除 `sort_order` 欄位，並恢復到執行 migration 之前的狀態。

---

## 相關檔案清單

### 後端檔案
1. **Migration 檔案**:
   - `app/Database/Migrations/2025-10-11-160000_AddSortOrderBackToRiskFactors.php`
   - `app/Database/Migrations/2024-09-17-110001_RemoveUnnecessaryFieldsFromRiskFactors.php` (原始移除檔案)

2. **Model 檔案**:
   - `app/Models/RiskAssessment/RiskFactorModel.php` (line 172)

3. **Controller 檔案**:
   - `app/Controllers/Api/V1/RiskAssessment/RiskFactorController.php` (line 431)

### 前端檔案
1. **管理頁面**:
   - `frontend/pages/admin/risk-assessment/templates/index.vue`

2. **組件**:
   - `frontend/components/DataTable.vue` (拖曳排序功能)

---

## 常見問題 (FAQ)

### Q1: Migration 執行失敗怎麼辦？
**A**:
1. 檢查錯誤訊息：`tail -f writable/logs/log-*.log`
2. 確認資料庫連線正常
3. 確認 `risk_factors` 表存在
4. 如果欄位已存在，migration 會自動跳過

### Q2: 執行 migration 後，現有資料會遺失嗎？
**A**: 不會。此 migration 只是添加新欄位，不會修改或刪除現有資料。所有現有記錄的 `sort_order` 會自動設為預設值 0。

### Q3: 為什麼所有記錄的 sort_order 都是 0？
**A**: 這是預設值。您需要透過以下方式設定實際的排序值：
- 使用前端拖曳排序功能
- 執行 SQL 更新語句初始化排序值

### Q4: 如何確認 API 已經修復？
**A**:
1. 檢查錯誤日誌不再出現 "Unknown column" 錯誤
2. 使用前端拖曳排序功能測試
3. 使用 curl 直接測試 API 端點

### Q5: Migration 可以重複執行嗎？
**A**: 可以。Migration 包含欄位存在性檢查，如果 `sort_order` 欄位已存在，會自動跳過添加步驟。

---

## 驗證清單

執行完 migration 後，請確認以下項目：

- [ ] Migration 執行成功，沒有錯誤訊息
- [ ] `risk_factors` 表中存在 `sort_order` 欄位
- [ ] 欄位型態為 `INT(11)`，預設值為 `0`，不允許 `NULL`
- [ ] 所有現有記錄的 `sort_order` 值為 `0`
- [ ] 錯誤日誌中不再出現 "Unknown column" 錯誤
- [ ] 前端拖曳排序功能正常運作
- [ ] API 端點 `/api/v1/risk-assessment/templates/{id}/factors/reorder` 正常回應

---

## 技術細節

### 欄位定義
```php
'sort_order' => [
    'type'       => 'INT',           // 整數型態
    'constraint' => 11,              // 最大長度 11 位數
    'default'    => 0,               // 預設值為 0
    'null'       => false,           // 不允許 NULL
    'comment'    => '排序順序 (用於拖曳排序功能)',
    'after'      => 'description'    // 放在 description 欄位之後
]
```

### 相關 API 端點
- **URL**: `POST /api/v1/risk-assessment/templates/{templateId}/factors/reorder`
- **請求格式**:
  ```json
  {
    "orders": [
      {"id": 1, "sort_order": 1},
      {"id": 2, "sort_order": 2}
    ]
  }
  ```
- **成功回應**:
  ```json
  {
    "success": true,
    "message": "重新排序成功"
  }
  ```

---

**最後更新**: 2025-10-11
**建立者**: Claude Code
**文件版本**: 1.0
