<template>
  <div class="max-w-3xl mx-auto p-6">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">
          Checkout
        </h1>
        <p class="text-gray-500 mt-1">
          Review your order before making payment.
        </p>
      </div>

      <router-link
        to="/buyer"
        class="text-green-600 hover:text-green-700 font-medium"
      >
        ← Marketplace
      </router-link>
    </div>

    <!-- Loading -->
    <div
      v-if="loading"
      class="bg-white rounded-lg shadow border p-6 text-gray-500"
    >
      Loading listing...
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4"
    >
      {{ error }}
    </div>

    <!-- Listing -->
    <div
      v-else-if="listing"
      class="space-y-5"
    >

      <!-- Product Summary -->
      <div class="bg-white rounded-lg shadow border p-6">

        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-gray-800">
              {{ listing.commodity?.name || "Agricultural Product" }}
            </h2>

            <p
              v-if="listing.commodity?.category?.name"
              class="text-sm text-gray-500 mt-1"
            >
              {{ listing.commodity.category.name }}
            </p>
          </div>

          <span
            class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full"
          >
            {{ listing.status || "available" }}
          </span>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mt-6 text-sm">

          <div>
            <span class="text-gray-500">Farmer</span>
            <p class="font-medium text-gray-800">
              {{ listing.seller?.name || "Unknown Farmer" }}
            </p>
          </div>

          <div>
            <span class="text-gray-500">Grade</span>
            <p class="font-medium text-gray-800">
              {{ listing.grade || "Not specified" }}
            </p>
          </div>

          <div>
            <span class="text-gray-500">Available</span>
            <p class="font-medium text-gray-800">
              {{ listing.quantity }}
              {{ listing.unit }}
            </p>
          </div>

          <div>
            <span class="text-gray-500">Price per {{ listing.unit }}</span>
            <p class="font-bold text-green-700">
              TZS {{ formatPrice(listing.price) }}
            </p>
          </div>

          <div>
            <span class="text-gray-500">Location</span>
            <p class="font-medium text-gray-800">
              {{ listing.district }}, {{ listing.region }}
            </p>
          </div>

        </div>

        <p
          v-if="listing.description"
          class="mt-5 pt-5 border-t text-gray-600"
        >
          {{ listing.description }}
        </p>

      </div>

      <!-- Order Details -->
      <div class="bg-white rounded-lg shadow border p-6">

        <h2 class="text-xl font-bold text-gray-800 mb-5">
          Order Details
        </h2>

        <!-- Quantity -->
        <div>
          <label class="block mb-1 font-medium text-gray-700">
            Quantity
          </label>

          <input
            v-model.number="form.quantity"
            type="number"
            min="1"
            :max="Number(listing.quantity)"
            step="0.01"
            required
            class="w-full border rounded-lg p-3"
          />

          <p class="text-sm text-gray-500 mt-1">
            Maximum available:
            {{ listing.quantity }} {{ listing.unit }}
          </p>
        </div>

        <!-- Total -->
        <div class="mt-5 p-4 rounded-lg bg-gray-50">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">
              Estimated Total
            </span>

            <span class="text-2xl font-bold text-green-700">
              TZS {{ totalAmount }}
            </span>
          </div>
        </div>

      </div>

      <!-- Delivery -->
      <div class="bg-white rounded-lg shadow border p-6">

        <h2 class="text-xl font-bold text-gray-800 mb-5">
          Pickup & Delivery
        </h2>

        <div class="grid md:grid-cols-2 gap-4">

          <!-- Pickup Region -->
          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Pickup Region
            </label>

            <input
              v-model="form.pickup_region"
              type="text"
              required
              class="w-full border rounded-lg p-3"
              placeholder="Morogoro"
            />
          </div>

          <!-- Pickup District -->
          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Pickup District
            </label>

            <input
              v-model="form.pickup_district"
              type="text"
              required
              class="w-full border rounded-lg p-3"
              placeholder="Kilosa"
            />
          </div>

          <!-- Delivery Region -->
          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Delivery Region
            </label>

            <input
              v-model="form.delivery_region"
              type="text"
              required
              class="w-full border rounded-lg p-3"
              placeholder="Dar es Salaam"
            />
          </div>

          <!-- Delivery District -->
          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Delivery District
            </label>

            <input
              v-model="form.delivery_district"
              type="text"
              required
              class="w-full border rounded-lg p-3"
              placeholder="Ilala"
            />
          </div>

        </div>

      </div>

      <!-- Payment -->
      <div class="bg-white rounded-lg shadow border p-6">

        <h2 class="text-xl font-bold text-gray-800 mb-5">
          Payment
        </h2>

        <label class="block mb-1 font-medium text-gray-700">
          Phone Number
        </label>

        <input
          v-model="phone"
          type="tel"
          required
          placeholder="2557XXXXXXXX"
          class="w-full border rounded-lg p-3"
        />

        <p class="text-sm text-gray-500 mt-2">
          You will be redirected to the secure payment page.
        </p>

        <!-- Error -->
        <div
          v-if="paymentError"
          class="mt-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 whitespace-pre-line"
        >
          {{ paymentError }}
        </div>

        <!-- Submit -->
        <button
          @click="checkout"
          :disabled="processing"
          class="w-full mt-6 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold disabled:opacity-50"
        >
          {{ processing ? "Processing..." : "Pay Securely" }}
        </button>

      </div>

    </div>

    <!-- Not Found -->
    <div
      v-else
      class="bg-white rounded-lg shadow border p-6 text-gray-600"
    >
      Listing not found.
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "../../services/api";

