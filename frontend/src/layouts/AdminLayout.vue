<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-red-700 text-white shadow sticky top-0 z-20">
      <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2 p-3 sm:p-4">
        <div class="flex items-center gap-3">
          <button type="button" class="lg:hidden p-2 rounded hover:bg-red-600" @click="open = !open">☰</button>
          <div class="flex items-center gap-2">
          <AppLogo :show-text="false" to="/admin" img-class="h-9 w-9 rounded-full bg-white/10 p-0.5" />
          <h1 class="text-lg sm:text-xl font-bold">Admin</h1>
        </div>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-sm opacity-90 truncate max-w-[140px]">{{ auth.user?.name }}</span>
          <button @click="logout" class="bg-white text-red-700 px-3 py-1.5 rounded text-sm font-medium hover:bg-gray-100">
            Logout
          </button>
        </div>
      </div>
    </header>

    <div class="flex relative">
      <div v-if="open" class="fixed inset-0 bg-black/40 z-10 lg:hidden" @click="open = false" />
      <aside
        class="fixed lg:sticky top-14 left-0 z-10 w-64 bg-white shadow min-h-[calc(100vh-3.5rem)] transform transition-transform lg:translate-x-0 overflow-y-auto"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
      >
        <nav class="flex flex-col p-4 gap-1 text-sm">
          <router-link class="nav" to="/admin" @click="open=false">📊 Dashboard</router-link>
          <router-link class="nav" to="/admin/subscriptions">Subscriptions</router-link>
        <router-link class="nav-link" to="/admin/users" @click="open=false">👥 Users</router-link>
          <router-link class="nav" to="/admin/listings" @click="open=false">🌾 Listings</router-link>
          <router-link class="nav" to="/admin/orders" @click="open=false">📦 Orders</router-link>
          <router-link class="nav" to="/admin/logistics" @click="open=false">🚚 Logistics</router-link>
          <router-link class="nav" to="/admin/payments" @click="open=false">💳 Payments</router-link>
          <router-link class="nav" to="/admin/withdrawals" @click="open=false">💰 Withdrawals</router-link>
          <router-link class="nav" to="/admin/reports" @click="open=false">📈 Reports</router-link>
          <router-link class="nav" to="/admin/analytics" @click="open=false">📉 Analytics</router-link>
          <router-link class="nav" to="/admin/government-review" @click="open=false">🏛 Gov. Review</router-link>
          <router-link class="nav" to="/admin/moderation" @click="open=false">🛡 Moderation</router-link>
          <router-link class="nav" to="/admin/catalog" @click="open=false">📚 Catalogs</router-link>
          <router-link class="nav" to="/marketplace" @click="open=false">🏪 Marketplace</router-link>
        </nav>
      </aside>
      <main class="flex-1 p-3 sm:p-6 min-w-0">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import AppLogo from "../components/AppLogo.vue";
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
const router = useRouter();
const open = ref(false);

function logout() {
  auth.logout();
  router.push("/login");
}
</script>

<style scoped>
.nav {
  display: block;
  padding: 0.65rem 0.75rem;
  border-radius: 0.375rem;
  color: #374151;
  text-decoration: none;
}
.nav:hover {
  background: #fef2f2;
}
.nav.router-link-active {
  background: #fee2e2;
  color: #991b1b;
  font-weight: 600;
}
</style>
