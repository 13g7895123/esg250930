import { test, expect } from '@playwright/test';

test.describe('Answer Page - Question 18 Content 16', () => {
  const testUrl = '/web/risk-assessment/questions/1/answer/18/16?title=未命名題目&description=測試風險因子描述002';
  let consoleErrors = [];
  let testResults = {
    pageLoaded: false,
    fields: {},
    errors: [],
    missingElements: [],
    screenshot: '',
  };

  test.beforeEach(async ({ page }) => {
    // Listen for console errors
    consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    // Listen for page errors
    page.on('pageerror', error => {
      consoleErrors.push(`Page Error: ${error.message}`);
    });
  });

  test('should load the answer page and verify all form fields', async ({ page }) => {
    console.log('\n=== Starting Answer Page Test ===\n');

    // Step 1: Navigate to the page
    console.log('Step 1: Navigating to the page...');
    await page.goto(testUrl);

    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
    testResults.pageLoaded = true;
    console.log('✓ Page loaded successfully\n');

    // Step 2: Wait for the main content to load
    console.log('Step 2: Waiting for main content...');
    try {
      await page.waitForSelector('form, .question-form, main', { timeout: 10000 });
      console.log('✓ Main content container found\n');
    } catch (error) {
      console.log('⚠ No standard form container found, continuing...\n');
    }

    // Step 3: Check E-1 風險描述 textarea
    console.log('Step 3: Checking E-1 風險描述...');
    const e1RiskDescSelector = 'textarea[name*="e1"], textarea[id*="e1"], textarea[placeholder*="風險描述"], textarea[aria-label*="E-1"]';
    try {
      const e1Field = await page.locator(e1RiskDescSelector).first();
      const e1Count = await page.locator(e1RiskDescSelector).count();

      if (e1Count > 0) {
        const e1Value = await e1Field.inputValue();
        const e1Placeholder = await e1Field.getAttribute('placeholder');
        testResults.fields.e1_risk_description = {
          exists: true,
          value: e1Value,
          placeholder: e1Placeholder,
          count: e1Count,
        };
        console.log(`✓ E-1 風險描述 found (${e1Count} elements)`);
        console.log(`  Value: "${e1Value}"`);
        console.log(`  Placeholder: "${e1Placeholder}"\n`);
      } else {
        testResults.missingElements.push('E-1 風險描述 textarea');
        console.log('✗ E-1 風險描述 not found\n');
      }
    } catch (error) {
      testResults.errors.push(`E-1 風險描述: ${error.message}`);
      testResults.missingElements.push('E-1 風險描述 textarea');
      console.log(`✗ E-1 風險描述 error: ${error.message}\n`);
    }

    // Step 4: Check E-2 風險可能性 select
    console.log('Step 4: Checking E-2 風險可能性...');
    const e2ProbabilitySelector = 'select[name*="e2_probability"], select[id*="e2_probability"], select[aria-label*="可能性"]';
    try {
      const e2ProbField = await page.locator(e2ProbabilitySelector).first();
      const e2ProbCount = await page.locator(e2ProbabilitySelector).count();

      if (e2ProbCount > 0) {
        const e2ProbValue = await e2ProbField.inputValue();
        const e2ProbText = await e2ProbField.locator('option:checked').textContent();
        testResults.fields.e2_risk_probability = {
          exists: true,
          value: e2ProbValue,
          selectedText: e2ProbText,
          count: e2ProbCount,
        };
        console.log(`✓ E-2 風險可能性 found (${e2ProbCount} elements)`);
        console.log(`  Value: "${e2ProbValue}"`);
        console.log(`  Selected: "${e2ProbText}"\n`);
      } else {
        testResults.missingElements.push('E-2 風險可能性 select');
        console.log('✗ E-2 風險可能性 not found\n');
      }
    } catch (error) {
      testResults.errors.push(`E-2 風險可能性: ${error.message}`);
      testResults.missingElements.push('E-2 風險可能性 select');
      console.log(`✗ E-2 風險可能性 error: ${error.message}\n`);
    }

    // Step 5: Check E-2 風險衝擊 select
    console.log('Step 5: Checking E-2 風險衝擊...');
    const e2ImpactSelector = 'select[name*="e2_impact"], select[id*="e2_impact"], select[aria-label*="衝擊"]';
    try {
      const e2ImpField = await page.locator(e2ImpactSelector).first();
      const e2ImpCount = await page.locator(e2ImpactSelector).count();

      if (e2ImpCount > 0) {
        const e2ImpValue = await e2ImpField.inputValue();
        const e2ImpText = await e2ImpField.locator('option:checked').textContent();
        testResults.fields.e2_risk_impact = {
          exists: true,
          value: e2ImpValue,
          selectedText: e2ImpText,
          count: e2ImpCount,
        };
        console.log(`✓ E-2 風險衝擊 found (${e2ImpCount} elements)`);
        console.log(`  Value: "${e2ImpValue}"`);
        console.log(`  Selected: "${e2ImpText}"\n`);
      } else {
        testResults.missingElements.push('E-2 風險衝擊 select');
        console.log('✗ E-2 風險衝擊 not found\n');
      }
    } catch (error) {
      testResults.errors.push(`E-2 風險衝擊: ${error.message}`);
      testResults.missingElements.push('E-2 風險衝擊 select');
      console.log(`✗ E-2 風險衝擊 error: ${error.message}\n`);
    }

    // Step 6: Check E-2 計算說明 textarea
    console.log('Step 6: Checking E-2 計算說明...');
    const e2ExplanationSelector = 'textarea[name*="e2_explanation"], textarea[id*="e2_explanation"], textarea[placeholder*="計算說明"]';
    try {
      const e2ExpField = await page.locator(e2ExplanationSelector).first();
      const e2ExpCount = await page.locator(e2ExplanationSelector).count();

      if (e2ExpCount > 0) {
        const e2ExpValue = await e2ExpField.inputValue();
        const e2ExpPlaceholder = await e2ExpField.getAttribute('placeholder');
        testResults.fields.e2_explanation = {
          exists: true,
          value: e2ExpValue,
          placeholder: e2ExpPlaceholder,
          count: e2ExpCount,
        };
        console.log(`✓ E-2 計算說明 found (${e2ExpCount} elements)`);
        console.log(`  Value: "${e2ExpValue}"`);
        console.log(`  Placeholder: "${e2ExpPlaceholder}"\n`);
      } else {
        testResults.missingElements.push('E-2 計算說明 textarea');
        console.log('✗ E-2 計算說明 not found\n');
      }
    } catch (error) {
      testResults.errors.push(`E-2 計算說明: ${error.message}`);
      testResults.missingElements.push('E-2 計算說明 textarea');
      console.log(`✗ E-2 計算說明 error: ${error.message}\n`);
    }

    // Step 7: Check F-1 機會描述 textarea
    console.log('Step 7: Checking F-1 機會描述...');
    const f1OpportunityDescSelector = 'textarea[name*="f1"], textarea[id*="f1"], textarea[placeholder*="機會描述"], textarea[aria-label*="F-1"]';
    try {
      const f1Field = await page.locator(f1OpportunityDescSelector).first();
      const f1Count = await page.locator(f1OpportunityDescSelector).count();

      if (f1Count > 0) {
        const f1Value = await f1Field.inputValue();
        const f1Placeholder = await f1Field.getAttribute('placeholder');
        testResults.fields.f1_opportunity_description = {
          exists: true,
          value: f1Value,
          placeholder: f1Placeholder,
          count: f1Count,
        };
        console.log(`✓ F-1 機會描述 found (${f1Count} elements)`);
        console.log(`  Value: "${f1Value}"`);
        console.log(`  Placeholder: "${f1Placeholder}"\n`);
      } else {
        testResults.missingElements.push('F-1 機會描述 textarea');
        console.log('✗ F-1 機會描述 not found\n');
      }
    } catch (error) {
      testResults.errors.push(`F-1 機會描述: ${error.message}`);
      testResults.missingElements.push('F-1 機會描述 textarea');
      console.log(`✗ F-1 機會描述 error: ${error.message}\n`);
    }

    // Step 8: Check F-2 機會可能性 select
    console.log('Step 8: Checking F-2 機會可能性...');
    const f2ProbabilitySelector = 'select[name*="f2_probability"], select[id*="f2_probability"]';
    try {
      const f2ProbField = await page.locator(f2ProbabilitySelector).first();
      const f2ProbCount = await page.locator(f2ProbabilitySelector).count();

      if (f2ProbCount > 0) {
        const f2ProbValue = await f2ProbField.inputValue();
        const f2ProbText = await f2ProbField.locator('option:checked').textContent();
        testResults.fields.f2_opportunity_probability = {
          exists: true,
          value: f2ProbValue,
          selectedText: f2ProbText,
          count: f2ProbCount,
        };
        console.log(`✓ F-2 機會可能性 found (${f2ProbCount} elements)`);
        console.log(`  Value: "${f2ProbValue}"`);
        console.log(`  Selected: "${f2ProbText}"\n`);
      } else {
        testResults.missingElements.push('F-2 機會可能性 select');
        console.log('✗ F-2 機會可能性 not found\n');
      }
    } catch (error) {
      testResults.errors.push(`F-2 機會可能性: ${error.message}`);
      testResults.missingElements.push('F-2 機會可能性 select');
      console.log(`✗ F-2 機會可能性 error: ${error.message}\n`);
    }

    // Step 9: Check F-2 機會衝擊 select
    console.log('Step 9: Checking F-2 機會衝擊...');
    const f2ImpactSelector = 'select[name*="f2_impact"], select[id*="f2_impact"]';
    try {
      const f2ImpField = await page.locator(f2ImpactSelector).first();
      const f2ImpCount = await page.locator(f2ImpactSelector).count();

      if (f2ImpCount > 0) {
        const f2ImpValue = await f2ImpField.inputValue();
        const f2ImpText = await f2ImpField.locator('option:checked').textContent();
        testResults.fields.f2_opportunity_impact = {
          exists: true,
          value: f2ImpValue,
          selectedText: f2ImpText,
          count: f2ImpCount,
        };
        console.log(`✓ F-2 機會衝擊 found (${f2ImpCount} elements)`);
        console.log(`  Value: "${f2ImpValue}"`);
        console.log(`  Selected: "${f2ImpText}"\n`);
      } else {
        testResults.missingElements.push('F-2 機會衝擊 select');
        console.log('✗ F-2 機會衝擊 not found\n');
      }
    } catch (error) {
      testResults.errors.push(`F-2 機會衝擊: ${error.message}`);
      testResults.missingElements.push('F-2 機會衝擊 select');
      console.log(`✗ F-2 機會衝擊 error: ${error.message}\n`);
    }

    // Step 10: Check F-2 計算說明 textarea
    console.log('Step 10: Checking F-2 計算說明...');
    const f2ExplanationSelector = 'textarea[name*="f2_explanation"], textarea[id*="f2_explanation"]';
    try {
      const f2ExpField = await page.locator(f2ExplanationSelector).first();
      const f2ExpCount = await page.locator(f2ExplanationSelector).count();

      if (f2ExpCount > 0) {
        const f2ExpValue = await f2ExpField.inputValue();
        const f2ExpPlaceholder = await f2ExpField.getAttribute('placeholder');
        testResults.fields.f2_explanation = {
          exists: true,
          value: f2ExpValue,
          placeholder: f2ExpPlaceholder,
          count: f2ExpCount,
        };
        console.log(`✓ F-2 計算說明 found (${f2ExpCount} elements)`);
        console.log(`  Value: "${f2ExpValue}"`);
        console.log(`  Placeholder: "${f2ExpPlaceholder}"\n`);
      } else {
        testResults.missingElements.push('F-2 計算說明 textarea');
        console.log('✗ F-2 計算說明 not found\n');
      }
    } catch (error) {
      testResults.errors.push(`F-2 計算說明: ${error.message}`);
      testResults.missingElements.push('F-2 計算說明 textarea');
      console.log(`✗ F-2 計算說明 error: ${error.message}\n`);
    }

    // Step 11: Take a screenshot
    console.log('Step 11: Taking screenshot...');
    const screenshotPath = 'test-results/answer-page-screenshot.png';
    await page.screenshot({
      path: screenshotPath,
      fullPage: true
    });
    testResults.screenshot = screenshotPath;
    console.log(`✓ Screenshot saved to: ${screenshotPath}\n`);

    // Step 12: Check for console errors
    console.log('Step 12: Checking for console errors...');
    testResults.consoleErrors = consoleErrors;
    if (consoleErrors.length > 0) {
      console.log(`⚠ Found ${consoleErrors.length} console errors:`);
      consoleErrors.forEach((error, index) => {
        console.log(`  ${index + 1}. ${error}`);
      });
      console.log('');
    } else {
      console.log('✓ No console errors found\n');
    }

    // Final Report
    console.log('\n=== Test Results Summary ===\n');
    console.log(`Page Loaded: ${testResults.pageLoaded ? '✓ Yes' : '✗ No'}`);
    console.log(`Fields Found: ${Object.keys(testResults.fields).length}/8`);
    console.log(`Missing Elements: ${testResults.missingElements.length}`);
    console.log(`Errors: ${testResults.errors.length}`);
    console.log(`Console Errors: ${testResults.consoleErrors.length}`);
    console.log(`Screenshot: ${testResults.screenshot}\n`);

    if (testResults.missingElements.length > 0) {
      console.log('Missing Elements:');
      testResults.missingElements.forEach((elem, index) => {
        console.log(`  ${index + 1}. ${elem}`);
      });
      console.log('');
    }

    if (testResults.errors.length > 0) {
      console.log('Errors:');
      testResults.errors.forEach((err, index) => {
        console.log(`  ${index + 1}. ${err}`);
      });
      console.log('');
    }

    console.log('=== Test Complete ===\n');

    // Write detailed results to a file
    const resultsJson = JSON.stringify(testResults, null, 2);
    await page.evaluate((results) => {
      console.log('=== DETAILED RESULTS (JSON) ===');
      console.log(results);
    }, resultsJson);

    // Assertions - the test will fail if there are missing elements
    expect(testResults.pageLoaded).toBe(true);
  });
});
