# Frontend Test Suite

This directory contains comprehensive tests for the Risk Assessment Management System frontend, following Test-Driven Development (TDD) principles.

## Testing Framework

- **Vitest**: Modern testing framework with great Vue.js support
- **@vue/test-utils**: Official Vue.js testing utilities
- **Happy DOM**: Fast DOM implementation for Node.js
- **Coverage**: V8 provider with 80% threshold requirements

## Test Structure

```
tests/
├── README.md              # This file
├── setup.ts              # Global test configuration
├── utils/
│   ├── api.test.js       # API client tests
│   └── test-helpers.js   # Test utilities and helpers
├── stores/
│   └── templates.test.js # Pinia store tests
├── pages/
│   └── templates.test.js # Page component tests
└── composables/          # Existing composable tests
    ├── useApi.test.ts
    ├── useDashboard.test.ts
    └── useProjects.test.ts
```

## TDD Approach

### Red-Green-Refactor Cycle

1. **Red Phase**: Write failing tests first
   - Define expected behavior before implementation
   - Tests should fail because functionality doesn't exist yet
   - Focus on API contracts and user interactions

2. **Green Phase**: Implement minimal code to make tests pass
   - Write the simplest implementation that satisfies tests
   - Focus on making tests pass, not on perfect code
   - Implement API integration to replace mock/localStorage data

3. **Refactor Phase**: Improve code while maintaining test coverage
   - Optimize performance and code quality
   - Ensure all tests continue to pass
   - Add error handling and edge cases

### Current Test Coverage

#### API Client Tests (`utils/api.test.js`)
- ✅ API client initialization
- ✅ Request method with headers and error handling
- ✅ Templates CRUD operations (getAll, getById, create, update, delete, copy)
- ✅ Categories CRUD operations
- ✅ Contents CRUD operations
- ✅ Network error handling
- ✅ Query parameter handling
- ✅ Response format validation

#### Templates Store Tests (`stores/templates.test.js`)
- ✅ Store initialization and state management
- ✅ fetchTemplates with API integration
- ✅ addTemplate with optimistic updates
- ✅ updateTemplate with rollback on errors
- ✅ deleteTemplate with state cleanup
- ✅ copyTemplate functionality
- ✅ Loading states management
- ✅ Error handling and recovery
- ✅ Computed getters
- ✅ Optimistic updates and rollback mechanisms

#### Templates Page Tests (`pages/templates.test.js`)
- ✅ Page rendering and layout
- ✅ Templates display and empty states
- ✅ Add template modal and form submission
- ✅ Edit template functionality
- ✅ Delete confirmation workflow
- ✅ Copy template operation
- ✅ Navigation to template content
- ✅ Action buttons and tooltips
- ✅ Form validation and error handling
- ✅ Keyboard and accessibility support
- ✅ Responsive design considerations
- ✅ Store integration

## Running Tests

### Basic Commands

```bash
# Run all tests
npm test

# Run tests in watch mode
npm run test:watch

# Run tests once
npm run test:run

# Run tests with coverage
npm run test:coverage
```

### Targeted Testing

```bash
# Run specific test file
npx vitest tests/utils/api.test.js

# Run tests matching pattern
npx vitest --grep "templates store"

# Run tests in specific directory
npx vitest tests/stores/
```

### Coverage Requirements

- **Branches**: 80% minimum
- **Functions**: 80% minimum  
- **Lines**: 80% minimum
- **Statements**: 80% minimum

## Test Utilities

### Helper Functions (`utils/test-helpers.js`)

- `createMockApiResponse()` - Standard API response format
- `createMockTemplate()` - Mock template objects
- `createMockLocalStorage()` - localStorage mock
- `createMock$fetch()` - Configurable fetch mock
- `generateTemplates()` - Test data generation
- `flushPromises()` - Async testing utilities
- `expectError()` - Error assertion helper

### Mock Data

