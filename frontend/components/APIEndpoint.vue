<template>
  <UCard class="border border-gray-200 dark:border-gray-700">
    <!-- Endpoint Header -->
    <div
      class="flex items-center justify-between cursor-pointer"
      @click="isExpanded = !isExpanded"
    >
      <div class="flex items-center space-x-4 flex-1">
        <UBadge
          :color="methodColor"
          :variant="'solid'"
          class="font-mono text-xs px-3 py-1 min-w-[60px] text-center"
        >
          {{ endpoint.method }}
        </UBadge>

        <div class="flex-1">
          <div class="font-mono text-sm font-medium text-gray-900 dark:text-white">
            {{ endpoint.path }}
          </div>
          <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ endpoint.title }}
          </div>
        </div>
      </div>

      <div class="flex items-center space-x-2">
        <UButton
          v-if="!isExpanded"
          variant="ghost"
          size="sm"
          @click.stop="testAPI"
          :loading="isTesting"
        >
          <Icon name="i-heroicons-play" class="w-4 h-4" />
          快速測試
        </UButton>

        <Icon
          :name="isExpanded ? 'i-heroicons-chevron-up' : 'i-heroicons-chevron-down'"
          class="w-5 h-5 text-gray-400 transition-transform"
        />
      </div>
    </div>

    <!-- Expanded Content -->
    <div v-if="isExpanded" class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
      <!-- Description -->
      <div class="mb-6">
        <h4 class="font-medium text-gray-900 dark:text-white mb-2">描述</h4>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ endpoint.description }}</p>
      </div>

      <!-- Path Parameters -->
      <div v-if="endpoint.pathParams && endpoint.pathParams.length > 0" class="mb-6">
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">路徑參數</h4>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">參數名稱</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">類型</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">必填</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">說明</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="param in endpoint.pathParams" :key="param.name">
                <td class="px-4 py-2 text-sm font-mono text-gray-900 dark:text-white">{{ param.name }}</td>
                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ param.type }}</td>
                <td class="px-4 py-2 text-sm">
                  <UBadge :color="param.required ? 'red' : 'gray'" variant="soft" size="xs">
                    {{ param.required ? '必填' : '選填' }}
                  </UBadge>
                </td>
                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ param.description }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Request Parameters -->
      <div v-if="endpoint.parameters && endpoint.parameters.length > 0" class="mb-6">
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">請求參數</h4>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">參數名稱</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">類型</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">必填</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">說明</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="param in endpoint.parameters" :key="param.name">
                <td class="px-4 py-2 text-sm font-mono text-gray-900 dark:text-white">{{ param.name }}</td>
                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ param.type }}</td>
                <td class="px-4 py-2 text-sm">
                  <UBadge :color="param.required ? 'red' : 'gray'" variant="soft" size="xs">
                    {{ param.required ? '必填' : '選填' }}
                  </UBadge>
                </td>
                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ param.description }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Example Response -->
      <div v-if="endpoint.example" class="mb-6">
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">回應範例</h4>
        <pre class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-sm overflow-x-auto"><code>{{ JSON.stringify(endpoint.example, null, 2) }}</code></pre>
      </div>

      <!-- API Testing Section -->
      <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
        <h4 class="font-medium text-gray-900 dark:text-white mb-4 flex items-center">
          <Icon name="i-heroicons-wrench-screwdriver" class="w-4 h-4 mr-2" />
          測試 API
        </h4>

        <form @submit.prevent="testAPI" class="space-y-4">
          <!-- Path Parameters Input -->
          <div v-if="endpoint.pathParams && endpoint.pathParams.length > 0">
            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">路徑參數</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="param in endpoint.pathParams" :key="param.name">
                <UFormGroup :label="`${param.name} ${param.required ? '*' : ''}`">
                  <UInput
                    v-model="pathParamsData[param.name]"
                    :placeholder="`輸入 ${param.description}...`"
                    :required="param.required"
                  />
                </UFormGroup>
              </div>
            </div>
          </div>

          <!-- Request Parameters Input -->
          <div v-if="endpoint.parameters && endpoint.parameters.length > 0 && endpoint.method !== 'GET'">
            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">請求參數</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="param in endpoint.parameters" :key="param.name">
                <UFormGroup :label="`${param.name} ${param.required ? '*' : ''}`">
                  <UInput
                    v-if="param.type !== 'file'"
                    v-model="requestData[param.name]"
                    :type="getInputType(param.type)"
                    :placeholder="`輸入 ${param.description}...`"
                    :required="param.required"
                  />
                  <input
                    v-else
                    type="file"
                    @change="handleFileChange($event, param.name)"
                    :required="param.required"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                  />
                </UFormGroup>
              </div>
            </div>
          </div>

          <!-- Query Parameters for GET requests -->
          <div v-if="endpoint.parameters && endpoint.parameters.length > 0 && endpoint.method === 'GET'">
            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">查詢參數</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="param in endpoint.parameters" :key="param.name">
                <UFormGroup :label="`${param.name} ${param.required ? '*' : ''}`">
                  <UInput
                    v-model="requestData[param.name]"
                    :type="getInputType(param.type)"
                    :placeholder="`輸入 ${param.description}...`"
                    :required="param.required"
                  />
                </UFormGroup>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end">
            <UButton
              type="submit"
              :loading="isTesting"
              color="blue"
              size="lg"
            >
              <Icon name="i-heroicons-play" class="w-4 h-4 mr-2" />
              {{ isTesting ? '測試中...' : '執行測試' }}
            </UButton>
          </div>
        </form>

        <!-- API Response -->
        <div v-if="apiResponse" class="mt-6">
          <div class="flex items-center justify-between mb-3">
            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">API 回應</h5>
            <UBadge
              :color="getStatusColor(apiResponse.status)"
              variant="soft"
            >
              {{ apiResponse.status }} {{ getStatusText(apiResponse.status) }}
            </UBadge>
          </div>

          <pre class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-sm overflow-x-auto max-h-96"><code>{{ formatResponse(apiResponse.data) }}</code></pre>
        </div>
      </div>
    </div>
  </UCard>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'

