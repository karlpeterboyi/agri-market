<template>
  <div>
    <h2 class="text-3xl font-bold mb-6">Loan Products</h2>
    <div v-if="loading" class="text-gray-500">Loading…</div>
    <div v-else class="grid md:grid-cols-2 gap-6">
      <div v-for="p in products" :key="p.id" class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start">
          <h3 class="text-xl font-bold">{{ p.name }}</h3>
          <span v-if="p.featured" class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Featured</span>
        </div>
        <p class="text-gray-500 text-sm mt-1">{{ p.loan_type }} · {{ p.financial_institution?.name || 'Institution' }}</p>
        <p class="mt-3 text-sm">{{ p.description }}</p>
        <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
          <div><span class="text-gray-500">Amount</span><br /><strong>TZS {{ Number(p.minimum_amount).toLocaleString() }} – {{ Number(p.maximum_amount).toLocaleString() }}</strong></div>
          <div><span class="text-gray-500">Interest</span><br /><strong>{{ p.interest_rate }}% p.a.</strong></div>
          <div><span class="text-gray-500">Duration</span><br /><strong>{{ p.minimum_duration_months }}–{{ p.maximum_duration_months }} months</strong></div>
          <div><span class="text-gray-500">Collateral</span><br /><strong>{{ p.requires_collateral ? 'Required' : 'Not required' }}</strong></div>
        </div>
        <router-link :to="`/farmer/loans/apply?product=${p.id}`" class="inline-block mt-4 bg-green-600 text-white px-4 py-2 rounded font-medium">Apply</router-link>
      </div>
      <div v-if="!products.length" class="text-gray-400 col-span-2">No loan products available.</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getLoanProducts } from "../../api/finance";

const products = ref([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const { data } = await getLoanProducts();
    products.value = data.data || data;
  } finally {
    loading.value = false;
  }
});
</script>
