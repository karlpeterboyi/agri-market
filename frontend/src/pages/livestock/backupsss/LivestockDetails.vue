<template>
  <div class="max-w-7xl mx-auto p-6">

    <div
      v-if="loading"
      class="text-center py-20"
    >
      Loading...
    </div>

    <div
      v-else
      class="grid lg:grid-cols-3 gap-10"
    >

      <!-- LEFT -->
      <div class="lg:col-span-2">

        <img
          :src="mainImage"
          class="w-full h-[500px] object-cover rounded-xl shadow"
        >

        <div class="grid grid-cols-5 gap-3 mt-4">
          <img
            v-for="image in listing.images"
            :key="image.id"
            :src="image.image"
            @click="mainImage = image.image"
            class="cursor-pointer h-24 object-cover rounded border hover:border-green-700"
          >
        </div>

        <!-- Description (Below gallery) -->
        <div class="bg-white rounded-xl shadow p-6 mt-8">
          <h2 class="text-2xl font-bold mb-4">
            Description
          </h2>

          <p>
            {{ listing.description }}
          </p>
        </div>

      </div>

      <!-- RIGHT -->
      <div>

        <h1 class="text-3xl font-bold">
          {{ listing.species }}
        </h1>

        <p class="text-gray-500">
          {{ listing.breed }}
        </p>

        <div class="text-4xl font-bold text-green-700 mt-6">
          TZS {{ Number(listing.price).toLocaleString() }}
        </div>

        <!-- Seller Card (Under the price) -->
        <div class="bg-white shadow rounded-xl p-5 mt-8">
          <h2 class="font-bold text-xl mb-4">
            Seller
          </h2>

          <div class="font-semibold">
            {{ listing.seller?.name }}
          </div>

          <div class="text-gray-500">
            {{ listing.region }}
          </div>

          <div class="mt-5">
            <button class="w-full bg-green-700 text-white py-3 rounded">
              Verified Seller
            </button>
          </div>

          <a
            :href="'https://wa.me/' + listing.seller?.phone"
            target="_blank"
            class="block mt-4"
          >
            <button class="w-full bg-green-600 text-white py-3 rounded">
              WhatsApp Seller
            </button>
          </a>

          <a :href="'tel:' + listing.seller?.phone">
            <button class="w-full bg-blue-600 text-white py-3 rounded mt-3">
              Call Seller
            </button>
          </a>
        </div>

        <!-- Animal Information (Below seller card) -->
        <div class="bg-white shadow rounded-xl p-6 mt-8">
          <h2 class="text-xl font-bold mb-5">
            Animal Information
          </h2>

          <div class="space-y-3">
            <div>
              Species:
              <strong>
                {{ listing.species }}
              </strong>
            </div>

            <div>
              Breed:
              <strong>
                {{ listing.breed }}
              </strong>
            </div>

            <div>
              Gender:
              <strong>
                {{ listing.gender }}
              </strong>
            </div>

            <div>
              Age:
              <strong>
                {{ listing.age }} Months
              </strong>
            </div>

            <div>
              Purpose:
              <strong>
                {{ listing.purpose }}
              </strong>
            </div>

            <div>
              Health:
              <strong>
                {{ listing.health_status }}
              </strong>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "../../services/api";

const loading = ref(true);
const listing = ref({});
const mainImage = ref("");
const route = useRoute();

async function load() {
  const res = await api.get("/livestock/" + route.params.id);
  listing.value = res.data;

  if (res.data.images?.length) {
    mainImage.value = res.data.images[0].image;
  }

  loading.value = false;
}

onMounted(load);
</script>
