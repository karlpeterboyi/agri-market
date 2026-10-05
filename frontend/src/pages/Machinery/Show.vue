<template>
  <div class="bg-gray-50 min-h-screen">

    <div v-if="loading" class="py-24 text-center">
      Loading machinery...
    </div>

    <div v-else-if="listing">

      <!-- Hero -->

      <div class="bg-white">

        <div class="max-w-7xl mx-auto px-6 py-8">

          <div class="grid lg:grid-cols-2 gap-8">

            <!-- Image -->

            <div>

              <MachineryGallery
                :images="gallery"
              />

            </div>

            <!-- Details -->

            <div>

              <div class="flex gap-2 mb-3">

                <span
                  v-if="listing.featured"
                  class="bg-yellow-500 text-white px-3 py-1 rounded"
                >
                  Featured
                </span>

                <span
                  v-if="listing.available"
                  class="bg-green-600 text-white px-3 py-1 rounded"
                >
                  Available
                </span>

              </div>

              <h1 class="text-4xl font-bold">

                {{ listing.title }}

              </h1>

              <p class="text-gray-500 mt-2">

                {{ listing.category?.name }}

              </p>

              <div class="grid grid-cols-2 gap-4 mt-8">

                <div>

                  <h3 class="font-semibold">
                    Brand
                  </h3>

                  {{ listing.brand?.name }}

                </div>

                <div>

                  <h3 class="font-semibold">
                    Model
                  </h3>

                  {{ listing.model?.name }}

                </div>

                <div>

                  <h3 class="font-semibold">
                    Region
                  </h3>

                  {{ listing.region }}

                </div>

                <div>

                  <h3 class="font-semibold">
                    District
                  </h3>

                  {{ listing.district }}

                </div>

                <div v-if="listing.horsepower">

                  <h3 class="font-semibold">
                    Horsepower
                  </h3>

                  {{ listing.horsepower }} HP

                </div>

                <div v-if="listing.year">

                  <h3 class="font-semibold">
                    Year
                  </h3>

                  {{ listing.year }}

                </div>

              </div>

              <div class="mt-8">

                <div
                  v-if="listing.for_rent"
                  class="text-3xl font-bold text-green-700"
                >
                  TZS {{ format(listing.rental_price) }}

                  <span class="text-lg text-gray-500">

                    / {{ listing.rental_period }}

                  </span>

                </div>

                <div
                  v-if="listing.for_sale"
                  class="text-3xl font-bold text-blue-700 mt-2"
                >
                  TZS {{ format(listing.sale_price) }}
                </div>

              </div>

              <p class="mt-8 text-gray-700 leading-7">

                {{ listing.description }}

              </p>

              <div class="flex gap-3 mt-10">

                <BookingModal
                  :listing-id="listing.id"
                />

                <a
                  :href="`tel:${listing.phone}`"
                  class="border px-6 py-3 rounded-lg"
                >
                  Call
                </a>

                <a
                  :href="`https://wa.me/${listing.phone}`"
                  target="_blank"
                  class="bg-green-500 text-white px-6 py-3 rounded-lg"
                >
                  WhatsApp
                </a>

              </div>

            </div>

          </div>

        </div>

      </div>

      <!-- Owner -->

      <div class="max-w-7xl mx-auto px-6 py-10">

        <OwnerCard
          :owner="listing.owner"
        />

      </div>

      <!-- Reviews -->

      <div class="max-w-7xl mx-auto px-6 py-10">

        <MachineryReviews
          :listing-id="listing.id"
        />

      </div>

      <!-- Location Map -->

      <div class="max-w-7xl mx-auto px-6 py-10">

        <MachineryLocationMap
          :latitude="Number(listing.latitude)"
          :longitude="Number(listing.longitude)"
        />

      </div>

      <!-- Related -->

      <div class="max-w-7xl mx-auto px-6 pb-16">

        <h2 class="text-2xl font-bold mb-6">

          Related Machinery

        </h2>

        <div class="grid md:grid-cols-3 gap-6">

          <MachineryCard
            v-for="item in related"
            :key="item.id"
            :listing="item"
          />

        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";

import MachineryCard from "../../components/MachineryCard.vue";
import OwnerCard from "../../components/OwnerCard.vue";
import BookingModal from "../../components/BookingModal.vue";
import MachineryReviews from "../../components/MachineryReviews.vue";
import MachineryGallery from "../../components/MachineryGallery.vue";
import MachineryLocationMap from "../../components/MachineryLocationMap.vue";

import {
  getMachineryDetails,
  getRelatedMachinery
} from "../../api/machinery";

const route = useRoute();

const loading = ref(true);

const listing = ref(null);

const related = ref([]);

const selectedImage = ref("");

const gallery = ref([]);

function format(v){
  return Number(v || 0).toLocaleString();
}

async function load(){

  const res = await getMachineryDetails(route.params.id);

  listing.value = res.data;

  gallery.value = [
    listing.value.cover_photo,
    ...(listing.value.gallery || [])
  ].filter(Boolean);

  selectedImage.value = gallery.value[0];

  const rel = await getRelatedMachinery(route.params.id);

  related.value = rel.data;

  loading.value = false;
}

onMounted(load);
</script>
