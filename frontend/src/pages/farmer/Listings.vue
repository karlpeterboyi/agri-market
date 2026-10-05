<template>
  <div class="p-6">

    <!-- Header -->
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6"
    >
      <div>
        <h1 class="text-2xl font-bold text-gray-800">
          My Listings
        </h1>

        <p class="text-gray-500 mt-1">
          Manage your agricultural products.
        </p>
      </div>

      <router-link
        to="/farmer/listings/create"
        class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2.5 rounded-lg transition"
      >
        + Create Listing
      </router-link>
    </div>

    <!-- Loading -->
    <div
      v-if="loading"
      class="text-gray-500 bg-white rounded-lg shadow-sm p-6"
    >
      Loading listings...
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4"
    >
      {{ error }}
    </div>

    <!-- Empty -->
    <div
      v-else-if="listings.length === 0"
      class="bg-white rounded-lg shadow-sm p-10 text-center"
    >
      <h2 class="text-lg font-semibold text-gray-800">
        No listings yet
      </h2>

      <p class="text-gray-500 mt-2 mb-5">
        You haven't created any agricultural product listings.
      </p>

      <router-link
        to="/farmer/listings/create"
        class="inline-block bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold"
      >
        Create Your First Listing
      </router-link>
    </div>

    <!-- Listings -->
    <div
      v-else
      class="grid gap-5 md:grid-cols-2 lg:grid-cols-3"
    >
      <div
        v-for="listing in listings"
        :key="listing.id"
        class="bg-white border rounded-xl shadow-sm overflow-hidden"
      >
        <div class="p-5">

          <!-- Commodity + Status -->
          <div class="flex items-start justify-between gap-3">

            <div>
              <h2 class="font-bold text-lg text-gray-800">
                {{ commodityName(listing) }}
              </h2>

              <p
                v-if="listing.commodity?.category?.name"
                class="text-sm text-green-600 mt-1"
              >
                {{ listing.commodity.category.name }}
              </p>

              <p
                v-if="listing.grade"
                class="text-sm text-gray-500 mt-1"
              >
                Grade: {{ listing.grade }}
              </p>
            </div>

            <span
              class="text-xs font-semibold px-2.5 py-1 rounded-full"
              :class="statusClass(listing.status)"
            >
              {{ listing.status || "available" }}
            </span>
          </div>

          <!-- Details -->
          <div class="mt-5 space-y-2 text-sm">

            <div class="flex justify-between">
              <span class="text-gray-500">
                Quantity
              </span>

              <span class="font-medium text-gray-800">
                {{ listing.quantity }}
                {{ listing.unit || "" }}
              </span>
            </div>

            <div class="flex justify-between">
              <span class="text-gray-500">
                Price
              </span>

              <span class="font-bold text-green-700">
                TZS {{ formatPrice(listing.price) }}
              </span>
            </div>

            <div class="flex justify-between gap-4">
              <span class="text-gray-500">
                Location
              </span>

              <span class="font-medium text-gray-800 text-right">
                {{ listing.district || "—" }},
                {{ listing.region || "—" }}
              </span>
            </div>

          </div>

          <!-- Description -->
          <p
            v-if="listing.description"
            class="text-sm text-gray-500 mt-4 line-clamp-2"
          >
            {{ listing.description }}
          </p>

          <!-- Actions -->
          <div class="flex gap-2 mt-5 pt-4 border-t">

            <router-link
              :to="`/farmer/listings/${listing.id}/edit`"
              class="flex-1 text-center bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium px-3 py-2 rounded-lg"
            >
              Edit
            </router-link>

            <button
              type="button"
              @click="deleteListing(listing)"
              :disabled="deletingId === listing.id"
              class="flex-1 bg-red-50 hover:bg-red-100 text-red-700 font-medium px-3 py-2 rounded-lg disabled:opacity-50"
            >
              {{
                deletingId === listing.id
                  ? "Deleting..."
                  : "Delete"
              }}
            </button>

          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const listings = ref([]);
const loading = ref(true);
const error = ref("");
const deletingId = ref(null);

/**
 * Load authenticated farmer's listings.
 *
 * IMPORTANT:
 * /listings = public marketplace listings
 * /my-listings = authenticated farmer's own listings
 */
const loadListings = async () => {
  loading.value = true;
  error.value = "";

  try {
    const res = await api.get("/my-listings");

    listings.value = Array.isArray(res.data)
      ? res.data
      : [];

  } catch (err) {
    console.error("Error loading farmer listings:", err);

    if (err.response?.status === 401) {
      error.value = "Your session has expired. Please log in again.";
    } else {
      error.value =
        err.response?.data?.message ||
        "Failed to load your listings.";
    }

  } finally {
    loading.value = false;
  }
};

/**
 * Get commodity name from API response.
 */
const commodityName = (listing) => {
  return (
    listing.commodity?.name ||
    listing.crop ||
    listing.title ||
    "Agricultural Product"
  );
};

/**
 * Format price in Tanzanian Shillings.
 */
const formatPrice = (price) => {
  const value = Number(price || 0);

  return value.toLocaleString("en-TZ");
};

/**
 * Status badge styling.
 */
const statusClass = (status) => {
  switch (status) {
    case "available":
      return "bg-green-100 text-green-700";

    case "sold":
      return "bg-gray-100 text-gray-700";

    case "expired":
      return "bg-red-100 text-red-700";

    default:
      return "bg-yellow-100 text-yellow-700";
  }
};

/**
 * Delete farmer's own listing.
 */
const deleteListing = async (listing) => {
  const name = commodityName(listing);

  const confirmed = window.confirm(
    `Are you sure you want to delete the ${name} listing?`
  );

  if (!confirmed) {
    return;
  }

  deletingId.value = listing.id;

  try {
    await api.delete(`/listings/${listing.id}`);

    listings.value = listings.value.filter(
      item => item.id !== listing.id
    );

  } catch (err) {
    console.error("Error deleting listing:", err);

    if (err.response?.status === 401) {
      alert("Your session has expired. Please log in again.");
    } else {
      alert(
        err.response?.data?.message ||
        "Failed to delete listing."
      );
    }

  } finally {
    deletingId.value = null;
  }
};

onMounted(loadListings);
</script>