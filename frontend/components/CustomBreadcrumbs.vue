<template>
  <nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm">
      <li>
        <NuxtLink
          to="/"
          class="text-gray-500 dark:text-gray-400 hover:text-primary-500 transition-colors duration-200"
        >
          首頁
        </NuxtLink>
      </li>
      <li v-for="(item, index) in breadcrumbItems" :key="index" class="flex items-center">
        <ChevronRightIcon class="w-4 h-4 mx-2 text-gray-400" />
        <NuxtLink
          v-if="item.href && index < breadcrumbItems.length - 1"
          :to="item.href"
          class="text-gray-500 dark:text-gray-400 hover:text-primary-500 transition-colors duration-200"
        >
          {{ item.name }}
        </NuxtLink>
        <span
          v-else
          class="text-gray-700 dark:text-gray-300 font-medium"
        >
          {{ item.name }}
        </span>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { ChevronRightIcon } from '@heroicons/vue/24/outline'
import { useBreadcrumbData } from '~/composables/useBreadcrumbData'

const route = useRoute()

const {
  breadcrumbCompanyNames,
  breadcrumbAssessmentMap,
  breadcrumbContentInfo,
  loadCompanyName,
  loadAssessmentData,
  loadContentData
} = useBreadcrumbData()

// Watch route changes to preload data
watch(() => route.path, async (newPath) => {
  // Editor path
  const editorMatch = newPath.match(/\/editor\/question-(\d+)-(\d+)/)
  if (editorMatch) {
    const contentId = editorMatch[2]
    await loadContentData(contentId)
    return
  }

  // Assignments or Statistics path
  const assessmentMatch = newPath.match(/\/questions\/(\d+)\/(assignments|statistics)/)
  if (assessmentMatch) {
    const assessmentId = assessmentMatch[1]
    await loadAssessmentData(assessmentId)
    return
  }

  // Content path
  const contentPathMatch = newPath.match(/\/questions\/(\d+)\/management/)
  if (contentPathMatch) {
    const companyId = contentPathMatch[1]
    await loadCompanyName(companyId)
    return
  }

  // Management path
  const managementMatch = newPath.match(/\/questions\/(\d+)\/management/)
  if (managementMatch) {
    const companyId = managementMatch[1]
    await loadCompanyName(companyId)
  }
}, { immediate: true })

// Custom breadcrumb configurations
const breadcrumbItems = computed(() => {
  const path = route.path

  // Editor: /admin/risk-assessment/editor/question-{questionId}-{contentId}
  const editorMatch = path.match(/^\/admin\/risk-assessment\/editor\/question-(\d+)-(\d+)$/)
  if (editorMatch) {
    const questionId = editorMatch[1]
    const contentId = editorMatch[2]
    const contentInfo = breadcrumbContentInfo.value[contentId]
    
    const companyId = contentInfo?.companyId
    const year = contentInfo?.year
    const companyName = companyId && breadcrumbCompanyNames.value[companyId] 
      ? breadcrumbCompanyNames.value[companyId] 
      : '載入中...'

    return [
      { name: '風險評估表', href: '/admin/risk-assessment' },
      { name: '公司列表', href: '/admin/risk-assessment/questions' },
      { name: `題項管理 (${companyName})`, href: companyId ? `/admin/risk-assessment/questions/${companyId}/management` : '#' },
      { name: `題項內容管理${year ? `(${year}年度)` : ''}`, href: companyId && questionId ? `/admin/risk-assessment/questions/${companyId}/management/${questionId}/content` : '#' },
      { name: '題目編輯', href: path }
    ]
  }

  // Assignments: /admin/risk-assessment/questions/{assessmentId}/assignments
  const assignmentsMatch = path.match(/^\/admin\/risk-assessment\/questions\/(\d+)\/assignments$/)
  if (assignmentsMatch) {
    const assessmentId = assignmentsMatch[1]
    const assessmentData = breadcrumbAssessmentMap.value[assessmentId]
    const companyId = assessmentData?.companyId
    const companyName = companyId && breadcrumbCompanyNames.value[companyId]
      ? breadcrumbCompanyNames.value[companyId]
      : '載入中...'

    return [
      { name: '風險評估表', href: '/admin/risk-assessment' },
      { name: '公司列表', href: '/admin/risk-assessment/questions' },
      { name: `題項管理 (${companyName})`, href: companyId ? `/admin/risk-assessment/questions/${companyId}/management` : '#' },
      { name: '評估表指派狀況', href: path }
    ]
  }

  // Statistics: /admin/risk-assessment/questions/{assessmentId}/statistics
  const statisticsMatch = path.match(/^\/admin\/risk-assessment\/questions\/(\d+)\/statistics$/)
  if (statisticsMatch) {
    const assessmentId = statisticsMatch[1]
    const assessmentData = breadcrumbAssessmentMap.value[assessmentId]
    const companyId = assessmentData?.companyId
    const companyName = companyId && breadcrumbCompanyNames.value[companyId]
      ? breadcrumbCompanyNames.value[companyId]
      : '載入中...'

    return [
      { name: '風險評估表', href: '/admin/risk-assessment' },
      { name: '公司列表', href: '/admin/risk-assessment/questions' },
      { name: `題項管理 (${companyName})`, href: companyId ? `/admin/risk-assessment/questions/${companyId}/management` : '#' },
      { name: '評估表統計結果', href: path }
    ]
  }

  // Content: /admin/risk-assessment/questions/{companyId}/management/{questionId}/content
  const contentMatch = path.match(/^\/admin\/risk-assessment\/questions\/(\d+)\/management\/(\d+)\/content$/)
  if (contentMatch) {
    const companyId = contentMatch[1]
    const questionId = contentMatch[2]
    const companyName = breadcrumbCompanyNames.value[companyId] || '載入中...'

    return [
      { name: '風險評估表', href: '/admin/risk-assessment' },
      { name: '公司列表', href: '/admin/risk-assessment/questions' },
      { name: `題項管理 (${companyName})`, href: `/admin/risk-assessment/questions/${companyId}/management` },
      { name: '題項內容管理', href: path }
    ]
  }

  // Default: generate breadcrumbs from path segments
  const pathSegments = path.split('/').filter(segment => segment)
  const items = []

  // Skip first segment if it's admin, teacher, or web
  const skipFirstSegment = ['admin', 'teacher', 'web'].includes(pathSegments[0])
  const startIndex = skipFirstSegment ? 1 : 0

  let currentPath = ''
  for (let i = startIndex; i < pathSegments.length; i++) {
    // Build the full path including skipped segments for routing
    const fullPath = '/' + pathSegments.slice(0, i + 1).join('/')
    currentPath = fullPath

    const segment = pathSegments[i]

    // Handle numeric segments (IDs) - skip them in breadcrumb
    if (/^\d+$/.test(segment)) {
      continue
    }

    // Map common segments to Chinese names
    const segmentNames = {
      'risk-assessment': '風險評估表',
      'templates': '範本管理',
      'questions': '公司列表',
      'management': '題項管理',
      'content': '內容管理',
      'assignments': '指派狀況',
      'statistics': '統計結果',
      'editor': '編輯器'
    }

    const name = segmentNames[segment] || segment

    items.push({
      name,
      href: currentPath
    })
  }

  return items
})
</script>
