<template>
  <nav class="bg-green-700 text-white px-4 sm:px-6 py-3 flex justify-between items-center gap-3">
    <AppLogo text="Agri-market" text-class="text-xl text-white" img-class="h-9 w-9 rounded-full bg-white/10 p-0.5" />

    <div class="flex items-center gap-3 sm:gap-4">
      <span class="text-sm sm:text-base truncate max-w-[40vw]">
        {{ auth.user?.name }}
      </span>
      <button
        type="button"
        @click="logout"
        class="bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded text-sm"
      >
        Logout
      </button>
    </div>
  </nav>
</template>

<script setup>
import AppLogo from "./AppLogo.vue";
import { useAuthStore } from "../stores/auth";
import { useRouter } from "vue-router";

const auth = useAuthStore();
const router = useRouter();

function logout() {
  auth.logout();
  router.push("/login");
}
</script>
