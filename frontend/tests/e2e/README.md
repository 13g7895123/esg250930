# E2E 測試說明

本目錄包含答題頁面的端到端測試腳本。

## 測試檔案

### 1. API 直接測試 (推薦)

**檔案**: `test-api-directly.js`

**用途**: 測試所有後端 API 端點，驗證資料完整性

**執行方式**:
```bash
cd /home/jarvis/project/job/twnict/esg-csr-new/frontend
node tests/e2e/test-api-directly.js
```

**測試內容**:
- ✅ 問題內容 API
- ✅ 公司評估 API
- ✅ 可能性量表 API
- ✅ 衝擊量表 API
- ✅ 下拉選單選項驗證

---

### 2. HTML 頁面測試

**檔案**: `test-answer-page.js`

**用途**: 檢查 HTML 頁面結構 (有限制，因 Nuxt 為客戶端渲染)

**執行方式**:
```bash
cd /home/jarvis/project/job/twnict/esg-csr-new/frontend
node tests/e2e/test-answer-page.js
```

**限制**: 無法看到 Vue.js 動態生成的元素

---

### 3. Playwright 瀏覽器自動化測試

**檔案**: `answer-page.spec.js`

**用途**: 完整的瀏覽器自動化測試，包含截圖和 Console 錯誤捕獲

**前置要求**:
```bash
# 安裝系統依賴 (需要 sudo 權限)
sudo apt-get install -y libnspr4 libnss3 libasound2 libgbm1 libxshmfence1

# 安裝 Playwright 瀏覽器
npx playwright install chromium
```

**執行方式**:
```bash
cd /home/jarvis/project/job/twnict/esg-csr-new/frontend

# 執行測試 (headless 模式)
npm run test:e2e

# 執行測試 (有視窗模式)
npm run test:e2e:headed

# 除錯模式
npm run test:e2e:debug
```

---

## 測試結果

測試結果會儲存在以下位置:

- **詳細報告**: `/test-results/answer-page-test-report.md`
- **總結報告**: `/test-results/TEST_SUMMARY.md`
- **JSON 格式**: `/test-results/test-results.json`
- **Playwright 報告**: `/test-results/` (執行 Playwright 測試後生成)

---

## 測試目標

測試以下 8 個表單欄位的資料可用性:

1. ✅ E-1 風險描述 (textarea)
2. ✅ E-2 風險可能性 (select)
3. ✅ E-2 風險衝擊 (select)
4. ✅ E-2 計算說明 (textarea)
5. ✅ F-1 機會描述 (textarea)
6. ✅ F-2 機會可能性 (select)
7. ✅ F-2 機會衝擊 (select)
8. ✅ F-2 計算說明 (textarea)

---

## 快速開始

### 最簡單的測試方式 (不需要 sudo)

```bash
# 1. 確保 dev server 正在運行
cd /home/jarvis/project/job/twnict/esg-csr-new/frontend
npm run dev

# 2. 在另一個終端視窗執行 API 測試
node tests/e2e/test-api-directly.js
```

### 完整測試 (需要 sudo)

```bash
# 1. 安裝系統依賴
sudo apt-get install -y libnspr4 libnss3 libasound2 libgbm1 libxshmfence1

# 2. 安裝 Playwright
cd /home/jarvis/project/job/twnict/esg-csr-new/frontend
npx playwright install chromium

# 3. 執行完整測試
npm run test:e2e
```

---

## 疑難排解

### 問題: dev server 沒有運行

**解決方式**:
```bash
cd /home/jarvis/project/job/twnict/esg-csr-new/frontend
npm run dev
```

### 問題: Playwright 測試失敗 (缺少系統依賴)

**解決方式**:
```bash
sudo apt-get install -y libnspr4 libnss3 libasound2 libgbm1 libxshmfence1
```

### 問題: 無法連接到 localhost:3000

**檢查事項**:
1. dev server 是否正在運行?
2. 防火牆是否阻擋 3000 port?
3. 是否有其他程式佔用 3000 port?

---

## 測試架構

```
tests/e2e/
├── README.md                    # 本檔案
├── test-api-directly.js         # API 測試 (推薦)
├── test-answer-page.js          # HTML 頁面測試
└── answer-page.spec.js          # Playwright 測試

test-results/
├── answer-page-test-report.md   # 詳細報告
├── TEST_SUMMARY.md              # 總結報告
├── test-results.json            # JSON 格式結果
└── answer-page-screenshot.png   # 截圖 (Playwright 測試後生成)
```

---

## 更多資訊

- **專案文檔**: `/CLAUDE.md`
- **Playwright 配置**: `/playwright.config.js`
- **Package.json 腳本**: `/package.json`

---

**最後更新**: 2025-10-02
