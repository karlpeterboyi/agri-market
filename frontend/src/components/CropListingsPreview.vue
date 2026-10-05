<template>
  <div>

    <div
      v-if="loading"
      class="text-gray-500"
    >
      Loading crop listings...
    </div>

    <div
      v-else
      class="grid md:grid-cols-2 lg:grid-cols-2 gap-4"
    >

      <div
        v-for="item in listings"
        :key="item.id"
        class="border rounded-lg bg-white p-4 hover:shadow-lg transition"
      >

        <h3 class="text-lg font-bold text-green-700">
          {{ item.commodity?.name || "Unknown Commodity" }}
        </h3>

        <div class="text-sm text-gray-500 mb-2">
          {{ item.commodity?.category?.name || "Uncategorized" }}
        </div>

        <div class="space-y-1 text-sm">

          <p>
            <strong>Seller:</strong>
            {{ item.seller?.name }}
          </p>

          <p>
            <strong>Quantity:</strong>
            {{ item.quantity }}
          </p>

          <p>
            <strong>Price:</strong>
            TZS {{ Number(item.price).toLocaleString() }}
          </p>

          <p>
            <strong>Region:</strong>
            {{ item.region }}
          </p>

        </div>

        <router-link
          :to="`/listings/${item.id}`"
          class="mt-4 inline-block text-green-700 font-semibold hover:underline"
        >
          View Details →
        </router-link>

      </div>

    </div>

    <div class="mt-6 text-center">

      <router-link
        to="/listings"
        class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
      >
        View All Crop Listings
      </router-link>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";

const listings = ref([]);
const loading = ref(true);

async function loadListings() {

    try {

        const res = await api.get("/listings");

        listings.value = res.data.data
            ? res.data.data.slice(0, 4)
            : res.data.slice(0, 4);

    } catch (err) {

        console.error(err);

    } finally {

        loading.value = false;

    }

}

onMounted(loadListings);
</script>