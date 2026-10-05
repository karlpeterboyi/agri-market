<template>
  <div class="p-6">
    <h1 class="text-3xl font-bold mb-6">
      Marketplace
    </h1>

    <div v-if="loading" class="text-gray-500">
      Loading...
    </div>

    <div v-else class="grid md:grid-cols-3 gap-6">

      <div
        v-for="listing in listings"
        :key="listing.id"
        class="bg-white rounded shadow p-5"
      >

        <h2 class="text-xl font-semibold">
          {{ listing.commodity?.name }}
        </h2>

        <p class="mt-2">
          Quantity:
          {{ listing.quantity }}
          {{ listing.unit }}
        </p>

        <p>
          Grade:
          {{ listing.grade }}
        </p>

        <p>
          Price:
          TZS {{ Number(listing.price).toLocaleString() }}
        </p>

        <p>
          Farmer:
          {{ listing.seller?.name }}
        </p>

        <p>
          {{ listing.region }}, {{ listing.district }}
        </p>

        <button
          @click="buy(listing)"
          class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
        >
          Buy
        </button>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";
import { useRouter } from "vue-router";

const router = useRouter();

const listings = ref([]);
const loading = ref(true);

async function loadListings() {
  try {
    const res = await api.get("/listings");
    listings.value = res.data;
  } finally {
    loading.value = false;
  }
}

function buy(listing) {
  router.push(`/buyer/checkout/${listing.id}`);
}

onMounted(loadListings);
</script>