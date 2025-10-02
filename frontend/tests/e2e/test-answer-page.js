#!/usr/bin/env node

/**
 * Answer Page Test Script
 * Tests the answer page at /web/risk-assessment/questions/1/answer/18/16
 * without requiring browser automation (Playwright dependencies)
 */

import fetch from 'node-fetch'
import { JSDOM } from 'jsdom'

const TEST_URL = 'http://localhost:3000/web/risk-assessment/questions/1/answer/18/16?title=未命名題目&description=測試風險因子描述002'
const TIMEOUT = 30000

// ANSI color codes for console output
const colors = {
  reset: '\x1b[0m',
  bright: '\x1b[1m',
  green: '\x1b[32m',
  red: '\x1b[31m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  cyan: '\x1b[36m',
}

function log(message, color = 'reset') {
  console.log(`${colors[color]}${message}${colors.reset}`)
}

function logSection(title) {
  console.log(`\n${colors.bright}${colors.blue}=== ${title} ===${colors.reset}\n`)
}

function logSuccess(message) {
  log(`✓ ${message}`, 'green')
}

function logError(message) {
  log(`✗ ${message}`, 'red')
}

function logWarning(message) {
  log(`⚠ ${message}`, 'yellow')
}

function logInfo(message) {
  log(`  ${message}`, 'cyan')
}

async function testAnswerPage() {
  const results = {
    pageLoaded: false,
    pageTitle: '',
    fields: {},
    missingElements: [],
    errors: [],
    html: null,
  }

  try {
    logSection('Starting Answer Page Test')
    log(`Test URL: ${TEST_URL}\n`)

    // Step 1: Fetch the page
    logInfo('Step 1: Fetching page...')
    const controller = new AbortController()
    const timeoutId = setTimeout(() => controller.abort(), TIMEOUT)

    const response = await fetch(TEST_URL, {
      signal: controller.signal,
      headers: {
        'User-Agent': 'Mozilla/5.0 (compatible; TestBot/1.0)',
      },
    })

    clearTimeout(timeoutId)

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    }

    const html = await response.text()
    results.html = html
    results.pageLoaded = true
    logSuccess(`Page loaded successfully (Status: ${response.status})\n`)

    // Step 2: Parse HTML
    logInfo('Step 2: Parsing HTML...')
    const dom = new JSDOM(html)
    const document = dom.window.document
    logSuccess('HTML parsed successfully\n')

    // Step 3: Check page title
    logInfo('Step 3: Checking page title...')
    const titleElement = document.querySelector('title')
    if (titleElement) {
      results.pageTitle = titleElement.textContent
      logSuccess(`Page title: "${results.pageTitle}"\n`)
    } else {
      logWarning('No title element found\n')
    }

    // Helper function to check textarea
    const checkTextarea = (stepNum, fieldName, selectors) => {
      logInfo(`Step ${stepNum}: Checking ${fieldName}...`)
      let found = false

      for (const selector of selectors) {
        const elements = document.querySelectorAll(selector)
        if (elements.length > 0) {
          const element = elements[0]
          const value = element.textContent || element.value || ''
          const placeholder = element.getAttribute('placeholder') || ''

          results.fields[fieldName] = {
            exists: true,
            value: value.trim(),
            placeholder,
            count: elements.length,
            selector,
          }

          logSuccess(`${fieldName} found (${elements.length} element${elements.length > 1 ? 's' : ''})`)
          logInfo(`  Selector: ${selector}`)
          logInfo(`  Value: "${value.trim()}"`)
          logInfo(`  Placeholder: "${placeholder}"\n`)
          found = true
          break
        }
      }

      if (!found) {
        results.missingElements.push(fieldName)
        logError(`${fieldName} not found\n`)
      }

      return found
    }

    // Helper function to check select
    const checkSelect = (stepNum, fieldName, selectors) => {
      logInfo(`Step ${stepNum}: Checking ${fieldName}...`)
      let found = false

      for (const selector of selectors) {
        const elements = document.querySelectorAll(selector)
        if (elements.length > 0) {
          const element = elements[0]
          const value = element.value || ''
          const selectedOption = element.querySelector('option[selected]')
          const selectedText = selectedOption ? selectedOption.textContent : ''

          results.fields[fieldName] = {
            exists: true,
            value,
            selectedText: selectedText.trim(),
            count: elements.length,
            selector,
          }

          logSuccess(`${fieldName} found (${elements.length} element${elements.length > 1 ? 's' : ''})`)
          logInfo(`  Selector: ${selector}`)
          logInfo(`  Value: "${value}"`)
          logInfo(`  Selected: "${selectedText.trim()}"\n`)
          found = true
          break
        }
      }

      if (!found) {
        results.missingElements.push(fieldName)
        logError(`${fieldName} not found\n`)
      }

      return found
    }

    // Check E-1 風險描述 textarea
    checkTextarea(4, 'E-1 風險描述', [
      'textarea[placeholder*="風險"]',
      'textarea[placeholder*="請描述風險"]',
    ])

    // Check E-2 風險可能性 select
    checkSelect(5, 'E-2 風險可能性', [
      'select:has(option:contains("風險發生可能性"))',
      'label:contains("風險發生可能性") + select',
      'select',
    ])

    // Check E-2 風險衝擊 select
    checkSelect(6, 'E-2 風險衝擊', [
      'select:has(option:contains("風險發生衝擊程度"))',
      'label:contains("風險發生衝擊程度") + select',
      'select',
    ])

    // Check E-2 計算說明 textarea
    checkTextarea(7, 'E-2 計算說明', [
      'textarea[placeholder*="計算"]',
      'textarea[placeholder*="請說明計算方式"]',
    ])

    // Check F-1 機會描述 textarea
    checkTextarea(8, 'F-1 機會描述', [
      'textarea[placeholder*="機會"]',
      'textarea[placeholder*="請描述機會"]',
    ])

    // Check F-2 機會可能性 select
    checkSelect(9, 'F-2 機會可能性', [
      'select:has(option:contains("機會發生可能性"))',
      'label:contains("機會發生可能性") + select',
    ])

    // Check F-2 機會衝擊 select
    checkSelect(10, 'F-2 機會衝擊', [
      'select:has(option:contains("機會發生衝擊程度"))',
      'label:contains("機會發生衝擊程度") + select',
    ])

    // Check F-2 計算說明 textarea
    checkTextarea(11, 'F-2 計算說明', [
      'textarea[placeholder*="計算"]',
      'textarea[placeholder*="請說明計算方式"]',
    ])

    // Additional checks
    logInfo('Step 12: Checking for form elements...')
    const allTextareas = document.querySelectorAll('textarea')
    const allSelects = document.querySelectorAll('select')
    logInfo(`  Total textareas found: ${allTextareas.length}`)
    logInfo(`  Total selects found: ${allSelects.length}\n`)

    // Look for component markers
    logInfo('Step 13: Looking for component markers...')
    const riskSection = document.querySelector(':contains("相關風險")')
    const oppSection = document.querySelector(':contains("相關機會")')

    if (riskSection) {
      logSuccess('Found "相關風險" section marker')
    } else {
      logWarning('Did not find "相關風險" section marker')
    }

    if (oppSection) {
      logSuccess('Found "相關機會" section marker')
    } else {
      logWarning('Did not find "相關機會" section marker')
    }

    console.log('')

  } catch (error) {
    results.errors.push(error.message)
    logError(`Test failed: ${error.message}\n`)
    console.error(error)
  }

  // Final report
  logSection('Test Results Summary')

  log(`Page Loaded: ${results.pageLoaded ? '✓ Yes' : '✗ No'}`, results.pageLoaded ? 'green' : 'red')
  log(`Page Title: ${results.pageTitle || 'N/A'}`)
  log(`Fields Found: ${Object.keys(results.fields).length}/8`, Object.keys(results.fields).length === 8 ? 'green' : 'yellow')
  log(`Missing Elements: ${results.missingElements.length}`, results.missingElements.length === 0 ? 'green' : 'red')
  log(`Errors: ${results.errors.length}`, results.errors.length === 0 ? 'green' : 'red')

  if (results.missingElements.length > 0) {
    console.log('\n' + colors.red + 'Missing Elements:' + colors.reset)
    results.missingElements.forEach((elem, index) => {
      console.log(`  ${index + 1}. ${elem}`)
    })
  }

  if (results.errors.length > 0) {
    console.log('\n' + colors.red + 'Errors:' + colors.reset)
    results.errors.forEach((err, index) => {
      console.log(`  ${index + 1}. ${err}`)
    })
  }

  console.log('\n' + colors.bright + 'Detailed Field Results:' + colors.reset)
  console.log(JSON.stringify(results.fields, null, 2))

  logSection('Test Complete')

  return results
}

// Run the test
testAnswerPage().catch((error) => {
  logError(`Fatal error: ${error.message}`)
  console.error(error)
  process.exit(1)
})
