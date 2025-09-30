import { ref, computed, mergeProps, unref, createVNode, resolveDynamicComponent, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderList, ssrRenderVNode, ssrInterpolate } from "vue/server-renderer";
import { FolderIcon, ClockIcon, ChartBarIcon, CurrencyDollarIcon, UsersIcon } from "@heroicons/vue/24/outline";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/hookable/dist/index.mjs";
import { u as useDashboard } from "./useDashboard-BIyzGZF7.js";
import "../server.mjs";
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
  __name: "index",
  __ssrInlineRender: true,
  setup(__props) {
    useDashboard();
    ref(true);
    ref(null);
    const dashboardData = ref({});
    const activities = ref([]);
    const stats = computed(() => [
      {
        name: "總專案數",
        value: dashboardData.value.total_projects || 0,
        icon: "FolderIcon"
      },
      {
        name: "總收入",
        value: `NT$${(dashboardData.value.total_revenue || 0).toLocaleString()}`,
        icon: "CurrencyDollarIcon"
      },
      {
        name: "進行中專案",
        value: dashboardData.value.in_progress_projects || 0,
        icon: "ClockIcon"
      },
      {
        name: "活躍業主",
        value: dashboardData.value.total_clients || 0,
        icon: "UsersIcon"
      }
    ]);
    const iconComponents = {
      UsersIcon,
      CurrencyDollarIcon,
      ChartBarIcon,
      ClockIcon,
      FolderIcon
    };
    const getIcon = (iconName) => {
      return iconComponents[iconName] || ChartBarIcon;
    };
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4"> 專案管理儀表板 </h2><p class="text-gray-600 dark:text-gray-300"> 管理您的專案、業主資訊，並追蹤專案進度和收入統計。 </p></div><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"><!--[-->`);
      ssrRenderList(unref(stats), (stat) => {
        _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><div class="flex items-center"><div class="p-3 rounded-lg bg-primary-100 dark:bg-primary-900">`);
        ssrRenderVNode(_push, createVNode(resolveDynamicComponent(getIcon(stat.icon)), { class: "w-6 h-6 text-primary-600 dark:text-primary-400" }, null), _parent);
        _push(`</div><div class="ml-4"><p class="text-sm font-medium text-gray-600 dark:text-gray-400">${ssrInterpolate(stat.name)}</p><p class="text-2xl font-bold text-gray-900 dark:text-white">${ssrInterpolate(stat.value)}</p></div></div></div>`);
      });
      _push(`<!--]--></div><div class="grid grid-cols-1 lg:grid-cols-2 gap-6"><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"> 收入趨勢 </h3><div class="h-64 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center"><p class="text-gray-500 dark:text-gray-400">月收入趨勢圖表 (待實作)</p></div></div><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"> 專案動態 </h3><div class="space-y-4">`);
      if (_ctx.activity) {
        _push(`<!--[-->`);
        ssrRenderList(unref(activities), (activity) => {
          _push(`<div class="flex items-start space-x-3"><div class="w-2 h-2 mt-2 bg-primary-500 rounded-full"></div><div class="flex-1"><p class="text-sm text-gray-900 dark:text-white">${ssrInterpolate((activity == null ? void 0 : activity.description) || "無描述")}</p><p class="text-xs text-gray-500 dark:text-gray-400">${ssrInterpolate((activity == null ? void 0 : activity.time) || "未知時間")}</p></div></div>`);
        });
        _push(`<!--]-->`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=index-_orNPGQA.js.map
