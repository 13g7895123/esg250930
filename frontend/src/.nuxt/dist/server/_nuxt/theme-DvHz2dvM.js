import { ref, watch, nextTick, mergeProps, unref, createVNode, resolveDynamicComponent, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderList, ssrRenderClass, ssrRenderVNode, ssrInterpolate, ssrRenderStyle, ssrRenderAttr } from "vue/server-renderer";
import { SunIcon, MoonIcon, ComputerDesktopIcon } from "@heroicons/vue/24/outline";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/hookable/dist/index.mjs";
import { e as useTheme } from "../server.mjs";
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
const _sfc_main = {
  __name: "theme",
  __ssrInlineRender: true,
  setup(__props) {
    const { colorMode, primaryColor } = useTheme();
    const customColor = ref(primaryColor.value);
    const themeModes = [
      { value: "light", label: "淺色模式", icon: SunIcon },
      { value: "dark", label: "深色模式", icon: MoonIcon },
      { value: "system", label: "跟隨系統", icon: ComputerDesktopIcon }
    ];
    const primaryColors = [
      { name: "Indigo", value: "#6366f1" },
      { name: "Blue", value: "#3b82f6" },
      { name: "Purple", value: "#8b5cf6" },
      { name: "Pink", value: "#ec4899" },
      { name: "Red", value: "#ef4444" },
      { name: "Orange", value: "#f97316" },
      { name: "Yellow", value: "#eab308" },
      { name: "Green", value: "#22c55e" },
      { name: "Emerald", value: "#10b981" },
      { name: "Teal", value: "#14b8a6" },
      { name: "Cyan", value: "#06b6d4" },
      { name: "Gray", value: "#6b7280" }
    ];
    watch(() => colorMode.preference, () => {
      nextTick(() => {
        (void 0).body.offsetHeight;
      });
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6"> 主題設定 </h2><div class="mb-8"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"> 顯示模式 </h3><div class="grid grid-cols-1 sm:grid-cols-3 gap-4"><!--[-->`);
      ssrRenderList(themeModes, (mode) => {
        _push(`<button class="${ssrRenderClass([[
          unref(colorMode).preference === mode.value ? "border-primary-500 bg-primary-50 dark:bg-primary-900/20" : "border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600"
        ], "p-4 border rounded-lg transition-all duration-200"])}">`);
        ssrRenderVNode(_push, createVNode(resolveDynamicComponent(mode.icon), { class: "w-6 h-6 mx-auto mb-2" }, null), _parent);
        _push(`<p class="text-sm font-medium">${ssrInterpolate(mode.label)}</p></button>`);
      });
      _push(`<!--]--></div></div><div class="mb-8"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"> 主要顏色 </h3><div class="grid grid-cols-6 sm:grid-cols-8 gap-3"><!--[-->`);
      ssrRenderList(primaryColors, (color) => {
        _push(`<button style="${ssrRenderStyle({ backgroundColor: color.value })}" class="${ssrRenderClass([[
          unref(primaryColor) === color.value ? "border-gray-900 dark:border-white" : "border-gray-300 dark:border-gray-600"
        ], "w-12 h-12 rounded-lg border-2 transition-transform duration-200 hover:scale-110"])}"${ssrRenderAttr("title", color.name)}></button>`);
      });
      _push(`<!--]--></div></div><div class="mb-8"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"> 自定義顏色 </h3><div class="flex items-center space-x-4"><input${ssrRenderAttr("value", unref(customColor))} type="color" class="w-12 h-12 rounded-lg border border-gray-300 dark:border-gray-600"><button class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors duration-200"> 應用顏色 </button></div></div><div class="mb-8"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"> 預覽 </h3><div class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg"><div class="flex items-center space-x-4 mb-4"><button class="px-4 py-2 bg-primary-500 text-white rounded-lg"> 主要按鈕 </button><button class="px-4 py-2 border border-primary-500 text-primary-500 rounded-lg"> 次要按鈕 </button></div><div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2"><div class="h-2 rounded-full" style="${ssrRenderStyle({ backgroundColor: unref(primaryColor), width: "60%" })}"></div></div></div></div></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/settings/theme.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=theme-DvHz2dvM.js.map
