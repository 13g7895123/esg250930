import { _ as __nuxt_component_0 } from "../server.mjs";
import { ref, computed, watch, mergeProps, withCtx, unref, createVNode, createTextVNode, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderAttr, ssrIncludeBooleanAttr, ssrLooseContain, ssrLooseEqual, ssrRenderList, ssrInterpolate, ssrRenderClass } from "vue/server-renderer";
import { PlusIcon, PencilIcon, TrashIcon } from "@heroicons/vue/24/outline";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/hookable/dist/index.mjs";
import { u as useProjects } from "./useProjects-BpvOU6mv.js";
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
    useProjects();
    const projects = ref([]);
    const searchQuery = ref("");
    const filterCategory = ref("");
    const filterStatus = ref("");
    ref(false);
    ref(null);
    const filteredProjects = computed(() => {
      return projects.value.filter((project) => {
        const matchesSearch = project.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || project.description.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = !filterCategory.value || project.category === filterCategory.value;
        const matchesStatus = !filterStatus.value || project.status === filterStatus.value;
        return matchesSearch && matchesCategory && matchesStatus;
      });
    });
    const getCategoryLabel = (category) => {
      const labels = {
        website: "網站",
        script: "腳本",
        server: "伺服器",
        custom: "自訂"
      };
      return labels[category] || category;
    };
    const getCategoryClass = (category) => {
      const classes = {
        website: "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
        script: "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
        server: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200",
        custom: "bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200"
      };
      return classes[category] || "bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200";
    };
    const getStatusLabel = (status) => {
      const labels = {
        contacted: "已接洽",
        in_progress: "進行中",
        completed: "已完成",
        paid: "已收款"
      };
      return labels[status] || status;
    };
    const getStatusClass = (status) => {
      const classes = {
        contacted: "bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200",
        in_progress: "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
        completed: "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
        paid: "bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200"
      };
      return classes[status] || "bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200";
    };
    const formatDate = (dateString) => {
      if (!dateString) return "未設定";
      return new Date(dateString).toLocaleDateString("zh-TW");
    };
    watch([searchQuery, filterCategory, filterStatus], () => {
    });
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLink = __nuxt_component_0;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="flex justify-between items-center"><div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">專案管理</h1><p class="text-gray-600 dark:text-gray-300">管理所有專案資訊</p></div>`);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/projects/create",
        class: "inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(unref(PlusIcon), { class: "w-4 h-4 mr-2" }, null, _parent2, _scopeId));
            _push2(` 新增專案 `);
          } else {
            return [
              createVNode(unref(PlusIcon), { class: "w-4 h-4 mr-2" }),
              createTextVNode(" 新增專案 ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><div class="grid grid-cols-1 md:grid-cols-4 gap-4"><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">搜尋專案</label><input${ssrRenderAttr("value", unref(searchQuery))} type="text" placeholder="搜尋專案名稱..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">專案類別</label><select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"><option value=""${ssrIncludeBooleanAttr(Array.isArray(unref(filterCategory)) ? ssrLooseContain(unref(filterCategory), "") : ssrLooseEqual(unref(filterCategory), "")) ? " selected" : ""}>全部類別</option><option value="website"${ssrIncludeBooleanAttr(Array.isArray(unref(filterCategory)) ? ssrLooseContain(unref(filterCategory), "website") : ssrLooseEqual(unref(filterCategory), "website")) ? " selected" : ""}>網站</option><option value="script"${ssrIncludeBooleanAttr(Array.isArray(unref(filterCategory)) ? ssrLooseContain(unref(filterCategory), "script") : ssrLooseEqual(unref(filterCategory), "script")) ? " selected" : ""}>腳本</option><option value="server"${ssrIncludeBooleanAttr(Array.isArray(unref(filterCategory)) ? ssrLooseContain(unref(filterCategory), "server") : ssrLooseEqual(unref(filterCategory), "server")) ? " selected" : ""}>伺服器</option><option value="custom"${ssrIncludeBooleanAttr(Array.isArray(unref(filterCategory)) ? ssrLooseContain(unref(filterCategory), "custom") : ssrLooseEqual(unref(filterCategory), "custom")) ? " selected" : ""}>自訂</option></select></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">專案狀態</label><select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"><option value=""${ssrIncludeBooleanAttr(Array.isArray(unref(filterStatus)) ? ssrLooseContain(unref(filterStatus), "") : ssrLooseEqual(unref(filterStatus), "")) ? " selected" : ""}>全部狀態</option><option value="contacted"${ssrIncludeBooleanAttr(Array.isArray(unref(filterStatus)) ? ssrLooseContain(unref(filterStatus), "contacted") : ssrLooseEqual(unref(filterStatus), "contacted")) ? " selected" : ""}>已接洽</option><option value="in_progress"${ssrIncludeBooleanAttr(Array.isArray(unref(filterStatus)) ? ssrLooseContain(unref(filterStatus), "in_progress") : ssrLooseEqual(unref(filterStatus), "in_progress")) ? " selected" : ""}>進行中</option><option value="completed"${ssrIncludeBooleanAttr(Array.isArray(unref(filterStatus)) ? ssrLooseContain(unref(filterStatus), "completed") : ssrLooseEqual(unref(filterStatus), "completed")) ? " selected" : ""}>已完成</option><option value="paid"${ssrIncludeBooleanAttr(Array.isArray(unref(filterStatus)) ? ssrLooseContain(unref(filterStatus), "paid") : ssrLooseEqual(unref(filterStatus), "paid")) ? " selected" : ""}>已收款</option></select></div><div class="flex items-end"><button class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"> 清除篩選 </button></div></div></div><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm overflow-hidden"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"> 專案名稱 </th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"> 業主 </th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"> 類別 </th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"> 金額 </th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"> 狀態 </th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"> 接洽日期 </th><th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"> 操作 </th></tr></thead><tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">`);
      if (_ctx.project) {
        _push(`<!--[-->`);
        ssrRenderList(unref(filteredProjects), (project) => {
          _push(`<tr class="hover:bg-gray-50 dark:hover:bg-gray-700"><td class="px-6 py-4 whitespace-nowrap"><div><div class="text-sm font-medium text-gray-900 dark:text-white">${ssrInterpolate((project == null ? void 0 : project.name) || "未知專案")}</div><div class="text-sm text-gray-500 dark:text-gray-400">${ssrInterpolate((project == null ? void 0 : project.description) || "無描述")}</div></div></td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${ssrInterpolate((project == null ? void 0 : project.client) || "未知業主")}</td><td class="px-6 py-4 whitespace-nowrap"><span class="${ssrRenderClass([getCategoryClass(project == null ? void 0 : project.category), "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"])}">${ssrInterpolate(getCategoryLabel(project == null ? void 0 : project.category))}</span></td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"> NT$${ssrInterpolate(((project == null ? void 0 : project.amount) || 0).toLocaleString())}</td><td class="px-6 py-4 whitespace-nowrap"><span class="${ssrRenderClass([getStatusClass(project == null ? void 0 : project.status), "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"])}">${ssrInterpolate(getStatusLabel(project == null ? void 0 : project.status))}</span></td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${ssrInterpolate(formatDate(project == null ? void 0 : project.contact_date))}</td><td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><div class="flex justify-end space-x-2"><button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">`);
          _push(ssrRenderComponent(unref(PencilIcon), { class: "w-4 h-4" }, null, _parent));
          _push(`</button><button class="text-red-600 hover:text-red-900 dark:text-red-400"${ssrIncludeBooleanAttr(!(project == null ? void 0 : project.id)) ? " disabled" : ""}>`);
          _push(ssrRenderComponent(unref(TrashIcon), { class: "w-4 h-4" }, null, _parent));
          _push(`</button></div></td></tr>`);
        });
        _push(`<!--]-->`);
      } else {
        _push(`<!---->`);
      }
      _push(`</tbody></table></div></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/projects/index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
//# sourceMappingURL=index-D-SR6kuM.js.map