const route = useRoute();

const listing = ref(null);
const loading = ref(true);
const processing = ref(false);

const error = ref("");
const paymentError = ref("");

const phone = ref("");

const form = ref({
  quantity: 1,
  pickup_region: "",
  pickup_district: "",
  delivery_region: "",
  delivery_district: ""
});

const formatPrice = (price) => {
  return Number(price || 0).toLocaleString("en-TZ");
};

const totalAmount = computed(() => {
  if (!listing.value) {
    return "0";
  }

  const price = Number(listing.value.price || 0);
  const quantity = Number(form.value.quantity || 0);

  return (price * quantity).toLocaleString("en-TZ");
});

async function loadListing() {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get(
      `/listings/${route.params.id}`
    );

    listing.value = response.data;

    // Default pickup location to farmer's location.
    form.value.pickup_region =
      listing.value.region || "";

    form.value.pickup_district =
      listing.value.district || "";

  } catch (err) {
    console.error("Failed to load listing:", err);

    if (err.response?.status === 404) {
      error.value = "Listing not found.";
    } else {
      error.value =
        err.response?.data?.message ||
        "Failed to load listing.";
    }
  } finally {
    loading.value = false;
  }
}

async function checkout() {
  paymentError.value = "";

  if (!listing.value) {
    paymentError.value = "Listing information is unavailable.";
    return;
  }

  const quantity = Number(form.value.quantity);
  const available = Number(listing.value.quantity);

  if (!quantity || quantity < 1) {
    paymentError.value =
      "Please enter a valid quantity.";
    return;
  }

  if (quantity > available) {
    paymentError.value =
      `Only ${available} ${listing.value.unit} is available.`;
    return;
  }

  if (!phone.value.trim()) {
    paymentError.value =
      "Please enter your phone number.";
    return;
  }

  processing.value = true;

  try {
    // Create order.
    const orderResponse = await api.post("/orders", {
      marketplace_type: "product",
      listing_id: listing.value.id,
      marketplace_id: listing.value.id,
      quantity: quantity,
      pickup_region: form.value.pickup_region,
      pickup_district: form.value.pickup_district,
      delivery_region: form.value.delivery_region,
      delivery_district: form.value.delivery_district
    });

    const order =
      orderResponse.data?.order ||
      orderResponse.data;

    const orderId = order?.id;

    if (!orderId) {
      throw new Error(
        "Order ID was not returned by the server."
      );
    }

    // Start payment.
    const paymentResponse = await api.post(
      "/payments",
      {
        order_id: orderId,
        phone: phone.value.trim()
      }
    );

    const redirectUrl =
      paymentResponse.data?.redirect_url ||
      paymentResponse.data?.checkout_url;

    if (!redirectUrl) {
      throw new Error(
        "Payment checkout URL was not returned."
      );
    }

    // Redirect to Pesapal.
    window.location.href = redirectUrl;

  } catch (err) {
    console.error(
      "Checkout failed:",
      err.response?.data || err
    );

    if (err.response?.data?.errors) {
      paymentError.value = Object.values(
        err.response.data.errors
      )
        .flat()
        .join("\n");
    } else {
      paymentError.value =
        err.response?.data?.message ||
        err.message ||
        "Checkout failed. Please try again.";
    }

    processing.value = false;
  }
}

onMounted(loadListing);
</script>