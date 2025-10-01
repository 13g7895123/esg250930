# Scale Components 量表元件

這個資料夾包含風險評估系統中用於管理「風險發生可能性量表」和「財務衝擊量表」的共用元件。

## 元件列表

### 1. ScaleEditorModal.vue
### 2. ScaleTable.vue

---

## ScaleEditorModal.vue

### 功能說明

`ScaleEditorModal.vue` 是一個完整的量表編輯模態框元件，提供使用者友善的介面來編輯風險評估中的兩種量表：
- **風險發生可能性量表** (Probability Scale)
- **財務衝擊量表** (Impact Scale)

### 主要特點

**雙量表切換**
- 使用 Tab 介面在兩種量表間切換
- 獨立管理機率量表和衝擊量表的資料

**動態表格管理**
- 動態新增/刪除表格欄位（橫向）
- 動態新增/刪除表格列（縱向）
- 支援固定欄位和自訂欄位的混合使用

**下拉選單顯示欄位設定**
- 允許使用者選擇在表單中（E-1、F-1）顯示哪個欄位作為下拉選單選項
- 預設顯示固定欄位，也可切換為任何自訂欄位

**說明文字功能**
- 為每個量表提供選擇性的說明文字區塊
- 支援新增/移除說明文字
- 使用 textarea 輸入多行說明

**載入狀態處理**
- 顯示載入動畫當資料從後端獲取時
- 優化使用者體驗

### Props 參數

| 參數名稱 | 類型 | 必填 | 預設值 | 說明 |
|---------|------|------|--------|------|
| `modelValue` | Boolean | 是 | - | 控制模態框顯示/隱藏 (v-model) |
| `title` | String | 否 | '量表編輯' | 模態框標題 |
| `isLoading` | Boolean | 否 | false | 是否顯示載入狀態 |
| `probabilityColumns` | Array | 是 | [] | 機率量表的動態欄位陣列 |
| `probabilityRows` | Array | 是 | [] | 機率量表的資料列陣列 |
| `selectedProbabilityDisplayColumn` | String | 否 | 'probability' | 機率量表下拉選單顯示欄位 |
| `showProbabilityDescription` | Boolean | 否 | false | 是否顯示機率量表說明文字 |
| `probabilityDescriptionText` | String | 否 | '' | 機率量表說明文字內容 |
| `impactColumns` | Array | 是 | [] | 衝擊量表的動態欄位陣列 |
| `impactRows` | Array | 是 | [] | 衝擊量表的資料列陣列 |
| `selectedImpactDisplayColumn` | String | 否 | 'impactLevel' | 衝擊量表下拉選單顯示欄位 |
| `showImpactDescription` | Boolean | 否 | false | 是否顯示衝擊量表說明文字 |
| `impactDescriptionText` | String | 否 | '' | 衝擊量表說明文字內容 |

### Emits 事件

#### 通用事件
| 事件名稱 | 參數 | 說明 |
|---------|------|------|
| `update:modelValue` | Boolean | 更新模態框顯示狀態 |
| `save` | - | 點擊儲存按鈕時觸發 |

#### 機率量表事件
| 事件名稱 | 參數 | 說明 |
|---------|------|------|
| `add-probability-column` | - | 新增機率量表欄位 |
| `remove-probability-column` | columnId: Number | 刪除指定機率量表欄位 |
| `add-probability-row` | - | 新增機率量表列 |
| `remove-probability-row` | rowIndex: Number | 刪除指定機率量表列 |
| `update:selected-probability-display-column` | columnId: String | 更新機率量表下拉選單顯示欄位 |
| `add-probability-description` | - | 新增機率量表說明文字 |
| `remove-probability-description` | - | 移除機率量表說明文字 |
| `update:probability-description-text` | text: String | 更新機率量表說明文字內容 |

#### 衝擊量表事件
| 事件名稱 | 參數 | 說明 |
|---------|------|------|
| `add-impact-column` | - | 新增衝擊量表欄位 |
| `remove-impact-column` | columnId: Number | 刪除指定衝擊量表欄位 |
| `add-impact-row` | - | 新增衝擊量表列 |
| `remove-impact-row` | rowIndex: Number | 刪除指定衝擊量表列 |
| `update:selected-impact-display-column` | columnId: String | 更新衝擊量表下拉選單顯示欄位 |
| `add-impact-description` | - | 新增衝擊量表說明文字 |
| `remove-impact-description` | - | 移除衝擊量表說明文字 |
| `update:impact-description-text` | text: String | 更新衝擊量表說明文字內容 |

### 使用範例

