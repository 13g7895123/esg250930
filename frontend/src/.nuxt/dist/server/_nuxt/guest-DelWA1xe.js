import { executeAsync } from "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/unctx/dist/index.mjs";
import { f as defineNuxtRouteMiddleware, b as useAuthStore, n as navigateTo } from "../server.mjs";
import "vue";
import "ofetch";
import "#internal/nuxt/paths";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/hookable/dist/index.mjs";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/h3/dist/index.mjs";
import "vue-router";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/radix3/dist/index.mjs";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/defu/dist/defu.mjs";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/ufo/dist/index.mjs";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/klona/dist/index.mjs";
import "@vueuse/core";
import "tailwind-merge";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/@unhead/vue/dist/index.mjs";
import "@iconify/vue";
import "vue/server-renderer";
import "@heroicons/vue/24/outline";
const guest = defineNuxtRouteMiddleware(async (to) => {
  let __temp, __restore;
  const authStore = useAuthStore();
  [__temp, __restore] = executeAsync(() => authStore.initializeAuth()), await __temp, __restore();
  if (authStore.isLoggedIn) {
    console.log("[Guest Middleware] User is authenticated, redirecting to dashboard");
    return navigateTo("/dashboard/analytics");
  }
});
export {
  guest as default
};
//# sourceMappingURL=guest-DelWA1xe.js.map
