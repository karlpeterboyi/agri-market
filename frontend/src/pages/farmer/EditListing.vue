<template>
  <div class="p-6 max-w-3xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">
          Edit Listing
        </h1>

        <p class="text-gray-500 mt-1">
          Update your agricultural product information.
        </p>
      </div>

      <router-link
        to="/farmer/listings"
        class="text-green-600 hover:text-green-700 font-medium"
      >
        ← My Listings
      </router-link>
    </div>

    <!-- Loading -->
    <div
      v-if="loading"
      class="bg-white rounded-lg shadow border p-6 text-gray-500"
    >
      Loading listing...
    </div>

    <!-- Error while loading -->
    <div
      v-else-if="error && !form.commodity_id"
      class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-5"
    >
      {{ error }}

      <div class="mt-4">
        <router-link
          to="/farmer/listings"
          class="inline-block bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg"
        >
          ← Back to My Listings
        </router-link>
      </div>
    </div>

    <!-- Form -->
    <div
      v-else
      class="bg-white rounded-lg shadow border p-6"
    >

      <!-- Success -->
      <div
        v-if="success"
        class="mb-5 p-4 rounded-lg bg-green-100 text-green-700"
      >
        {{ success }}
      </div>

      <!-- Error -->
      <div
        v-if="error"
        class="mb-5 p-4 rounded-lg bg-red-100 text-red-700 whitespace-pre-line"
      >
        {{ error }}
      </div>

      <form
        @submit.prevent="updateListing"
        class="space-y-5"
      >

        <!-- Commodity -->
        <div>
          <label class="block mb-1 font-medium text-gray-700">
            Commodity
          </label>

          <select
            v-model="form.commodity_id"
            required
            class="w-full border rounded-lg p-3 bg-white"
          >
            <option value="" disabled>
              Select commodity
            </option>

            <option
              v-for="commodity in commodities"
              :key="commodity.id"
              :value="commodity.id"
            >
              {{ commodity.name }}
            </option>
          </select>

          <p
            v-if="commodities.length === 0"
            class="text-sm text-gray-500 mt-1"
          >
            No commodities available.
          </p>
        </div>

        <!-- Quantity + Unit -->
        <div class="grid md:grid-cols-2 gap-4">

          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Quantity
            </label>

            <input
              v-model.number="form.quantity"
              type="number"
              min="0.01"
              step="0.01"
              required
              class="w-full border rounded-lg p-3"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Unit
            </label>

            <select
              v-model="form.unit"
              required
              class="w-full border rounded-lg p-3 bg-white"
            >
              <option value="" disabled>
                Select unit
              </option>

              <option value="kg">Kilograms (kg)</option>
              <option value="ton">Tonnes</option>
              <option value="bag">Bags</option>
              <option value="crate">Crates</option>
              <option value="piece">Pieces</option>
              <option value="litre">Litres</option>
            </select>
          </div>

        </div>

        <!-- Grade + Price -->
        <div class="grid md:grid-cols-2 gap-4">

          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Grade
            </label>

            <input
              v-model="form.grade"
              type="text"
              placeholder="Grade A"
              class="w-full border rounded-lg p-3"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Price (TZS)
            </label>

            <input
              v-model.number="form.price"
              type="number"
              min="0"
              step="0.01"
              required
              class="w-full border rounded-lg p-3"
            />
          </div>

        </div>

        <!-- Location -->
        <div class="grid md:grid-cols-2 gap-4">

          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Region
            </label>

            <input
              v-model="form.region"
              type="text"
              required
              class="w-full border rounded-lg p-3"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700">
              District
            </label>

            <input
              v-model="form.district"
              type="text"
              required
              class="w-full border rounded-lg p-3"
            />
          </div>

        </div>

        <!-- Status -->
        <div>
          <label class="block mb-1 font-medium text-gray-700">
            Status
          </label>

          <select
            v-model="form.status"
            required
            class="w-full border rounded-lg p-3 bg-white"
          >
            <option value="available">
              Available
            </option>

            <option value="sold">
              Sold
            </option>

            <option value="expired">
              Expired
            </option>
          </select>
        </div>

        <!-- Description -->
        <div>
          <label class="block mb-1 font-medium text-gray-700">
            Description
          </label>

          <textarea
            v-model="form.description"
            rows="5"
            placeholder="Describe the produce..."
            class="w-full border rounded-lg p-3"
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-3">

          <router-link
            to="/farmer/listings"
            class="flex-1 text-center border border-gray-300 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-50"
          >
            Cancel
          </router-link>

          <button
            type="submit"
            :disabled="saving"
            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold disabled:opacity-50"
          >
            {{ saving ? "Saving..." : "Save Changes" }}
          </button>

        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../../services/api";

const route = useRoute();
const router = useRouter();

const listingId = route.params.id;

const loading = ref(true);
const saving = ref(false);

const commodities = ref([]);

const success = ref("");
const error = ref("");

const form = ref({
  commodity_id: "",
  quantity: null,
  unit: "",
  grade: "",
  price: null,
  region: "",
  district: "",
  description: "",
  status: "available"
});

async function loadData() {
  loading.value = true;
  error.value = "";

  try {
    const [listingRes, commoditiesRes] = await Promise.all([
      api.get(`/listings/${listingId}`),
      api.get("/commodities")
    ]);

    const listing = listingRes.data;

    commodities.value = Array.isArray(commoditiesRes.data)
      ? commoditiesRes.data
      : [];

    form.value = {
      commodity_id: listing.commodity_id ?? "",
      quantity: listing.quantity ?? null,
      unit: listing.unit ?? "",
      grade: listing.grade ?? "",
      price: listing.price ?? null,
      region: listing.region ?? "",
      district: listing.district ?? "",
      description: listing.description ?? "",
      status: listing.status ?? "available"
    };

  } catch (err) {
    console.error("Failed to load listing:", err);

    if (err.response?.status === 404) {
      error.value = "Listing not found.";
    } else if (err.response?.status === 401) {
      error.value = "Please log in to edit this listing.";
    } else {
      error.value =
        err.response?.data?.message ||
        "Failed to load listing.";
    }
  } finally {
    loading.value = false;
  }
}

async function updateListing() {
  saving.value = true;
  success.value = "";
  error.value = "";

  try {
    const response = await api.put(
      `/listings/${listingId}`,
      {
        commodity_id: form.value.commodity_id,
        quantity: form.value.quantity,
        unit: form.value.unit,
        grade: form.value.grade || null,
        price: form.value.price,
        region: form.value.region,
        district: form.value.district,
        description: form.value.description || null,
        status: form.value.status
      }
    );

    console.log("Listing updated:", response.data);

    success.value = "Listing updated successfully.";

    setTimeout(() => {
      router.push("/farmer/listings");
    }, 800);

  } catch (err) {
    console.error("Failed to update listing:", err);

    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)
        .flat()
        .join("\n");
    } else {
      error.value =
        err.response?.data?.message ||
        "Failed to update listing. Please try again.";
    }
  } finally {
    saving.value = false;
  }
}

onMounted(loadData);
</script>