```javascript
import { 
  createMockTemplate, 
  generateTemplates, 
  createMockApiResponse 
} from '@/tests/utils/test-helpers'

// Single template
const template = createMockTemplate({ 
  version_name: 'Custom Template' 
})

// Multiple templates
const templates = generateTemplates(5)

// API response
const response = createMockApiResponse({ templates })
```

## Testing Strategy

### API Integration Testing

1. **Mock API Responses**: Use realistic response formats
2. **Error Scenarios**: Test network failures, validation errors
3. **Loading States**: Verify UI feedback during operations
4. **Optimistic Updates**: Test immediate UI updates with rollback

### Store Testing

1. **State Management**: Verify reactive state updates
2. **Action Testing**: Test async operations and side effects
3. **Error Handling**: Ensure graceful error recovery
4. **Computed Properties**: Test derived state calculations

### Component Testing

1. **User Interactions**: Test clicks, form submissions, navigation
2. **Props and Events**: Verify component communication
3. **Conditional Rendering**: Test different UI states
4. **Accessibility**: Ensure keyboard navigation and screen readers

### Integration Testing

1. **Store-Component Integration**: Test data flow
2. **API-Store Integration**: Test real API calls (when implemented)
3. **Route Navigation**: Test page transitions
4. **Error Boundaries**: Test error propagation

## Implementation Roadmap

### Phase 1: TDD Test Suite (COMPLETED)
- ✅ API client comprehensive tests
- ✅ Templates store tests with mocked API
- ✅ Templates page component tests
- ✅ Test utilities and helpers

### Phase 2: API Integration (NEXT - GREEN PHASE)
- [ ] Update templates store to use real API calls
- [ ] Replace localStorage with API persistence
- [ ] Implement loading states and error handling
- [ ] Add optimistic updates with server synchronization

### Phase 3: Enhanced Features (REFACTOR PHASE)
- [ ] Add pagination support
- [ ] Implement advanced search and filtering
- [ ] Add bulk operations (multi-select, bulk delete)
- [ ] Enhance error messages and user feedback

### Phase 4: Advanced Testing
- [ ] E2E tests with Playwright
- [ ] Visual regression testing
- [ ] Performance testing
- [ ] Cross-browser compatibility tests

## Best Practices

### Writing Tests

1. **Descriptive Names**: Use clear, behavior-focused test names
2. **Single Responsibility**: One assertion per test when possible
3. **Setup/Teardown**: Use beforeEach/afterEach for clean state
4. **Mock Strategy**: Mock external dependencies, test internal logic

### API Testing

1. **Response Formats**: Use consistent mock response structures
2. **Error Scenarios**: Test all error conditions
3. **Edge Cases**: Test empty responses, malformed data
4. **Network Conditions**: Simulate timeouts, connection errors

### Component Testing

1. **User Perspective**: Test from user interaction standpoint
2. **Accessibility**: Include keyboard and screen reader testing
3. **Responsive Design**: Test different viewport sizes
4. **Performance**: Verify efficient rendering and updates

## Debugging Tests

### Common Issues

1. **Async Timing**: Use `await` and `flushPromises()`
2. **Mock Configuration**: Ensure mocks are reset between tests
3. **DOM Updates**: Wait for `nextTick()` after state changes
4. **Store State**: Reset Pinia store state in beforeEach

### Debug Tools

```javascript
// Debug component state
console.log(wrapper.vm.$data)

// Debug store state
console.log(store.$state)

// Debug DOM output
console.log(wrapper.html())

// Debug event emissions
console.log(wrapper.emitted())
```

## Contributing

When adding new tests:

1. Follow TDD principles - write tests first
2. Maintain 80%+ coverage requirements
3. Use existing test helpers and patterns
4. Document complex test scenarios
5. Update this README for new test categories

## Continuous Integration

Tests are run automatically on:
- Pull request creation
- Branch updates
- Main branch merges

Coverage reports are generated and stored as artifacts.