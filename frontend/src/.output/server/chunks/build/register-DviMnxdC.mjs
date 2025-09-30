import { a as useI18n, b as useAuthStore, _ as __nuxt_component_0$2 } from './server.mjs';
import { ref, mergeProps, unref, withCtx, createTextVNode, toDisplayString, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrInterpolate, ssrRenderAttr, ssrIncludeBooleanAttr, ssrRenderComponent } from 'vue/server-renderer';
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
  __name: "register",
  __ssrInlineRender: true,
  setup(__props) {
    const { t } = useI18n();
    useAuthStore();
    const form = ref({
      name: "",
      username: "",
      email: "",
      password: "",
      confirmPassword: ""
    });
    const loading = ref(false);
    const error = ref("");
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLink = __nuxt_component_0$2;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8" }, _attrs))}><div class="max-w-md w-full space-y-8"><div class="text-center"><h2 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white">${ssrInterpolate(unref(t)("auth.register_title"))}</h2><p class="mt-2 text-sm text-gray-600 dark:text-gray-400">${ssrInterpolate(unref(t)("auth.register_subtitle"))}</p></div><form class="mt-8 space-y-6"><div class="space-y-4"><div><label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("auth.full_name"))}</label><input id="name"${ssrRenderAttr("value", unref(form).name)} type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"${ssrRenderAttr("placeholder", unref(t)("auth.full_name_placeholder"))}></div><div><label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("auth.username"))}</label><input id="username"${ssrRenderAttr("value", unref(form).username)} type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"${ssrRenderAttr("placeholder", unref(t)("auth.username_placeholder"))}></div><div><label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("auth.email"))}</label><input id="email"${ssrRenderAttr("value", unref(form).email)} type="email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"${ssrRenderAttr("placeholder", unref(t)("auth.email_placeholder"))}></div><div><label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("auth.password"))}</label><input id="password"${ssrRenderAttr("value", unref(form).password)} type="password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"${ssrRenderAttr("placeholder", unref(t)("auth.password_placeholder"))}></div><div><label for="confirmPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("auth.confirm_password"))}</label><input id="confirmPassword"${ssrRenderAttr("value", unref(form).confirmPassword)} type="password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"${ssrRenderAttr("placeholder", unref(t)("auth.confirm_password_placeholder"))}></div></div>`);
      if (unref(error)) {
        _push(`<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4"><p class="text-sm text-red-700 dark:text-red-400">${ssrInterpolate(unref(error))}</p></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<button type="submit"${ssrIncludeBooleanAttr(unref(loading)) ? " disabled" : ""} class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">`);
      if (!unref(loading)) {
        _push(`<span>${ssrInterpolate(unref(t)("auth.register"))}</span>`);
      } else {
        _push(`<span class="flex items-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ${ssrInterpolate(unref(t)("auth.registering"))}</span>`);
      }
      _push(`</button><div class="text-center"><p class="text-sm text-gray-600 dark:text-gray-400">${ssrInterpolate(unref(t)("auth.have_account"))} `);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/auth/login",
        class: "font-medium text-primary-500 hover:text-primary-600 transition-colors duration-200"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`${ssrInterpolate(unref(t)("auth.login"))}`);
          } else {
            return [
              createTextVNode(toDisplayString(unref(t)("auth.login")), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</p></div></form></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/auth/register.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};

export { _sfc_main as default };
//# sourceMappingURL=register-DviMnxdC.mjs.map
