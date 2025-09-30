import { ref, computed, mergeProps, unref, createVNode, resolveDynamicComponent, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrRenderList, ssrRenderVNode, ssrInterpolate } from 'vue/server-renderer';
import { FolderIcon, ClockIcon, ChartBarIcon, CurrencyDollarIcon, UsersIcon } from '@heroicons/vue/24/outline';
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
        name: "\u7E3D\u5C08\u6848\u6578",
        value: dashboardData.value.total_projects || 0,
        icon: "FolderIcon"
      },
      {
        name: "\u7E3D\u6536\u5165",
        value: `NT$${(dashboardData.value.total_revenue || 0).toLocaleString()}`,
        icon: "CurrencyDollarIcon"
      },
      {
        name: "\u9032\u884C\u4E2D\u5C08\u6848",
        value: dashboardData.value.in_progress_projects || 0,
        icon: "ClockIcon"
      },
      {
        name: "\u6D3B\u8E8D\u696D\u4E3B",
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
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4"> \u5C08\u6848\u7BA1\u7406\u5100\u8868\u677F </h2><p class="text-gray-600 dark:text-gray-300"> \u7BA1\u7406\u60A8\u7684\u5C08\u6848\u3001\u696D\u4E3B\u8CC7\u8A0A\uFF0C\u4E26\u8FFD\u8E64\u5C08\u6848\u9032\u5EA6\u548C\u6536\u5165\u7D71\u8A08\u3002 </p></div><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"><!--[-->`);
      ssrRenderList(unref(stats), (stat) => {
        _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><div class="flex items-center"><div class="p-3 rounded-lg bg-primary-100 dark:bg-primary-900">`);
        ssrRenderVNode(_push, createVNode(resolveDynamicComponent(getIcon(stat.icon)), { class: "w-6 h-6 text-primary-600 dark:text-primary-400" }, null), _parent);
        _push(`</div><div class="ml-4"><p class="text-sm font-medium text-gray-600 dark:text-gray-400">${ssrInterpolate(stat.name)}</p><p class="text-2xl font-bold text-gray-900 dark:text-white">${ssrInterpolate(stat.value)}</p></div></div></div>`);
      });
      _push(`<!--]--></div><div class="grid grid-cols-1 lg:grid-cols-2 gap-6"><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"> \u6536\u5165\u8DA8\u52E2 </h3><div class="h-64 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center"><p class="text-gray-500 dark:text-gray-400">\u6708\u6536\u5165\u8DA8\u52E2\u5716\u8868 (\u5F85\u5BE6\u4F5C)</p></div></div><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"> \u5C08\u6848\u52D5\u614B </h3><div class="space-y-4">`);
      if (_ctx.activity) {
        _push(`<!--[-->`);
        ssrRenderList(unref(activities), (activity) => {
          _push(`<div class="flex items-start space-x-3"><div class="w-2 h-2 mt-2 bg-primary-500 rounded-full"></div><div class="flex-1"><p class="text-sm text-gray-900 dark:text-white">${ssrInterpolate((activity == null ? void 0 : activity.description) || "\u7121\u63CF\u8FF0")}</p><p class="text-xs text-gray-500 dark:text-gray-400">${ssrInterpolate((activity == null ? void 0 : activity.time) || "\u672A\u77E5\u6642\u9593")}</p></div></div>`);
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

export { _sfc_main as default };
//# sourceMappingURL=index-_orNPGQA.mjs.map
