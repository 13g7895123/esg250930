import { _ as __nuxt_component_0 } from "../server.mjs";
import { mergeProps, withCtx, createVNode, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderComponent } from "vue/server-renderer";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/hookable/dist/index.mjs";
import "ofetch";
import "#internal/nuxt/paths";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/unctx/dist/index.mjs";
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
import "@heroicons/vue/24/outline";
const _sfc_main = {
  __name: "index",
  __ssrInlineRender: true,
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLink = __nuxt_component_0;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4"> 設定 </h2><p class="text-gray-600 dark:text-gray-300 mb-6"> 管理您的應用程式設定和偏好。 </p><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">`);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/settings/theme",
        class: "block p-6 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-primary-500 transition-colors duration-200"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"${_scopeId}> 主題設定 </h3><p class="text-gray-600 dark:text-gray-300 text-sm"${_scopeId}> 自定義顏色主題和顯示模式 </p>`);
          } else {
            return [
              createVNode("h3", { class: "text-lg font-semibold text-gray-900 dark:text-white mb-2" }, " 主題設定 "),
              createVNode("p", { class: "text-gray-600 dark:text-gray-300 text-sm" }, " 自定義顏色主題和顯示模式 ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> 一般設定 </h3><p class="text-gray-600 dark:text-gray-300 text-sm"> 基本應用程式設定 </p></div><div class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> 用戶管理 </h3><p class="text-gray-600 dark:text-gray-300 text-sm"> 管理用戶帳戶和權限 </p></div></div></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/settings/index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=index-DHitTSZ1.js.map
