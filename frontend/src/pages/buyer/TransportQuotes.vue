<template>
  <div class="max-w-3xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-2">Transport quotes</h1>
    <p class="text-sm text-gray-500 mb-4">
      Order #{{ orderId }} — distance from pickup to delivery × price/km. Nearest/cheapest trucks first.
    </p>

    <div class="grid sm:grid-cols-2 gap-3 mb-4">
      <input v-model="form.delivery_region" placeholder="Delivery region" class="border rounded px-3 py-2" />
      <input v-model="form.pickup_region" placeholder="Pickup region (optional)" class="border rounded px-3 py-2" />
      <button type="button" class="sm:col-span-2 bg-green-600 text-white py-2 rounded font-semibold" @click="load">
        Get quotes
      </button>
    </div>

    <p v-if="result.distance_km" class="text-sm mb-4">
      Trip ≈ <strong>{{ result.distance_km }} km</strong> ({{ result.distance_miles }} mi)
    </p>
    <p v-if="result.message" class="text-amber-700 text-sm mb-4">{{ result.message }}</p>
    <p v-if="error" class="text-red-600 text-sm mb-4">{{ error }}</p>

    <div v-for="q in result.quotes || []" :key="q.vehicle_id" class="bg-white rounded-lg shadow p-4 mb-3 flex justify-between gap-3">
      <div>
        <p class="font-bold">{{ q.transporter_name }} · {{ q.vehicle_name }}</p>
        <p class="text-sm text-gray-500">
          {{ q.vehicle_type }} · {{ q.capacity }} t · TZS {{ Number(q.price_per_km).toLocaleString() }}/km
        </p>
        <p class="text-xs text-gray-400">Billable {{ q.billable_km }} km (trip + empty leg)</p>
      </div>
      <div class="text-right">
        <p class="text-lg font-bold text-green-700">TZS {{ Number(q.total_cost).toLocaleString() }}</p>
        <button type="button" class="text-sm bg-green-600 text-white px-3 py-1 rounded mt-1" @click="select(q)">
          Select
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import api from "../../services/api";

const route = useRoute();
const orderId = computed(() => route.params.orderId);
const form = ref({ delivery_region: "", pickup_region: "" });
const result = ref({ quotes: [] });
const error = ref("");

async function load() {
  error.value = "";
  try {
    const { data } = await api.get(`/orders/${orderId.value}/transport-quotes`, { params: form.value });
    result.value = data;
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load quotes";
  }
}

async function select(q) {
  try {
    await api.post(`/orders/${orderId.value}/transport-quotes/select`, {
      vehicle_id: q.vehicle_id,
      transporter_id: q.transporter_id,
    });
    alert("Transporter selected. Logistics can be assigned next.");
  } catch (e) {
    alert(e.response?.data?.message || "Select failed");
  }
}

onMounted(load);
</script>
