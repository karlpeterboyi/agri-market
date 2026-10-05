<template>
  <div>
    <h2 class="text-2xl font-bold mb-2">Purchase orders</h2>
    <p class="text-sm text-gray-500 mb-6">Orders where you buy raw materials from the marketplace.</p>
    <div v-if="loading" class="text-gray-400">Loading…</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>
    <div v-else class="space-y-3">
      <div v-for="o in orders" :key="o.id" class="bg-white rounded-lg shadow p-4 flex flex-col sm:flex-row sm:justify-between gap-2">
        <div>
          <p class="font-semibold">Order #{{ o.id }} · {{ o.listing?.title || o.listing?.commodity?.name || "Item" }}</p>
          <p class="text-sm text-gray-500">
            Qty {{ o.quantity }} · TZS {{ Number(o.total_amount || o.amount || 0).toLocaleString() }} · {{ o.status }}
          </p>
        </div>
        <div class="flex gap-2">
          <router-link :to="`/buyer/orders/${o.id}`" class="text-sm text-green-700 font-medium">Details</router-link>
          <router-link :to="`/buyer/orders/${o.id}/transport`" class="text-sm border border-green-600 text-green-700 px-2 py-1 rounded">Transport</router-link>
        </div>
      </div>
      <p v-if="!orders.length" class="text-gray-400 text-sm">No purchase orders yet. Start from the marketplace.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const orders = ref([]);
const loading = ref(true);
const error = ref("");

onMounted(async () => {
  try {
    const { data } = await api.get("/orders", { params: { as: "buyer" } });
    orders.value = data.data || data || [];
    if (!Array.isArray(orders.value)) orders.value = [];
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load orders";
  } finally {
    loading.value = false;
  }
});
</script>
