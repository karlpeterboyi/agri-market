<template>
  <div class="max-w-6xl mx-auto p-4 sm:p-6">
    <h1 class="text-2xl font-bold mb-2">Agricultural Insurance</h1>
    <p class="text-gray-500 mb-6 text-sm">Crop, livestock, weather-index and equipment cover from partner insurers.</p>
    <p v-if="loading" class="text-gray-400">Loading…</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="p in products" :key="p.id" class="bg-white rounded-xl shadow p-5">
        <p class="text-xs uppercase text-green-700 font-semibold">{{ p.insurance_type }}</p>
        <h2 class="font-bold text-lg mt-1">{{ p.name }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ p.institution?.name }}</p>
        <p class="text-sm mt-3 line-clamp-3">{{ p.description }}</p>
        <p class="font-semibold text-green-800 mt-3" v-if="p.premium_rate">
          Premium ≈ {{ (Number(p.premium_rate) * 100).toFixed(1) }}% of sum insured
        </p>
        <button
          type="button"
          class="mt-4 w-full bg-green-700 text-white rounded-lg py-2 text-sm font-semibold"
          @click="apply(p)"
        >
          Apply
        </button>
      </div>
    </div>
    <p v-if="!loading && !products.length" class="text-gray-400">No insurance products yet.</p>
    <p v-if="msg" class="mt-4 text-sm text-green-700">{{ msg }}</p>
  </div>
</template>
<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";
const products = ref([]);
const loading = ref(true);
const msg = ref("");
onMounted(async () => {
  try {
    const { data } = await api.get("/insurance-products");
    products.value = data.data || data || [];
  } catch {
    products.value = [];
  } finally {
    loading.value = false;
  }
});
async function apply(p) {
  const sum = prompt("Sum insured (TZS)?", "1000000");
  if (!sum) return;
  try {
    await api.post(`/insurance-products/${p.id}/apply`, { sum_insured: Number(sum) });
    msg.value = "Application submitted for " + p.name;
  } catch (e) {
    alert(e.response?.data?.message || "Login required to apply");
  }
}
</script>
