import { ref, computed, watch, mergeProps, unref, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrInterpolate, ssrRenderAttr, ssrRenderComponent, ssrRenderList, ssrRenderClass, ssrIncludeBooleanAttr, ssrLooseContain, ssrLooseEqual } from 'vue/server-renderer';
import { MagnifyingGlassIcon, PlusIcon, ShieldExclamationIcon, UserCircleIcon, UsersIcon } from '@heroicons/vue/24/outline';
import { a as useI18n, b as useAuthStore, u as useApi } from './server.mjs';
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

const useUsers = () => {
  const { get, post, put, patch, del } = useApi();
  const getUsers = async (params = {}) => {
    const queryParams = new URLSearchParams();
    if (params.search) queryParams.append("search", params.search);
    if (params.role) queryParams.append("role", params.role);
    if (params.status) queryParams.append("status", params.status);
    if (params.page) queryParams.append("page", params.page);
    if (params.per_page) queryParams.append("per_page", params.per_page);
    if (params.sort) queryParams.append("sort", params.sort);
    if (params.order) queryParams.append("order", params.order);
    const queryString = queryParams.toString();
    const endpoint = queryString ? `/users?${queryString}` : "/users";
    return await get(endpoint);
  };
  const getUser = async (id) => {
    return await get(`/users/${id}`);
  };
  const createUser = async (userData) => {
    return await post("/users", userData);
  };
  const updateUser = async (id, userData) => {
    return await put(`/users/${id}`, userData);
  };
  const deleteUser = async (id) => {
    return await del(`/users/${id}`);
  };
  const toggleUserStatus = async (id, status) => {
    return await patch(`/users/${id}/status`, { status });
  };
  const changeUserPassword = async (id, passwordData) => {
    return await patch(`/users/${id}/password`, passwordData);
  };
  const restoreUser = async (id) => {
    return await patch(`/users/${id}/restore`);
  };
  const forceDeleteUser = async (id) => {
    return await del(`/users/${id}/force`);
  };
  const getUserStats = async () => {
    return await get("/users/stats");
  };
  return {
    // Core operations
    getUsers,
    getUser,
    createUser,
    updateUser,
    deleteUser,
    // Status management
    toggleUserStatus,
    changeUserPassword,
    // Advanced operations
    restoreUser,
    forceDeleteUser,
    getUserStats
  };
};
const _sfc_main = {
  __name: "users",
  __ssrInlineRender: true,
  setup(__props) {
    const { t } = useI18n();
    const authStore = useAuthStore();
    const { getUsers } = useUsers();
    const searchQuery = ref("");
    const showEditModal = ref(false);
    const showCreateModal = ref(false);
    const editForm = ref({});
    const createForm = ref({
      name: "",
      username: "",
      email: "",
      password: "",
      password_confirmation: "",
      role: "user",
      status: "active"
    });
    const users = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0
    });
    const currentPage = ref(1);
    const loadUsers = async (page = 1) => {
      try {
        loading.value = true;
        error.value = null;
        const response = await getUsers({
          search: searchQuery.value,
          page,
          per_page: 15,
          sort: "-created_at"
        });
        if (response.success) {
          users.value = response.data || [];
          pagination.value = response.pagination || {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: 0,
            to: 0
          };
          currentPage.value = page;
        } else {
          throw new Error(response.message || "Failed to load users");
        }
      } catch (err) {
        error.value = err.message;
        console.error("Failed to load users:", err);
        users.value = [];
        pagination.value = {
          current_page: 1,
          last_page: 1,
          per_page: 15,
          total: 0,
          from: 0,
          to: 0
        };
      } finally {
        loading.value = false;
      }
    };
    const filteredUsers = computed(() => {
      return users.value;
    });
    watch(searchQuery, async () => {
      currentPage.value = 1;
      await loadUsers(1);
    }, { debounce: 500 });
    const formatDate = (date) => {
      if (!date) return "\u672A\u66FE\u767B\u5165";
      try {
        return new Date(date).toLocaleDateString("zh-TW", {
          year: "numeric",
          month: "2-digit",
          day: "2-digit",
          hour: "2-digit",
          minute: "2-digit",
          hour12: false
        });
      } catch (error2) {
        console.error("Invalid date:", date);
        return "\u65E5\u671F\u683C\u5F0F\u932F\u8AA4";
      }
    };
    return (_ctx, _push, _parent, _attrs) => {
      var _a;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><div class="flex items-center justify-between mb-6"><h2 class="text-2xl font-bold text-gray-900 dark:text-white">${ssrInterpolate(unref(t)("nav.user_management"))}</h2><div class="flex items-center space-x-4"><div class="relative"><input${ssrRenderAttr("value", unref(searchQuery))} type="text" placeholder="\u641C\u5C0B\u7528\u6236..." class="w-64 px-4 py-2 pl-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">`);
      _push(ssrRenderComponent(unref(MagnifyingGlassIcon), { class: "w-5 h-5 text-gray-400 absolute left-3 top-2.5" }, null, _parent));
      _push(`</div><button class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">`);
      _push(ssrRenderComponent(unref(PlusIcon), { class: "w-4 h-4 mr-2" }, null, _parent));
      _push(` \u65B0\u589E\u7528\u6236 </button></div></div>`);
      if (!unref(authStore).isAdmin) {
        _push(`<div class="text-center py-12">`);
        _push(ssrRenderComponent(unref(ShieldExclamationIcon), { class: "w-12 h-12 text-red-500 mx-auto mb-4" }, null, _parent));
        _push(`<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">${ssrInterpolate(unref(t)("auth.access_denied"))}</h3><p class="text-gray-600 dark:text-gray-400">${ssrInterpolate(unref(t)("auth.admin_only_feature"))}</p></div>`);
      } else {
        _push(`<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 tracking-wide"> \u7528\u6236\u8CC7\u8A0A </th><th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 tracking-wide"> \u89D2\u8272\u6B0A\u9650 </th><th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 tracking-wide"> \u5E33\u865F\u72C0\u614B </th><th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 tracking-wide"> \u6700\u5F8C\u767B\u5165\u6642\u9593 </th><th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 tracking-wide"> \u64CD\u4F5C\u529F\u80FD </th></tr></thead><tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"><!--[-->`);
        ssrRenderList(unref(filteredUsers), (user) => {
          var _a2, _b;
          _push(`<tr class="hover:bg-gray-50 dark:hover:bg-gray-700"><td class="px-6 py-4 whitespace-nowrap"><div class="flex items-center"><div class="w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">`);
          _push(ssrRenderComponent(unref(UserCircleIcon), { class: "w-8 h-8 text-gray-500 dark:text-gray-400" }, null, _parent));
          _push(`</div><div class="ml-4"><div class="text-sm font-medium text-gray-900 dark:text-white">${ssrInterpolate((user == null ? void 0 : user.name) || "\u672A\u77E5\u7528\u6236")}</div><div class="text-sm text-gray-500 dark:text-gray-400">${ssrInterpolate((user == null ? void 0 : user.email) || "\u672A\u8A2D\u5B9AEmail")}</div>`);
          if (user == null ? void 0 : user.username) {
            _push(`<div class="text-xs text-gray-400 dark:text-gray-500">@${ssrInterpolate(user.username)}</div>`);
          } else {
            _push(`<!---->`);
          }
          _push(`</div></div></td><td class="px-6 py-4 whitespace-nowrap"><span class="${ssrRenderClass([{
            "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400": (user == null ? void 0 : user.role) === "admin",
            "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400": (user == null ? void 0 : user.role) === "user"
          }, "inline-flex px-3 py-1 text-sm font-medium rounded-full"])}">${ssrInterpolate((user == null ? void 0 : user.role) === "admin" ? "\u7BA1\u7406\u54E1" : "\u4E00\u822C\u7528\u6236")}</span></td><td class="px-6 py-4 whitespace-nowrap"><span class="${ssrRenderClass([{
            "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400": (user == null ? void 0 : user.status) === "active",
            "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400": (user == null ? void 0 : user.status) === "inactive",
            "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400": (user == null ? void 0 : user.status) === "suspended"
          }, "inline-flex px-3 py-1 text-sm font-medium rounded-full"])}">${ssrInterpolate((user == null ? void 0 : user.status) === "active" ? "\u6B63\u5E38\u4F7F\u7528" : (user == null ? void 0 : user.status) === "suspended" ? "\u5DF2\u66AB\u505C" : "\u5DF2\u505C\u7528")}</span></td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${ssrInterpolate(formatDate(user == null ? void 0 : user.last_login_at))}</td><td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><div class="flex items-center space-x-2">`);
          if ((user == null ? void 0 : user.id) !== ((_a2 = unref(authStore).user) == null ? void 0 : _a2.id)) {
            _push(`<button class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors duration-200"${ssrIncludeBooleanAttr(!(user == null ? void 0 : user.id)) ? " disabled" : ""}>${ssrInterpolate((user == null ? void 0 : user.status) === "active" ? unref(t)("auth.deactivate") : unref(t)("auth.activate"))}</button>`);
          } else {
            _push(`<!---->`);
          }
          _push(`<button class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors duration-200">${ssrInterpolate(unref(t)("common.edit"))}</button>`);
          if ((user == null ? void 0 : user.id) !== ((_b = unref(authStore).user) == null ? void 0 : _b.id)) {
            _push(`<button class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-200"${ssrIncludeBooleanAttr(!(user == null ? void 0 : user.id)) ? " disabled" : ""}> \u522A\u9664 </button>`);
          } else {
            _push(`<!---->`);
          }
          _push(`</div></td></tr>`);
        });
        _push(`<!--]--></tbody></table>`);
        if (!unref(loading) && unref(filteredUsers).length === 0) {
          _push(`<div class="text-center py-12">`);
          _push(ssrRenderComponent(unref(UsersIcon), { class: "w-12 h-12 text-gray-400 mx-auto mb-4" }, null, _parent));
          _push(`<p class="text-gray-500 dark:text-gray-400">${ssrInterpolate(unref(t)("auth.no_users_found"))}</p></div>`);
        } else {
          _push(`<!---->`);
        }
        if (unref(loading)) {
          _push(`<div class="text-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500 mx-auto mb-4"></div><p class="text-gray-500 dark:text-gray-400">\u8F09\u5165\u4E2D...</p></div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div>`);
      }
      if (!unref(loading) && unref(filteredUsers).length > 0 && unref(pagination).last_page > 1) {
        _push(`<div class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4"><div class="text-sm text-gray-700 dark:text-gray-300"> \u986F\u793A ${ssrInterpolate(unref(pagination).from)} \u5230 ${ssrInterpolate(unref(pagination).to)} \u7B46\uFF0C\u5171 ${ssrInterpolate(unref(pagination).total)} \u7B46\u7528\u6236 </div><div class="flex items-center space-x-2"><button${ssrIncludeBooleanAttr(unref(pagination).current_page <= 1 || unref(loading)) ? " disabled" : ""} class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"> \u4E0A\u4E00\u9801 </button><span class="text-sm text-gray-700 dark:text-gray-300"> \u7B2C ${ssrInterpolate(unref(pagination).current_page)} \u9801\uFF0C\u5171 ${ssrInterpolate(unref(pagination).last_page)} \u9801 </span><button${ssrIncludeBooleanAttr(unref(pagination).current_page >= unref(pagination).last_page || unref(loading)) ? " disabled" : ""} class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"> \u4E0B\u4E00\u9801 </button></div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
      if (unref(showEditModal)) {
        _push(`<div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-xl max-w-md w-full p-6"><h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">${ssrInterpolate(unref(t)("auth.edit_user"))}</h3><div class="space-y-4"><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("auth.full_name"))}</label><input${ssrRenderAttr("value", unref(editForm).name)} type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("auth.email"))}</label><input${ssrRenderAttr("value", unref(editForm).email)} type="email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></div>`);
        if (unref(editForm).id !== ((_a = unref(authStore).user) == null ? void 0 : _a.id)) {
          _push(`<div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("auth.role"))}</label><select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"><option value="user"${ssrIncludeBooleanAttr(Array.isArray(unref(editForm).role) ? ssrLooseContain(unref(editForm).role, "user") : ssrLooseEqual(unref(editForm).role, "user")) ? " selected" : ""}>${ssrInterpolate(unref(t)("auth.role_user"))}</option><option value="admin"${ssrIncludeBooleanAttr(Array.isArray(unref(editForm).role) ? ssrLooseContain(unref(editForm).role, "admin") : ssrLooseEqual(unref(editForm).role, "admin")) ? " selected" : ""}>${ssrInterpolate(unref(t)("auth.role_admin"))}</option></select></div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div><div class="flex justify-end space-x-3 mt-6"><button class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">${ssrInterpolate(unref(t)("common.cancel"))}</button><button class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors duration-200">${ssrInterpolate(unref(t)("common.save"))}</button></div></div></div>`);
      } else {
        _push(`<!---->`);
      }
      if (unref(showCreateModal)) {
        _push(`<div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto"><h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4"> \u65B0\u589E\u7528\u6236 </h3><div class="space-y-4"><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u59D3\u540D * </label><input${ssrRenderAttr("value", unref(createForm).name)} type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u7528\u6236\u540D * </label><input${ssrRenderAttr("value", unref(createForm).username)} type="text" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> Email * </label><input${ssrRenderAttr("value", unref(createForm).email)} type="email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u5BC6\u78BC * </label><input${ssrRenderAttr("value", unref(createForm).password)} type="password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u78BA\u8A8D\u5BC6\u78BC * </label><input${ssrRenderAttr("value", unref(createForm).password_confirmation)} type="password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></div><div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> \u89D2\u8272 </label><select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"><option value="user"${ssrIncludeBooleanAttr(Array.isArray(unref(createForm).role) ? ssrLooseContain(unref(createForm).role, "user") : ssrLooseEqual(unref(createForm).role, "user")) ? " selected" : ""}>\u4E00\u822C\u7528\u6236</option><option value="admin"${ssrIncludeBooleanAttr(Array.isArray(unref(createForm).role) ? ssrLooseContain(unref(createForm).role, "admin") : ssrLooseEqual(unref(createForm).role, "admin")) ? " selected" : ""}>\u7BA1\u7406\u54E1</option></select></div></div><div class="flex justify-end space-x-3 mt-6"><button class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"> \u53D6\u6D88 </button><button class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors duration-200"> \u5EFA\u7ACB </button></div></div></div>`);
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/settings/users.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};

export { _sfc_main as default };
//# sourceMappingURL=users-p3NElDIx.mjs.map
