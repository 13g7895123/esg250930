# 風險評估答題頁面

## 頁面路徑
`/web/risk-assessment/questions/[companyId]/answer/[questionId]/[contentId]`

## 功能說明

這是一個**使用者填寫風險評估題目的頁面**。主要功能包括：

### 1. 題目填寫功能
- 使用者可以填寫完整的風險評估表單
- 包含 A~H 各個區段的內容：
  - **A區**: 風險因子議題描述（唯讀顯示）
  - **B區**: 參考文字&模組工具評估結果（唯讀顯示）
  - **C區**: 風險事件發生情況
  - **D區**: 對應作為與成本
  - **E區**: 相關風險評估（E-1 描述、E-2 財務影響）
  - **F區**: 相關機會評估（F-1 描述、F-2 財務影響）
  - **G區**: 對外負面衝擊
  - **H區**: 對外正面影響

### 2. 答案保存與載入
- **首次填寫**：使用者填寫表單後點擊「儲存」，資料會保存到資料庫
- **再次進入**：當使用者再次點進此頁面時，系統會自動載入上次的填答結果
- **資料持久化**：確保使用者看到的是上次保存的答案，不會遺失任何填寫內容

### 3. 資料載入機制

頁面載入時會執行以下步驟：

1. **用戶身份驗證**（支援兩種方式）：

   **方式一：使用 Pinia Store（優先）**
   - Pinia store 使用 sessionStorage 儲存 userId
   - 在同一瀏覽器分頁中，如果 store 已有 userId，直接使用，不需要 token
   - 這確保使用者在同一分頁中多次進入時能立即載入答案

   **方式二：使用 URL Token**
   - 從 URL query 參數取得 token
   - 解密 token 取得用戶資料（userId, externalId）
   - 將用戶資料儲存到 Pinia store

   **備援機制：**
   - 如果 userId 為空但有 externalId，嘗試從 externalId 重新獲取 userId

2. **題目內容載入**：
   - 載入 A 和 B 區段的題目內容（唯讀顯示）
   - 這些內容來自題目範本，不可編輯

3. **現有答案載入**：
   - 使用當前用戶的 userId 查詢是否有現有答案
   - API 端點：`/api/v1/question-management/assessment/${questionId}/responses`
   - 查詢條件：`content_id` 和 `answered_by`（用戶ID）
   - 如果找到現有答案，將答案資料填入表單欄位
   - 使用者可以看到上次填寫的內容並繼續編輯
   - **重要**：只要 store 中有 userId 就可以載入答案，不一定需要 token

4. **量表資料載入**：
   - 載入可能性量表和衝擊程度量表的選項
   - 用於 E-2 和 F-2 的下拉選單

### 4. 表單驗證
- E-2 和 F-2 區段的必填欄位驗證：
  - 風險/機會發生可能性（必填）
  - 風險/機會發生衝擊程度（必填）
  - 計算說明（必填）
- 送出前會檢查所有必填欄位是否完整

### 5. 測試資料功能
- 提供「填入測試資料」按鈕（開發/測試用）
- 可快速填入隨機的測試資料，方便測試流程

## 技術實現

### 資料結構
- 前端 formData 使用扁平化結構，欄位命名為 snake_case（如 `e1_risk_description`）
- 後端 API 返回 `response_fields` 物件，包含所有答案欄位
- 使用 `Object.assign()` 將後端資料合併到前端表單

### 關鍵函數
- `loadQuestionData()`: 載入題目內容和現有答案
- `saveAnswer()`: 儲存答案到資料庫
- `validateRequiredFields()`: 驗證必填欄位
- `fillTestData()`: 填入測試資料（開發用）

### API 端點
- `GET /api/v1/question-management/contents/${contentId}`: 取得題目內容
- `GET /api/v1/question-management/assessment/${questionId}/responses`: 取得現有答案
- `POST /api/v1/question-management/assessment/${questionId}/responses`: 儲存答案
- `POST /api/v1/external-personnel/find-by-external-id`: 查詢內部用戶ID
- `POST /api/v1/personnel/companies/${comId}/sync`: 同步人員資料

## 重要技術細節

### Pinia Store 快取機制
- **儲存方式**: 使用 `sessionStorage`
- **快取範圍**: 僅限當前瀏覽器分頁（關閉分頁後清除）
- **快取欄位**:
  - `userInfo`: 完整的用戶資訊（包含 user_name, email, com_id 等）
  - `token`: 原始 token 值
  - `internalUserId`: 查詢得到的內部用戶ID
  - `isLoaded`: 資料載入狀態
  - `lastUpdated`: 最後更新時間

### userId 的取得流程
1. **從 Token 解密**:
   - 解密 token 取得 `externalId`（外部系統的用戶ID）
   - 透過 `externalId` 查詢 `external_personnel` 表取得內部 `userId`

2. **自動同步機制**:
   - 如果找不到對應的 `userId`，系統會自動觸發人員同步
   - 同步完成後重新查詢，取得新建立的 `userId`

3. **快取保存**:
   - `internalUserId` 儲存到 Pinia store
   - 自動快取到 `sessionStorage`
   - 在同一分頁中，下次進入頁面時直接使用，不需要重新查詢

### 為什麼不需要 Token 也能載入答案？
1. 首次進入時，Token 解密並儲存 userId 到 sessionStorage
2. 在同一瀏覽器分頁中，Pinia 自動從 sessionStorage 恢復資料
3. Store 中已有 userId，直接用來查詢答案，無需再次使用 Token
4. 這提供了更好的使用者體驗，在同一分頁中避免重複驗證
5. 關閉分頁後，資料會自動清除，提升安全性

## 使用流程

### 情境一：首次填寫（有 Token）
1. 使用者透過帶有 token 的連結進入頁面
2. 系統從 token 解密取得 userId，儲存到 Pinia store（sessionStorage）
3. 看到空白表單（A、B 區段有題目內容）
4. 填寫 C~H 各區段的內容
5. 點擊「儲存」按鈕提交答案
6. userId 已快取在 Pinia store（sessionStorage）

### 情境二：同一分頁中再次進入（無 Token，但 Store 有 userId）
1. 使用者在同一瀏覽器分頁中再次進入頁面（可能沒有 token 參數）
2. 系統檢查 Pinia store（sessionStorage），發現已有 userId
3. **直接使用 store 中的 userId 載入答案**（不需要 token！）
4. 表單欄位已預填上次的答案
5. 使用者可以查看或修改後重新儲存

### 情境三：新分頁或關閉後重開（需要 Token）
1. 使用者關閉分頁後重新開啟，或在新分頁開啟
2. sessionStorage 已清除，Pinia store 沒有 userId
3. 需要重新使用帶有 token 的連結進入
4. 系統從 token 取得 userId，重新快取到 sessionStorage

### 情境四：查看模式（管理員查看使用者答案）
1. 管理員透過特殊 URL 參數（viewUserId）進入
2. 系統使用 viewUserId 載入該使用者的答案
3. 表單顯示該使用者的填答內容

### 資料保存機制
- **答案資料**：每次儲存都會更新資料庫中的答案記錄
- **用戶身份快取**：
  - Pinia store 使用 `sessionStorage` 快取 userId
  - 在同一瀏覽器分頁中有效，關閉分頁後會清除
  - 使用者在同一分頁中無需重複使用 token，直接用快取的 userId 載入答案
  - 快取欄位：`userInfo`, `token`, `internalUserId`, `isLoaded`, `lastUpdated`
