<template>
  <div class="bg-gray-50 min-h-screen">

    <!-- Hero -->
    <section class="bg-green-700 text-white py-12">
      <div class="max-w-7xl mx-auto px-6">

        <h1 class="text-4xl font-bold">
          🚜 Machinery Marketplace
        </h1>

        <p class="mt-3 text-green-100">
          Find tractors, harvesters, planters, irrigation equipment,
          and other agricultural machinery available for rent or sale.
        </p>

      </div>
    </section>

    <div class="max-w-7xl mx-auto px-6 py-8">

      <div class="grid lg:grid-cols-4 gap-8">

        <!-- Filters -->

        <div>

          <div class="bg-white rounded-xl shadow p-5 space-y-4">

            <h2 class="font-bold text-lg">
              Filters
            </h2>

            <input
              v-model="filters.search"
              placeholder="Search machinery..."
              class="w-full border rounded-lg p-2"
            >

            <select
              v-model="filters.category"
              class="w-full border rounded-lg p-2"
            >
              <option value="">
                All Categories
              </option>

              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>

            </select>

            <select
              v-model="filters.brand"
              class="w-full border rounded-lg p-2"
            >

              <option value="">
                All Brands
              </option>

              <option
                v-for="brand in brands"
                :key="brand.id"
                :value="brand.id"
              >
                {{ brand.name }}
              </option>

            </select>

            <input
              v-model="filters.region"
              placeholder="Region"
              class="w-full border rounded-lg p-2"
            >

            <input
              type="number"
              v-model="filters.min_price"
              placeholder="Minimum Price"
              class="w-full border rounded-lg p-2"
            >

            <input
              type="number"
              v-model="filters.max_price"
              placeholder="Maximum Price"
              class="w-full border rounded-lg p-2"
            >

            <label class="flex items-center gap-2">

              <input
                type="checkbox"
                v-model="filters.for_rent"
              >

              For Rent

            </label>

            <label class="flex items-center gap-2">

              <input
                type="checkbox"
                v-model="filters.for_sale"
              >

              For Sale

            </label>

            <label class="flex items-center gap-2">

              <input
                type="checkbox"
                v-model="filters.featured"
              >

              Featured Only

            </label>

            <button
              @click="loadMachinery"
              class="w-full bg-green-600 text-white py-2 rounded-lg"
            >
              Apply Filters
            </button>

          </div>

        </div>

        <!-- Listings -->

        <div class="lg:col-span-3">

          <div
            v-if="loading"
            class="text-center py-24 text-gray-500"
          >
            Loading machinery...
          </div>

          <div
            v-else
            class="grid md:grid-cols-2 xl:grid-cols-3 gap-6"
          >

            <MachineryCard
              v-for="listing in machinery"
              :key="listing.id"
              :listing="listing"
            />

          </div>

          <div
            v-if="!loading && machinery.length===0"
            class="bg-white rounded-xl shadow p-10 text-center"
          >

            No machinery found.

          </div>

        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

import MachineryCard from "../../components/MachineryCard.vue";

import {
    getMachinery,
    getMachineryCategories,
    getMachineryBrands
}
from "../../api/machinery";

const loading = ref(true);

const machinery = ref([]);

const categories = ref([]);

const brands = ref([]);

const filters = ref({

    search: "",

    category: "",

    brand: "",

    region: "",

    min_price: "",

    max_price: "",

    for_rent: false,

    for_sale: false,

    featured: false

});

async function loadMachinery(){

    loading.value=true;

    const params={};

    Object.keys(filters.value).forEach(key=>{

        if(filters.value[key]!=="" &&
           filters.value[key]!==false){

            params[key]=filters.value[key];

        }

    });

    try{

        const res=await getMachinery(params);

        machinery.value=res.data.data ?? res.data;

    }

    finally{

        loading.value=false;

    }

}

async function loadFilters(){

    categories.value=(await getMachineryCategories()).data;

    brands.value=(await getMachineryBrands()).data;

}

onMounted(async()=>{

    await loadFilters();

    await loadMachinery();

});
</script>