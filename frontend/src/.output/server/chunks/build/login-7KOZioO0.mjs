import { a as useI18n, b as useAuthStore, _ as __nuxt_component_0$2 } from './server.mjs';
import { ref, mergeProps, unref, withCtx, createTextVNode, toDisplayString, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrRenderStyle, ssrInterpolate, ssrRenderAttr, ssrIncludeBooleanAttr, ssrRenderComponent } from 'vue/server-renderer';
import '../_/nitro.mjs';
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
import 'vue-router';
import '@vueuse/core';
import 'tailwind-merge';
import '@iconify/vue';
import '@heroicons/vue/24/outline';
import '../routes/renderer.mjs';
import 'vue-bundle-renderer/runtime';
import 'unhead/server';
import 'devalue';
import 'unhead/utils';
import 'unhead/plugins';

const _sfc_main = {
  __name: "login",
  __ssrInlineRender: true,
  setup(__props) {
    const { t } = useI18n();
    useAuthStore();
    const form = ref({
      username: "",
      password: ""
    });
    const loading = ref(false);
    const error = ref("");
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLink = __nuxt_component_0$2;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden" }, _attrs))}><div class="absolute inset-0 opacity-5 dark:opacity-10"><div class="absolute inset-0" style="${ssrRenderStyle({ "background-image": "radial-gradient(circle at 1px 1px, rgba(99, 102, 241, 0.15) 1px, transparent 0)", "background-size": "20px 20px" })}"></div></div><div class="relative z-10 w-full max-w-md space-y-8"><div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 p-8 text-center transform hover:scale-[1.02] transition-all duration-300"><div class="mx-auto w-16 h-16 bg-gradient-to-tr from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg"><svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div><h1 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">${ssrInterpolate(unref(t)("auth.login_title"))}</h1><p class="mt-2 text-sm text-gray-600 dark:text-gray-400 font-medium">${ssrInterpolate(unref(t)("auth.login_subtitle"))}</p></div><div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 p-8 transform hover:shadow-2xl transition-all duration-300"><form class="space-y-6"><div class="space-y-5"><div class="group"><label for="username" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">${ssrInterpolate(unref(t)("auth.username_email"))}</label><div class="relative"><div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div><input id="username"${ssrRenderAttr("value", unref(form).username)} type="text" required class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 backdrop-blur-sm"${ssrRenderAttr("placeholder", unref(t)("auth.username_email_placeholder"))}></div></div><div class="group"><label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">${ssrInterpolate(unref(t)("auth.password"))}</label><div class="relative"><div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></div><input id="password"${ssrRenderAttr("value", unref(form).password)} type="password" required class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 backdrop-blur-sm"${ssrRenderAttr("placeholder", unref(t)("auth.password_placeholder"))}></div></div></div>`);
      if (unref(error)) {
        _push(`<div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 backdrop-blur-sm"><div class="flex items-center"><svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><p class="text-sm text-red-700 dark:text-red-300 font-medium">${ssrInterpolate(unref(error))}</p></div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-5 backdrop-blur-sm"><div class="flex items-center mb-3"><svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300">${ssrInterpolate(unref(t)("auth.demo_accounts"))}</h4></div><div class="space-y-2"><div class="flex items-center justify-between text-xs"><span class="font-medium text-blue-700 dark:text-blue-400">${ssrInterpolate(unref(t)("auth.admin_account"))}:</span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded text-blue-800 dark:text-blue-200">admin / password</code></div><div class="flex items-center justify-between text-xs"><span class="font-medium text-blue-700 dark:text-blue-400">${ssrInterpolate(unref(t)("auth.email_login"))}:</span><code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded text-blue-800 dark:text-blue-200">admin@example.com / password</code></div></div></div><button type="submit"${ssrIncludeBooleanAttr(unref(loading)) ? " disabled" : ""} class="group relative w-full flex justify-center py-3.5 px-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.02] transition-all duration-200">`);
      if (!unref(loading)) {
        _push(`<span class="flex items-center">${ssrInterpolate(unref(t)("auth.login"))} <svg class="ml-2 h-4 w-4 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg></span>`);
      } else {
        _push(`<span class="flex items-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ${ssrInterpolate(unref(t)("auth.logging_in"))}</span>`);
      }
      _push(`</button><div class="text-center pt-4 border-t border-gray-200 dark:border-slate-600"><p class="text-sm text-gray-600 dark:text-gray-400">${ssrInterpolate(unref(t)("auth.no_account"))} `);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/auth/register",
        class: "font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors duration-200 underline-offset-4 hover:underline"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`${ssrInterpolate(unref(t)("auth.register"))}`);
          } else {
            return [
              createTextVNode(toDisplayString(unref(t)("auth.register")), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</p></div></form></div><div class="text-center"><p class="text-xs text-gray-500 dark:text-gray-400"> Professional Project Management for Freelancers </p></div></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/auth/login.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};

export { _sfc_main as default };
//# sourceMappingURL=login-7KOZioO0.mjs.map
