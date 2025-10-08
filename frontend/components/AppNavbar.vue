<template>
  <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 flex items-center justify-between navbar-container">
    <!-- Left side - Mobile hamburger or Breadcrumb -->
    <div class="flex items-center space-x-4">
      <!-- Mobile hamburger button -->
      <button
        @click="toggleMobileSidebar"
        class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
      >
        <Bars3Icon class="w-6 h-6" />
      </button>

      <!-- Breadcrumb (Desktop only) -->
      <div class="hidden lg:flex items-center space-x-4">
        <!-- Current Page Title -->
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
          {{ currentPageTitle }}
        </h1>
        
        <!-- Separator -->
        <div class="h-6 w-px bg-gray-300 dark:bg-gray-600"></div>
        
        <!-- Custom Breadcrumb Navigation -->
        <CustomBreadcrumbs />
      </div>
    </div>

    <!-- Right side icons -->
    <div class="flex items-center space-x-3">
      <!-- Search -->
      <button
        v-if="enableSearch"
        @click="toggleSearch"
        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200 relative"
      >
        <MagnifyingGlassIcon class="w-5 h-5" />
      </button>

      <!-- Language -->
      <div v-if="enableMultilingual" class="relative">
        <button
          @click="toggleLanguage"
          class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
        >
          <GlobeAltIcon class="w-5 h-5" />
        </button>
        
        <!-- Language Dropdown -->
        <transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <div
            v-if="showLanguage"
            class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50"
          >
            <button
              v-for="lang in languages"
              :key="lang.code"
              @click="selectLanguage(lang)"
              class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
            >
              <span class="text-lg mr-2">{{ lang.flag }}</span>
              {{ lang.name }}
            </button>
          </div>
        </transition>
      </div>

      <!-- Notifications -->
      <div v-if="enableNotifications" class="relative">
        <button
          @click="toggleNotifications"
          class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200 relative"
        >
          <BellIcon class="w-5 h-5" />
          <span 
            v-if="unreadCount > 0"
            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 rounded-full flex items-center justify-center text-xs text-white font-bold"
          >
            {{ unreadCount > 99 ? '99+' : unreadCount }}
          </span>
        </button>

        <!-- Notifications Dropdown -->
        <transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <div
            v-if="showNotifications"
            class="absolute right-0 top-full mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
          >
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('notifications.title') }}</h3>
              <div class="flex items-center space-x-2">
                <button
                  @click="markAllAsRead"
                  class="text-xs text-primary-500 hover:text-primary-600 transition-colors duration-200"
                >
                  {{ t('notifications.mark_all_read') }}
                </button>
              </div>
            </div>
            <div class="max-h-80 overflow-y-auto">
              <div
                v-for="notification in recentNotifications"
                :key="notification.id"
                class="p-4 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 cursor-pointer"
                :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.read }"
                @click="markAsRead(notification.id)"
              >
                <div class="flex items-start space-x-3">
                  <div 
                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                    :class="{
                      'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400': notification.priority === 'high',
                      'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400': notification.priority === 'medium',
                      'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400': notification.priority === 'low'
                    }"
                  >
                    <component :is="getNotificationIcon(notification.icon)" class="w-4 h-4" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ typeof notification.title === 'string' && notification.title.includes('.') ? t(notification.title) : notification.title }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ notification.message }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ notificationsStore.getTimeAgo(notification.time) }}</p>
                  </div>
                  <div class="flex-shrink-0">
                    <div
                      v-if="!notification.read"
                      class="w-2 h-2 bg-primary-500 rounded-full"
                    ></div>
                  </div>
                </div>
              </div>
              <div
                v-if="recentNotifications.length === 0"
                class="p-8 text-center text-gray-500 dark:text-gray-400"
              >
                {{ t('notifications.no_notifications') }}
              </div>
            </div>
            <div class="p-3 border-t border-gray-200 dark:border-gray-700">
              <button
                @click="clearReadNotifications"
                class="w-full text-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200"
              >
                {{ t('notifications.clear_all') }}
              </button>
            </div>
          </div>
        </transition>
      </div>

      <!-- User Avatar -->
      <div class="relative">
        <button
          @click="toggleUserMenu"
          class="flex items-center space-x-2 p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
        >
          <img
            :src="user?.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user?.name || user?.username || 'User')}&background=6366f1&color=fff`"
            :alt="user?.name || user?.username || 'User Avatar'"
            class="w-8 h-8 rounded-full object-cover"
          />
          <ChevronDownIcon class="w-4 h-4 hidden sm:block" />
        </button>

        <!-- User Dropdown -->
        <transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <div
            v-if="showUserMenu"
            class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50"
          >
            <!-- User info section -->
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
              <p class="text-sm font-medium text-gray-900 dark:text-white">
                {{ user?.name || user?.username || 'User' }}
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ user?.email }}
              </p>
            </div>
            
            <NuxtLink
              to="/profile"
              class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
              @click="closeUserMenu"
            >
              {{ t('common.profile') }}
            </NuxtLink>
            <NuxtLink
              to="/settings"
              class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
              @click="closeUserMenu"
            >
              {{ t('common.settings') }}
            </NuxtLink>
            <hr class="my-1 border-gray-200 dark:border-gray-700" />
            <button
              @click="logout"
              class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
            >
              {{ t('common.logout') }}
            </button>
          </div>
        </transition>
      </div>
    </div>

    <!-- Search Modal -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showSearch"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-start justify-center pt-20"
        @click="closeSearch"
      >
        <div
          class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg mx-4 flex flex-col"
          style="max-height: 90vh;"
          @click.stop
        >
          <div class="p-4 overflow-y-auto flex-1 min-h-0">
            <input
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              :placeholder="t('common.search') + '...'"
              class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            />
          </div>
        </div>
      </div>
    </transition>
  </header>
