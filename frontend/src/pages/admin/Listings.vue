<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:justify-between gap-3 mb-6">
      <h1 class="text-2xl sm:text-3xl font-bold">Listings</h1>
      <button @click="load" class="text-sm bg-green-700 text-white px-4 py-2 rounded">Refresh</button>
    </div>

    <div v-if="error" class="bg-red-50 text-red-700 text-sm p-3 rounded mb-4">{{ error }}</div>
    <div v-if="loading" class="text-gray-500">Loading…</div>

    <div v-else class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm min-w-[640px]">
        <thead class="bg-green-700 text-white text-left">
          <tr>
            <th class="p-3">Commodity</th>
            <th class="p-3">Farmer</th>
            <th class="p-3">Region</th>
            <th class="p-3">Price</th>
            <th class="p-3">Quantity</th>
            <th class="p-3">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="listing in listings" :key="listing.id" class="border-t">
            <td class="p-3 font-medium">{{ listing.commodity?.name || '—' }}</td>
            <td class="p-3">{{ listing.seller?.name || '—' }}</td>
            <td class="p-3">{{ listing.region || '—' }}</td>
            <td class="p-3">TZS {{ Number(listing.price || 0).toLocaleString() }}</td>
            <td class="p-3">{{ listing.quantity }} {{ listing.unit }}</td>
            <td class="p-3">{{ listing.status }}</td>
          </tr>
          <tr v-if="!listings.length">
            <td colspan="6" class="p-6 text-gray-400 text-center">
              No product listings yet. Seed with
              <code class="text-xs">php artisan db:seed --class=ProductListingSeeder</code>
              or have farmers publish listings.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";
import { unwrapList } from "../../utils/apiData";

const listings = ref([]);
const loading = ref(true);
const error = ref("");

async function load() {
  loading.value = true;
  error.value = "";
  try {
    const res = await api.get("/admin/listings");
    listings.value = unwrapList(res.data);
    // Admin dashboard returns plain array sometimes
    if (!listings.value.length && Array.isArray(res.data)) {
      listings.value = res.data;
    }
  } catch (e) {
    error.value =
      e.response?.data?.message ||
      `Failed to load listings (${e.response?.status || "network"}). Login as admin and ensure user.role middleware is active.`;
    listings.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
