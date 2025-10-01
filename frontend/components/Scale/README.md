# Scale Components 量表元件

這個資料夾包含風險評估系統中用於管理「風險發生可能性量表」和「財務衝擊量表」的共用元件。

## 元件列表

### 1. ScaleEditorModal.vue - 量表編輯模態框（可編輯）
### 2. ScaleViewerModal.vue - 量表檢視模態框（唯讀）
### 3. ScaleTable.vue - 動態表格元件

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

## ScaleViewerModal.vue

### 功能說明

`ScaleViewerModal.vue` 是一個唯讀的量表檢視模態框元件，提供使用者友善的介面來檢視風險評估中的兩種量表：
- **風險發生可能性量表** (Probability Scale)
- **財務衝擊量表** (Impact Scale)

此元件專為**唯讀檢視**設計，不包含編輯功能，適用於問題回答頁面、預覽頁面等需要檢視量表資料的場景。

### 主要特點

**雙量表切換**
- 使用 Tab 介面在兩種量表間切換
- 完整模式：大型 Tab 導航
- 精簡模式：標題旁的緊湊切換按鈕

**精簡模式 (Compact Mode)**
- 一鍵切換精簡/完整模式
- 精簡模式特點：
  - 視窗大小縮小（max-w-3xl）
  - 量表切換按鈕移至標題旁邊
  - 支援拖曳移動視窗
  - 移除背景遮罩
  - 隱藏底部關閉按鈕

**拖曳功能**
- 精簡模式下可拖曳移動視窗
- 點擊標題區域拖曳
- 自動記憶視窗位置

**響應式設計**
- 支援深色模式
- 自適應不同螢幕尺寸
- 表格橫向滾動（處理大量欄位）

**載入狀態**
- 顯示載入動畫當資料從後端獲取時
- 優化使用者體驗

### Props 參數

| 參數名稱 | 類型 | 必填 | 預設值 | 說明 |
|---------|------|------|--------|------|
| `modelValue` | Boolean | 是 | false | 控制模態框顯示/隱藏 (v-model) |
| `loading` | Boolean | 否 | false | 是否顯示載入狀態 |
| `probabilityScaleColumns` | Array | 否 | [] | 可能性量表的動態欄位陣列 |
| `probabilityScaleRows` | Array | 否 | [] | 可能性量表的資料列陣列 |
| `impactScaleColumns` | Array | 否 | [] | 財務衝擊量表的動態欄位陣列 |
| `impactScaleRows` | Array | 否 | [] | 財務衝擊量表的資料列陣列 |
| `showDescriptionText` | Boolean | 否 | false | 是否顯示可能性量表說明文字 |
| `descriptionText` | String | 否 | '' | 可能性量表說明文字內容 |

### 資料結構

#### probabilityScaleColumns / impactScaleColumns 結構
```javascript
[
  {
    id: 1,                          // 欄位唯一識別碼
    name: '如風險不曾發生過',        // 欄位名稱
    removable: true,                // 是否可刪除（在此元件中不使用）
    amountNote: '新台幣'            // 金額說明（僅 impact 使用）
  },
  // ...
]
```

#### probabilityScaleRows 結構
```javascript
[
  {
    dynamicFields: {                // 動態欄位資料
      1: '預計該事件...',            // key 對應 column.id
      2: '平均每季可能發生...'
    },
    probability: '非常大',          // 發生可能性程度
    scoreRange: '4'                 // 分數級距
  },
  // ...
]
```

#### impactScaleRows 結構
```javascript
[
  {
    dynamicFields: {                // 動態欄位資料
      1: '> 10億',                  // key 對應 column.id
      2: '> 10%'
    },
    impactLevel: '極高',            // 財務衝擊程度
    scoreRange: '5'                 // 分數級距
  },
  // ...
]
```

### Emits 事件

| 事件名稱 | 參數 | 說明 |
|---------|------|------|
| `update:modelValue` | Boolean | 更新模態框顯示狀態 |

### 內部狀態管理

元件內部管理以下狀態（父元件無需關心）：
- `activeTab`: 當前顯示的量表 ('probability' | 'impact')
- `isCompactMode`: 是否為精簡模式
- `modalPosition`: 視窗位置（精簡模式下）
- `isDragging`: 是否正在拖曳
- `dragOffset`: 拖曳偏移量

### 使用範例

```vue
<template>
  <div>
    <!-- 觸發按鈕 -->
    <button
      @click="showScaleModal = true"
      class="px-4 py-2 bg-green-600 text-white rounded"
    >
      查看可能性量表
    </button>

    <!-- 量表檢視模態框 -->
    <ScaleViewerModal
      v-model="showScaleModal"
      :loading="isLoadingScales"
      :probability-scale-columns="probabilityScaleColumns"
      :probability-scale-rows="probabilityScaleRows"
      :impact-scale-columns="impactScaleColumns"
      :impact-scale-rows="impactScaleRows"
      :show-description-text="showDescriptionText"
      :description-text="descriptionText"
    />
  </div>
</template>

<script setup>
import ScaleViewerModal from '~/components/Scale/ScaleViewerModal.vue'
import { ref } from 'vue'

const showScaleModal = ref(false)
const isLoadingScales = ref(false)

// 量表資料
const probabilityScaleColumns = ref([
  { id: 1, name: '如風險不曾發生過', removable: true },
  { id: 2, name: '如風險曾經發生過', removable: true }
])

const probabilityScaleRows = ref([
  {
    dynamicFields: {
      1: '預計該事件非常有可能發生(發生機率91~100%)',
      2: '平均每季可能發生二次以上'
    },
    probability: '非常大',
    scoreRange: '4'
  },
  {
    dynamicFields: {
      1: '預計該事件可能有時會發生(發生機率11~50%)',
      2: '平均每季可能發生一次以上'
    },
    probability: '大',
    scoreRange: '3'
  }
])

const impactScaleColumns = ref([
  { id: 1, name: '股東權益金額', amountNote: '新台幣', removable: true }
])

const impactScaleRows = ref([
  {
    dynamicFields: {
      1: '> 10億'
    },
    impactLevel: '極高',
    scoreRange: '5'
  }
])

const showDescriptionText = ref(true)
const descriptionText = ref('這是可能性量表的說明文字...')
</script>
```

