export const useSettingsStore = defineStore('settings', () => {
  const showFootbar = ref(true)
  const sidebarMenuItems = ref([
    {
      name: '風險評估表',
      icon: 'ClipboardDocumentListIcon',
      href: '/admin/risk-assessment',
      children: [
        { name: '範本管理', href: '/admin/risk-assessment/templates' },
        { name: '題項管理', href: '/admin/risk-assessment/questions' }
      ]
    }
  ])
  
  const toggleFootbar = () => {
    showFootbar.value = !showFootbar.value
  }
  
  const updateMenuItems = (newItems) => {
    sidebarMenuItems.value = newItems
  }
  
  return {
    showFootbar,
    sidebarMenuItems,
    toggleFootbar,
    updateMenuItems
  }
})