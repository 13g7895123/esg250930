import { ref, computed, mergeProps, unref, createVNode, resolveDynamicComponent, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrRenderList, ssrRenderClass, ssrRenderVNode, ssrInterpolate } from 'vue/server-renderer';
import { CurrencyDollarIcon, BriefcaseIcon, UsersIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import { u as useDashboard } from './useDashboard-BIyzGZF7.mjs';
import './server.mjs';
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
        title: "\u7E3D\u6536\u5165",
        value: `NT$${(dashboardStats.value.total_revenue || 0).toLocaleString()}`,
        icon: CurrencyDollarIcon,
        iconClass: "bg-green-500"
      },
      {
        title: "\u5C08\u6848\u7E3D\u6578",
        value: dashboardStats.value.total_projects || 0,
        icon: BriefcaseIcon,
        iconClass: "bg-blue-500"
      },
      {
        title: "\u696D\u4E3B\u7E3D\u6578",
        value: dashboardStats.value.total_clients || 0,
        icon: UsersIcon,
        iconClass: "bg-purple-500"
      },
      {
        title: "\u5DF2\u5B8C\u6210\u5C08\u6848",
        value: dashboardStats.value.completed_projects || 0,
        icon: CheckCircleIcon,
        iconClass: "bg-green-500"
      }
    ]);
    const formatDate = (dateString) => {
      if (!dateString) return "\u672A\u8A2D\u5B9A";
      return new Date(dateString).toLocaleDateString("zh-TW", {
        year: "numeric",
        month: "short",
        day: "numeric"
      });
    };
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4"> \u5206\u6790\u6982\u89BD </h2><p class="text-gray-600 dark:text-gray-300"> \u8A73\u7D30\u7684\u696D\u52D9\u5206\u6790\u6578\u64DA\u548C\u5716\u8868\u7D71\u8A08 </p></div>`);
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
        _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">\u6536\u5165\u8DA8\u52E2</h3><div class="h-64 flex items-center justify-center text-gray-500 dark:text-gray-400">${ssrInterpolate(unref(revenueTrend).length > 0 ? `${unref(revenueTrend).length} \u500B\u6708\u7684\u6578\u64DA` : "\u66AB\u7121\u6536\u5165\u8DA8\u52E2\u6578\u64DA")}</div></div>`);
      } else {
        _push(`<!---->`);
      }
      if (!unref(loading)) {
        _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">\u8FD1\u671F\u6D3B\u52D5</h3>`);
        if (unref(activities).length > 0) {
          _push(`<div class="space-y-3">`);
          if (_ctx.activity) {
            _push(`<!--[-->`);
            ssrRenderList(unref(activities), (activity) => {
              _push(`<div class="flex items-center space-x-3 py-2"><div class="flex-shrink-0"><div class="w-2 h-2 bg-blue-400 rounded-full"></div></div><div class="flex-1 min-w-0"><p class="text-sm text-gray-900 dark:text-white">${ssrInterpolate((activity == null ? void 0 : activity.description) || "\u7121\u63CF\u8FF0")}</p><p class="text-xs text-gray-500 dark:text-gray-400">${ssrInterpolate(formatDate(activity == null ? void 0 : activity.created_at))}</p></div></div>`);
            });
            _push(`<!--]-->`);
          } else {
            _push(`<!---->`);
          }
          _push(`</div>`);
        } else {
          _push(`<p class="text-gray-500 dark:text-gray-400">\u66AB\u7121\u8FD1\u671F\u6D3B\u52D5</p>`);
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

export { _sfc_main as default };
//# sourceMappingURL=analytics-CE28hZLu.mjs.map
