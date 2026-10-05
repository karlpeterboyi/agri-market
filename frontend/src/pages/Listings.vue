<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-green-700 mb-4">
      🌾 Marketplace Listings
    </h1>

    <div v-if="loading" class="text-gray-600 animate-pulse">
      Loading listings...
    </div>

    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="item in listings"
        :key="item.id"
        class="border p-4 rounded-lg shadow-sm bg-white flex flex-col justify-between"
      >
        <div>
          <!-- Commodity Name -->
          <h2 class="font-bold text-xl text-gray-800">
            {{ item.commodity?.name || item.crop || "Unknown Commodity" }}
          </h2>

          <!-- Category (Upgraded per Step 2.1.4) -->
          <div class="text-xs text-gray-500 font-medium mb-3">
            {{ item.commodity?.category?.name || "Uncategorized" }}
          </div>

          <div class="space-y-1 text-sm text-gray-600">
            <p>
              <span class="font-semibold text-gray-700">Quantity:</span> 
              {{ item.quantity }}
            </p>

            <p>
              <span class="font-semibold text-gray-700">Price:</span> 
              {{ item.price }} TZS
            </p>
          </div>
        </div>

        <div class="mt-4 flex items-center justify-between">
          <span 
            class="px-2.5 py-1 text-xs font-semibold rounded-full"
            :class="{
              'bg-green-100 text-green-800': item.status?.toLowerCase() === 'active',
              'bg-yellow-100 text-yellow-800': item.status?.toLowerCase() === 'pending',
              'bg-gray-100 text-gray-800': !['active', 'pending'].includes(item.status?.toLowerCase())
            }"
          >
            {{ item.status || 'Unknown' }}
          </span>
          
          <button class="bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-1.5 rounded transition">
            View Details
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getListings } from "../api/listings";

const listings = ref([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const res = await getListings();
    listings.value = res.data;
  } catch (err) {
    console.error("Error fetching my-listings:", err);
  } finally {
    loading.value = false;
  }
});
</script>
