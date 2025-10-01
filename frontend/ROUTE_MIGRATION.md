# 路由遷移說明

## 目的
將原本分散的題目編輯頁面整合為單一共用編輯器組件，提升程式碼維護性。

## 變更摘要

### 舊路由結構
```
/admin/risk-assessment/questions/edit/[templateId]-[contentId].vue
/admin/risk-assessment/templates/edit/[templateId]-[contentId].vue
/admin/risk-assessment/templates/edit/[templateId]-[contentId]-preview.vue
```

### 新路由結構
```
/admin/risk-assessment/editor/[mode]-[id]-[contentId].vue
```

其中 `mode` 可以是：
- `template` - 範本編輯模式
- `question` - 題目編輯模式
- `preview` - 預覽模式

## 路由對應

| 舊路由 | 新路由 | 說明 |
|--------|--------|------|
| `/questions/edit/16-13` | `/editor/question-16-13` | 題目編輯 |
| `/templates/edit/2-1` | `/editor/template-2-1` | 範本編輯 |
| `/templates/edit/2-1-preview` | `/editor/preview-2-1` | 預覽模式 |

## 已更新的檔案

1. **frontend/components/ContentManagement.vue**
   - 第 812 行：更新範本編輯路由
   - 第 815 行：更新題目編輯路由

2. **frontend/composables/useEditorFeatures.js**
   - 第 232 行：預覽路徑使用新格式

## 備份檔案位置

原始檔案已備份至：
```
frontend/pages/admin/risk-assessment/questions/edit/[templateId]-[contentId].vue.backup2
frontend/pages/admin/risk-assessment/templates/edit/[templateId]-[contentId].vue.backup
frontend/pages/admin/risk-assessment/templates/edit/[templateId]-[contentId]-preview.vue.backup
```

## 測試建議

1. 清除瀏覽器快取和 Nuxt 快取
2. 測試從題目列表點擊「題目編輯」按鈕
3. 測試從範本內容點擊「題目編輯」按鈕
4. 確認預覽功能正常運作

## 故障排除

如果遇到路由問題：
1. 清除 `.nuxt` 資料夾：`rm -rf .nuxt`
2. 重新啟動開發伺服器：`npm run dev`
3. 檢查瀏覽器控制台是否有錯誤訊息

## 回滾步驟（如需要）

如果需要回滾到舊路由：
```bash
# 還原備份檔案
mv frontend/pages/admin/risk-assessment/questions/edit/[templateId]-[contentId].vue.backup2 \
   frontend/pages/admin/risk-assessment/questions/edit/[templateId]-[contentId].vue

mv frontend/pages/admin/risk-assessment/templates/edit/[templateId]-[contentId].vue.backup \
   frontend/pages/admin/risk-assessment/templates/edit/[templateId]-[contentId].vue

mv frontend/pages/admin/risk-assessment/templates/edit/[templateId]-[contentId]-preview.vue.backup \
   frontend/pages/admin/risk-assessment/templates/edit/[templateId]-[contentId]-preview.vue

# 還原 ContentManagement.vue
git checkout HEAD~2 frontend/components/ContentManagement.vue
```

---

*遷移日期：2025-10-01*
