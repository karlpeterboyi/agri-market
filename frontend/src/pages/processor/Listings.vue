<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
      <div>
        <h2 class="text-2xl font-bold">Processed goods listings</h2>
        <p class="text-sm text-gray-500">Products you sell after processing</p>
      </div>
      <router-link to="/processor/listings/create" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold text-sm">
        + New listing
      </router-link>
    </div>
    <div v-if="loading" class="text-gray-400">Loading…</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>
    <div v-else class="grid sm:grid-cols-2 gap-4">
      <div v-for="l in listings" :key="l.id" class="bg-white rounded-lg shadow p-4">
        <p class="font-semibold">{{ l.title || l.commodity?.name || "Listing #" + l.id }}</p>
        <p class="text-sm text-gray-500">TZS {{ Number(l.price || l.unit_price || 0).toLocaleString() }} · {{ l.status || "active" }}</p>
      </div>
      <p v-if="!listings.length" class="text-gray-400 text-sm sm:col-span-2">No listings yet.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const listings = ref([]);
const loading = ref(true);
const error = ref("");

onMounted(async () => {
  try {
    const { data } = await api.get("/my-listings");
    listings.value = data.data || data || [];
    if (!Array.isArray(listings.value)) listings.value = [];
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load listings";
  } finally {
    loading.value = false;
  }
});
</script>
