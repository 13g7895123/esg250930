// Test script to verify questions management page functionality
// Run this in browser console on http://localhost:3000/admin/risk-assessment/questions/1/management

console.log('=== Testing Questions Management Page ===');

// Check if page loads without errors
const checkPageLoad = () => {
  const errors = [];

  // Check for Vue errors
  if (window.$nuxt && window.$nuxt.$root) {
    console.log('✓ Nuxt app loaded successfully');
  } else {
    errors.push('✗ Nuxt app not loaded');
  }

  // Check for data table
  const dataTable = document.querySelector('[data-testid="data-table"], .data-table, table');
  if (dataTable) {
    console.log('✓ Data table found');
  } else {
    errors.push('✗ Data table not found');
  }

  // Check for structure management button
  const structureBtn = Array.from(document.querySelectorAll('button')).find(btn =>
    btn.textContent.includes('架構管理') || btn.title?.includes('架構管理')
  );
  if (structureBtn) {
    console.log('✓ Structure management button found');
  } else {
    errors.push('✗ Structure management button not found');
  }

  return errors;
};

// Test structure management modal
const testStructureModal = async () => {
  console.log('\n=== Testing Structure Management Modal ===');

  // Find and click structure management button
  const structureBtn = Array.from(document.querySelectorAll('button')).find(btn =>
    btn.textContent.includes('架構管理') || btn.title?.includes('架構管理')
  );

  if (structureBtn) {
    console.log('Clicking structure management button...');
    structureBtn.click();

    // Wait for modal to appear
    await new Promise(resolve => setTimeout(resolve, 1000));

    // Check if modal opened
    const modal = document.querySelector('[role="dialog"]');
    if (modal) {
      console.log('✓ Structure modal opened');

      // Check for categories button
      const categoriesBtn = Array.from(modal.querySelectorAll('button')).find(btn =>
        btn.textContent.includes('管理風險類別')
      );
      if (categoriesBtn) {
        console.log('✓ Categories management button found');

        // Click categories button
        categoriesBtn.click();
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Check if categories modal opened
        const categoriesModal = Array.from(document.querySelectorAll('[role="dialog"]')).find(modal =>
          modal.textContent.includes('管理風險類別')
        );
        if (categoriesModal) {
          console.log('✓ Categories modal opened successfully');

          // Check for data table in categories modal
          const categoriesTable = categoriesModal.querySelector('table, [data-testid="data-table"]');
          if (categoriesTable) {
            console.log('✓ Categories data table found');
          } else {
            console.log('⚠ Categories data table not found (may be empty)');
          }
        } else {
          console.log('✗ Categories modal did not open');
        }
      } else {
        console.log('✗ Categories management button not found');
      }
    } else {
      console.log('✗ Structure modal did not open');
    }
  } else {
    console.log('✗ Cannot find structure management button');
  }
};

// Test refresh functionality
const testRefresh = async () => {
  console.log('\n=== Testing Refresh Functionality ===');

  const refreshBtn = Array.from(document.querySelectorAll('button')).find(btn =>
    btn.textContent.includes('重新整理')
  );

  if (refreshBtn) {
    console.log('Clicking refresh button...');
    refreshBtn.click();

    // Check for loading state
    await new Promise(resolve => setTimeout(resolve, 100));

    const isLoading = refreshBtn.querySelector('.animate-spin');
    if (isLoading) {
      console.log('✓ Refresh button shows loading state');
    } else {
      console.log('⚠ Refresh button may not show loading state');
    }

    // Wait for refresh to complete
    await new Promise(resolve => setTimeout(resolve, 2000));
    console.log('✓ Refresh completed');
  } else {
    console.log('✗ Refresh button not found');
  }
};

// Run all tests
const runTests = async () => {
  console.log('Starting tests...\n');

  // Test 1: Check page load
  const pageErrors = checkPageLoad();
  if (pageErrors.length === 0) {
    console.log('✓ Page loaded without errors');
  } else {
    console.log('Page load errors:');
    pageErrors.forEach(error => console.log(error));
  }

  // Test 2: Test structure modal
  await testStructureModal();

  // Test 3: Test refresh
  await testRefresh();

  console.log('\n=== Tests Complete ===');
  console.log('Check browser console for any errors or warnings');
};

// Run tests
runTests();