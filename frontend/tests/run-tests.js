#!/usr/bin/env node

/**
 * Test runner script for the Risk Assessment Management System
 * This script helps run tests with different configurations and options
 */

import { spawn } from 'child_process'
import { resolve } from 'path'

const args = process.argv.slice(2)
const isWatch = args.includes('--watch') || args.includes('-w')
const isCoverage = args.includes('--coverage') || args.includes('-c')
const isVerbose = args.includes('--verbose') || args.includes('-v')
const pattern = args.find(arg => arg.startsWith('--pattern='))?.split('=')[1]

// Build vitest command
let command = ['npx', 'vitest']

if (isWatch) {
  // Default behavior for vitest is watch mode
} else {
  command.push('run')
}

if (isCoverage) {
  command.push('--coverage')
}

if (isVerbose) {
  command.push('--reporter=verbose')
}

if (pattern) {
  command.push('--grep', pattern)
}

// Add any additional vitest arguments
const additionalArgs = args.filter(arg => 
  !arg.startsWith('--') || 
  (arg.startsWith('--') && !['--watch', '--coverage', '--verbose'].includes(arg.split('=')[0]))
)

command.push(...additionalArgs)

console.log('🧪 Running tests with command:', command.join(' '))
console.log('')

// Spawn the vitest process
const child = spawn(command[0], command.slice(1), {
  stdio: 'inherit',
  cwd: resolve(process.cwd()),
  shell: process.platform === 'win32'
})

child.on('close', (code) => {
  if (code === 0) {
    console.log('')
    console.log('✅ All tests passed!')
  } else {
    console.log('')
    console.log('❌ Some tests failed.')
    process.exit(code)
  }
})

child.on('error', (error) => {
  console.error('Failed to start test runner:', error)
  process.exit(1)
})

// Handle graceful shutdown
process.on('SIGINT', () => {
  child.kill('SIGINT')
})

process.on('SIGTERM', () => {
  child.kill('SIGTERM')
})