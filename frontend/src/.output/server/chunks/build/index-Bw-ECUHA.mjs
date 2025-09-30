import { mergeProps, useSSRContext } from 'vue';
import { ssrRenderAttrs } from 'vue/server-renderer';

const _sfc_main = {
  __name: "index",
  __ssrInlineRender: true,
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4"> Help Center </h2><p class="text-gray-600 dark:text-gray-300 mb-6"> \u5C0B\u627E\u7B54\u6848\u3001\u7372\u5F97\u652F\u63F4\u6216\u700F\u89BD\u6587\u6A94\u3002 </p><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"><div class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> \u5E38\u898B\u554F\u984C (FAQ) </h3><p class="text-gray-600 dark:text-gray-300 text-sm"> \u67E5\u770B\u6700\u5E38\u898B\u7684\u554F\u984C\u548C\u89E3\u7B54 </p></div><div class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> \u806F\u7D61\u652F\u63F4 </h3><p class="text-gray-600 dark:text-gray-300 text-sm"> \u76F4\u63A5\u806F\u7D61\u6211\u5011\u7684\u652F\u63F4\u5718\u968A </p></div><div class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> \u6280\u8853\u6587\u4EF6 </h3><p class="text-gray-600 dark:text-gray-300 text-sm"> \u6DF1\u5165\u7684\u6280\u8853\u6587\u6A94\u548C\u6307\u5357 </p></div></div></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/help/index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};

export { _sfc_main as default };
//# sourceMappingURL=index-Bw-ECUHA.mjs.map
