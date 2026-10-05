<template>
  <div>
    <h2 class="text-2xl sm:text-3xl font-bold mb-2">Buyer Dashboard</h2>
    <p class="text-gray-500 mb-6">Source produce, pay via escrow, and arrange transport.</p>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-8">
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Orders</p>
        <p class="text-2xl font-bold">{{ stats.orders }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Subscription</p>
        <p class="text-lg font-bold" :class="subActive ? 'text-green-700' : 'text-amber-600'">
          {{ subActive ? "Active" : "Recommended" }}
        </p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Wallet (TZS)</p>
        <p class="text-lg font-bold">{{ Number(stats.wallet || 0).toLocaleString() }}</p>
      </div>
    </div>

    <p class="text-sm font-semibold text-gray-600 mb-3">Core flows</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <router-link to="/marketplace" class="card-link">
        <p class="font-semibold">1. Browse marketplace</p>
        <p class="text-sm text-gray-500 mt-1">Crops, livestock, inputs</p>
      </router-link>
      <router-link to="/buyer/orders" class="card-link">
        <p class="font-semibold">2. My orders</p>
        <p class="text-sm text-gray-500 mt-1">Escrow and delivery status</p>
      </router-link>
      <router-link to="/finance/subscriptions" class="card-link">
        <p class="font-semibold">Subscription</p>
        <p class="text-sm text-gray-500 mt-1">Unlock full commercial tools</p>
      </router-link>
      <router-link to="/finance/dossier" class="card-link">
        <p class="font-semibold">Finance dossier</p>
        <p class="text-sm text-gray-500 mt-1">Purchase history pack</p>
      </router-link>
      <router-link to="/knowledge" class="card-link">
        <p class="font-semibold">Knowledge Hub</p>
        <p class="text-sm text-gray-500 mt-1">Market and quality training</p>
      </router-link>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";
const stats = ref({ orders: 0, wallet: 0 });
const subActive = ref(false);
onMounted(async () => {
  try {
    const [o, w, s] = await Promise.allSettled([
      api.get("/orders"),
      api.get("/wallet"),
      api.get("/my-subscription"),
    ]);
    if (o.status === "fulfilled") {
      const d = o.value.data?.data || o.value.data || [];
      stats.value.orders = Array.isArray(d) ? d.length : 0;
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
