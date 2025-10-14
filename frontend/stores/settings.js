export const useSettingsStore = defineStore('settings', () => {
  const route = useRoute()
  const externalUserStore = useExternalUserStore()

  const showFootbar = ref(true)

  // 動態計算側邊欄選單項目
  const sidebarMenuItems = computed(() => {
    // 檢查是否為 /web 路徑（domain 後面第一個參數是 web）
    const isWebPath = route.path.startsWith('/web')

    // 檢查是否有 company_agent 群組
    const hasCompanyAgent = externalUserStore.group?.includes('company_agent')

    // 取得內部公司ID
    const companyId = externalUserStore.companyId

    // 基本選單結構
    const menuItems = [
      {
        name: '風險評估表',
        icon: 'ClipboardDocumentListIcon',
        href: '/admin/risk-assessment',
        children: []
      }
    ]

    // 如果是 /web 路徑，不顯示範本管理
    if (!isWebPath) {
      menuItems[0].children.push({
        name: '範本管理',
        href: '/admin/risk-assessment/templates'
      })
    }

    // 題項管理項目
    let questionManagementItem = { name: '題項管理' }

    // 如果有 company_agent 群組且有 companyId，連結到 /web 路徑
    if (hasCompanyAgent && companyId) {
      questionManagementItem.href = `/web/risk-assessment/questions/${companyId}/management`
    } else {
      // 否則連結到 /admin 路徑
      questionManagementItem.href = '/admin/risk-assessment/questions'
    }

    menuItems[0].children.push(questionManagementItem)

    return menuItems
  })

  const toggleFootbar = () => {
    showFootbar.value = !showFootbar.value
  }

  return {
    showFootbar,
    sidebarMenuItems,
    toggleFootbar
  }
})