const props = defineProps({
  endpoint: {
    type: Object,
    required: true
  },
  searchQuery: {
    type: String,
    default: ''
  }
})

// Reactive state
const isExpanded = ref(false)
const isTesting = ref(false)
const apiResponse = ref(null)
const pathParamsData = reactive({})
const requestData = reactive({})
const fileData = reactive({})

// Computed properties
const methodColor = computed(() => {
  const colors = {
    GET: 'green',
    POST: 'blue',
    PUT: 'yellow',
    DELETE: 'red',
    PATCH: 'purple'
  }
  return colors[props.endpoint.method] || 'gray'
})

// Methods
const getInputType = (paramType) => {
  const types = {
    integer: 'number',
    decimal: 'number',
    email: 'email',
    password: 'password'
  }
  return types[paramType] || 'text'
}

const handleFileChange = (event, paramName) => {
  const file = event.target.files[0]
  if (file) {
    fileData[paramName] = file
  }
}

const buildApiUrl = () => {
  let url = props.endpoint.path

  // Replace path parameters
  if (props.endpoint.pathParams) {
    props.endpoint.pathParams.forEach(param => {
      const value = pathParamsData[param.name]
      if (value) {
        url = url.replace(`{${param.name}}`, encodeURIComponent(value))
      }
    })
  }

  // Add query parameters for GET requests
  if (props.endpoint.method === 'GET' && props.endpoint.parameters) {
    const queryParams = new URLSearchParams()
    props.endpoint.parameters.forEach(param => {
      const value = requestData[param.name]
      if (value) {
        queryParams.append(param.name, value)
      }
    })

    if (queryParams.toString()) {
      url += '?' + queryParams.toString()
    }
  }

  return url
}

const testAPI = async () => {
  isTesting.value = true
  apiResponse.value = null

  try {
    const url = buildApiUrl()
    const config = useRuntimeConfig()
    const fullUrl = `${window.location.origin}${url}`

    const options = {
      method: props.endpoint.method,
      headers: {
        'Accept': 'application/json'
      }
    }

    // Handle request body for non-GET requests
    if (props.endpoint.method !== 'GET') {
      if (props.endpoint.contentType === 'multipart/form-data') {
        // Handle file upload
        const formData = new FormData()

        if (props.endpoint.parameters) {
          props.endpoint.parameters.forEach(param => {
            if (param.type === 'file' && fileData[param.name]) {
              formData.append(param.name, fileData[param.name])
            } else if (requestData[param.name]) {
              formData.append(param.name, requestData[param.name])
            }
          })
        }

        options.body = formData
      } else {
        // Handle JSON data
        options.headers['Content-Type'] = 'application/json'

        const body = {}
        if (props.endpoint.parameters) {
          props.endpoint.parameters.forEach(param => {
            if (requestData[param.name]) {
              body[param.name] = requestData[param.name]
            }
          })
        }

        if (Object.keys(body).length > 0) {
          options.body = JSON.stringify(body)
        }
      }
    }

    const response = await fetch(fullUrl, options)
    const responseText = await response.text()

    let responseData
    try {
      responseData = JSON.parse(responseText)
    } catch (e) {
      responseData = responseText
    }

    apiResponse.value = {
      status: response.status,
      data: responseData
    }

  } catch (error) {
    apiResponse.value = {
      status: 'ERROR',
      data: { error: error.message }
    }
  } finally {
    isTesting.value = false
  }
}

const getStatusColor = (status) => {
  if (status >= 200 && status < 300) return 'green'
  if (status >= 400 && status < 500) return 'yellow'
  if (status >= 500) return 'red'
  return 'gray'
}

const getStatusText = (status) => {
  const statusTexts = {
    200: 'OK',
    201: 'Created',
    400: 'Bad Request',
    401: 'Unauthorized',
    403: 'Forbidden',
    404: 'Not Found',
    422: 'Unprocessable Entity',
    500: 'Internal Server Error',
    'ERROR': 'Network Error'
  }
  return statusTexts[status] || 'Unknown'
}

const formatResponse = (data) => {
  if (typeof data === 'string') {
    return data
  }
  return JSON.stringify(data, null, 2)
}
</script>