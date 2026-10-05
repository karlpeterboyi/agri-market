<template>
  <div>
    <h2 class="text-2xl sm:text-3xl font-bold mb-2">Agrodealer Dashboard</h2>
    <p class="text-gray-500 mb-6">List farm inputs, manage stock visibility, and grow regional sales.</p>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-8">
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">My input listings</p>
        <p class="text-2xl font-bold">{{ stats.listings }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Subscription</p>
        <p class="text-lg font-bold" :class="subActive ? 'text-green-700' : 'text-amber-600'">
          {{ subActive ? "Active" : "Required" }}
        </p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Wallet (TZS)</p>
        <p class="text-lg font-bold">{{ Number(stats.wallet || 0).toLocaleString() }}</p>
      </div>
    </div>

    <p class="text-sm font-semibold text-gray-600 mb-3">Core flows</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
      <router-link to="/finance/subscriptions" class="card-link">
        <p class="font-semibold">1. Activate subscription</p>
        <p class="text-sm text-gray-500 mt-1">Required before listing inputs</p>
      </router-link>
      <router-link to="/agrodealer/inputs" class="card-link">
        <p class="font-semibold">2. My farm inputs</p>
        <p class="text-sm text-gray-500 mt-1">Seeds, fertiliser, chemicals</p>
      </router-link>
      <router-link to="/marketplace" class="card-link">
        <p class="font-semibold">Marketplace</p>
        <p class="text-sm text-gray-500 mt-1">See demand and crop prices</p>
      </router-link>
      <router-link to="/finance/dossier" class="card-link">
        <p class="font-semibold">Finance dossier</p>
        <p class="text-sm text-gray-500 mt-1">Trade history for lenders</p>
      </router-link>
      <router-link to="/knowledge" class="card-link">
        <p class="font-semibold">Knowledge Hub</p>
        <p class="text-sm text-gray-500 mt-1">Product training for farmers</p>
      </router-link>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";
const stats = ref({ listings: 0, wallet: 0 });
const subActive = ref(false);
onMounted(async () => {
  try {
    const [l, w, s] = await Promise.allSettled([
      api.get("/my-input-listings"),
      api.get("/wallet"),
      api.get("/my-subscription"),
    ]);
    if (l.status === "fulfilled") {
      const d = l.value.data?.data || l.value.data || [];
      stats.value.listings = Array.isArray(d) ? d.length : 0;
    }
    if (w.status === "fulfilled") {
      const ww = w.value.data?.data || w.value.data || {};
      stats.value.wallet = ww.available_balance ?? ww.balance ?? 0;
    }
    if (s.status === "fulfilled") subActive.value = !!s.value.data?.active;
  } catch {}
});
</script>
<style scoped>
.card-link { display:block; background:#fff; border-radius:.5rem; box-shadow:0 1px 3px rgb(0 0 0/.08); padding:1.25rem; }
.card-link:hover { box-shadow:0 4px 12px rgb(0 0 0/.1); }
</style>
