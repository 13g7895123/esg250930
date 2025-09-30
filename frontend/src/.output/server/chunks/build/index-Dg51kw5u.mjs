import { _ as __nuxt_component_0$2 } from './server.mjs';
import { ref, computed, watch, mergeProps, withCtx, unref, createVNode, createTextVNode, resolveDynamicComponent, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderAttr, ssrInterpolate, ssrRenderList, ssrIncludeBooleanAttr, ssrRenderVNode } from 'vue/server-renderer';
import { PlusIcon, PencilIcon, TrashIcon, UsersIcon, DevicePhoneMobileIcon, ChatBubbleLeftIcon, EnvelopeIcon, PhoneIcon } from '@heroicons/vue/24/outline';
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
  __name: "index",
  __ssrInlineRender: true,
  setup(__props) {
    useClients();
    const clients = ref([]);
    const searchQuery = ref("");
    const loading = ref(false);
    const error = ref(null);
    const filteredClients = computed(() => {
      if (!searchQuery.value) return clients.value;
      return clients.value.filter(
        (client) => client.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || client.how_we_met.toLowerCase().includes(searchQuery.value.toLowerCase()) || client.notes && client.notes.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });
    const getContactIcon = (type) => {
      const icons = {
        phone: PhoneIcon,
        email: EnvelopeIcon,
        line: ChatBubbleLeftIcon,
        wechat: ChatBubbleLeftIcon,
        telegram: ChatBubbleLeftIcon,
        mobile: DevicePhoneMobileIcon
      };
      return icons[type] || PhoneIcon;
    };
    watch(searchQuery, () => {
    });
    return (_ctx, _push, _parent, _attrs) => {
      const _component_NuxtLink = __nuxt_component_0$2;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="flex justify-between items-center"><div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">\u696D\u4E3B\u7BA1\u7406</h1><p class="text-gray-600 dark:text-gray-300">\u7BA1\u7406\u6240\u6709\u696D\u4E3B\u8CC7\u8A0A\u548C\u806F\u7E6B\u65B9\u5F0F</p></div>`);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/clients/create",
        class: "inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(unref(PlusIcon), { class: "w-4 h-4 mr-2" }, null, _parent2, _scopeId));
            _push2(` \u65B0\u589E\u696D\u4E3B `);
          } else {
            return [
              createVNode(unref(PlusIcon), { class: "w-4 h-4 mr-2" }),
              createTextVNode(" \u65B0\u589E\u696D\u4E3B ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><div class="grid grid-cols-1 md:grid-cols-3 gap-4"><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">\u641C\u5C0B\u696D\u4E3B</label><input${ssrRenderAttr("value", unref(searchQuery))} type="text" placeholder="\u641C\u5C0B\u696D\u4E3B\u540D\u7A31..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></div><div class="flex items-end"><button class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"> \u6E05\u9664\u641C\u5C0B </button></div></div></div>`);
      if (unref(loading)) {
        _push(`<div class="text-center py-12"><div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite]" role="status"><span class="!absolute !-m-px !h-px !w-px !overflow-hidden !whitespace-nowrap !border-0 !p-0 ![clip:rect(0,0,0,0)]">\u8F09\u5165\u4E2D...</span></div><p class="mt-2 text-gray-500 dark:text-gray-400">\u6B63\u5728\u8F09\u5165\u696D\u4E3B\u8CC7\u6599...</p></div>`);
      } else if (unref(error)) {
        _push(`<div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700 rounded-md p-4"><div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg></div><div class="ml-3"><p class="text-sm font-medium text-red-800 dark:text-red-200">${ssrInterpolate(unref(error))}</p><button class="mt-2 text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 underline"> \u91CD\u65B0\u8F09\u5165 </button></div></div></div>`);
      } else {
        _push(`<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">`);
        if (_ctx.client) {
          _push(`<!--[-->`);
          ssrRenderList(unref(filteredClients), (client) => {
            _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6 hover:shadow-md transition-shadow duration-200"><div class="flex justify-between items-start mb-4"><div><h3 class="text-lg font-semibold text-gray-900 dark:text-white">${ssrInterpolate((client == null ? void 0 : client.name) || "\u672A\u77E5\u696D\u4E3B")}</h3><p class="text-sm text-gray-500 dark:text-gray-400"> \u8A8D\u8B58\u65BC ${ssrInterpolate((client == null ? void 0 : client.how_we_met) || "\u672A\u8A18\u9304")}</p></div><div class="flex space-x-2"><button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 p-1">`);
            _push(ssrRenderComponent(unref(PencilIcon), { class: "w-4 h-4" }, null, _parent));
            _push(`</button><button class="text-red-600 hover:text-red-900 dark:text-red-400 p-1"${ssrIncludeBooleanAttr(!(client == null ? void 0 : client.id)) ? " disabled" : ""}>`);
            _push(ssrRenderComponent(unref(TrashIcon), { class: "w-4 h-4" }, null, _parent));
            _push(`</button></div></div><div class="space-y-2 mb-4"><h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">\u806F\u7E6B\u65B9\u5F0F</h4><div class="space-y-1">`);
            if (_ctx.contact) {
              _push(`<!--[-->`);
              ssrRenderList((client == null ? void 0 : client.contacts) || [], (contact) => {
                _push(`<div class="flex items-center space-x-2 text-sm">`);
                ssrRenderVNode(_push, createVNode(resolveDynamicComponent(getContactIcon(contact == null ? void 0 : contact.type)), { class: "w-4 h-4 text-gray-400" }, null), _parent);
                _push(`<span class="text-gray-600 dark:text-gray-300">${ssrInterpolate((contact == null ? void 0 : contact.type) || "\u672A\u77E5")}:</span><span class="text-gray-900 dark:text-white">${ssrInterpolate((contact == null ? void 0 : contact.value) || "\u672A\u586B\u5BEB")}</span>`);
                if (contact == null ? void 0 : contact.is_primary) {
                  _push(`<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200"> \u4E3B\u8981 </span>`);
                } else {
                  _push(`<!---->`);
                }
                _push(`</div>`);
              });
              _push(`<!--]-->`);
            } else {
              _push(`<!---->`);
            }
            _push(`</div></div><div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700"><span class="text-sm text-gray-500 dark:text-gray-400"> \u76F8\u95DC\u5C08\u6848: ${ssrInterpolate((client == null ? void 0 : client.projects_count) || 0)} \u500B </span><button class="text-sm text-primary-600 hover:text-primary-900 dark:text-primary-400"> \u67E5\u770B\u5C08\u6848 </button></div>`);
            if (client == null ? void 0 : client.notes) {
              _push(`<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">\u5099\u8A3B</h4><p class="text-sm text-gray-600 dark:text-gray-300">${ssrInterpolate(client.notes)}</p></div>`);
            } else {
              _push(`<!---->`);
            }
            _push(`</div>`);
          });
          _push(`<!--]-->`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div>`);
      }
      if (!unref(loading) && !unref(error) && unref(filteredClients).length === 0) {
        _push(`<div class="text-center py-12">`);
        _push(ssrRenderComponent(unref(UsersIcon), { class: "mx-auto h-12 w-12 text-gray-400 mb-4" }, null, _parent));
        _push(`<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">${ssrInterpolate(unref(searchQuery) ? "\u627E\u4E0D\u5230\u7B26\u5408\u689D\u4EF6\u7684\u696D\u4E3B" : "\u5C1A\u672A\u65B0\u589E\u4EFB\u4F55\u696D\u4E3B")}</h3><p class="text-gray-500 dark:text-gray-400">${ssrInterpolate(unref(searchQuery) ? "\u8ACB\u5617\u8A66\u5176\u4ED6\u641C\u5C0B\u689D\u4EF6" : "\u958B\u59CB\u5EFA\u7ACB\u60A8\u7684\u7B2C\u4E00\u500B\u696D\u4E3B\u8CC7\u6599")}</p></div>`);
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/clients/index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};

export { _sfc_main as default };
//# sourceMappingURL=index-Dg51kw5u.mjs.map
