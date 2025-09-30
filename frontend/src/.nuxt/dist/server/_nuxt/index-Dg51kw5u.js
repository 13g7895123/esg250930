import { _ as __nuxt_component_0 } from "../server.mjs";
import { ref, computed, watch, mergeProps, withCtx, unref, createVNode, createTextVNode, resolveDynamicComponent, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderAttr, ssrInterpolate, ssrRenderList, ssrIncludeBooleanAttr, ssrRenderVNode } from "vue/server-renderer";
import { PlusIcon, PencilIcon, TrashIcon, UsersIcon, DevicePhoneMobileIcon, ChatBubbleLeftIcon, EnvelopeIcon, PhoneIcon } from "@heroicons/vue/24/outline";
import "C:/Jarvis/17_idea/project_management/frontend/src/node_modules/hookable/dist/index.mjs";
import { u as useClients } from "./useClients-NUI8b-Oz.js";
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
      const _component_NuxtLink = __nuxt_component_0;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="flex justify-between items-center"><div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">業主管理</h1><p class="text-gray-600 dark:text-gray-300">管理所有業主資訊和聯繫方式</p></div>`);
      _push(ssrRenderComponent(_component_NuxtLink, {
        to: "/clients/create",
        class: "inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(unref(PlusIcon), { class: "w-4 h-4 mr-2" }, null, _parent2, _scopeId));
            _push2(` 新增業主 `);
          } else {
            return [
              createVNode(unref(PlusIcon), { class: "w-4 h-4 mr-2" }),
              createTextVNode(" 新增業主 ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><div class="grid grid-cols-1 md:grid-cols-3 gap-4"><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">搜尋業主</label><input${ssrRenderAttr("value", unref(searchQuery))} type="text" placeholder="搜尋業主名稱..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></div><div class="flex items-end"><button class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"> 清除搜尋 </button></div></div></div>`);
      if (unref(loading)) {
        _push(`<div class="text-center py-12"><div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite]" role="status"><span class="!absolute !-m-px !h-px !w-px !overflow-hidden !whitespace-nowrap !border-0 !p-0 ![clip:rect(0,0,0,0)]">載入中...</span></div><p class="mt-2 text-gray-500 dark:text-gray-400">正在載入業主資料...</p></div>`);
      } else if (unref(error)) {
        _push(`<div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700 rounded-md p-4"><div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg></div><div class="ml-3"><p class="text-sm font-medium text-red-800 dark:text-red-200">${ssrInterpolate(unref(error))}</p><button class="mt-2 text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 underline"> 重新載入 </button></div></div></div>`);
      } else {
        _push(`<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">`);
        if (_ctx.client) {
          _push(`<!--[-->`);
          ssrRenderList(unref(filteredClients), (client) => {
            _push(`<div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6 hover:shadow-md transition-shadow duration-200"><div class="flex justify-between items-start mb-4"><div><h3 class="text-lg font-semibold text-gray-900 dark:text-white">${ssrInterpolate((client == null ? void 0 : client.name) || "未知業主")}</h3><p class="text-sm text-gray-500 dark:text-gray-400"> 認識於 ${ssrInterpolate((client == null ? void 0 : client.how_we_met) || "未記錄")}</p></div><div class="flex space-x-2"><button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 p-1">`);
            _push(ssrRenderComponent(unref(PencilIcon), { class: "w-4 h-4" }, null, _parent));
            _push(`</button><button class="text-red-600 hover:text-red-900 dark:text-red-400 p-1"${ssrIncludeBooleanAttr(!(client == null ? void 0 : client.id)) ? " disabled" : ""}>`);
            _push(ssrRenderComponent(unref(TrashIcon), { class: "w-4 h-4" }, null, _parent));
            _push(`</button></div></div><div class="space-y-2 mb-4"><h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">聯繫方式</h4><div class="space-y-1">`);
            if (_ctx.contact) {
              _push(`<!--[-->`);
              ssrRenderList((client == null ? void 0 : client.contacts) || [], (contact) => {
                _push(`<div class="flex items-center space-x-2 text-sm">`);
                ssrRenderVNode(_push, createVNode(resolveDynamicComponent(getContactIcon(contact == null ? void 0 : contact.type)), { class: "w-4 h-4 text-gray-400" }, null), _parent);
                _push(`<span class="text-gray-600 dark:text-gray-300">${ssrInterpolate((contact == null ? void 0 : contact.type) || "未知")}:</span><span class="text-gray-900 dark:text-white">${ssrInterpolate((contact == null ? void 0 : contact.value) || "未填寫")}</span>`);
                if (contact == null ? void 0 : contact.is_primary) {
                  _push(`<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200"> 主要 </span>`);
                } else {
                  _push(`<!---->`);
                }
                _push(`</div>`);
              });
              _push(`<!--]-->`);
            } else {
              _push(`<!---->`);
            }
            _push(`</div></div><div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700"><span class="text-sm text-gray-500 dark:text-gray-400"> 相關專案: ${ssrInterpolate((client == null ? void 0 : client.projects_count) || 0)} 個 </span><button class="text-sm text-primary-600 hover:text-primary-900 dark:text-primary-400"> 查看專案 </button></div>`);
            if (client == null ? void 0 : client.notes) {
              _push(`<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">備註</h4><p class="text-sm text-gray-600 dark:text-gray-300">${ssrInterpolate(client.notes)}</p></div>`);
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
        _push(`<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">${ssrInterpolate(unref(searchQuery) ? "找不到符合條件的業主" : "尚未新增任何業主")}</h3><p class="text-gray-500 dark:text-gray-400">${ssrInterpolate(unref(searchQuery) ? "請嘗試其他搜尋條件" : "開始建立您的第一個業主資料")}</p></div>`);
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
export {
  _sfc_main as default
};
//# sourceMappingURL=index-Dg51kw5u.js.map
