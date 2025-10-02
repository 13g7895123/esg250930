# 答題頁面測試報告

**測試日期**: 2025-10-02
**測試頁面**: `/web/risk-assessment/questions/1/answer/18/16`
**測試 URL**: `http://localhost:3000/web/risk-assessment/questions/1/answer/18/16?title=未命名題目&description=測試風險因子描述002`

## 測試概覽

本測試針對風險評估答題頁面進行完整的後端 API 和資料可用性驗證。

## 測試環境

- **前端**: Nuxt 3 (執行於 http://localhost:3000)
- **後端**: CodeIgniter 4 API
- **測試工具**: Node.js + node-fetch
- **測試範圍**:
  - API 端點驗證
  - 資料完整性檢查
  - 表單欄位資料可用性

## 測試結果總覽

### ✅ 所有測試通過 (5/5)

| 測試項目 | 狀態 | 說明 |
|---------|------|------|
| 問題內容 API | ✅ 通過 | Content ID: 16 |
| 公司評估 API | ✅ 通過 | Assessment ID: 18 |
| 可能性量表 API | ✅ 通過 | 2 筆資料列 |
| 衝擊量表 API | ✅ 通過 | 2 筆資料列 |
| 資料完整性 | ✅ 通過 | 所有欄位資料齊全 |

## 詳細測試結果

### 1. 問題內容載入測試

**API 端點**: `GET /api/v1/question-management/contents/16`

**結果**: ✅ 成功

**資料詳情**:
- Content ID: 16
- 包含 A 區段內容: ✅ 是
- 包含 B 區段內容: ✅ 是
- A 區段內容預覽:
  ```
  <p><strong>企業營運高度依賴自然資源風險評估</strong></p>
  <p>企業的營運往往高度依賴自然資源，如水資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、森林等。隨著氣候變遷、生態...
  ```

### 2. 公司評估載入測試

**API 端點**: `GET /api/v1/risk-assessment/company-assessments/18`

**結果**: ✅ 成功

**資料詳情**:
- Assessment ID: 18
- Template ID: 2
- Company ID: 1
- Status: pending

### 3. 可能性量表載入測試

**API 端點**: `GET /api/v1/risk-assessment/templates/2/scales/probability`

**結果**: ✅ 成功

**資料詳情**:
- Template ID: 2
- 資料列數: 2
- 資料欄數: 2

**下拉選單選項**:
1. Value: "4", Text: "000"
2. Value: "3", Text: "111"

**範例資料列**:
```json
{
  "id": "13",
  "scale_id": "1",
  "probability": "000",
  "score_range": "4",
  "sort_order": "0",
  "created_at": "2025-09-30 14:27:51",
  "updated_at": "2025-09-30 14:27:51",
  "dynamicFields": {
    "1": "測試風險不曾發生過001",
    "2": "123",
    "3": "666"
  }
}
```

### 4. 衝擊量表載入測試

**API 端點**: `GET /api/v1/risk-assessment/templates/2/scales/impact`

**結果**: ✅ 成功

**資料詳情**:
- Template ID: 2
- 資料列數: 2
- 資料欄數: 3

**下拉選單選項**:
1. Value: "4", Text: "123"
2. Value: "3", Text: "222"

**範例資料列**:
```json
{
  "id": "13",
  "scale_id": "1",
  "impact_level": "123",
  "score_range": "4",
  "sort_order": "0",
  "created_at": "2025-09-30 14:27:51",
  "updated_at": "2025-09-30 14:27:51",
  "dynamicFields": {
    "1": "股東權益金額001",
    "2": "444",
    "3": "333"
  }
}
```

## 表單欄位資料可用性

以下是頁面上應該顯示的所有表單欄位及其資料來源驗證：

| # | 欄位名稱 | 類型 | 資料可用性 | 資料來源 |
|---|---------|------|-----------|---------|
| 1 | E-1 風險描述 | textarea | ✅ 可用 | Question Content API |
| 2 | E-2 風險可能性 | select | ✅ 可用 | Probability Scale API (2 選項) |
| 3 | E-2 風險衝擊 | select | ✅ 可用 | Impact Scale API (2 選項) |
| 4 | E-2 計算說明 | textarea | ✅ 可用 | Question Content API |
| 5 | F-1 機會描述 | textarea | ✅ 可用 | Question Content API |
| 6 | F-2 機會可能性 | select | ✅ 可用 | Probability Scale API (2 選項) |
| 7 | F-2 機會衝擊 | select | ✅ 可用 | Impact Scale API (2 選項) |
| 8 | F-2 計算說明 | textarea | ✅ 可用 | Question Content API |

### 欄位位置說明

#### E 區域 (相關風險)
- **E-1**: 位於 RiskSection 組件中
  - 標題: "相關風險"
  - 包含一個 textarea 用於「風險描述」

- **E-2**: 位於 RiskSection 組件中的框框內
  - 標題: "請依上述公司盤點之風險情境評估一旦發生風險對公司之財務影響"
  - 包含:
    - Select: 風險發生可能性 (必填)
    - Select: 風險發生衝擊程度 (必填)
    - Textarea: 計算說明 (必填)

#### F 區域 (相關機會)
- **F-1**: 位於 OpportunitySection 組件中
  - 標題: "相關機會"
  - 包含一個 textarea 用於「機會描述」

- **F-2**: 位於 OpportunitySection 組件中的框框內
  - 標題: "請依上述公司盤點之機會情境評估一旦發生機會對公司之財務影響"
  - 包含:
    - Select: 機會發生可能性 (必填)
    - Select: 機會發生衝擊程度 (必填)
    - Textarea: 計算說明 (必填)

## 元件架構

```
[contentId].vue (主頁面)
├── RiskSection.vue (E-1, E-2)
│   ├── E-1 風險描述 (textarea)
│   └── E-2 風險評估 (框框)
│       ├── 風險發生可能性 (select)
│       ├── 風險發生衝擊程度 (select)
│       └── 計算說明 (textarea)
│
└── OpportunitySection.vue (F-1, F-2)
    ├── F-1 機會描述 (textarea)
    └── F-2 機會評估 (框框)
        ├── 機會發生可能性 (select)
        ├── 機會發生衝擊程度 (select)
        └── 計算說明 (textarea)
```

## 資料流程

1. **頁面載入時** (`onMounted`)
   - 檢查用戶資料 (externalUserStore)
   - 載入問題內容 (A, B 區段)
   - 載入現有答案 (如果有的話)
   - 載入量表資料 (可能性、衝擊)

2. **量表資料處理**
   - 從 company_assessments 取得 template_id
   - 使用 template_id 載入 probability scale
   - 使用 template_id 載入 impact scale
   - 將量表資料轉換為下拉選單選項

3. **表單資料綁定**
   - 使用 v-model 進行雙向綁定
   - E-1/E-2/F-1/F-2 欄位都存儲在 `formData` ref 中
   - 子元件透過 props 和 emits 與父元件通訊

## 已知問題

無

## 瀏覽器端渲染測試

**注意**: 由於 Nuxt 是客戶端渲染 (CSR) 框架，直接抓取 HTML 無法看到完整的表單元素。這些元素是由 Vue.js 在瀏覽器中動態生成的。

以下測試方式已驗證：
- ✅ API 端點測試 (本報告)
- ⏭️ 瀏覽器自動化測試 (需要 Playwright 系統依賴)

## 建議

1. **正常功能運作**: 所有 API 端點都正常回應，資料完整
2. **表單可用性**: 所有 8 個必要欄位的資料來源都已確認可用
3. **下拉選單**: 可能性和衝擊量表都有有效的選項資料

## 測試執行方式

```bash
# 執行 API 測試
cd frontend
node tests/e2e/test-api-directly.js
```

## 結論

✅ **所有測試通過**

答題頁面的所有後端 API 端點都正常運作，資料完整且可用。E-1、E-2、F-1、F-2 的所有欄位資料來源都已確認存在且可正常載入。

---

**測試人員**: Claude Code
**測試工具**: Node.js API Testing Script
**報告生成時間**: 2025-10-02