</template>

<script setup>
import {
  Bars3Icon,
  MagnifyingGlassIcon,
  GlobeAltIcon,
  BellIcon,
  ChevronDownIcon,
  ChevronRightIcon,
  ExclamationCircleIcon,
  UserPlusIcon,
  DocumentTextIcon,
  ShieldExclamationIcon,
  InformationCircleIcon,
  WrenchScrewdriverIcon
} from '@heroicons/vue/24/outline'

const { t, locale, locales } = useI18n()
const route = useRoute()
const sidebarStore = useSidebarStore()
const { toggleMobileSidebar } = sidebarStore

const notificationsStore = useNotificationsStore()
const { recentNotifications, unreadCount, markAsRead, markAllAsRead, clearReadNotifications } = notificationsStore

// Website settings
const websiteSettingsStore = useWebsiteSettingsStore()
const { enableSearch, enableMultilingual, enableNotifications, enableDarkMode } = storeToRefs(websiteSettingsStore)

// App title from environment
const config = useRuntimeConfig()
const currentPageTitle = computed(() => config.public.appTitle)

// Breadcrumb company names cache
const breadcrumbCompanyNames = ref({})

// Local companies for breadcrumb
const { getCompanyNameById, loading: companiesLoading } = useLocalCompanies()

// Load company name for breadcrumb asynchronously
const loadBreadcrumbCompanyName = async (companyId) => {
  try {
    const companyName = await getCompanyNameById(companyId)
    if (companyName) {
      breadcrumbCompanyNames.value[companyId] = companyName
    }
  } catch (error) {
    console.error('Error loading breadcrumb company name:', error)
  }
}

const breadcrumbItems = computed(() => {
  if (!route?.path) return []
  
  const pathSegments = route.path.split('/').filter(segment => segment)
  const items = []
  
  let currentPath = ''
  for (let i = 0; i < pathSegments.length; i++) {
    currentPath += '/' + pathSegments[i]
    
    const segmentTitles = {
      '/dashboard': t('nav.dashboards'),
      '/dashboard/crm': t('nav.crm'),
      '/dashboard/ecommerce': t('nav.ecommerce'),
      '/profile': t('common.profile'),
      '/settings': t('nav.settings'),
      '/settings/general': t('nav.general_settings'),
      '/settings/ui': t('nav.ui_settings'),
      '/settings/users': t('nav.user_management'),
      '/help': t('nav.help_center'),
      '/help/faq': t('nav.faq'),
      '/help/support': t('nav.support'),
      '/help/docs': t('nav.docs'),
      '/admin/risk-assessment': t('nav.risk_assessment'),
      '/admin/risk-assessment/templates': t('nav.template_management'),
      '/admin/risk-assessment/questions': '公司列表',
      '/clients': '業主管理',
      '/clients/create': '新增業主',
      '/projects': '專案管理',
      '/projects/create': '新增專案'
    }
    
    let title = segmentTitles[currentPath] || pathSegments[i]
    
    // Handle specific segments
    if (pathSegments[i] === 'management') {
      title = '題項管理'
    }
    
    // Handle dynamic routes
    else if (!segmentTitles[currentPath]) {
      // Handle template content dynamic route
      if (currentPath.includes('/admin/risk-assessment/templates/') && currentPath.endsWith('/content')) {
        title = t('nav.template_content')
      }
      // Handle question content dynamic route
      else if (currentPath.includes('/admin/risk-assessment/questions/') && currentPath.endsWith('/content')) {
        title = getQuestionItemName(pathSegments)
      }
      // Handle question management dynamic route
      else if (currentPath.includes('/admin/risk-assessment/questions/') && currentPath.endsWith('/management')) {
        title = t('nav.question_management')
      }
      // Handle numeric segments (IDs)
      else if (/^\d+$/.test(pathSegments[i])) {
        // Special handling for template IDs
        if (currentPath.includes('/admin/risk-assessment/templates/')) {
          const templateId = parseInt(pathSegments[i])
          const templateInfo = getTemplateInfo(templateId)
          title = templateInfo?.version_name || `範本 #${pathSegments[i]}`
        }
        // Special handling for question routes
        else if (currentPath.includes('/admin/risk-assessment/questions/')) {
          const questionIndex = pathSegments.findIndex(segment => segment === 'questions')
          
          if (i === questionIndex + 1) {
            // This is the company ID (first number after /questions/)
            const { getCompanyNameById, loading: companiesLoading } = useLocalCompanies()
            
            if (companiesLoading.value) {
              title = '載入中...'
            } else {
              // Since getCompanyNameById is async, we need to handle this differently
              // For breadcrumbs, we'll use a reactive approach
              const companyId = pathSegments[i]
              if (breadcrumbCompanyNames.value[companyId]) {
                title = breadcrumbCompanyNames.value[companyId]
              } else {
                title = `公司 #${companyId}`
                // Load company name asynchronously and update breadcrumb
                loadBreadcrumbCompanyName(companyId)
              }
            }
          } else if (i === questionIndex + 3 && !currentPath.endsWith('/content')) {
            // This is the question management ID (after /company/management/) - only show as ID if not content route
            title = `#${pathSegments[i]}`
          } else {
            // Fallback for other numeric IDs
            title = `#${pathSegments[i]}`
          }
        }
        else {
          title = `#${pathSegments[i]}`
        }
      }
    }
    
    // Determine if this item should be clickable
    let href = currentPath
    
    // Don't make intermediate numeric IDs clickable (they don't have their own pages)
    if (/^\d+$/.test(pathSegments[i])) {
      // Only template and company root pages should be clickable
      if (currentPath.includes('/admin/risk-assessment/questions/') && !currentPath.endsWith('/management') && !currentPath.endsWith('/content')) {
        // This is an intermediate ID (company or question ID), don't make clickable
        href = null
      } else if (currentPath.includes('/admin/risk-assessment/templates/') && !currentPath.endsWith('/content')) {
        // This is an intermediate template ID, don't make clickable  
        href = null
      }
    }
    
    // Skip questionId segment for content routes to avoid duplicate template names in breadcrumb
    if (route?.path?.endsWith('/content') && route.path.includes('/admin/risk-assessment/questions/')) {
      const questionIndex = pathSegments.findIndex(segment => segment === 'questions')
      if (i === questionIndex + 3 && /^\d+$/.test(pathSegments[i])) {
        // This is the questionId segment in a content route, skip it
        continue
      }
    }
    
    items.push({
      name: title,
      href: href
    })
  }
  
  return items
})

