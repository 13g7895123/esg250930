# Migration 執行指南

## 最新修正：答題頁面統一使用 Editor 頁面 (2025-10-02)

### 問題說明
系統原本有三個不同的頁面實現相似的功能：
1. **管理員編輯器** (`/admin/risk-assessment/editor/[mode]-[id]-[contentId]`)
2. **管理員結果查看** (`/admin/risk-assessment/questions/[id]/assignments/[userId]/results`)
3. **使用者答題** (`/web/risk-assessment/questions/[companyId]/answer/[questionId]/[contentId]`)

這導致：
- 程式碼重複（三個頁面共 2000+ 行）
- 樣式不一致
- 維護困難（同樣的修改需要改三個地方）

### 解決方案
將所有題目相關頁面統一使用 **Editor 頁面架構**，透過 `mode` 參數控制不同的顯示模式。

### 修正內容

#### 1. 新增 `answer` 模式到 `useEditorFeatures`
**檔案**：`frontend/composables/useEditorFeatures.js`

新增的 `answer` 模式特性：
- ✅ 顯示儲存按鈕
- ✅ 不顯示預覽按鈕
- ✅ 不顯示測試資料按鈕
- ✅ 顯示資訊圖示 (hover text)
- ✅ 使用 viewer-compact 量表模式
- ✅ 欄位可編輯（非唯讀）
- ✅ Section A/B 可收折
- ✅ 頁面標題：「風險評估作答」
- ✅ 頁面副標題：「請完整填寫以下表單內容」

#### 2. 創建 Web 端 Editor 頁面
**新檔案**：`frontend/pages/web/risk-assessment/editor/[mode]-[id]-[contentId].vue`

複製 admin editor 頁面到 web 端，使用完全相同的邏輯。

**新路徑格式**：
```
/web/risk-assessment/editor/answer-{assessmentId}-{contentId}
```

#### 3. 支援的所有模式
現在系統支援四種模式：
- **template**: 範本編輯模式（管理員編輯範本）
- **question**: 題目編輯模式（管理員編輯評估題目）
- **preview**: 預覽模式（查看使用者將看到的表單）
- **answer**: 答題模式（使用者填寫評估）✨ 新增

### 路徑對應表

| 功能 | 舊路徑 | 新路徑 |
|------|--------|--------|
| 管理員編輯範本 | `/admin/risk-assessment/templates/{id}/edit` | `/admin/risk-assessment/editor/template-{id}-{contentId}` |
| 管理員編輯題目 | `/admin/risk-assessment/questions/{id}/edit` | `/admin/risk-assessment/editor/question-{id}-{contentId}` |
| 管理員預覽 | - | `/admin/risk-assessment/editor/preview-{id}-{contentId}` |
| **使用者答題** | `/web/risk-assessment/questions/{companyId}/answer/{questionId}/{contentId}` | **`/web/risk-assessment/editor/answer-{assessmentId}-{contentId}`** ✨ |

### 優點

1. **程式碼統一**：
   - 所有題目相關頁面使用同一個 editor 架構
   - 減少 1000+ 行重複程式碼
   - 透過 `mode` 參數控制不同行為

2. **樣式一致**：
   - 所有頁面使用相同的 UI 組件
   - 顏色、間距、交互行為完全一致
   - 自動獲得深色模式支援

3. **維護性提升**：
   - 只需維護一個 editor 頁面
   - 新功能添加一次即可應用到所有模式
   - Bug 修復一次即可解決所有頁面

4. **擴展性**：
   - 未來可輕鬆新增新的模式（如 review, approve 等）
   - 所有模式自動繼承 editor 的新功能

### 注意事項

**重要**：需要更新所有指向舊答題路徑的連結，改為新的 editor 路徑格式。

例如：
```javascript
// 舊的
router.push(`/web/risk-assessment/questions/${companyId}/answer/${questionId}/${contentId}`)

// 新的
router.push(`/web/risk-assessment/editor/answer-${assessmentId}-${contentId}`)
```

