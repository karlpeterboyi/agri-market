<template>
  <div>
    <h2 class="text-2xl font-bold mb-2">Sales orders</h2>
    <p class="text-sm text-gray-500 mb-6">Orders for your processed-goods listings (you are the seller).</p>
    <div v-if="loading" class="text-gray-400">Loading…</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>
    <div v-else class="space-y-3">
      <div v-for="o in orders" :key="o.id" class="bg-white rounded-lg shadow p-4">
        <p class="font-semibold">Order #{{ o.id }} · {{ o.listing?.title || "Listing" }}</p>
        <p class="text-sm text-gray-500">
          Buyer: {{ o.buyer?.name || "—" }} · TZS {{ Number(o.total_amount || 0).toLocaleString() }} · {{ o.status }}
        </p>
      </div>
      <p v-if="!orders.length" class="text-gray-400 text-sm">No sales yet. List processed goods to start selling.</p>
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
    const { data } = await api.get("/orders", { params: { as: "seller" } });
    orders.value = data.data || data || [];
    if (!Array.isArray(orders.value)) orders.value = [];
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load sales";
  } finally {
    loading.value = false;
  }
});
</script>
