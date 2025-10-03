/**
 * 資料映射轉換 Composable
 *
 * 用途: 處理後端 API 資料格式與前端表單資料格式之間的轉換
 *
 * 後端欄位命名規則:
 * - 新版欄位: a_content, b_content, c_placeholder, d_placeholder_1, e1_select_1 等
 * - 舊版欄位: risk_factor_description, reference_text 等
 * - 為了相容性，同時對應新舊欄位
 *
 * @returns {Object} 資料映射方法
 */
export const useDataMapper = () => {
  /**
   * 後端資料 -> 前端表單資料
   * @param {Object} apiData - 從後端 API 取得的原始資料
   * @returns {Object} 前端表單使用的資料結構
   */
  const backendToForm = (apiData) => {
    if (!apiData) {
      return getDefaultFormData()
    }

    return {
      // ===== Section A: 風險因子議題描述 =====
      riskFactorDescription:
        apiData.a_content ||
        apiData.risk_factor_description ||
        '',

      // ===== Section B: 參考文字 =====
      referenceText:
        apiData.b_content ||
        apiData.reference_text ||
        '',

      // ===== Section C: 風險事件 =====
      hasRiskEvent:
        apiData.has_risk_event ||
        'no',
      riskEventDescription:
        apiData.c_placeholder ||
        apiData.risk_event_description ||
        '',

      // ===== Section D: 對應作為 =====
      hasCounterAction:
        apiData.has_counter_action ||
        'yes',
      counterActionDescription:
        apiData.d_placeholder_1 ||
        apiData.counter_action_description ||
        '',
      counterActionCost:
        apiData.d_placeholder_2 ||
        apiData.counter_action_cost ||
        '',

      // ===== Section E: 風險評估 =====
      risk: {
        description:
          apiData.e1_risk_description ||
          apiData.e1_placeholder_1 ||
          apiData.risk_description ||
          '',
        probability: parseInt(
          apiData.e2_risk_probability ||
          apiData.e1_select_1 ||
          apiData.risk_probability ||
          1
        ),
        impactLevel: parseInt(
          apiData.e2_risk_impact ||
          apiData.e1_select_2 ||
          apiData.risk_impact_level ||
          1
        ),
        calculation:
          apiData.e2_risk_calculation ||
          apiData.e1_placeholder_2 ||
          apiData.risk_calculation ||
          ''
      },

      // ===== Section F: 機會評估 =====
      opportunity: {
        description:
          apiData.f1_opportunity_description ||
          apiData.f1_placeholder_1 ||
          apiData.opportunity_description ||
          '',
        probability: parseInt(
          apiData.f2_opportunity_probability ||
          apiData.f1_select_1 ||
          apiData.opportunity_probability ||
          1
        ),
        impactLevel: parseInt(
          apiData.f2_opportunity_impact ||
          apiData.f1_select_2 ||
          apiData.opportunity_impact_level ||
          3
        ),
        calculation:
          apiData.f2_opportunity_calculation ||
          apiData.f1_placeholder_2 ||
          apiData.opportunity_calculation ||
          ''
      },

      // ===== Section G: 對外負面衝擊 =====
      negativeImpact: {
        level: apiData.g1_negative_impact_level ||
          apiData.negative_impact_level ||
          'level-2',
        description:
          apiData.g1_negative_impact_description ||
          apiData.negative_impact_description ||
          ''
      },

      // ===== Section H: 對外正面影響 =====
      positiveImpact: {
        level: apiData.h1_positive_impact_level ||
          apiData.positive_impact_level ||
          'level-2',
        description:
          apiData.h1_positive_impact_description ||
          apiData.positive_impact_description ||
          ''
      },

      // ===== Hover 資訊圖示文字 =====
      hoverTexts: {
        E1: apiData.e1_info || '相關風險說明：企業面臨的風險評估相關資訊',
        F1: apiData.f1_info || '相關機會說明：企業可能的機會評估相關資訊',
        G1: apiData.g1_info || '對外負面衝擊說明：企業對外部環境可能造成的負面影響',
        H1: apiData.h1_info || '對外正面影響說明：企業對外部環境可能產生的正面影響'
      }
    }
  }

  /**
   * 前端表單資料 -> 後端 API 資料
   * @param {Object} formData - 前端表單資料
   * @param {Object} originalData - 原始後端資料 (保留未修改欄位)
   * @returns {Object} 後端 API 接受的資料格式
   */
  const formToBackend = (formData, originalData = {}) => {
    // 排除結構欄位（風險類別、風險主題、風險因子），這些欄位不應該在編輯題目時被更新
    const {
      category_id,
      topic_id,
      risk_factor_id,
      ...originalDataWithoutStructure
    } = originalData

    return {
      // 保留原始資料中的所有欄位（排除結構欄位）
      ...originalDataWithoutStructure,

      // ===== Section A: 風險因子議題描述 (雙重對應) =====
      a_content: formData.riskFactorDescription,
      risk_factor_description: formData.riskFactorDescription,

      // ===== Section B: 參考文字 (雙重對應) =====
      b_content: formData.referenceText,
      reference_text: formData.referenceText,

      // ===== Section C: 風險事件 (雙重對應) =====
      has_risk_event: formData.hasRiskEvent,
      c_placeholder: formData.riskEventDescription,
      risk_event_description: formData.riskEventDescription,

      // ===== Section D: 對應作為 (雙重對應) =====
      has_counter_action: formData.hasCounterAction,
      d_placeholder_1: formData.counterActionDescription,
      counter_action_description: formData.counterActionDescription,
      d_placeholder_2: formData.counterActionCost,
      counter_action_cost: formData.counterActionCost,

      // ===== Section E: 風險評估 (新版 + 舊版雙重對應) =====
      e1_risk_description: formData.risk.description,
      e1_placeholder_1: formData.risk.description,
      risk_description: formData.risk.description,
      e2_risk_probability: formData.risk.probability,
      e1_select_1: formData.risk.probability.toString(),
      risk_probability: formData.risk.probability,
      e2_risk_impact: formData.risk.impactLevel,
      e1_select_2: formData.risk.impactLevel.toString(),
      risk_impact_level: formData.risk.impactLevel,
      e2_risk_calculation: formData.risk.calculation,
      e1_placeholder_2: formData.risk.calculation,
      risk_calculation: formData.risk.calculation,

      // ===== Section F: 機會評估 (新版 + 舊版雙重對應) =====
      f1_opportunity_description: formData.opportunity.description,
      f1_placeholder_1: formData.opportunity.description,
      opportunity_description: formData.opportunity.description,
      f2_opportunity_probability: formData.opportunity.probability,
      f1_select_1: formData.opportunity.probability.toString(),
      opportunity_probability: formData.opportunity.probability,
      f2_opportunity_impact: formData.opportunity.impactLevel,
      f1_select_2: formData.opportunity.impactLevel.toString(),
      opportunity_impact_level: formData.opportunity.impactLevel,
      f2_opportunity_calculation: formData.opportunity.calculation,
      f1_placeholder_2: formData.opportunity.calculation,
      opportunity_calculation: formData.opportunity.calculation,

      // ===== Section G: 對外負面衝擊 (新版 + 舊版雙重對應) =====
      g1_negative_impact_level: formData.negativeImpact.level,
      negative_impact_level: formData.negativeImpact.level,
      g1_negative_impact_description: formData.negativeImpact.description,
      negative_impact_description: formData.negativeImpact.description,

      // ===== Section H: 對外正面影響 (新版 + 舊版雙重對應) =====
      h1_positive_impact_level: formData.positiveImpact.level,
      positive_impact_level: formData.positiveImpact.level,
      h1_positive_impact_description: formData.positiveImpact.description,
      positive_impact_description: formData.positiveImpact.description,

      // ===== Hover 資訊圖示文字 =====
      e1_info: formData.hoverTexts?.E1 || originalData.e1_info,
      f1_info: formData.hoverTexts?.F1 || originalData.f1_info,
      g1_info: formData.hoverTexts?.G1 || originalData.g1_info,
      h1_info: formData.hoverTexts?.H1 || originalData.h1_info
    }
  }

  /**
   * 取得預設表單資料
   * @returns {Object} 預設的空白表單資料
   */
  const getDefaultFormData = () => {
    return {
      riskFactorDescription: '',
      referenceText: '',
      hasRiskEvent: 'no',
      riskEventDescription: '',
      hasCounterAction: 'yes',
      counterActionDescription: '',
      counterActionCost: '',
      risk: {
        description: '',
        probability: 1,
        impactLevel: 1,
        calculation: ''
      },
      opportunity: {
        description: '',
        probability: 1,
        impactLevel: 3,
        calculation: ''
      },
      negativeImpact: {
        level: 'level-2',
        description: ''
      },
      positiveImpact: {
        level: 'level-2',
        description: ''
      },
      hoverTexts: {
        E1: '相關風險說明：企業面臨的風險評估相關資訊',
        F1: '相關機會說明：企業可能的機會評估相關資訊',
        G1: '對外負面衝擊說明：企業對外部環境可能造成的負面影響',
        H1: '對外正面影響說明：企業對外部環境可能產生的正面影響'
      }
    }
  }

  /**
   * 準備預覽用的查詢參數
   * 將表單資料轉換為適合 URL query 的格式
   * @param {Object} formData - 表單資料
   * @returns {Object} 查詢參數物件
   */
  const preparePreviewQuery = (formData) => {
    return {
      riskFactorDescription: formData.riskFactorDescription || '',
      referenceText: formData.referenceText || '',
      riskEventDescription: formData.riskEventDescription || '',
      counterActionDescription: formData.counterActionDescription || '',
      counterActionCost: formData.counterActionCost || '',
      riskDescription: formData.risk?.description || '',
      riskProbability: formData.risk?.probability || 1,
      riskImpactLevel: formData.risk?.impactLevel || 1,
      riskCalculation: formData.risk?.calculation || '',
      opportunityDescription: formData.opportunity?.description || '',
      opportunityProbability: formData.opportunity?.probability || 1,
      opportunityImpactLevel: formData.opportunity?.impactLevel || 3,
      opportunityCalculation: formData.opportunity?.calculation || '',
      negativeImpactLevel: formData.negativeImpact?.level || 'level-2',
      negativeImpactDescription: formData.negativeImpact?.description || '',
      positiveImpactLevel: formData.positiveImpact?.level || 'level-2',
      positiveImpactDescription: formData.positiveImpact?.description || '',
      riskEventChoice: formData.hasRiskEvent || 'no',
      counterActionChoice: formData.hasCounterAction || 'yes'
    }
  }

  /**
   * 生成測試資料
   * @returns {Object} 隨機測試資料
   */
  const generateTestData = () => {
    const riskDescriptions = [
      '氣候變遷可能導致原物料供應中斷，影響生產營運',
      '水資源短缺風險可能影響製造流程，增加營運成本',
      '法規變動可能要求額外的合規成本和流程改善',
      '生物多樣性衝擊可能引發社會關注和商譽風險',
      '能源價格波動可能增加營運成本和財務壓力'
    ]

    const opportunityDescriptions = [
      '發展綠色產品可開拓新市場，提升品牌價值',
      '節能技術導入可降低營運成本，提高競爭力',
      '循環經濟模式可創造新的收入來源',
      'ESG績效提升可吸引投資者，降低融資成本',
      '永續供應鏈管理可提升客戶滿意度和忠誠度'
    ]

    const negativeImpactDescriptions = [
      '生產活動可能對當地生態環境造成一定程度影響',
      '廢棄物處理不當可能影響周邊社區環境品質',
      '水資源使用可能與當地社區產生資源競爭',
      '碳排放可能加劇氣候變遷對全球環境的衝擊',
      '化學物質使用可能對土壤和水體造成污染風險'
    ]

    const positiveImpactDescriptions = [
      '持續投資環保技術，對環境保護產生正面效益',
      '創造就業機會，促進當地經濟發展',
      '推動供應商ESG改善，帶動產業鏈永續發展',
      '環境教育推廣提升社會環保意識',
      '綠色產品推廣有助減少環境足跡'
    ]

    const randomPick = (arr) => arr[Math.floor(Math.random() * arr.length)]
    const randomLevel = () => Math.floor(Math.random() * 5) + 1

    return {
      // Section A: 風險因子議題描述
      riskFactorDescription: '<p><strong>企業營運高度依賴自然資源風險評估</strong></p><p>企業的營運往往高度依賴自然資源，如水資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、森林等。隨著氣候變遷、生態退化與資源稀缺問題日益嚴峻，若企業未能妥善管理資源使用及環境衝擊，可能面臨供應中斷、成本上升與合規壓力等風險。</p>',

      // Section B: 參考文字
      referenceText: '<p><strong>🔵 去年報告書文字或第三方背景資料整理：</strong></p><ul><li>台灣與生產據點的用水，皆不屬於水資源稀缺地區</li><li>政府調查顯示有' + (Math.floor(Math.random() * 5) + 3) + '個生產據點位於水稀缺風險地區</li></ul><p><strong>🔵 可能思考之風險情境面：</strong></p><ol><li>自然資源依賴性(對內)</li><li>自然資源衝擊性(對外)</li></ol>',

      // Section C: 風險事件
      hasRiskEvent: Math.random() > 0.5 ? 'yes' : 'no',
      riskEventDescription: '去年發生供應鏈中斷事件，造成短期生產調整',

      // Section D: 對應作為
      hasCounterAction: Math.random() > 0.5 ? 'yes' : 'no',
      counterActionDescription: '導入TNFD框架，加強自然資源風險評估與管理機制，建立更完善的風險預警系統',
      counterActionCost: '顧問費用約80萬元，系統建置費用約200萬元，年度維運成本約50萬元',

      // Section E: 風險評估 (E1描述 + E2可能性/衝擊/計算)
      risk: {
        description: randomPick(riskDescriptions),
        probability: randomLevel(), // 1-5的數字，對應量表選項
        impactLevel: randomLevel(), // 1-5的數字，對應量表選項
        calculation: '根據歷史數據分析與專家評估，考量發生機率與影響程度，預估此風險對公司營收的潛在影響約為2-5%，建議持續監控並建立應變機制'
      },

      // Section F: 機會評估 (F1描述 + F2可能性/衝擊/計算)
      opportunity: {
        description: randomPick(opportunityDescriptions),
        probability: randomLevel(), // 1-5的數字，對應量表選項
        impactLevel: randomLevel(), // 1-5的數字，對應量表選項
        calculation: '考量ESG市場趨勢與客戶需求轉變，結合公司既有優勢，預期此機會可帶來5-10%的營收成長，並提升品牌價值與市場競爭力'
      },

      // Section G: 對外負面衝擊
      negativeImpact: {
        level: randomLevel(), // 1-5的數字
        description: randomPick(negativeImpactDescriptions)
      },

      // Section H: 對外正面影響
      positiveImpact: {
        level: randomLevel(), // 1-5的數字
        description: randomPick(positiveImpactDescriptions)
      },

      // Hover 資訊圖示文字
      hoverTexts: {
        E1: '相關風險說明：企業面臨的風險評估相關資訊',
        F1: '相關機會說明：企業可能的機會評估相關資訊',
        G1: '對外負面衝擊說明：企業對外部環境可能造成的負面影響',
        H1: '對外正面影響說明：企業對外部環境可能產生的正面影響'
      }
    }
  }

  return {
    backendToForm,
    formToBackend,
    getDefaultFormData,
    preparePreviewQuery,
    generateTestData
  }
}
