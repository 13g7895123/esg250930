import { reactive, ref, mergeProps, unref, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderAttr, ssrInterpolate, ssrRenderList, ssrIncludeBooleanAttr, ssrLooseContain, ssrLooseEqual } from 'vue/server-renderer';
import { ArrowLeftIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { u as useClients } from './useClients-NUI8b-Oz.mjs';
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
  __name: "create",
  __ssrInlineRender: true,
  setup(__props) {
    const form = reactive({
      name: "",
      how_we_met: "",
      notes: "",
      contacts: []
    });
    const isSubmitting = ref(false);
    const getContactPlaceholder = (type) => {
      const placeholders = {
        phone: "02-1234-5678",
        mobile: "0912-345-678",
        email: "example@email.com",
        line: "LINE ID",
        wechat: "WeChat ID",
        telegram: "Telegram ID",
        other: "\u5176\u4ED6\u806F\u7E6B\u8CC7\u8A0A"
      };
      return placeholders[type] || "\u8ACB\u8F38\u5165\u806F\u7E6B\u8CC7\u8A0A";
    };
    useClients();
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="flex items-center space-x-4"><button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">`);
      _push(ssrRenderComponent(unref(ArrowLeftIcon), { class: "w-5 h-5" }, null, _parent));
      _push(`</button><div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">\u65B0\u589E\u696D\u4E3B</h1><p class="text-gray-600 dark:text-gray-300">\u5EFA\u7ACB\u65B0\u7684\u696D\u4E3B\u8CC7\u6599</p></div></div><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm"><form class="p-6 space-y-6"><div><h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">\u57FA\u672C\u8CC7\u8A0A</h3><div class="grid grid-cols-1 md:grid-cols-2 gap-6"><div><label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u696D\u4E3B\u7A31\u547C <span class="text-red-500">*</span></label><input id="name"${ssrRenderAttr("value", unref(form).name)} type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white" placeholder="\u8ACB\u8F38\u5165\u696D\u4E3B\u7A31\u547C"></div><div><label for="how_we_met" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u8A8D\u8B58\u65B9\u5F0F </label><input id="how_we_met"${ssrRenderAttr("value", unref(form).how_we_met)} type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white" placeholder="\u4F8B\u5982\uFF1A\u670B\u53CB\u4ECB\u7D39\u3001\u7DB2\u8DEF\u63A5\u6D3D..."></div></div></div><div><label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u5099\u8A3B </label><textarea id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white" placeholder="\u95DC\u65BC\u9019\u500B\u696D\u4E3B\u7684\u5176\u4ED6\u8CC7\u8A0A...">${ssrInterpolate(unref(form).notes)}</textarea></div><div><div class="flex justify-between items-center mb-4"><h3 class="text-lg font-medium text-gray-900 dark:text-white">\u806F\u7E6B\u65B9\u5F0F</h3><button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-900 dark:text-primary-200">`);
      _push(ssrRenderComponent(unref(PlusIcon), { class: "w-4 h-4 mr-1" }, null, _parent));
      _push(` \u65B0\u589E\u806F\u7E6B\u65B9\u5F0F </button></div><div class="space-y-4"><!--[-->`);
      ssrRenderList(unref(form).contacts, (contact, index) => {
        _push(`<div class="flex items-end space-x-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg"><div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4"><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u806F\u7E6B\u65B9\u5F0F\u985E\u578B </label><select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"><option value="phone"${ssrIncludeBooleanAttr(Array.isArray(contact.type) ? ssrLooseContain(contact.type, "phone") : ssrLooseEqual(contact.type, "phone")) ? " selected" : ""}>\u96FB\u8A71</option><option value="mobile"${ssrIncludeBooleanAttr(Array.isArray(contact.type) ? ssrLooseContain(contact.type, "mobile") : ssrLooseEqual(contact.type, "mobile")) ? " selected" : ""}>\u624B\u6A5F</option><option value="email"${ssrIncludeBooleanAttr(Array.isArray(contact.type) ? ssrLooseContain(contact.type, "email") : ssrLooseEqual(contact.type, "email")) ? " selected" : ""}>Email</option><option value="line"${ssrIncludeBooleanAttr(Array.isArray(contact.type) ? ssrLooseContain(contact.type, "line") : ssrLooseEqual(contact.type, "line")) ? " selected" : ""}>LINE</option><option value="wechat"${ssrIncludeBooleanAttr(Array.isArray(contact.type) ? ssrLooseContain(contact.type, "wechat") : ssrLooseEqual(contact.type, "wechat")) ? " selected" : ""}>WeChat</option><option value="telegram"${ssrIncludeBooleanAttr(Array.isArray(contact.type) ? ssrLooseContain(contact.type, "telegram") : ssrLooseEqual(contact.type, "telegram")) ? " selected" : ""}>Telegram</option><option value="other"${ssrIncludeBooleanAttr(Array.isArray(contact.type) ? ssrLooseContain(contact.type, "other") : ssrLooseEqual(contact.type, "other")) ? " selected" : ""}>\u5176\u4ED6</option></select></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u806F\u7E6B\u8CC7\u8A0A </label><input${ssrRenderAttr("value", contact.value)} type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"${ssrRenderAttr("placeholder", getContactPlaceholder(contact.type))}></div><div class="flex items-center"><label class="flex items-center"><input${ssrIncludeBooleanAttr(Array.isArray(contact.is_primary) ? ssrLooseContain(contact.is_primary, null) : contact.is_primary) ? " checked" : ""} type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"><span class="ml-2 text-sm text-gray-700 dark:text-gray-300">\u8A2D\u70BA\u4E3B\u8981</span></label></div></div><button type="button" class="p-2 text-red-600 hover:text-red-900 dark:text-red-400">`);
        _push(ssrRenderComponent(unref(TrashIcon), { class: "w-4 h-4" }, null, _parent));
        _push(`</button></div>`);
      });
      _push(`<!--]-->`);
      if (unref(form).contacts.length === 0) {
        _push(`<div class="text-center py-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg"><p class="text-gray-500 dark:text-gray-400">\u5C1A\u672A\u65B0\u589E\u4EFB\u4F55\u806F\u7E6B\u65B9\u5F0F</p><button type="button" class="mt-2 text-primary-600 hover:text-primary-500 dark:text-primary-400"> \u9EDE\u64CA\u65B0\u589E\u7B2C\u4E00\u500B\u806F\u7E6B\u65B9\u5F0F </button></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div><div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700"><button type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"> \u53D6\u6D88 </button><button type="submit"${ssrIncludeBooleanAttr(unref(isSubmitting)) ? " disabled" : ""} class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">`);
      if (unref(isSubmitting)) {
        _push(`<span>\u5132\u5B58\u4E2D...</span>`);
      } else {
        _push(`<span>\u5132\u5B58\u696D\u4E3B</span>`);
      }
      _push(`</button></div></form></div></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/clients/create.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};

export { _sfc_main as default };
//# sourceMappingURL=create-D9VUJMRF.mjs.map
