<template>
  <div class="min-h-screen bg-gray-100">

    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto p-6">

        <h1 class="text-3xl font-bold text-green-700">
          Livestock Marketplace
        </h1>

        <p class="text-gray-500 mt-2">
          Buy and sell livestock across Tanzania.
        </p>

      </div>
    </div>

    <div class="max-w-7xl mx-auto mt-8 grid grid-cols-12 gap-6 pb-12">

      <!-- Sidebar -->
      <div class="col-span-3 bg-white rounded shadow p-5">

        <h2 class="font-bold mb-4">
          Filters
        </h2>

        <input
          v-model="filters.search"
          placeholder="Search..."
          class="w-full border rounded p-2 mb-4"
        />

        <select
          v-model="filters.species"
          class="w-full border rounded p-2 mb-4"
        >
          <option value="">All Species</option>
          <option>Cattle</option>
          <option>Goat</option>
          <option>Sheep</option>
          <option>Chicken</option>
          <option>Pig</option>
          <option>Rabbit</option>
          <option>Camel</option>
          <option>Fish</option>
        </select>

        <input
          v-model="filters.region"
          placeholder="Region"
          class="w-full border rounded p-2 mb-4"
        />

        <!-- Sort Filter -->
        <select
          v-model="filters.sort"
          class="w-full border rounded p-2 mb-4"
        >
          <option value="">Newest</option>
          <option value="price_low">
            Lowest Price
          </option>
          <option value="price_high">
            Highest Price
          </option>
          <option value="oldest">
            Oldest
          </option>
        </select>

        <input
          v-model="filters.min_price"
          type="number"
          placeholder="Minimum Price"
          class="w-full border rounded p-2 mb-4"
        />

        <input
          v-model="filters.max_price"
          type="number"
          placeholder="Maximum Price"
          class="w-full border rounded p-2 mb-4"
        />

        <button
          @click="loadListings(1)"
          class="w-full bg-green-700 text-white py-2 rounded"
        >
          Apply Filters
        </button>

      </div>

      <!-- Listings -->
      <div class="col-span-9">

        <div
          v-if="loading"
          class="text-center py-16"
        >
          Loading livestock...
        </div>

        <div v-else>
          <div class="grid md:grid-cols-3 gap-6">

            <div
              v-for="animal in listings"
              :key="animal.id"
              class="bg-white rounded shadow hover:shadow-lg transition flex flex-col justify-between overflow-hidden"
            >

              <!-- Clickable Content Wrapper -->
              <RouterLink :to="`/livestock/${animal.id}`" class="block group">
                <img
                  :src="animal.image || '/placeholder-animal.jpg'"
                  class="h-48 w-full object-cover group-hover:opacity-90 transition"
                  :alt="animal.breed"
                />

                <div class="p-4">

                  <div class="text-lg font-bold group-hover:text-green-700 transition">
                    {{ animal.breed }}
                  </div>

                  <div class="text-gray-500">
                    {{ animal.species }}
                  </div>

                  <div class="text-green-700 font-bold text-xl mt-2">
                    TZS {{ Number(animal.price).toLocaleString() }}
                  </div>

                  <div class="mt-2 text-sm text-gray-500">
                    {{ animal.region }}
                  </div>

                  <div class="mt-1 text-sm">
                    Age: {{ animal.age }}
                  </div>

                  <div class="mt-1 text-sm">
                    Weight: {{ animal.weight }} kg
                  </div>

                </div>
              </RouterLink>

              <!-- Action Button -->
              <div class="px-4 pb-4">
                <RouterLink
                  :to="`/livestock/${animal.id}`"
                  class="block bg-green-700 text-white text-center py-2 rounded hover:bg-green-800 transition"
                >
                  View Details
                </RouterLink>
              </div>

            </div>

          </div>

          <!-- Pagination Controls -->
          <div class="flex justify-center mt-10">

            <button
              v-if="currentPage > 1"
              @click="changePage(currentPage - 1)"
              class="px-4 py-2 border rounded mr-2 hover:bg-gray-50"
            >
              Previous
            </button>

            <span class="px-4 py-2">
              Page {{ currentPage }}
            </span>

            <button
              v-if="hasMore"
              @click="changePage(currentPage + 1)"
              class="px-4 py-2 border rounded ml-2 hover:bg-gray-50"
            >
              Next
            </button>

          </div>
        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import api from "../../services/api";

const loading = ref(true);
const listings = ref([]);
const currentPage = ref(1);
const hasMore = ref(false);

const filters = reactive({
  search: "",
  species: "",
  region: "",
  min_price: "",
  max_price: "",
  sort: ""
});

async function loadListings(page = 1) {
  loading.value = true;

  const res = await api.get("/livestock", {
    params: {
      ...filters,
      page
    }
  });

  listings.value = res.data.data;
  currentPage.value = res.data.current_page;
  hasMore.value = res.data.next_page_url !== null;

  loading.value = false;
}

function changePage(page) {
  loadListings(page);
}

onMounted(() => {
  loadListings();
});
</script>
