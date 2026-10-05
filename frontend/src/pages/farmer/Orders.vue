<template>
  <div class="p-6">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">
      Farmer Orders
    </h1>

    <div v-if="loading" class="text-gray-600 animate-pulse">
      Loading orders...
    </div>

    <div v-else-if="error" class="text-red-600 font-medium">
      {{ error }}
    </div>

    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div 
        v-for="order in orders" 
        :key="order.id" 
        class="border p-5 rounded-lg shadow-sm bg-white flex flex-col justify-between"
      >
        <div class="space-y-2">
          <div>
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Buyer</span>
            <h2 class="font-bold text-lg text-gray-900">
              {{ order.buyer?.name || "Anonymous Buyer" }}
            </h2>
          </div>

          <div>
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Commodity</span>
            <p class="font-medium text-gray-800">
              {{ order.listing?.commodity?.name || "Not Specified" }}
            </p>
          </div>

          <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
            <div>
              <span class="block text-xs text-gray-500">Quantity</span>
              <span class="font-semibold text-gray-800">{{ order.quantity }}</span>
            </div>
            <div>
              <span class="block text-xs text-gray-500">Amount</span>
              <span class="font-semibold text-green-700">{{ Number(order.total_amount).toLocaleString() }} TZS</span>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-xs text-gray-500">Status</span>
          <span 
            class="px-3 py-1 text-xs font-semibold rounded-full"
            :class="{
              'bg-blue-100 text-blue-800': order.status?.toLowerCase() === 'pending',
              'bg-green-100 text-green-800': order.status?.toLowerCase() === 'completed',
              'bg-red-100 text-red-800': order.status?.toLowerCase() === 'cancelled',
              'bg-gray-100 text-gray-800': !['pending', 'completed', 'cancelled'].includes(order.status?.toLowerCase())
            }"
          >
            {{ order.status || 'Unknown' }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
// Imported the updated API client module
import api from "../../services/api";

const orders = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  try {
    // Updated API call path and structure per instructions
    const res = await api.get("/orders");
    orders.value = res.data;
  } catch (err) {
    console.error("Error fetching farmer orders:", err);
    error.value = "Failed to load orders. Please try again later.";
  } finally {
    loading.value = false;
  }
});
</script>
