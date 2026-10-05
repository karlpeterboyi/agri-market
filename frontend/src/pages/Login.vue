<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6 sm:p-8">
      <div class="flex justify-center mb-4">
        <AppLogo text-class="text-xl text-gray-800" img-class="h-12 w-12 rounded-full" />
      </div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">{{ t('auth.loginTitle') }}</h1>
        <select :value="locale" @change="setLocale($event.target.value)" class="text-xs border rounded px-2 py-1">
          <option v-for="l in available" :key="l.code" :value="l.code">{{ l.label }}</option>
        </select>
      </div>

      <form @submit.prevent="login" class="space-y-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">{{ t('common.email') }}</label>
          <input
            v-model="email"
            type="email"
            required
            autocomplete="username"
            class="border rounded-lg px-3 py-2.5 w-full focus:ring-2 focus:ring-green-500 outline-none"
          />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">{{ t('common.password') }}</label>
          <input
            v-model="password"
            type="password"
            required
            autocomplete="current-password"
            class="border rounded-lg px-3 py-2.5 w-full focus:ring-2 focus:ring-green-500 outline-none"
          />
        </div>
        <p v-if="error" class="text-red-600 text-sm break-words">{{ error }}</p>
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-lg disabled:opacity-50"
        >
          {{ loading ? t('common.loading') : t('common.login') }}
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-500">
        {{ t('auth.noAccount') }}
        <router-link to="/register" class="text-green-600 font-medium hover:underline">{{ t('common.register') }}</router-link>
      </p>
      <p class="mt-2 text-center">
        <router-link to="/" class="text-sm text-gray-400 hover:text-gray-600">← {{ t('common.home') }}</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import AppLogo from "../components/AppLogo.vue";
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useI18n } from "../i18n";
import { homeForRole } from "../utils/roleHome";
import { getApiBaseURL } from "../services/api";

const { t, locale, setLocale, available } = useI18n();
const email = ref("");
const password = ref("");
const error = ref("");
const loading = ref(false);
const router = useRouter();
const auth = useAuthStore();

const login = async () => {
  loading.value = true;
  error.value = "";
  try {
    await auth.login({ email: email.value, password: password.value });
    const path = homeForRole(auth.role) || "/";
    await router.push(path);
  } catch (e) {
    const status = e.response?.status;
    const msg = e.response?.data?.message;
    if (msg) {
      error.value = msg;
    } else if (e.code === "ERR_NETWORK" || !e.response) {
      error.value =
        "Cannot reach API at " +
        getApiBaseURL() +
        ". Keep `php artisan serve` running and restart Vite with proxy (empty VITE_API_URL).";
    } else if (status === 401) {
      error.value = t("auth.invalidCredentials") || "Invalid credentials";
    } else {
      error.value = e.message || t("auth.invalidCredentials");
    }
    console.error("Login failed", e);
  } finally {
    loading.value = false;
  }
};
</script>
