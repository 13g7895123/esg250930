import { _ as __nuxt_component_0$2 } from './server.mjs';
import { ref, mergeProps, unref, withCtx, createTextVNode, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderAttr, ssrIncludeBooleanAttr, ssrLooseContain, ssrLooseEqual, ssrRenderList } from 'vue/server-renderer';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { u as useProjects } from './useProjects-BpvOU6mv.mjs';
import { u as useClients } from './useClients-NUI8b-Oz.mjs';
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
  __name: "create",
  __ssrInlineRender: true,
  setup(__props) {
    useProjects();
    useClients();
    const clients = ref([]);
    const loadingClients = ref(false);
    const clientsError = ref("");
    const form = ref({
      name: "",
      client_id: "",
      description: "",
      category: "",
      amount: null,
      contact_date: "",
      start_date: "",
      completion_date: "",
      payment_date: "",
      status: "contacted"
    });
    const isSubmitting = ref(false);
    const errorMessage = ref("");
    const successMessage = ref("");
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLink = __nuxt_component_0$2;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="flex items-center justify-between"><div class="flex items-center space-x-4"><button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">`);
      _push(ssrRenderComponent(unref(ArrowLeftIcon), { class: "w-5 h-5" }, null, _parent));
      _push(`</button><div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">\u65B0\u589E\u5C08\u6848</h1><p class="text-gray-600 dark:text-gray-300">\u5EFA\u7ACB\u65B0\u7684\u5C08\u6848\u8A18\u9304</p></div></div><button type="button" class="px-4 py-2 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 rounded-md text-sm font-medium transition-colors duration-200 flex items-center space-x-2" title="\u586B\u5165\u6E2C\u8A66\u8CC7\u6599"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg><span>\u586B\u5165\u5047\u8CC7\u6599</span></button></div>`);
      if (unref(loadingClients)) {
        _push(`<div class="bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-700 rounded-md p-4"><div class="flex"><div class="flex-shrink-0"><div class="h-5 w-5 animate-spin rounded-full border-2 border-solid border-current border-r-transparent"></div></div><div class="ml-3"><p class="text-sm font-medium text-blue-800 dark:text-blue-200">\u6B63\u5728\u8F09\u5165\u696D\u4E3B\u5217\u8868...</p></div></div></div>`);
      } else {
        _push(`<!---->`);
      }
      if (unref(successMessage)) {
        _push(`<div class="bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-700 rounded-md p-4"><div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg></div><div class="ml-3"><p class="text-sm font-medium text-green-800 dark:text-green-200">${ssrInterpolate(unref(successMessage))}</p></div></div></div>`);
      } else {
        _push(`<!---->`);
      }
      if (unref(errorMessage)) {
        _push(`<div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700 rounded-md p-4"><div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg></div><div class="ml-3"><p class="text-sm font-medium text-red-800 dark:text-red-200">${ssrInterpolate(unref(errorMessage))}</p></div></div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm"><form class="p-6 space-y-6"><div><h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">\u57FA\u672C\u8CC7\u8A0A</h3><div class="grid grid-cols-1 md:grid-cols-2 gap-6"><div><label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u5C08\u6848\u540D\u7A31 <span class="text-red-500">*</span></label><input id="name"${ssrRenderAttr("value", unref(form).name)} type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white" placeholder="\u8ACB\u8F38\u5165\u5C08\u6848\u540D\u7A31"></div><div><label for="client" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u696D\u4E3B <span class="text-red-500">*</span></label><div class="relative"><select id="client" required${ssrIncludeBooleanAttr(unref(loadingClients)) ? " disabled" : ""} class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"><option value="" disabled${ssrIncludeBooleanAttr(Array.isArray(unref(form).client_id) ? ssrLooseContain(unref(form).client_id, "") : ssrLooseEqual(unref(form).client_id, "")) ? " selected" : ""}>${ssrInterpolate(unref(loadingClients) ? "\u8F09\u5165\u4E2D..." : "\u8ACB\u9078\u64C7\u696D\u4E3B")}</option><!--[-->`);
      ssrRenderList(unref(clients), (client) => {
        _push(`<option${ssrRenderAttr("value", client == null ? void 0 : client.id)}${ssrIncludeBooleanAttr(Array.isArray(unref(form).client_id) ? ssrLooseContain(unref(form).client_id, client == null ? void 0 : client.id) : ssrLooseEqual(unref(form).client_id, client == null ? void 0 : client.id)) ? " selected" : ""}>${ssrInterpolate((client == null ? void 0 : client.name) || "\u672A\u77E5\u696D\u4E3B")}</option>`);
      });
      _push(`<!--]--></select>`);
      if (unref(loadingClients)) {
        _push(`<div class="absolute right-3 top-1/2 transform -translate-y-1/2"><div class="h-4 w-4 animate-spin rounded-full border-2 border-solid border-current border-r-transparent" role="status"><span class="sr-only">\u8F09\u5165\u4E2D...</span></div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
      if (unref(clientsError)) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(clientsError))} <button class="ml-2 underline hover:no-underline"> \u91CD\u65B0\u8F09\u5165 </button></p>`);
      } else if (!unref(loadingClients) && unref(clients).length === 0) {
        _push(`<p class="mt-1 text-sm text-yellow-600 dark:text-yellow-400"> \u5C1A\u7121\u696D\u4E3B\u8CC7\u6599\uFF0C\u8ACB\u5148 `);
        _push(ssrRenderComponent(_component_NuxtLink, {
          to: "/clients/create",
          class: "underline hover:no-underline"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(` \u65B0\u589E\u696D\u4E3B `);
            } else {
              return [
                createTextVNode(" \u65B0\u589E\u696D\u4E3B ")
              ];
            }
          }),
          _: 1
        }, _parent));
        _push(`</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div></div><div><label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u5C08\u6848\u63CF\u8FF0 </label><textarea id="description" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white" placeholder="\u8ACB\u63CF\u8FF0\u5C08\u6848\u5167\u5BB9...">${ssrInterpolate(unref(form).description)}</textarea></div><div class="grid grid-cols-1 md:grid-cols-2 gap-6"><div><label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u5C08\u6848\u985E\u5225 <span class="text-red-500">*</span></label><select id="category" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"><option value=""${ssrIncludeBooleanAttr(Array.isArray(unref(form).category) ? ssrLooseContain(unref(form).category, "") : ssrLooseEqual(unref(form).category, "")) ? " selected" : ""}>\u8ACB\u9078\u64C7\u985E\u5225</option><option value="website"${ssrIncludeBooleanAttr(Array.isArray(unref(form).category) ? ssrLooseContain(unref(form).category, "website") : ssrLooseEqual(unref(form).category, "website")) ? " selected" : ""}>\u7DB2\u7AD9</option><option value="script"${ssrIncludeBooleanAttr(Array.isArray(unref(form).category) ? ssrLooseContain(unref(form).category, "script") : ssrLooseEqual(unref(form).category, "script")) ? " selected" : ""}>\u8173\u672C</option><option value="server"${ssrIncludeBooleanAttr(Array.isArray(unref(form).category) ? ssrLooseContain(unref(form).category, "server") : ssrLooseEqual(unref(form).category, "server")) ? " selected" : ""}>\u4F3A\u670D\u5668</option><option value="custom"${ssrIncludeBooleanAttr(Array.isArray(unref(form).category) ? ssrLooseContain(unref(form).category, "custom") : ssrLooseEqual(unref(form).category, "custom")) ? " selected" : ""}>\u81EA\u8A02</option></select></div><div><label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u5C08\u6848\u91D1\u984D <span class="text-red-500">*</span></label><div class="relative"><span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400"> NT$ </span><input id="amount"${ssrRenderAttr("value", unref(form).amount)} type="number" min="0" step="1" required class="w-full pl-12 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white" placeholder="0"></div></div></div><div><h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">\u65E5\u671F\u8CC7\u8A0A</h3><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"><div><label for="contact_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u63A5\u6D3D\u65E5\u671F <span class="text-red-500">*</span></label><input id="contact_date"${ssrRenderAttr("value", unref(form).contact_date)} type="date" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></div><div><label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u958B\u59CB\u57F7\u884C\u65E5\u671F </label><input id="start_date"${ssrRenderAttr("value", unref(form).start_date)} type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></div><div><label for="completion_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u5B8C\u6210\u65E5\u671F </label><input id="completion_date"${ssrRenderAttr("value", unref(form).completion_date)} type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></div><div><label for="payment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u6536\u6B3E\u65E5\u671F </label><input id="payment_date"${ssrRenderAttr("value", unref(form).payment_date)} type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></div></div></div><div><label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u5C08\u6848\u72C0\u614B </label><select id="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"><option value="contacted"${ssrIncludeBooleanAttr(Array.isArray(unref(form).status) ? ssrLooseContain(unref(form).status, "contacted") : ssrLooseEqual(unref(form).status, "contacted")) ? " selected" : ""}>\u5DF2\u63A5\u6D3D</option><option value="in_progress"${ssrIncludeBooleanAttr(Array.isArray(unref(form).status) ? ssrLooseContain(unref(form).status, "in_progress") : ssrLooseEqual(unref(form).status, "in_progress")) ? " selected" : ""}>\u9032\u884C\u4E2D</option><option value="completed"${ssrIncludeBooleanAttr(Array.isArray(unref(form).status) ? ssrLooseContain(unref(form).status, "completed") : ssrLooseEqual(unref(form).status, "completed")) ? " selected" : ""}>\u5DF2\u5B8C\u6210</option><option value="paid"${ssrIncludeBooleanAttr(Array.isArray(unref(form).status) ? ssrLooseContain(unref(form).status, "paid") : ssrLooseEqual(unref(form).status, "paid")) ? " selected" : ""}>\u5DF2\u6536\u6B3E</option></select></div><div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700"><button type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"> \u53D6\u6D88 </button><button type="submit"${ssrIncludeBooleanAttr(unref(isSubmitting)) ? " disabled" : ""} class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">`);
      if (unref(isSubmitting)) {
        _push(`<span>\u5132\u5B58\u4E2D...</span>`);
      } else {
        _push(`<span>\u5132\u5B58\u5C08\u6848</span>`);
      }
      _push(`</button></div></form></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/projects/create.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};

export { _sfc_main as default };
//# sourceMappingURL=create-CfYn_zZx.mjs.map
