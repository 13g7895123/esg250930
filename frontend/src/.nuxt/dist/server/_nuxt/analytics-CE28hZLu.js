import { ref, computed, mergeProps, unref, createVNode, resolveDynamicComponent, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderList, ssrRenderClass, ssrRenderVNode, ssrInterpolate } from "vue/server-renderer";
import { CurrencyDollarIcon, BriefcaseIcon, UsersIcon, CheckCircleIcon } from "@heroicons/vue/24/outline";
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
  __name: "analytics",
  __ssrInlineRender: true,
  setup(__props) {
    useDashboard();
    const loading = ref(true);
    ref(null);
    const dashboardStats = ref({});
    const revenueTrend = ref([]);
    const activities = ref([]);
    const statsCards = computed(() => [
      {
        title: "總收入",
        value: `NT$${(dashboardStats.value.total_revenue || 0).toLocaleString()}`,
        icon: CurrencyDollarIcon,
        iconClass: "bg-green-500"
      },
      {
        title: "專案總數",
        value: dashboardStats.value.total_projects || 0,
        icon: BriefcaseIcon,
        iconClass: "bg-blue-500"
      },
      {
        title: "業主總數",
        value: dashboardStats.value.total_clients || 0,
        icon: UsersIcon,
        iconClass: "bg-purple-500"
      },
      {
        title: "已完成專案",
        value: dashboardStats.value.completed_projects || 0,
        icon: CheckCircleIcon,
        iconClass: "bg-green-500"
      }
    ]);
    const formatDate = (dateString) => {
      if (!dateString) return "未設定";
      return new Date(dateString).toLocaleDateString("zh-TW", {
        year: "numeric",
        month: "short",
        day: "numeric"
      });
    };
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4"> 分析概覽 </h2><p class="text-gray-600 dark:text-gray-300"> 詳細的業務分析數據和圖表統計 </p></div>`);
      if (unref(loading)) {
        _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><div class="animate-pulse"><div class="h-4 bg-gray-300 rounded w-1/4 mb-4"></div><div class="space-y-3"><div class="h-4 bg-gray-300 rounded w-full"></div><div class="h-4 bg-gray-300 rounded w-3/4"></div><div class="h-4 bg-gray-300 rounded w-1/2"></div></div></div></div>`);
      } else {
        _push(`<!---->`);
      }
      if (!unref(loading)) {
        _push(`<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"><!--[-->`);
        ssrRenderList(unref(statsCards), (stat) => {
          _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><div class="flex items-center"><div class="flex-shrink-0"><div class="${ssrRenderClass([stat.iconClass, "w-8 h-8 rounded-md flex items-center justify-center"])}">`);
          ssrRenderVNode(_push, createVNode(resolveDynamicComponent(stat.icon), { class: "w-5 h-5 text-white" }, null), _parent);
          _push(`</div></div><div class="ml-5 w-0 flex-1"><dl><dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">${ssrInterpolate(stat.title)}</dt><dd class="text-lg font-medium text-gray-900 dark:text-white">${ssrInterpolate(stat.value)}</dd></dl></div></div></div>`);
        });
        _push(`<!--]--></div>`);
      } else {
        _push(`<!---->`);
      }
      if (!unref(loading)) {
        _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">收入趨勢</h3><div class="h-64 flex items-center justify-center text-gray-500 dark:text-gray-400">${ssrInterpolate(unref(revenueTrend).length > 0 ? `${unref(revenueTrend).length} 個月的數據` : "暫無收入趨勢數據")}</div></div>`);
      } else {
        _push(`<!---->`);
      }
      if (!unref(loading)) {
        _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">近期活動</h3>`);
        if (unref(activities).length > 0) {
          _push(`<div class="space-y-3">`);
          if (_ctx.activity) {
            _push(`<!--[-->`);
            ssrRenderList(unref(activities), (activity) => {
              _push(`<div class="flex items-center space-x-3 py-2"><div class="flex-shrink-0"><div class="w-2 h-2 bg-blue-400 rounded-full"></div></div><div class="flex-1 min-w-0"><p class="text-sm text-gray-900 dark:text-white">${ssrInterpolate((activity == null ? void 0 : activity.description) || "無描述")}</p><p class="text-xs text-gray-500 dark:text-gray-400">${ssrInterpolate(formatDate(activity == null ? void 0 : activity.created_at))}</p></div></div>`);
            });
            _push(`<!--]-->`);
          } else {
            _push(`<!---->`);
          }
          _push(`</div>`);
        } else {
          _push(`<p class="text-gray-500 dark:text-gray-400">暫無近期活動</p>`);
        }
        _push(`</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/dashboard/analytics.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=analytics-CE28hZLu.js.map
