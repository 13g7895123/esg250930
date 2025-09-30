import { C as executeAsync } from '../_/nitro.mjs';
import { f as defineNuxtRouteMiddleware, b as useAuthStore, n as navigateTo, g as createError } from './server.mjs';
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

const admin = defineNuxtRouteMiddleware(async (to) => {
  let __temp, __restore;
  const authStore = useAuthStore();
  [__temp, __restore] = executeAsync(() => authStore.initializeAuth()), await __temp, __restore();
  if (!authStore.isLoggedIn) {
    console.log("[Admin Middleware] User not authenticated, redirecting to login");
    return navigateTo("/auth/login");
  }
  try {
    ;
    [__temp, __restore] = executeAsync(() => authStore.fetchUser()), await __temp, __restore();
  } catch (error) {
    console.warn("[Admin Middleware] Token validation failed:", error.message);
    return navigateTo("/auth/login");
  }
  if (!authStore.isAdmin) {
    console.log("[Admin Middleware] User is not admin, access denied");
    throw createError({
      statusCode: 403,
      statusMessage: "Access denied. Administrator privileges required."
    });
  }
});

export { admin as default };
//# sourceMappingURL=admin-hl85HoFd.mjs.map
