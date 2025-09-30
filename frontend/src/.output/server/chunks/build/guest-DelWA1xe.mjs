import { C as executeAsync } from '../_/nitro.mjs';
import { f as defineNuxtRouteMiddleware, b as useAuthStore, n as navigateTo } from './server.mjs';
import 'node:http';
import 'node:https';
import 'node:events';
import 'node:buffer';
import 'node:fs';
import 'node:url';
import '@iconify/utils';
import 'node:crypto';
import 'consola';
import 'node:path';
import 'vue';
import 'vue-router';
import '@vueuse/core';
import 'tailwind-merge';
import '@iconify/vue';
import 'vue/server-renderer';
import '@heroicons/vue/24/outline';
import '../routes/renderer.mjs';
import 'vue-bundle-renderer/runtime';
import 'unhead/server';
import 'devalue';
import 'unhead/utils';
import 'unhead/plugins';

const guest = defineNuxtRouteMiddleware(async (to) => {
  let __temp, __restore;
  const authStore = useAuthStore();
  [__temp, __restore] = executeAsync(() => authStore.initializeAuth()), await __temp, __restore();
  if (authStore.isLoggedIn) {
    console.log("[Guest Middleware] User is authenticated, redirecting to dashboard");
    return navigateTo("/dashboard/analytics");
  }
});

export { guest as default };
//# sourceMappingURL=guest-DelWA1xe.mjs.map
