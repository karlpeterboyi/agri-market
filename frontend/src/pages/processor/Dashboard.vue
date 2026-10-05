<template>
  <div>
    <h2 class="text-2xl sm:text-3xl font-bold mb-2">Processor Dashboard</h2>
    <p class="text-gray-500 mb-6">
      Source raw produce, process it, and sell finished goods through Agri-market escrow trade.
    </p>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Purchase orders</p>
        <p class="text-2xl font-bold">{{ stats.buyOrders }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Sell listings</p>
        <p class="text-2xl font-bold">{{ stats.listings }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Sales orders</p>
        <p class="text-2xl font-bold">{{ stats.sellOrders }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Wallet (TZS)</p>
        <p class="text-lg font-bold">{{ Number(stats.wallet || 0).toLocaleString() }}</p>
      </div>
    </div>

    <p class="text-sm font-semibold text-gray-600 mb-3">Core flows</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
      <router-link to="/marketplace" class="card-link">
        <p class="font-semibold">1. Source produce</p>
        <p class="text-sm text-gray-500 mt-1">Browse marketplace and buy from farmers</p>
      </router-link>
      <router-link to="/processor/orders" class="card-link">
        <p class="font-semibold">2. Purchase orders</p>
        <p class="text-sm text-gray-500 mt-1">Track buys, escrow and delivery</p>
      </router-link>
      <router-link to="/processor/listings/create" class="card-link">
        <p class="font-semibold">3. List processed goods</p>
        <p class="text-sm text-gray-500 mt-1">Sell flour, packaged produce, etc.</p>
      </router-link>
      <router-link to="/processor/listings" class="card-link">
        <p class="font-semibold">My sell listings</p>
        <p class="text-sm text-gray-500 mt-1">Manage what you offer on the market</p>
      </router-link>
      <router-link to="/processor/sales" class="card-link">
        <p class="font-semibold">Sales orders</p>
        <p class="text-sm text-gray-500 mt-1">Orders where you are the seller</p>
      </router-link>
      <router-link to="/processor/wallet" class="card-link">
        <p class="font-semibold">Wallet & withdrawals</p>
        <p class="text-sm text-gray-500 mt-1">Balances from released escrow</p>
      </router-link>
    </div>

    <p class="text-sm font-semibold text-gray-600 mb-3">Support</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <router-link to="/finance/subscriptions" class="card-link">
        <p class="font-semibold">Subscription</p>
        <p class="text-sm text-gray-500 mt-1">Required to list processed products</p>
      </router-link>
      <router-link to="/finance/dossier" class="card-link">
        <p class="font-semibold">Finance dossier</p>
        <p class="text-sm text-gray-500 mt-1">Credibility pack for lenders</p>
      </router-link>
      <router-link to="/knowledge" class="card-link">
        <p class="font-semibold">Knowledge Hub</p>
        <p class="text-sm text-gray-500 mt-1">Training and research</p>
      </router-link>
    </div>

    <p v-if="error" class="text-red-600 text-sm mt-4">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const stats = ref({ buyOrders: 0, sellOrders: 0, listings: 0, wallet: 0 });
const error = ref("");

onMounted(async () => {
  try {
    const [ordersRes, listingsRes, walletRes] = await Promise.allSettled([
      api.get("/orders"),
      api.get("/my-listings"),
      api.get("/wallet"),
    ]);

    if (ordersRes.status === "fulfilled") {
      const orders = ordersRes.value.data?.data || ordersRes.value.data || [];
      const list = Array.isArray(orders) ? orders : [];
      // heuristic: buyer vs seller if fields present
      stats.value.buyOrders = list.filter((o) => o.buyer_id || o.role === "buyer" || !o.seller_side).length || list.length;
      stats.value.sellOrders = list.filter((o) => o.seller_id || o.is_seller).length;
    }
    if (listingsRes.status === "fulfilled") {
      const L = listingsRes.value.data?.data || listingsRes.value.data || [];
      stats.value.listings = Array.isArray(L) ? L.length : L?.total || 0;
    }
    if (walletRes.status === "fulfilled") {
      const w = walletRes.value.data?.data || walletRes.value.data || {};
      stats.value.wallet = w.available_balance ?? w.balance ?? 0;
    }
  } catch (e) {
    error.value = e.response?.data?.message || "";
  }
});
</script>

<style scoped>
.card-link {
  display: block;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgb(0 0 0 / 0.08);
  padding: 1.25rem;
  transition: box-shadow 0.15s;
}
.card-link:hover {
  box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
}
</style>
