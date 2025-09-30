import { ref, mergeProps, unref, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrRenderClass, ssrRenderList, ssrRenderAttr, ssrIncludeBooleanAttr, ssrLooseContain, ssrLooseEqual, ssrRenderComponent } from 'vue/server-renderer';
import { TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { d as useSettingsStore, s as storeToRefs } from './server.mjs';
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
import '../routes/renderer.mjs';
import 'vue-bundle-renderer/runtime';
import 'unhead/server';
import 'devalue';
import 'unhead/utils';
import 'unhead/plugins';

const _sfc_main = {
  __name: "ui",
  __ssrInlineRender: true,
  setup(__props) {
    const settingsStore = useSettingsStore();
    const { showFootbar, sidebarMenuItems } = storeToRefs(settingsStore);
    const { toggleFootbar, updateMenuItems } = settingsStore;
    const localMenuItems = ref(JSON.parse(JSON.stringify(sidebarMenuItems.value)));
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6"> \u4ECB\u9762\u8A2D\u5B9A </h2><div class="mb-8"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"> \u9801\u5C3E\u8A2D\u5B9A </h3><div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg"><div><h4 class="font-medium text-gray-900 dark:text-white">\u986F\u793A\u9801\u5C3E</h4><p class="text-sm text-gray-600 dark:text-gray-400">\u5728\u9801\u9762\u5E95\u90E8\u986F\u793A\u9801\u5C3E\u8CC7\u8A0A</p></div><button class="${ssrRenderClass([[
        unref(showFootbar) ? "bg-primary-500" : "bg-gray-200 dark:bg-gray-700"
      ], "relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"])}"><span class="${ssrRenderClass([[
        unref(showFootbar) ? "translate-x-6" : "translate-x-1"
      ], "inline-block h-4 w-4 transform rounded-full bg-white transition-transform"])}"></span></button></div></div><div class="mb-8"><div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold text-gray-900 dark:text-white"> \u5074\u908A\u9078\u55AE\u8A2D\u5B9A </h3><button class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors duration-200"> \u65B0\u589E\u9078\u55AE\u9805\u76EE </button></div><div class="space-y-4"><!--[-->`);
      ssrRenderList(unref(localMenuItems), (item, index) => {
        _push(`<div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"><div class="flex items-center justify-between mb-3"><input${ssrRenderAttr("value", item.name)} type="text" placeholder="\u9078\u55AE\u540D\u7A31" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white mr-3"><select class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white mr-3"><option value="ChartBarIcon"${ssrIncludeBooleanAttr(Array.isArray(item.icon) ? ssrLooseContain(item.icon, "ChartBarIcon") : ssrLooseEqual(item.icon, "ChartBarIcon")) ? " selected" : ""}>\u5716\u8868</option><option value="CogIcon"${ssrIncludeBooleanAttr(Array.isArray(item.icon) ? ssrLooseContain(item.icon, "CogIcon") : ssrLooseEqual(item.icon, "CogIcon")) ? " selected" : ""}>\u8A2D\u5B9A</option><option value="QuestionMarkCircleIcon"${ssrIncludeBooleanAttr(Array.isArray(item.icon) ? ssrLooseContain(item.icon, "QuestionMarkCircleIcon") : ssrLooseEqual(item.icon, "QuestionMarkCircleIcon")) ? " selected" : ""}>\u5E6B\u52A9</option><option value="UsersIcon"${ssrIncludeBooleanAttr(Array.isArray(item.icon) ? ssrLooseContain(item.icon, "UsersIcon") : ssrLooseEqual(item.icon, "UsersIcon")) ? " selected" : ""}>\u7528\u6236</option><option value="DocumentIcon"${ssrIncludeBooleanAttr(Array.isArray(item.icon) ? ssrLooseContain(item.icon, "DocumentIcon") : ssrLooseEqual(item.icon, "DocumentIcon")) ? " selected" : ""}>\u6587\u4EF6</option></select><button class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200">`);
        _push(ssrRenderComponent(unref(TrashIcon), { class: "w-5 h-5" }, null, _parent));
        _push(`</button></div><div class="ml-4 space-y-2"><div class="flex items-center justify-between mb-2"><span class="text-sm font-medium text-gray-700 dark:text-gray-300">\u5B50\u9078\u55AE\u9805\u76EE</span><button class="text-sm text-primary-500 hover:text-primary-600 transition-colors duration-200"> + \u65B0\u589E\u5B50\u9805\u76EE </button></div><!--[-->`);
        ssrRenderList(item.children, (child, childIndex) => {
          _push(`<div class="flex items-center space-x-2"><input${ssrRenderAttr("value", child.name)} type="text" placeholder="\u5B50\u9078\u55AE\u540D\u7A31" class="flex-1 px-3 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"><input${ssrRenderAttr("value", child.href)} type="text" placeholder="/path" class="flex-1 px-3 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"><button class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors duration-200">`);
          _push(ssrRenderComponent(unref(XMarkIcon), { class: "w-4 h-4" }, null, _parent));
          _push(`</button></div>`);
        });
        _push(`<!--]--></div></div>`);
      });
      _push(`<!--]--></div><div class="flex justify-end space-x-3 mt-6"><button class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"> \u91CD\u7F6E\u70BA\u9810\u8A2D </button><button class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors duration-200"> \u5132\u5B58\u8A2D\u5B9A </button></div></div></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/settings/ui.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};

export { _sfc_main as default };
//# sourceMappingURL=ui-DSLmqfGF.mjs.map
