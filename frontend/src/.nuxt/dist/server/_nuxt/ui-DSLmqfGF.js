import { ref, mergeProps, unref, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderClass, ssrRenderList, ssrRenderAttr, ssrIncludeBooleanAttr, ssrLooseContain, ssrLooseEqual, ssrRenderComponent } from "vue/server-renderer";
import { TrashIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/hookable/dist/index.mjs";
import { d as useSettingsStore, s as storeToRefs } from "../server.mjs";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/klona/dist/index.mjs";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/defu/dist/defu.mjs";
import "#internal/nuxt/paths";
import "ofetch";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/unctx/dist/index.mjs";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/h3/dist/index.mjs";
import "vue-router";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/radix3/dist/index.mjs";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/ufo/dist/index.mjs";
import "@vueuse/core";
import "tailwind-merge";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/@unhead/vue/dist/index.mjs";
import "@iconify/vue";
const _sfc_main = {
  __name: "ui",
  __ssrInlineRender: true,
  setup(__props) {
    const settingsStore = useSettingsStore();
    const { showFootbar, sidebarMenuItems } = storeToRefs(settingsStore);
    const { toggleFootbar, updateMenuItems } = settingsStore;
    const localMenuItems = ref(JSON.parse(JSON.stringify(sidebarMenuItems.value)));
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6"> 介面設定 </h2><div class="mb-8"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"> 頁尾設定 </h3><div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg"><div><h4 class="font-medium text-gray-900 dark:text-white">顯示頁尾</h4><p class="text-sm text-gray-600 dark:text-gray-400">在頁面底部顯示頁尾資訊</p></div><button class="${ssrRenderClass([[
        unref(showFootbar) ? "bg-primary-500" : "bg-gray-200 dark:bg-gray-700"
      ], "relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"])}"><span class="${ssrRenderClass([[
        unref(showFootbar) ? "translate-x-6" : "translate-x-1"
      ], "inline-block h-4 w-4 transform rounded-full bg-white transition-transform"])}"></span></button></div></div><div class="mb-8"><div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold text-gray-900 dark:text-white"> 側邊選單設定 </h3><button class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors duration-200"> 新增選單項目 </button></div><div class="space-y-4"><!--[-->`);
      ssrRenderList(unref(localMenuItems), (item, index) => {
        _push(`<div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"><div class="flex items-center justify-between mb-3"><input${ssrRenderAttr("value", item.name)} type="text" placeholder="選單名稱" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white mr-3"><select class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white mr-3"><option value="ChartBarIcon"${ssrIncludeBooleanAttr(Array.isArray(item.icon) ? ssrLooseContain(item.icon, "ChartBarIcon") : ssrLooseEqual(item.icon, "ChartBarIcon")) ? " selected" : ""}>圖表</option><option value="CogIcon"${ssrIncludeBooleanAttr(Array.isArray(item.icon) ? ssrLooseContain(item.icon, "CogIcon") : ssrLooseEqual(item.icon, "CogIcon")) ? " selected" : ""}>設定</option><option value="QuestionMarkCircleIcon"${ssrIncludeBooleanAttr(Array.isArray(item.icon) ? ssrLooseContain(item.icon, "QuestionMarkCircleIcon") : ssrLooseEqual(item.icon, "QuestionMarkCircleIcon")) ? " selected" : ""}>幫助</option><option value="UsersIcon"${ssrIncludeBooleanAttr(Array.isArray(item.icon) ? ssrLooseContain(item.icon, "UsersIcon") : ssrLooseEqual(item.icon, "UsersIcon")) ? " selected" : ""}>用戶</option><option value="DocumentIcon"${ssrIncludeBooleanAttr(Array.isArray(item.icon) ? ssrLooseContain(item.icon, "DocumentIcon") : ssrLooseEqual(item.icon, "DocumentIcon")) ? " selected" : ""}>文件</option></select><button class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200">`);
        _push(ssrRenderComponent(unref(TrashIcon), { class: "w-5 h-5" }, null, _parent));
        _push(`</button></div><div class="ml-4 space-y-2"><div class="flex items-center justify-between mb-2"><span class="text-sm font-medium text-gray-700 dark:text-gray-300">子選單項目</span><button class="text-sm text-primary-500 hover:text-primary-600 transition-colors duration-200"> + 新增子項目 </button></div><!--[-->`);
        ssrRenderList(item.children, (child, childIndex) => {
          _push(`<div class="flex items-center space-x-2"><input${ssrRenderAttr("value", child.name)} type="text" placeholder="子選單名稱" class="flex-1 px-3 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"><input${ssrRenderAttr("value", child.href)} type="text" placeholder="/path" class="flex-1 px-3 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"><button class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors duration-200">`);
          _push(ssrRenderComponent(unref(XMarkIcon), { class: "w-4 h-4" }, null, _parent));
          _push(`</button></div>`);
        });
        _push(`<!--]--></div></div>`);
      });
      _push(`<!--]--></div><div class="flex justify-end space-x-3 mt-6"><button class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"> 重置為預設 </button><button class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors duration-200"> 儲存設定 </button></div></div></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/settings/ui.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=ui-DSLmqfGF.js.map
