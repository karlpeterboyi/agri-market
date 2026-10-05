<template>
  <div>
    <h2 class="text-2xl sm:text-3xl font-bold mb-2">Transporter Dashboard</h2>
    <p class="text-gray-500 mb-6">List trucks, set price per km, and win delivery jobs after orders.</p>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-8">
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Trucks</p>
        <p class="text-2xl font-bold">{{ stats.trucks }}</p>
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
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <router-link to="/finance/subscriptions" class="card-link">
        <p class="font-semibold">1. Activate subscription</p>
        <p class="text-sm text-gray-500 mt-1">Required to list trucks</p>
      </router-link>
      <router-link to="/transporter/trucks" class="card-link">
        <p class="font-semibold">2. My trucks</p>
        <p class="text-sm text-gray-500 mt-1">Fleet, capacity, price/km</p>
      </router-link>
      <router-link to="/marketplace" class="card-link">
        <p class="font-semibold">Marketplace volume</p>
        <p class="text-sm text-gray-500 mt-1">See active trade corridors</p>
      </router-link>
      <router-link to="/knowledge" class="card-link">
        <p class="font-semibold">Knowledge Hub</p>
        <p class="text-sm text-gray-500 mt-1">Safety and logistics training</p>
      </router-link>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";
const stats = ref({ trucks: 0, wallet: 0 });
const subActive = ref(false);
onMounted(async () => {
  try {
    const [t, w, s] = await Promise.allSettled([
      api.get("/my-trucks"),
      api.get("/wallet"),
      api.get("/my-subscription"),
    ]);
    if (t.status === "fulfilled") {
      const d = t.value.data?.data || t.value.data || [];
      stats.value.trucks = Array.isArray(d) ? d.length : 0;
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
