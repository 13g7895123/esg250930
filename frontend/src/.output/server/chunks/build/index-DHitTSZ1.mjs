import { _ as __nuxt_component_0$2 } from './server.mjs';
import { mergeProps, withCtx, createVNode, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrRenderComponent } from 'vue/server-renderer';
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
  __name: "index",
  __ssrInlineRender: true,
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLink = __nuxt_component_0$2;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4"> \u8A2D\u5B9A </h2><p class="text-gray-600 dark:text-gray-300 mb-6"> \u7BA1\u7406\u60A8\u7684\u61C9\u7528\u7A0B\u5F0F\u8A2D\u5B9A\u548C\u504F\u597D\u3002 </p><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">`);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/settings/theme",
        class: "block p-6 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-primary-500 transition-colors duration-200"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"${_scopeId}> \u4E3B\u984C\u8A2D\u5B9A </h3><p class="text-gray-600 dark:text-gray-300 text-sm"${_scopeId}> \u81EA\u5B9A\u7FA9\u984F\u8272\u4E3B\u984C\u548C\u986F\u793A\u6A21\u5F0F </p>`);
          } else {
            return [
              createVNode("h3", { class: "text-lg font-semibold text-gray-900 dark:text-white mb-2" }, " \u4E3B\u984C\u8A2D\u5B9A "),
              createVNode("p", { class: "text-gray-600 dark:text-gray-300 text-sm" }, " \u81EA\u5B9A\u7FA9\u984F\u8272\u4E3B\u984C\u548C\u986F\u793A\u6A21\u5F0F ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> \u4E00\u822C\u8A2D\u5B9A </h3><p class="text-gray-600 dark:text-gray-300 text-sm"> \u57FA\u672C\u61C9\u7528\u7A0B\u5F0F\u8A2D\u5B9A </p></div><div class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> \u7528\u6236\u7BA1\u7406 </h3><p class="text-gray-600 dark:text-gray-300 text-sm"> \u7BA1\u7406\u7528\u6236\u5E33\u6236\u548C\u6B0A\u9650 </p></div></div></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/settings/index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};

export { _sfc_main as default };
//# sourceMappingURL=index-DHitTSZ1.mjs.map
