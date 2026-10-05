<template>
  <div class="min-h-screen bg-gray-100">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">
      <div>
        <h1 class="text-3xl sm:text-4xl font-bold text-green-700">Admin Control Center</h1>
        <p class="text-gray-600 mt-1">Live platform metrics</p>
        <p v-if="stats.generated_at" class="text-xs text-gray-400 mt-1">Updated {{ stats.generated_at }}</p>
      </div>
      <button @click="loadDashboard" class="bg-green-700 text-white px-5 py-2 rounded hover:bg-green-800 text-sm font-medium">
        Refresh
      </button>
    </div>

    <div v-if="loading" class="text-center py-20 text-gray-500 text-xl">Loading dashboard…</div>
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6">
      {{ error }}
      <p class="text-sm mt-2">Ensure you are logged in as <strong>admin</strong> and API is reachable.</p>
    </div>
    <template v-else>
      <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-4 sm:gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-gray-500 text-sm">Users</p>
          <p class="text-3xl font-bold">{{ stats.users ?? 0 }}</p>
          <p class="text-xs text-gray-400 mt-1">
            {{ stats.farmers ?? 0 }} farmers · {{ stats.buyers ?? 0 }} buyers
          </p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-gray-500 text-sm">Active listings</p>
          <p class="text-3xl font-bold">{{ stats.active_listings ?? stats.listings ?? 0 }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ stats.listings ?? 0 }} total crop listings</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-gray-500 text-sm">Orders</p>
          <p class="text-3xl font-bold">{{ stats.orders ?? 0 }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ stats.pending_orders ?? 0 }} pending</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-gray-500 text-sm">GMV (paid)</p>
          <p class="text-3xl font-bold text-green-700">TZS {{ Number(stats.total_sales || 0).toLocaleString() }}</p>
          <p class="text-xs text-gray-400 mt-1">Escrow: {{ Number(stats.escrow_balance || 0).toLocaleString() }}</p>
        </div>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-5">
          <h3 class="font-semibold mb-3">Users by role</h3>
          <ul class="text-sm space-y-1">
            <li v-for="(count, role) in (stats.users_by_role || {})" :key="role" class="flex justify-between">
              <span class="capitalize">{{ role }}</span>
              <span class="font-medium">{{ count }}</span>
            </li>
          </ul>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
          <h3 class="font-semibold mb-3">Marketplace</h3>
          <ul class="text-sm space-y-1">
            <li class="flex justify-between"><span>Crop listings</span><span class="font-medium">{{ stats.listings ?? 0 }}</span></li>
            <li class="flex justify-between"><span>Input listings</span><span class="font-medium">{{ stats.input_listings ?? 0 }}</span></li>
            <li class="flex justify-between"><span>Services</span><span class="font-medium">{{ stats.services ?? 0 }}</span></li>
            <li class="flex justify-between"><span>Machinery</span><span class="font-medium">{{ stats.machinery_listings ?? 0 }}</span></li>
          </ul>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
          <h3 class="font-semibold mb-3">Finance ops</h3>
          <ul class="text-sm space-y-1">
            <li class="flex justify-between"><span>Payments</span><span class="font-medium">{{ stats.payments ?? 0 }}</span></li>
            <li class="flex justify-between"><span>Pending withdrawals</span><span class="font-medium text-yellow-700">{{ stats.pending_withdrawals ?? 0 }}</span></li>
            <li class="flex justify-between"><span>All withdrawals</span><span class="font-medium">{{ stats.withdrawals ?? 0 }}</span></li>
          </ul>
        </div>
      </div>

      <div class="flex flex-wrap gap-3">
        <router-link to="/admin/users" class="bg-white shadow rounded-lg px-4 py-3 text-sm font-medium hover:bg-green-50">Manage users →</router-link>
        <router-link to="/admin/listings" class="bg-white shadow rounded-lg px-4 py-3 text-sm font-medium hover:bg-green-50">Listings →</router-link>
        <router-link to="/admin/withdrawals" class="bg-white shadow rounded-lg px-4 py-3 text-sm font-medium hover:bg-green-50">Withdrawals →</router-link>
        <router-link to="/admin/analytics" class="bg-white shadow rounded-lg px-4 py-3 text-sm font-medium hover:bg-green-50">Analytics →</router-link>
        <router-link to="/admin/government-review" class="bg-white shadow rounded-lg px-4 py-3 text-sm font-medium hover:bg-green-50">Gov. review →</router-link>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const loading = ref(true);
const error = ref("");
const stats = ref({});

async function loadDashboard() {
  loading.value = true;
  error.value = "";
  try {
    const res = await api.get("/admin/dashboard");
    stats.value = res.data || {};
  } catch (e) {
    console.error(e);
    error.value =
      e.response?.data?.message ||
      `Failed to load dashboard (${e.response?.status || "network"}).`;
    stats.value = {};
  } finally {
    loading.value = false;
  }
}

onMounted(loadDashboard);
</script>