```vue
<template>
  <div>
    <button @click="showModal = true">編輯量表</button>

    <ScaleEditorModal
      v-model="showModal"
      title="量表編輯"
      :is-loading="isLoadingScales"
      :probability-columns="probabilityScaleColumns"
      :probability-rows="probabilityScaleRows"
      :selected-probability-display-column="selectedProbabilityDisplayColumn"
      :show-probability-description="showDescriptionText"
      :probability-description-text="descriptionText"
      :impact-columns="impactScaleColumns"
      :impact-rows="impactScaleRows"
      :selected-impact-display-column="selectedImpactDisplayColumn"
      :show-impact-description="showImpactDescriptionText"
      :impact-description-text="impactDescriptionText"
      @update:selected-probability-display-column="selectedProbabilityDisplayColumn = $event"
      @update:selected-impact-display-column="selectedImpactDisplayColumn = $event"
      @update:probability-description-text="descriptionText = $event"
      @update:impact-description-text="impactDescriptionText = $event"
      @add-probability-column="addProbabilityColumn"
      @remove-probability-column="removeProbabilityColumn"
      @add-probability-row="addProbabilityRow"
      @remove-probability-row="removeProbabilityRow"
      @add-probability-description="addProbabilityDescriptionText"
      @remove-probability-description="removeProbabilityDescriptionText"
      @add-impact-column="addImpactColumn"
      @remove-impact-column="removeImpactColumn"
      @add-impact-row="addImpactRow"
      @remove-impact-row="removeImpactRow"
      @add-impact-description="addImpactDescriptionText"
      @remove-impact-description="removeImpactDescriptionText"
      @save="saveScales"
    />
  </div>
</template>

<script setup>
import ScaleEditorModal from '~/components/Scale/ScaleEditorModal.vue'
import { useScaleManagement } from '~/composables/useScaleManagement'

const showModal = ref(false)
const isLoadingScales = ref(false)

const {
  probabilityScaleColumns,
  probabilityScaleRows,
  selectedProbabilityDisplayColumn,
  showDescriptionText,
  descriptionText,
  impactScaleColumns,
  impactScaleRows,
  selectedImpactDisplayColumn,
  showImpactDescriptionText,
  impactDescriptionText,
  addProbabilityColumn,
  removeProbabilityColumn,
  addProbabilityRow,
  removeProbabilityRow,
  addProbabilityDescriptionText,
  removeProbabilityDescriptionText,
  addImpactColumn,
  removeImpactColumn,
  addImpactRow,
  removeImpactRow,
  addImpactDescriptionText,
  removeImpactDescriptionText
} = useScaleManagement()

const saveScales = async () => {
  // 儲存邏輯
  showModal.value = false
}
</script>
```

### 目前引用位置

此元件被以下頁面引用：

1. **範本編輯頁面**
   檔案：`frontend/pages/admin/risk-assessment/templates/edit/[templateId]-[contentId].vue`
   路徑：`/admin/risk-assessment/templates/edit/:templateId-:contentId`
   用途：編輯範本中的風險量表設定

2. **題項編輯頁面**
   檔案：`frontend/pages/admin/risk-assessment/questions/edit/[templateId]-[contentId].vue`
   路徑：`/admin/risk-assessment/questions/edit/:templateId-:contentId`
   用途：編輯題項中的風險量表設定（資料獨立於範本）

---

## ScaleTable.vue

### 功能說明

`ScaleTable.vue` 是一個可重用的動態表格元件，用於在 `ScaleEditorModal` 中顯示和編輯量表資料。

### 主要特點

**動態欄位管理**
- 支援任意數量的動態欄位（使用者自訂欄位）
- 每個欄位可以獨立命名和刪除
- 自動處理欄位的新增/刪除

**固定欄位支援**
- 支援固定欄位（如「發生可能性程度」、「分數級距」）
- 固定欄位不可刪除，但內容可編輯

**內聯編輯**
- 所有欄位和儲存格都支援即時編輯
- 使用 v-model 雙向綁定，變更即時反映到父元件

**列操作**
- 支援刪除任意列（至少保留一列）
- 提供視覺化的刪除按鈕

### Props 參數

| 參數名稱 | 類型 | 必填 | 預設值 | 說明 |
|---------|------|------|--------|------|
| `columns` | Array | 是 | [] | 動態欄位陣列 |
| `rows` | Array | 是 | [] | 資料列陣列 |
| `fixedColumns` | Array | 是 | [] | 固定欄位定義陣列 |
| `columnPlaceholder` | String | 否 | '欄位名稱' | 欄位名稱輸入框的 placeholder |

### 資料結構

#### columns 結構
```javascript
[
  {
    id: 1,              // 欄位唯一識別碼
    name: '金額',        // 欄位名稱
    removable: true     // 是否可刪除
  },
  // ...
]
```

#### rows 結構
```javascript
[
  {
    probability: '極低 (1-5%)',     // 固定欄位：發生可能性程度
    scoreRange: '1',                // 固定欄位：分數級距
    dynamicFields: {                // 動態欄位資料
      1: '< 1百萬',                  // key 對應 column.id
      2: '財務損失很小'
    }
  },
  // ...
]
```

