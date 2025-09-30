import { mergeProps, useSSRContext } from "vue";
import { ssrRenderAttrs } from "vue/server-renderer";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/hookable/dist/index.mjs";
const _sfc_main = {
  __name: "index",
  __ssrInlineRender: true,
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4"> Help Center </h2><p class="text-gray-600 dark:text-gray-300 mb-6"> 尋找答案、獲得支援或瀏覽文檔。 </p><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"><div class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> 常見問題 (FAQ) </h3><p class="text-gray-600 dark:text-gray-300 text-sm"> 查看最常見的問題和解答 </p></div><div class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> 聯絡支援 </h3><p class="text-gray-600 dark:text-gray-300 text-sm"> 直接聯絡我們的支援團隊 </p></div><div class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> 技術文件 </h3><p class="text-gray-600 dark:text-gray-300 text-sm"> 深入的技術文檔和指南 </p></div></div></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/help/index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=index-Bw-ECUHA.js.map