---

## 答題頁面重構為共用組件 (2025-10-02)

### 問題說明
系統中有兩個頁面顯示 E-1~H-1 區塊，但它們使用不同的實現方式：
1. **管理員編輯器頁面** (`/admin/risk-assessment/editor/template-2-2`)
   - 路徑：`frontend/pages/admin/risk-assessment/editor/[mode]-[id]-[contentId].vue`
   - 之前使用 Editor 專用組件，已在前次重構中改為共用組件

2. **使用者答題頁面** (`/web/risk-assessment/questions/1/answer/18/16`)
   - 路徑：`frontend/pages/web/risk-assessment/questions/[companyId]/answer/[questionId]/[contentId].vue`
   - **之前直接在頁面內撰寫 HTML**，沒有使用組件化

### 修正內容
將答題頁面的 E-1~H-1 區塊重構為使用共用的 RiskAssessment 組件。

### 變更檔案
**frontend/pages/web/risk-assessment/questions/[companyId]/answer/[questionId]/[contentId].vue**

**替換內容**：
- E-1/E-2 區塊（約 80 行 HTML）→ `RiskSection` 組件（8 行）
- F-1/F-2 區塊（約 80 行 HTML）→ `OpportunitySection` 組件（8 行）
- G-1 區塊（約 40 行 HTML）→ `NegativeImpactSection` 組件（5 行）
- H-1 區塊（約 40 行 HTML）→ `PositiveImpactSection` 組件（5 行）

**程式碼減少**：約 240 行 → 26 行（減少 89%）

### 顏色一致性分析
**確認結果**：兩個頁面使用的顏色設定相同
- Focus ring: `focus:ring-blue-500`（藍色）
- 綠色按鈕: `bg-green-600`
- 標籤背景: `bg-blue-100 text-blue-800`（淺藍色）

共用組件本身已包含相同的顏色設定，因此重構後顏色保持一致。

### 優點
1. **程式碼重用**：減少重複程式碼約 214 行
2. **一致性**：三個頁面（編輯器、結果、答題）現在使用完全相同的 UI 組件
3. **維護性**：未來修改 E-1~H-1 UI 時只需更新共用組件
4. **深色模式**：答題頁面自動獲得深色模式支援
5. **無障礙性**：共用組件包含完整的 ARIA 屬性和鍵盤導航支援

---

## 編輯器頁面重構為共用組件 (2025-10-02)

### 修正內容
重構編輯器頁面 `frontend/pages/admin/risk-assessment/editor/[mode]-[id]-[contentId].vue` 以使用共用的 RiskAssessment 組件。

### 變更檔案
**frontend/pages/admin/risk-assessment/editor/[mode]-[id]-[contentId].vue**
- 將 `EditorRiskSection` 替換為 `RiskSection`
- 將 `EditorOpportunitySection` 替換為 `OpportunitySection`
- 將 `EditorImpactSection` (negative) 替換為 `NegativeImpactSection`
- 將 `EditorImpactSection` (positive) 替換為 `PositiveImpactSection`

### 資料結構對應
編輯器使用巢狀物件結構，透過 v-model 綁定對應到共用組件的扁平結構：

**風險區塊 (E-1/E-2)**:
- `formData.risk.description` → `e1-risk-description`
- `formData.risk.probability` → `e2-risk-probability`
- `formData.risk.impactLevel` → `e2-risk-impact`
- `formData.risk.calculation` → `e2-risk-calculation`

**機會區塊 (F-1/F-2)**:
- `formData.opportunity.description` → `f1-opportunity-description`
- `formData.opportunity.probability` → `f2-opportunity-probability`
- `formData.opportunity.impactLevel` → `f2-opportunity-impact`
- `formData.opportunity.calculation` → `f2-opportunity-calculation`

**負面衝擊 (G-1)**:
- `formData.negativeImpact.level` → `g1-negative-impact-level`
- `formData.negativeImpact.description` → `g1-negative-impact-description`