### 完整模式 vs 精簡模式

#### 完整模式（預設）
- 全螢幕背景遮罩
- 大型模態框（max-w-6xl）
- Tab 導航在內容區域上方
- 底部有關閉按鈕
- 不可拖曳

#### 精簡模式
- 無背景遮罩
- 較小模態框（max-w-3xl）
- 量表切換按鈕在標題旁邊
- 無底部關閉按鈕
- 可拖曳移動

用戶可透過右上角的精簡化按鈕切換模式。

### 空狀態處理

當量表資料為空時，會顯示友善的提示訊息：
- 黃色警告框
- 提示圖示
- 說明文字：「尚未設定量表資料」

### 目前引用位置

此元件被以下頁面引用：

1. **問題回答頁面**
   檔案：`frontend/pages/web/risk-assessment/questions/[companyId]/answer/[questionId]/[contentId].vue`
   路徑：`/web/risk-assessment/questions/:companyId/answer/:questionId/:contentId`
   用途：在回答問題時檢視對應的風險量表資料

2. **範本預覽頁面**（潛在使用場景）
   檔案：`frontend/pages/admin/risk-assessment/templates/edit/[templateId]-[contentId]-preview.vue`
   路徑：`/admin/risk-assessment/templates/edit/:templateId-:contentId-preview`
   用途：預覽範本中的風險量表設定

### 與 ScaleEditorModal 的差異

| 特性 | ScaleEditorModal | ScaleViewerModal |
|-----|------------------|------------------|
| 用途 | 編輯量表 | 檢視量表 |
| 可編輯 | ✅ 是 | ❌ 否 |
| 精簡模式 | ❌ 無 | ✅ 有 |
| 拖曳功能 | ❌ 無 | ✅ 有（精簡模式） |
| 新增/刪除欄位 | ✅ 有 | ❌ 無 |
| 新增/刪除列 | ✅ 有 | ❌ 無 |
| 儲存按鈕 | ✅ 有 | ❌ 無 |
| 使用場景 | 管理後台 | 問題回答、預覽 |

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
將複雜的量表功能拆分為：
1. **ScaleEditorModal** - 可編輯的容器元件，處理編輯邏輯和狀態協調
2. **ScaleViewerModal** - 唯讀的容器元件，專注於檢視和展示
3. **ScaleTable** - 展示元件，專注於表格的渲染和互動

### 關注點分離
- **ScaleEditorModal**: 編輯場景，包含新增/刪除/儲存功能
- **ScaleViewerModal**: 檢視場景，包含精簡模式/拖曳功能
- **ScaleTable**: 純展示邏輯，可被兩種模態框重用

### Props Down, Events Up
- 父元件通過 props 傳遞資料
- 子元件通過 emits 觸發事件
- 實現單向資料流，便於追蹤和除錯

### 可重用性
- 通過 props 和 slots 提供高度客製化能力
- 不綁定特定業務邏輯
- 可在不同場景中重複使用
- 編輯和檢視分離，避免功能耦合

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

### ScaleEditorModal
- 支援欄位拖曳排序
- 支援儲存格資料驗證
- 匯出/匯入量表資料（JSON、Excel）
- 量表範本功能（預設範本快速套用）
- 歷史版本管理
- 批次編輯功能

### ScaleViewerModal
- ✅ 精簡模式與拖曳功能（已完成）
- 列印/匯出 PDF 功能
- 量表數據分析視圖
- 多量表對比功能
- 全螢幕模式

---

## 相關文件

- [風險評估系統主文件](../../CLAUDE.md)
- [useScaleManagement Composable](../../composables/useScaleManagement.js)
- [範本編輯頁面](../../pages/admin/risk-assessment/templates/edit/)
- [題項編輯頁面](../../pages/admin/risk-assessment/questions/edit/)
- [問題回答頁面](../../pages/web/risk-assessment/questions/)

---

## 變更紀錄

### v1.1.0 (2025-10-01)
- ✨ 新增 `ScaleViewerModal.vue` 唯讀檢視元件
- ✨ 精簡模式功能（量表切換按鈕移至標題旁）
- ✨ 拖曳功能（精簡模式下可拖曳視窗）
- 📝 更新文檔，補充 ScaleViewerModal 完整說明
- 🔧 重構問題回答頁面，使用 ScaleViewerModal component

### v1.0.0 (2025-09-30)
- 🎉 初始版本
- ✨ ScaleEditorModal 量表編輯元件
- ✨ ScaleTable 動態表格元件
- 📝 完整文檔說明

---

**最後更新**：2025-10-01
**版本**：1.1.0
**維護者**：風險評估系統開發團隊