#### fixedColumns 結構
```javascript
[
  {
    key: 'probability',                    // 欄位鍵名（對應 row 物件的屬性）
    label: '發生可能性程度',                // 欄位顯示名稱
    placeholder: '例：極低 (1-5%)',        // 輸入框 placeholder
    type: 'text'                           // 輸入框類型
  },
  {
    key: 'scoreRange',
    label: '分數級距',
    placeholder: '數字',
    type: 'number'
  }
]
```

### Emits 事件

| 事件名稱 | 參數 | 說明 |
|---------|------|------|
| `remove-column` | columnId: Number | 刪除指定動態欄位 |
| `remove-row` | rowIndex: Number | 刪除指定資料列 |

### Slots 插槽

| 插槽名稱 | Scope | 說明 |
|---------|-------|------|
| `column-extra` | `{ column }` | 在欄位標題下方顯示額外內容（如金額說明） |

### 使用範例

```vue
<template>
  <ScaleTable
    :columns="probabilityScaleColumns"
    :rows="probabilityScaleRows"
    :fixed-columns="[
      {
        key: 'probability',
        label: '發生可能性程度',
        placeholder: '例：極低 (1-5%)',
        type: 'text'
      },
      {
        key: 'scoreRange',
        label: '分數級距',
        placeholder: '數字',
        type: 'number'
      }
    ]"
    column-placeholder="欄位名稱"
    @remove-column="removeProbabilityColumn"
    @remove-row="removeProbabilityRow"
  >
    <!-- 插槽範例：為衝擊量表的金額欄位顯示額外說明 -->
    <template #column-extra="{ column }">
      <input
        v-if="column.id === amountColumnId"
        v-model="column.description"
        type="text"
        class="mt-2 w-full px-2 py-1 border rounded text-xs"
        placeholder="金額說明（例：新台幣）"
      />
    </template>
  </ScaleTable>
</template>

<script setup>
import ScaleTable from '~/components/Scale/ScaleTable.vue'

const probabilityScaleColumns = ref([
  { id: 1, name: '金額', removable: true }
])

const probabilityScaleRows = ref([
  {
    probability: '極低 (1-5%)',
    scoreRange: '1',
    dynamicFields: { 1: '< 1百萬' }
  }
])

const removeProbabilityColumn = (columnId) => {
  // 處理刪除欄位
}

const removeProbabilityRow = (rowIndex) => {
  // 處理刪除列
}
</script>
```

### 目前引用位置

此元件被以下元件引用：

1. **ScaleEditorModal.vue**
   檔案：`frontend/components/Scale/ScaleEditorModal.vue`
   用途：在模態框中顯示機率量表和衝擊量表的表格

---

## 相關 Composable

### useScaleManagement.js

位置：`frontend/composables/useScaleManagement.js`

這個 composable 提供了完整的量表管理邏輯，包括：
- 狀態管理（columns、rows、settings）
- CRUD 操作（新增、刪除欄位和列）
- 資料載入和準備
- 計算屬性（下拉選單選項）

**與 Scale 元件的關係：**
- Scale 元件負責 UI 呈現
- useScaleManagement 負責業務邏輯
- 兩者配合使用實現完整的量表管理功能

---

## 設計模式

### 組件化設計
將複雜的量表編輯功能拆分為：
1. **ScaleEditorModal** - 容器元件，處理整體佈局和狀態協調
2. **ScaleTable** - 展示元件，專注於表格的渲染和互動

### Props Down, Events Up
- 父元件通過 props 傳遞資料
- 子元件通過 emits 觸發事件
- 實現單向資料流，便於追蹤和除錯

### 可重用性
- 通過 props 和 slots 提供高度客製化能力
- 不綁定特定業務邏輯
- 可在不同場景中重複使用

---

## 開發注意事項

### 1. 資料響應性
確保傳入的 `columns` 和 `rows` 是響應式的 ref 或 reactive 物件，變更才能正確反映到 UI。

### 2. 唯一 ID 管理
- 動態欄位的 `id` 必須唯一
- 建議使用遞增計數器或 timestamp 生成 ID
- 避免使用陣列索引作為 ID（會導致刪除時的資料錯亂）

### 3. 最少資料限制
- 表格至少保留一列，防止資料完全清空
- 刪除最後一列時按鈕會自動禁用

### 4. 深色模式支援
所有元件都支援深色模式，使用 Tailwind 的 `dark:` 修飾符。

---

## 未來擴展方向

- 支援欄位拖曳排序
- 支援儲存格資料驗證
- 匯出/匯入量表資料（JSON、Excel）
- 量表範本功能（預設範本快速套用）
- 歷史版本管理
- 批次編輯功能

---

## 相關文件

- [風險評估系統主文件](../../CLAUDE.md)
- [useScaleManagement Composable](../../composables/useScaleManagement.js)
- [範本編輯頁面](../../pages/admin/risk-assessment/templates/edit/)
- [題項編輯頁面](../../pages/admin/risk-assessment/questions/edit/)

---

**最後更新**：2025-10-01
**版本**：1.0.0
**維護者**：風險評估系統開發團隊
