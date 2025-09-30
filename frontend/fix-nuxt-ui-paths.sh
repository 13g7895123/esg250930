#!/bin/sh

# Fix Nuxt UI path issues in Docker container
echo "🔧 Starting Nuxt with path fixing..."

# Start the development server in background
echo "🚀 Starting development server..."
"$@" &
DEV_PID=$!

# Monitor and fix config files
echo "👀 Monitoring for config files..."
while true; do
  if [ -f ".nuxt/nuxtui-tailwind.config.mjs" ]; then
    if grep -q "/home/jarvis/project/job/twnict/esg-csr-new/frontend" .nuxt/nuxtui-tailwind.config.mjs 2>/dev/null; then
      echo "🔧 Fixing paths in nuxtui-tailwind.config.mjs..."
      sed -i 's|/home/jarvis/project/job/twnict/esg-csr-new/frontend|/app|g' .nuxt/nuxtui-tailwind.config.mjs
      echo "✅ Paths fixed!"
    fi
  fi
  sleep 5
done &
MONITOR_PID=$!

# Wait for the development server
wait $DEV_PID