**正面影響 (H-1)**:
- `formData.positiveImpact.level` → `h1-positive-impact-level`
- `formData.positiveImpact.description` → `h1-positive-impact-description`

### 優點
1. **程式碼重用**：減少重複程式碼，E-1~H-1 區塊現在使用相同組件
2. **一致性**：編輯器、結果頁面、答題頁面使用相同的 UI 組件
3. **維護性**：未來修改 E-1~H-1 區塊時只需更新一處
4. **深色模式支援**：共用組件已包含完整的深色模式支援

---

## E-1~H-1 資料檢索問題修正 (2025-10-02)

### 問題說明
在 `/web/risk-assessment/questions/[companyId]/answer/[questionId]/[contentId]` 頁面：
- 送出後 E-1~H-1 的資料已經正確寫入資料庫
- 但是重新進入頁面時，E-1~H-1 的資料沒有正確帶入表單

### 根本原因
後端 API 的資料檢索方法沒有正確返回 E-1~H-1 欄位：
1. `QuestionResponseModel::getResponsesByAssessment()` - `response_fields` 物件沒有包含完整的 E-1~H-1 欄位
2. `QuestionResponseModel::getResponse()` - 同上
3. `QuestionManagementController::getUserContentResponse()` - 嘗試解析不存在的 `response_value` JSON 欄位

### 已修正內容
修正以下檔案：
1. **backend/app/Models/QuestionManagement/QuestionResponseModel.php**
   - 修正 `getResponsesByAssessment()` 方法的 `response_fields` 物件
   - 修正 `getResponse()` 方法的 `response_fields` 物件
   - 確保返回完整的 E-1~H-1 欄位資料

2. **backend/app/Controllers/Api/V1/QuestionManagement/QuestionManagementController.php**
   - 修正 `getUserContentResponse()` 方法
   - 從分離的資料庫欄位組合成前端期望的 `response_value` 物件
   - 包含所有 E-1~H-1 欄位

### 修正後的欄位對應
現在 API 正確返回以下欄位：
- **E-1**: `e1_risk_description`
- **E-2**: `e2_risk_probability`, `e2_risk_impact`, `e2_risk_calculation`
- **F-1**: `f1_opportunity_description`
- **F-2**: `f2_opportunity_probability`, `f2_opportunity_impact`, `f2_opportunity_calculation`
- **G-1**: `g1_negative_impact_level`, `g1_negative_impact_description`
- **H-1**: `h1_positive_impact_level`, `h1_positive_impact_description`

---

## E-1/F-1 欄位結構修正 (2025-10-01)

### 問題說明
`question_responses` 資料表的 E-1 和 F-1 欄位結構不正確：
- E-1 錯誤地包含：`e1_risk_probability`, `e1_risk_impact`, `e1_risk_calculation` (這些應該在 E-2)
- F-1 錯誤地包含：`f1_opportunity_probability`, `f1_opportunity_impact`, `f1_opportunity_calculation` (這些應該在 F-2)

正確的結構應該是：
- **E-1** 只有：`e1_risk_description` (文字描述)
- **E-2** 有：`e2_risk_probability`, `e2_risk_impact`, `e2_risk_calculation` (下拉選單 + 文字說明)
- **F-1** 只有：`f1_opportunity_description` (文字描述)
- **F-2** 有：`f2_opportunity_probability`, `f2_opportunity_impact`, `f2_opportunity_calculation` (下拉選單 + 文字說明)

### 解決方案
執行最新的 migration：`2025-10-01-200001_FixE1F1Fields.php`

這個 migration 會：
1. 將現有 E-1 的 probability/impact/calculation 資料遷移到 E-2
2. 將現有 F-1 的 probability/impact/calculation 資料遷移到 F-2
3. 刪除錯誤的 E-1/F-1 欄位
4. **不會遺失任何資料** - 所有現有資料會自動遷移

---

## 之前的問題：量表資料表未建立

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