import { l as useSidebarStore, s as storeToRefs, d as useSettingsStore, m as __nuxt_component_0, o as _sfc_main$1, p as _sfc_main$2 } from "../server.mjs";
import { mergeProps, unref, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderClass, ssrRenderSlot } from "vue/server-renderer";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/hookable/dist/index.mjs";
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
import "@heroicons/vue/24/outline";
const _sfc_main = {
  __name: "default",
  __ssrInlineRender: true,
  setup(__props) {
    const sidebarStore = useSidebarStore();
    const { sidebarCollapsed } = storeToRefs(sidebarStore);
    const settingsStore = useSettingsStore();
    const { showFootbar } = storeToRefs(settingsStore);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_AppSidebar = __nuxt_component_0;
      const _component_AppNavbar = _sfc_main$1;
      const _component_AppFootbar = _sfc_main$2;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "min-h-screen bg-gray-50 dark:bg-gray-900" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_AppSidebar, null, null, _parent));
      _push(`<div class="${ssrRenderClass([{
        "sidebar-collapsed": unref(sidebarCollapsed)
      }, "min-h-screen flex flex-col main-content-area"])}">`);
      _push(ssrRenderComponent(_component_AppNavbar, null, null, _parent));
      _push(`<main class="flex-1 p-6 overflow-auto">`);
      ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
      _push(`</main>`);
      if (unref(showFootbar)) {
        _push(ssrRenderComponent(_component_AppFootbar, null, null, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("layouts/default.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=default-DXW8eE0m.js.map
