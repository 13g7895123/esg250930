import { reactive, ref, watch, computed, mergeProps, unref, useSSRContext } from 'vue';
import { ssrRenderAttrs, ssrInterpolate, ssrRenderAttr, ssrRenderComponent, ssrRenderClass, ssrIncludeBooleanAttr } from 'vue/server-renderer';
import { CameraIcon, TrashIcon, CheckCircleIcon, ExclamationCircleIcon, InformationCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { a as useI18n, u as useApi, b as useAuthStore, s as storeToRefs, c as useHead } from './server.mjs';
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

const useAuth = () => {
  const { post, get, put } = useApi();
  const login = async (credentials) => {
    const loginData = {
      login: credentials.username || credentials.email,
      // Backend expects 'login' field
      password: credentials.password
    };
    return await post("/auth/login", loginData);
  };
  const register = async (userData) => {
    return await post("/auth/register", userData);
  };
  const logout = async () => {
    return await post("/auth/logout");
  };
  const logoutAll = async () => {
    return await post("/auth/logout-all");
  };
  const getCurrentUser = async () => {
    return await get("/auth/me");
  };
  const updateProfile = async (profileData) => {
    return await put("/profile", profileData);
  };
  const changePassword = async (passwordData) => {
    return await post("/auth/change-password", passwordData);
  };
  const refreshToken = async () => {
    return await post("/auth/refresh");
  };
  const requestPasswordReset = async (email) => {
    return await post("/auth/forgot-password", { email });
  };
  const resetPassword = async (resetData) => {
    return await post("/auth/reset-password", resetData);
  };
  const verifyEmail = async (token) => {
    return await post("/auth/verify-email", { token });
  };
  const checkAuth = async () => {
    var _a;
    try {
      const response = await getCurrentUser();
      return response.success && ((_a = response.data) == null ? void 0 : _a.user);
    } catch (error) {
      return false;
    }
  };
  const validateToken = async () => {
    return await checkAuth();
  };
  return {
    // Core authentication
    login,
    register,
    logout,
    logoutAll,
    getCurrentUser,
    checkAuth,
    validateToken,
    // Profile management
    updateProfile,
    changePassword,
    // Token management
    refreshToken,
    // Password reset (optional - depends on backend implementation)
    requestPasswordReset,
    resetPassword,
    // Email verification (optional - depends on backend implementation)
    verifyEmail
  };
};
const _sfc_main = {
  __name: "profile",
  __ssrInlineRender: true,
  setup(__props) {
    const { t } = useI18n();
    useAuth();
    useApi();
    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);
    const profileForm = reactive({
      name: "",
      username: "",
      email: "",
      phone: "",
      bio: "",
      website: "",
      location: "",
      company: "",
      position: "",
      avatar: ""
    });
    const passwordForm = reactive({
      currentPassword: "",
      newPassword: "",
      confirmPassword: ""
    });
    const profileErrors = ref({});
    const passwordErrors = ref({});
    const isUpdatingProfile = ref(false);
    const isChangingPassword = ref(false);
    const isDeletingAccount = ref(false);
    const showDeleteConfirm = ref(false);
    const deleteConfirmPassword = ref("");
    const message = ref(null);
    const initializeProfile = () => {
      if (user.value) {
        Object.assign(profileForm, {
          name: user.value.name || "",
          username: user.value.username || "",
          email: user.value.email || "",
          phone: user.value.phone || "",
          bio: user.value.bio || "",
          website: user.value.website || "",
          location: user.value.location || "",
          company: user.value.company || "",
          position: user.value.position || "",
          avatar: user.value.avatar || ""
        });
      }
    };
    const isDefaultAvatar = (avatarUrl) => {
      return !avatarUrl || avatarUrl.includes("ui-avatars.com");
    };
    const formatDate = (date) => {
      if (!date) return "N/A";
      return new Date(date).toLocaleDateString();
    };
    const getInputClasses = (hasError) => {
      const baseClasses = "w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-0 dark:bg-gray-700 dark:text-white transition-colors duration-200";
      if (hasError) {
        return `${baseClasses} border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500`;
      }
      return `${baseClasses} border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500`;
    };
    const getTextareaClasses = (hasError) => {
      const baseClasses = "w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-0 dark:bg-gray-700 dark:text-white transition-colors duration-200 resize-vertical";
      if (hasError) {
        return `${baseClasses} border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500`;
      }
      return `${baseClasses} border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500`;
    };
    watch(user, () => {
      initializeProfile();
    }, { deep: true });
    useHead({
      title: computed(() => t("profile.title"))
    });
    return (_ctx, _push, _parent, _attrs) => {
      var _a, _b, _c, _d, _e, _f, _g;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">${ssrInterpolate(unref(t)("profile.title"))}</h1><p class="text-gray-600 dark:text-gray-300">${ssrInterpolate(unref(t)("profile.subtitle"))}</p></div><div class="grid grid-cols-1 lg:grid-cols-3 gap-6"><div class="lg:col-span-1"><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6"><div class="text-center mb-6"><div class="relative inline-block"><img${ssrRenderAttr("src", unref(profileForm).avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(unref(profileForm).name || "User")}&background=6366f1&color=fff`)}${ssrRenderAttr("alt", unref(profileForm).name || "User Avatar")} class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-white dark:border-gray-700 shadow-lg"><label for="avatar-upload" class="absolute bottom-0 right-0 bg-primary-600 hover:bg-primary-700 text-white rounded-full p-2 cursor-pointer shadow-lg transition-colors duration-200">`);
      _push(ssrRenderComponent(unref(CameraIcon), { class: "w-4 h-4" }, null, _parent));
      _push(`<input id="avatar-upload" type="file" accept="image/*" class="hidden"></label></div><h3 class="text-lg font-semibold text-gray-900 dark:text-white">${ssrInterpolate(unref(profileForm).name || "User")}</h3><p class="text-sm text-gray-500 dark:text-gray-400">@${ssrInterpolate(unref(profileForm).username)}</p><div class="mt-2"><span class="${ssrRenderClass([{
        "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200": ((_a = unref(user)) == null ? void 0 : _a.status) === "active",
        "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200": ((_b = unref(user)) == null ? void 0 : _b.status) === "inactive"
      }, "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"])}">${ssrInterpolate(((_c = unref(user)) == null ? void 0 : _c.status) === "active" ? unref(t)("profile.active") : unref(t)("profile.inactive"))}</span></div></div><div class="space-y-2 mb-6"><button class="w-full px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900 dark:text-primary-200 rounded-lg transition-colors duration-200">`);
      _push(ssrRenderComponent(unref(CameraIcon), { class: "w-4 h-4 inline mr-2" }, null, _parent));
      _push(` ${ssrInterpolate(unref(t)("profile.change_avatar"))}</button>`);
      if (unref(profileForm).avatar && !isDefaultAvatar(unref(profileForm).avatar)) {
        _push(`<button class="w-full px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900 dark:text-red-200 rounded-lg transition-colors duration-200">`);
        _push(ssrRenderComponent(unref(TrashIcon), { class: "w-4 h-4 inline mr-2" }, null, _parent));
        _push(` ${ssrInterpolate(unref(t)("profile.remove_avatar"))}</button>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div class="border-t border-gray-200 dark:border-gray-700 pt-6"><h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">${ssrInterpolate(unref(t)("profile.account_info"))}</h4><div class="space-y-3"><div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">${ssrInterpolate(unref(t)("profile.role"))}</span><span class="text-gray-900 dark:text-white font-medium">${ssrInterpolate(((_d = unref(user)) == null ? void 0 : _d.role) === "admin" ? unref(t)("auth.role_admin") : unref(t)("auth.role_user"))}</span></div><div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">${ssrInterpolate(unref(t)("profile.member_since"))}</span><span class="text-gray-900 dark:text-white">${ssrInterpolate(formatDate((_e = unref(user)) == null ? void 0 : _e.createdAt))}</span></div><div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">${ssrInterpolate(unref(t)("profile.last_login"))}</span><span class="text-gray-900 dark:text-white">${ssrInterpolate(formatDate((_f = unref(user)) == null ? void 0 : _f.lastLogin))}</span></div></div></div></div></div><div class="lg:col-span-2 space-y-6"><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm"><div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"><h3 class="text-lg font-medium text-gray-900 dark:text-white">${ssrInterpolate(unref(t)("profile.personal_info"))}</h3></div><form class="p-6 space-y-6"><div class="grid grid-cols-1 md:grid-cols-2 gap-6"><div><label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.name"))} <span class="text-red-500">*</span></label><input id="name"${ssrRenderAttr("value", unref(profileForm).name)} type="text" required class="${ssrRenderClass(getInputClasses(unref(profileErrors).name))}"${ssrRenderAttr("placeholder", unref(t)("profile.name_placeholder"))}>`);
      if (unref(profileErrors).name) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(profileErrors).name)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div><label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.username"))} <span class="text-red-500">*</span></label><input id="username"${ssrRenderAttr("value", unref(profileForm).username)} type="text" required class="${ssrRenderClass(getInputClasses(unref(profileErrors).username))}"${ssrRenderAttr("placeholder", unref(t)("profile.username_placeholder"))}>`);
      if (unref(profileErrors).username) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(profileErrors).username)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div><label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.email"))} <span class="text-red-500">*</span></label><div class="relative"><input id="email"${ssrRenderAttr("value", unref(profileForm).email)} type="email" required class="${ssrRenderClass(getInputClasses(unref(profileErrors).email))}"${ssrRenderAttr("placeholder", unref(t)("profile.email_placeholder"))}><div class="absolute inset-y-0 right-0 pr-3 flex items-center">`);
      if ((_g = unref(user)) == null ? void 0 : _g.emailVerified) {
        _push(ssrRenderComponent(unref(CheckCircleIcon), {
          class: "w-5 h-5 text-green-500",
          title: unref(t)("profile.email_verified")
        }, null, _parent));
      } else {
        _push(ssrRenderComponent(unref(ExclamationCircleIcon), {
          class: "w-5 h-5 text-yellow-500",
          title: unref(t)("profile.email_unverified")
        }, null, _parent));
      }
      _push(`</div></div>`);
      if (unref(profileErrors).email) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(profileErrors).email)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div><label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.phone"))}</label><input id="phone"${ssrRenderAttr("value", unref(profileForm).phone)} type="tel" class="${ssrRenderClass(getInputClasses(unref(profileErrors).phone))}"${ssrRenderAttr("placeholder", unref(t)("profile.phone_placeholder"))}>`);
      if (unref(profileErrors).phone) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(profileErrors).phone)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div><label for="company" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.company"))}</label><input id="company"${ssrRenderAttr("value", unref(profileForm).company)} type="text" class="${ssrRenderClass(getInputClasses(unref(profileErrors).company))}"${ssrRenderAttr("placeholder", unref(t)("profile.company_placeholder"))}>`);
      if (unref(profileErrors).company) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(profileErrors).company)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div><label for="position" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.position"))}</label><input id="position"${ssrRenderAttr("value", unref(profileForm).position)} type="text" class="${ssrRenderClass(getInputClasses(unref(profileErrors).position))}"${ssrRenderAttr("placeholder", unref(t)("profile.position_placeholder"))}>`);
      if (unref(profileErrors).position) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(profileErrors).position)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div><label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.location"))}</label><input id="location"${ssrRenderAttr("value", unref(profileForm).location)} type="text" class="${ssrRenderClass(getInputClasses(unref(profileErrors).location))}"${ssrRenderAttr("placeholder", unref(t)("profile.location_placeholder"))}>`);
      if (unref(profileErrors).location) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(profileErrors).location)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div><label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.website"))}</label><input id="website"${ssrRenderAttr("value", unref(profileForm).website)} type="url" class="${ssrRenderClass(getInputClasses(unref(profileErrors).website))}"${ssrRenderAttr("placeholder", unref(t)("profile.website_placeholder"))}>`);
      if (unref(profileErrors).website) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(profileErrors).website)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div><div><label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.bio"))}</label><textarea id="bio" rows="4" class="${ssrRenderClass(getTextareaClasses(unref(profileErrors).bio))}"${ssrRenderAttr("placeholder", unref(t)("profile.bio_placeholder"))}>${ssrInterpolate(unref(profileForm).bio)}</textarea>`);
      if (unref(profileErrors).bio) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(profileErrors).bio)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div class="flex justify-end"><button type="submit"${ssrIncludeBooleanAttr(unref(isUpdatingProfile)) ? " disabled" : ""} class="px-6 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white font-medium rounded-lg transition-colors duration-200">`);
      if (unref(isUpdatingProfile)) {
        _push(`<span>${ssrInterpolate(unref(t)("common.loading"))}</span>`);
      } else {
        _push(`<span>${ssrInterpolate(unref(t)("profile.update_profile"))}</span>`);
      }
      _push(`</button></div></form></div><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm"><div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"><h3 class="text-lg font-medium text-gray-900 dark:text-white">${ssrInterpolate(unref(t)("profile.security"))}</h3></div><form class="p-6 space-y-6"><div><label for="current-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.current_password"))} <span class="text-red-500">*</span></label><input id="current-password"${ssrRenderAttr("value", unref(passwordForm).currentPassword)} type="password" required class="${ssrRenderClass(getInputClasses(unref(passwordErrors).currentPassword))}"${ssrRenderAttr("placeholder", unref(t)("profile.current_password_placeholder"))}>`);
      if (unref(passwordErrors).currentPassword) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(passwordErrors).currentPassword)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div class="grid grid-cols-1 md:grid-cols-2 gap-6"><div><label for="new-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.new_password"))} <span class="text-red-500">*</span></label><input id="new-password"${ssrRenderAttr("value", unref(passwordForm).newPassword)} type="password" required class="${ssrRenderClass(getInputClasses(unref(passwordErrors).newPassword))}"${ssrRenderAttr("placeholder", unref(t)("profile.new_password_placeholder"))}>`);
      if (unref(passwordErrors).newPassword) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(passwordErrors).newPassword)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div><label for="confirm-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${ssrInterpolate(unref(t)("profile.confirm_new_password"))} <span class="text-red-500">*</span></label><input id="confirm-password"${ssrRenderAttr("value", unref(passwordForm).confirmPassword)} type="password" required class="${ssrRenderClass(getInputClasses(unref(passwordErrors).confirmPassword))}"${ssrRenderAttr("placeholder", unref(t)("profile.confirm_new_password_placeholder"))}>`);
      if (unref(passwordErrors).confirmPassword) {
        _push(`<p class="mt-1 text-sm text-red-600 dark:text-red-400">${ssrInterpolate(unref(passwordErrors).confirmPassword)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div><div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg"><p class="text-sm text-blue-800 dark:text-blue-200">`);
      _push(ssrRenderComponent(unref(InformationCircleIcon), { class: "w-4 h-4 inline mr-1" }, null, _parent));
      _push(` ${ssrInterpolate(unref(t)("profile.password_requirements"))}</p></div><div class="flex justify-end"><button type="submit"${ssrIncludeBooleanAttr(unref(isChangingPassword)) ? " disabled" : ""} class="px-6 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white font-medium rounded-lg transition-colors duration-200">`);
      if (unref(isChangingPassword)) {
        _push(`<span>${ssrInterpolate(unref(t)("common.loading"))}</span>`);
      } else {
        _push(`<span>${ssrInterpolate(unref(t)("profile.change_password"))}</span>`);
      }
      _push(`</button></div></form></div><div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm border-2 border-red-200 dark:border-red-900"><div class="px-6 py-4 border-b border-red-200 dark:border-red-900"><h3 class="text-lg font-medium text-red-900 dark:text-red-200">${ssrInterpolate(unref(t)("profile.danger_zone"))}</h3></div><div class="p-6"><div class="flex items-start space-x-4"><div class="flex-shrink-0">`);
      _push(ssrRenderComponent(unref(ExclamationTriangleIcon), { class: "w-8 h-8 text-red-500" }, null, _parent));
      _push(`</div><div class="flex-1"><h4 class="text-base font-medium text-gray-900 dark:text-white mb-2">${ssrInterpolate(unref(t)("profile.delete_account"))}</h4><p class="text-sm text-gray-600 dark:text-gray-300 mb-4">${ssrInterpolate(unref(t)("profile.delete_account_warning"))}</p><button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200">${ssrInterpolate(unref(t)("profile.delete_account"))}</button></div></div></div></div></div></div>`);
      if (unref(showDeleteConfirm)) {
        _push(`<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"><div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6"><div class="flex items-center mb-4">`);
        _push(ssrRenderComponent(unref(ExclamationTriangleIcon), { class: "w-6 h-6 text-red-500 mr-3" }, null, _parent));
        _push(`<h3 class="text-lg font-medium text-gray-900 dark:text-white">${ssrInterpolate(unref(t)("profile.confirm_delete"))}</h3></div><p class="text-sm text-gray-600 dark:text-gray-300 mb-4">${ssrInterpolate(unref(t)("profile.delete_confirmation"))}</p><input${ssrRenderAttr("value", unref(deleteConfirmPassword))} type="password"${ssrRenderAttr("placeholder", unref(t)("profile.current_password_placeholder"))} class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg mb-4 dark:bg-gray-700 dark:text-white"><div class="flex justify-end space-x-3"><button class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">${ssrInterpolate(unref(t)("common.cancel"))}</button><button${ssrIncludeBooleanAttr(!unref(deleteConfirmPassword) || unref(isDeletingAccount)) ? " disabled" : ""} class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white rounded-lg">${ssrInterpolate(unref(isDeletingAccount) ? unref(t)("common.loading") : unref(t)("common.delete"))}</button></div></div></div>`);
      } else {
        _push(`<!---->`);
      }
      if (unref(message)) {
        _push(`<div class="${ssrRenderClass([unref(message).type === "success" ? "bg-green-600 text-white" : "bg-red-600 text-white", "fixed bottom-4 right-4 z-50 p-4 rounded-lg shadow-lg"])}">${ssrInterpolate(unref(message).text)}</div>`);
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("pages/profile.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};

export { _sfc_main as default };
//# sourceMappingURL=profile-CM_yff84.mjs.map
