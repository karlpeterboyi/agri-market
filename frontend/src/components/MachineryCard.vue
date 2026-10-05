<template>
  <div
    class="bg-white rounded-xl shadow hover:shadow-xl transition overflow-hidden border border-gray-100 flex flex-col"
  >
    <!-- Image -->
    <div class="relative h-56 overflow-hidden">
      <img
        :src="listing.cover_photo || placeholder"
        class="w-full h-full object-cover hover:scale-105 transition duration-500"
      />

      <!-- Featured -->
      <div
        v-if="listing.featured"
        class="absolute top-3 left-3 bg-yellow-500 text-white text-xs px-2 py-1 rounded"
      >
        ⭐ Featured
      </div>

      <!-- Rental -->
      <div
        v-if="listing.for_rent"
        class="absolute top-3 right-3 bg-green-600 text-white text-xs px-2 py-1 rounded"
      >
        🚜 For Rent
      </div>

      <!-- Sale -->
      <div
        v-if="listing.for_sale"
        class="absolute top-11 right-3 bg-blue-600 text-white text-xs px-2 py-1 rounded"
      >
        💰 For Sale
      </div>
    </div>

    <!-- Content -->
    <div class="p-4 flex-1 flex flex-col">

      <h2 class="font-bold text-lg text-gray-800">
        {{ listing.title }}
      </h2>

      <p class="text-sm text-gray-500 mt-1">
        {{ listing.category?.name }}
      </p>

      <div class="mt-3 text-sm text-gray-700 space-y-1">

        <p>
          <strong>Brand:</strong>
          {{ listing.brand?.name }}
        </p>

        <p>
          <strong>Model:</strong>
          {{ listing.model?.name }}
        </p>

        <p v-if="listing.horsepower">
          <strong>Power:</strong>
          {{ listing.horsepower }} HP
        </p>

        <p>
          <strong>Location:</strong>
          {{ listing.region }}
        </p>

      </div>

      <!-- Prices -->
      <div class="mt-4">

        <div
          v-if="listing.for_rent && listing.rental_price"
          class="text-green-700 font-bold text-lg"
        >
          TZS {{ formatPrice(listing.rental_price) }}

          <span class="text-sm font-normal text-gray-500">
            / {{ listing.rental_period }}
          </span>
        </div>

        <div
          v-if="listing.for_sale && listing.sale_price"
          class="text-blue-700 font-bold text-lg"
        >
          TZS {{ formatPrice(listing.sale_price) }}
        </div>

      </div>

      <!-- Availability -->
      <div class="mt-3">

        <span
          v-if="listing.available"
          class="inline-flex px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs"
        >
          Available
        </span>

        <span
          v-else
          class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs"
        >
          Booked
        </span>

      </div>

      <!-- Rating -->
      <div class="mt-4 flex items-center justify-between">

        <div class="text-yellow-500">

          ⭐ {{ averageRating }}

          <span class="text-gray-500 text-sm">
            ({{ reviewCount }})
          </span>

        </div>

      </div>

      <!-- Buttons -->
      <div class="mt-5">

        <router-link
    :to="{ name: 'machinery.show', params: { id: listing.id } }"
    class="block text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg"
>
    View Details
</router-link>

      </div>

    </div>

  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  listing: {
    type: Object,
    required: true,
  },
});

const placeholder =
  "https://via.placeholder.com/800x500?text=Machinery";

const reviewCount = computed(() => {
  return props.listing.reviews?.length || 0;
});

const averageRating = computed(() => {
  if (!props.listing.reviews?.length) return "0.0";

  const total = props.listing.reviews.reduce(
    (sum, review) => sum + Number(review.rating),
    0
  );

  return (total / props.listing.reviews.length).toFixed(1);
});

function formatPrice(value) {
  return Number(value).toLocaleString();
}
</script>