// Template info helper function
const getTemplateInfo = (templateId) => {
  // Use templates store
  const templatesStore = useTemplatesStore()
  return templatesStore.templates.find(t => t.id === templateId)
}

// Question item name helper function
const getQuestionItemName = (pathSegments) => {
  const questionIndex = pathSegments.findIndex(segment => segment === 'questions')
  const questionId = pathSegments[questionIndex + 3] // The question management ID (after /company/management/ but before /content)
  
  // Use templates store to get the proper template name for question content
  const templatesStore = useTemplatesStore()
  const template = templatesStore.templates.find(t => t.id === parseInt(questionId))
  return template?.version_name || `題項內容管理 #${questionId}`
}

const showSearch = ref(false)
const showLanguage = ref(false)
const showNotifications = ref(false)
const showUserMenu = ref(false)
const searchQuery = ref('')
const searchInput = ref(null)

const languages = computed(() => locales.value)

const toggleSearch = () => {
  showSearch.value = !showSearch.value
  if (showSearch.value) {
    nextTick(() => {
      searchInput.value?.focus()
    })
  }
}

const closeSearch = () => {
  showSearch.value = false
  searchQuery.value = ''
}

const toggleLanguage = () => {
  showLanguage.value = !showLanguage.value
  showNotifications.value = false
  showUserMenu.value = false
}

const selectLanguage = (lang) => {
  locale.value = lang.code
  showLanguage.value = false
}


const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  showLanguage.value = false
  showUserMenu.value = false
}

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
  showLanguage.value = false
  showNotifications.value = false
}

const closeUserMenu = () => {
  showUserMenu.value = false
}

const authStore = useAuthStore()
const { user } = storeToRefs(authStore)

const logout = async () => {
  try {
    showUserMenu.value = false
    await authStore.logout()
  } catch (error) {
    console.error('Logout error:', error)
    // Even if logout API fails, we still clear local state and redirect
    await authStore.logout(true) // Skip API call
  }
}

const getNotificationIcon = (iconName) => {
  const iconComponents = {
    ExclamationCircleIcon,
    UserPlusIcon,
    DocumentTextIcon,
    ShieldExclamationIcon,
    InformationCircleIcon,
    WrenchScrewdriverIcon
  }
  return iconComponents[iconName] || InformationCircleIcon
}

// Close dropdowns when clicking outside
onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
      showLanguage.value = false
      showNotifications.value = false
      showUserMenu.value = false
    }
  })
  
  // Start real-time notifications simulation
  notificationsStore.simulateRealTimeNotifications()
})
</script>