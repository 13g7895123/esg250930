import { ref, computed } from 'vue'

/**
 * Composable for managing probability and impact scales
 * Shared between template and question edit pages
 */
export const useScaleManagement = () => {
  // ===== Probability Scale State =====
  const probabilityScaleColumns = ref([])
  const probabilityScaleRows = ref([])
  const probabilityScaleId = ref(null)
  const selectedProbabilityDisplayColumn = ref('probability')
  const showDescriptionText = ref(false)
  const descriptionText = ref('')

  // ===== Impact Scale State =====
  const impactScaleColumns = ref([])
  const impactScaleRows = ref([])
  const impactScaleId = ref(null)
  const selectedImpactDisplayColumn = ref('impactLevel')
  const showImpactDescriptionText = ref(false)
  const impactDescriptionText = ref('')

  // ===== Column ID Counters =====
  let nextProbabilityColumnId = 1
  let nextImpactColumnId = 1

  // ===== Probability Scale Functions =====

  const addProbabilityColumn = () => {
    const newColumnId = nextProbabilityColumnId++
    probabilityScaleColumns.value.push({
      id: newColumnId,
      name: '',
      removable: true
    })

    // Add empty field to all rows
    probabilityScaleRows.value.forEach(row => {
      if (!row.dynamicFields) row.dynamicFields = {}
      row.dynamicFields[newColumnId] = ''
    })
  }

  const removeProbabilityColumn = (columnId) => {
    const index = probabilityScaleColumns.value.findIndex(col => col.id === columnId)
    if (index > -1) {
      probabilityScaleColumns.value.splice(index, 1)

      // Remove from all rows
      probabilityScaleRows.value.forEach(row => {
        if (row.dynamicFields && row.dynamicFields[columnId] !== undefined) {
          delete row.dynamicFields[columnId]
        }
      })
    }
  }

  const addProbabilityRow = () => {
    const newRow = {
      id: Date.now(), // Temporary ID for UI
      dynamicFields: {},
      probability: '',
      scoreRange: ''
    }

    // Initialize dynamic fields for all columns
    probabilityScaleColumns.value.forEach(col => {
      newRow.dynamicFields[col.id] = ''
    })

    probabilityScaleRows.value.push(newRow)
  }

  const removeProbabilityRow = (rowIndex) => {
    if (probabilityScaleRows.value.length > 1) {
      probabilityScaleRows.value.splice(rowIndex, 1)
    }
  }

  const addProbabilityDescriptionText = () => {
    showDescriptionText.value = true
  }

  const removeProbabilityDescriptionText = () => {
    showDescriptionText.value = false
    descriptionText.value = ''
  }

  // ===== Impact Scale Functions =====

  const addImpactColumn = () => {
    const newColumnId = nextImpactColumnId++
    impactScaleColumns.value.push({
      id: newColumnId,
      name: '',
      removable: true
    })

    // Add empty field to all rows
    impactScaleRows.value.forEach(row => {
      if (!row.dynamicFields) row.dynamicFields = {}
      row.dynamicFields[newColumnId] = ''
    })
  }

  const removeImpactColumn = (columnId) => {
    const index = impactScaleColumns.value.findIndex(col => col.id === columnId)
    if (index > -1) {
      impactScaleColumns.value.splice(index, 1)

      // Remove from all rows
      impactScaleRows.value.forEach(row => {
        if (row.dynamicFields && row.dynamicFields[columnId] !== undefined) {
          delete row.dynamicFields[columnId]
        }
      })
    }
  }

  const addImpactRow = () => {
    const newRow = {
      id: Date.now(),
      dynamicFields: {},
      impactLevel: '',
      scoreRange: ''
    }

    // Initialize dynamic fields for all columns
    impactScaleColumns.value.forEach(col => {
      newRow.dynamicFields[col.id] = ''
    })

    impactScaleRows.value.push(newRow)
  }

  const removeImpactRow = (rowIndex) => {
    if (impactScaleRows.value.length > 1) {
      impactScaleRows.value.splice(rowIndex, 1)
    }
  }

  const addImpactDescriptionText = () => {
    showImpactDescriptionText.value = true
  }

  const removeImpactDescriptionText = () => {
    showImpactDescriptionText.value = false
    impactDescriptionText.value = ''
  }

  // ===== Computed Properties =====

  // 可能性量表下拉選單選項
  const probabilityScaleOptions = computed(() => {
    return probabilityScaleRows.value.map(row => {
      // 使用分數級距作為 value
      const value = row.scoreRange || ''

      // 根據選擇的欄位顯示 text
      let text = ''
      if (selectedProbabilityDisplayColumn.value === 'probability') {
        text = row.probability || ''
      } else {
        // 如果選擇的是變動欄位
        const columnId = parseInt(selectedProbabilityDisplayColumn.value)
        text = row.dynamicFields[columnId] || ''
      }

      return {
        value: value,
        text: text ? `${value} (${text})` : value
      }
    }).filter(opt => opt.value) // 過濾掉空的選項
  })

  // 財務衝擊量表下拉選單選項
  const impactScaleOptions = computed(() => {
    return impactScaleRows.value.map(row => {
      // 使用分數級距作為 value
      const value = row.scoreRange || ''

      // 根據選擇的欄位顯示 text
      let text = ''
      if (selectedImpactDisplayColumn.value === 'impactLevel') {
        text = row.impactLevel || ''
      } else {
        // 如果選擇的是變動欄位
        const columnId = parseInt(selectedImpactDisplayColumn.value)
        text = row.dynamicFields[columnId] || ''
      }

      return {
        value: value,
        text: text ? `${value} (${text})` : value
      }
    }).filter(opt => opt.value) // 過濾掉空的選項
  })

  // ===== Data Loading =====

  /**
   * Load scale data from API response
   * @param {Object} scalesData - The scales data from API
   */
  const loadScalesData = (scalesData) => {
    if (!scalesData) return

    // Load probability scale
    if (scalesData.probability_scale) {
      const { scale, columns, rows } = scalesData.probability_scale

      if (scale) {
        probabilityScaleId.value = scale.id
        descriptionText.value = scale.description_text || ''
        showDescriptionText.value = !!scale.show_description
        selectedProbabilityDisplayColumn.value = scale.selected_display_column || 'probability'
      }

      // 更新欄位定義
      if (columns && columns.length > 0) {
        probabilityScaleColumns.value = columns.map(col => ({
          id: col.column_id,
          name: col.name,
          removable: !!col.removable
        }))

        // Update nextColumnId
        if (probabilityScaleColumns.value.length > 0) {
          nextProbabilityColumnId = Math.max(...probabilityScaleColumns.value.map(c => c.id)) + 1
        }
      }

      // 更新資料列
      if (rows && rows.length > 0) {
        probabilityScaleRows.value = rows.map(row => {
          // Convert dynamic field keys from strings to integers to match column IDs
          const dynamicFields = {}

          // Initialize all columns with empty values
          probabilityScaleColumns.value.forEach(col => {
            dynamicFields[col.id] = ''
          })

          // Fill in actual values from row data
          if (row.dynamic_fields) {
            Object.keys(row.dynamic_fields).forEach(key => {
              const colId = parseInt(key)
              dynamicFields[colId] = row.dynamic_fields[key]
            })
          }

          return {
            id: row.id || Date.now(),
            dynamicFields: dynamicFields,
            probability: row.probability || '',
            scoreRange: row.score_range || ''
          }
        })
      }
    }

    // Load impact scale
    if (scalesData.impact_scale) {
      const { scale, columns, rows } = scalesData.impact_scale

      if (scale) {
        impactScaleId.value = scale.id
        impactDescriptionText.value = scale.description_text || ''
        showImpactDescriptionText.value = !!scale.show_description
        selectedImpactDisplayColumn.value = scale.selected_display_column || 'impactLevel'
      }

      // 更新欄位定義
      if (columns && columns.length > 0) {
        impactScaleColumns.value = columns.map(col => ({
          id: col.column_id,
          name: col.name,
          removable: !!col.removable
        }))

        // Update nextColumnId
        if (impactScaleColumns.value.length > 0) {
          nextImpactColumnId = Math.max(...impactScaleColumns.value.map(c => c.id)) + 1
        }
      }

      // 更新資料列
      if (rows && rows.length > 0) {
        impactScaleRows.value = rows.map(row => {
          const dynamicFields = {}

          // Initialize all columns with empty values
          impactScaleColumns.value.forEach(col => {
            dynamicFields[col.id] = ''
          })

          // Fill in actual values from row data
          if (row.dynamic_fields) {
            Object.keys(row.dynamic_fields).forEach(key => {
              const colId = parseInt(key)
              dynamicFields[colId] = row.dynamic_fields[key]
            })
          }

          return {
            id: row.id || Date.now(),
            dynamicFields: dynamicFields,
            impactLevel: row.impact_level || '',
            scoreRange: row.score_range || ''
          }
        })
      }
    }
  }

  /**
   * Prepare scale data for API submission
   * @returns {Object} Formatted scale data for API
   */
  const prepareScaleDataForSubmission = () => {
    return {
      probability_scale: {
        id: probabilityScaleId.value,
        description_text: descriptionText.value,
        show_description: showDescriptionText.value ? 1 : 0,
        selected_display_column: selectedProbabilityDisplayColumn.value,
        columns: probabilityScaleColumns.value.map(col => ({
          column_id: col.id,
          name: col.name,
          removable: col.removable ? 1 : 0
        })),
        rows: probabilityScaleRows.value.map(row => ({
          probability: row.probability,
          score_range: row.scoreRange,
          dynamic_fields: row.dynamicFields
        }))
      },
      impact_scale: {
        id: impactScaleId.value,
        description_text: impactDescriptionText.value,
        show_description: showImpactDescriptionText.value ? 1 : 0,
        selected_display_column: selectedImpactDisplayColumn.value,
        columns: impactScaleColumns.value.map(col => ({
          column_id: col.id,
          name: col.name,
          removable: col.removable ? 1 : 0
        })),
        rows: impactScaleRows.value.map(row => ({
          impact_level: row.impactLevel,
          score_range: row.scoreRange,
          dynamic_fields: row.dynamicFields
        }))
      }
    }
  }

  /**
   * Reset all scale data to defaults
   */
  const resetScaleData = () => {
    probabilityScaleColumns.value = [
      { id: 1, name: '如風險不曾發生過', removable: true },
      { id: 2, name: '如風險曾經發生過', removable: true }
    ]
    probabilityScaleRows.value = [
      { id: Date.now(), dynamicFields: { 1: '', 2: '' }, probability: '', scoreRange: '' },
      { id: Date.now() + 1, dynamicFields: { 1: '', 2: '' }, probability: '', scoreRange: '' }
    ]
    nextProbabilityColumnId = 3

    impactScaleColumns.value = [
      { id: 1, name: '股東權益金額', removable: true },
      { id: 2, name: '股東權益金額百分比', removable: true },
      { id: 3, name: '實際權益金額(分配後)', removable: true }
    ]
    impactScaleRows.value = [
      { id: Date.now(), dynamicFields: { 1: '', 2: '', 3: '' }, impactLevel: '', scoreRange: '' },
      { id: Date.now() + 1, dynamicFields: { 1: '', 2: '', 3: '' }, impactLevel: '', scoreRange: '' }
    ]
    nextImpactColumnId = 4
  }

  return {
    // State
    probabilityScaleColumns,
    probabilityScaleRows,
    probabilityScaleId,
    selectedProbabilityDisplayColumn,
    showDescriptionText,
    descriptionText,

    impactScaleColumns,
    impactScaleRows,
    impactScaleId,
    selectedImpactDisplayColumn,
    showImpactDescriptionText,
    impactDescriptionText,

    // Probability Scale Methods
    addProbabilityColumn,
    removeProbabilityColumn,
    addProbabilityRow,
    removeProbabilityRow,
    addProbabilityDescriptionText,
    removeProbabilityDescriptionText,

    // Impact Scale Methods
    addImpactColumn,
    removeImpactColumn,
    addImpactRow,
    removeImpactRow,
    addImpactDescriptionText,
    removeImpactDescriptionText,

    // Computed
    probabilityScaleOptions,
    impactScaleOptions,

    // Utility Methods
    loadScalesData,
    prepareScaleDataForSubmission,
    resetScaleData
  }
}
