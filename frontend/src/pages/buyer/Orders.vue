<template>
  <div class="p-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">
          My Orders
        </h1>

        <p class="text-gray-500 mt-1">
          Track your agricultural purchases and deliveries.
        </p>
      </div>

      <router-link
        to="/buyer/marketplace"
        class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2.5 rounded-lg"
      >
        ← Marketplace
      </router-link>
    </div>

    <!-- Loading -->
    <div
      v-if="loading"
      class="bg-white rounded-lg shadow-sm p-6 text-gray-500"
    >
      Loading orders...
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
      v-else-if="orders.length === 0"
      class="bg-white rounded-lg shadow-sm p-10 text-center"
    >
      <h2 class="text-lg font-semibold text-gray-800">
        No orders yet
      </h2>

      <p class="text-gray-500 mt-2 mb-5">
        Your marketplace purchases will appear here.
      </p>

      <router-link
        to="/buyer/marketplace"
        class="inline-block bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold"
      >
        Browse Marketplace
      </router-link>
    </div>

    <!-- Orders -->
    <div v-else class="space-y-5">

      <div
        v-for="order in orders"
        :key="order.id"
        class="bg-white border rounded-xl shadow-sm overflow-hidden"
      >

        <!-- Order Header -->
        <div class="p-5 border-b bg-gray-50">

          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

            <div>
              <p class="text-sm text-gray-500">
                Order #{{ order.id }}
              </p>

              <h2 class="text-xl font-bold text-gray-800">
                {{ commodityName(order) }}
              </h2>

              <p class="text-sm text-gray-500 mt-1">
                {{ formatDate(order.created_at) }}
              </p>
            </div>

            <span
              class="inline-flex w-fit text-xs font-semibold px-3 py-1.5 rounded-full"
              :class="statusClass(order.status)"
            >
              {{ formatStatus(order.status) }}
            </span>

          </div>

        </div>

        <!-- Order Details -->
        <div class="p-5">

          <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">

            <div>
              <p class="text-sm text-gray-500">
                Farmer
              </p>

              <p class="font-semibold text-gray-800 mt-1">
                {{ order.seller?.name || "Unknown Farmer" }}
              </p>
            </div>

            <div>
              <p class="text-sm text-gray-500">
                Quantity
              </p>

              <p class="font-semibold text-gray-800 mt-1">
                {{ order.quantity }}
                {{ order.listing?.unit || "" }}
              </p>
            </div>

            <div>
              <p class="text-sm text-gray-500">
                Unit Price
              </p>

              <p class="font-semibold text-gray-800 mt-1">
                TZS {{ formatPrice(order.price) }}
              </p>
            </div>

            <div>
              <p class="text-sm text-gray-500">
                Total
              </p>

              <p class="font-bold text-green-700 mt-1">
                TZS {{ formatPrice(order.total_amount) }}
              </p>
            </div>

          </div>

          <!-- Delivery -->
          <div
            v-if="order.delivery || order.logistics_request"
            class="mt-5 pt-5 border-t"
          >
            <h3 class="font-semibold text-gray-800 mb-3">
              Delivery
            </h3>

            <div class="grid md:grid-cols-2 gap-4 text-sm">

              <div>
                <p class="text-gray-500">
                  Pickup
                </p>

                <p class="font-medium text-gray-800">
                  {{ pickupLocation(order) }}
                </p>
              </div>

              <div>
                <p class="text-gray-500">
                  Delivery
                </p>

                <p class="font-medium text-gray-800">
                  {{ deliveryLocation(order) }}
                </p>
              </div>

            </div>
          </div>

          <!-- Actions -->
          <div class="mt-5 pt-5 border-t flex flex-wrap gap-3">

            <button
              type="button"
              class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium px-4 py-2 rounded-lg"
              @click="$router.push(`/buyer/orders/${order.id}`)"
            >
              Track Order
            </button>

            <button
              v-if="order.status === 'in_escrow'"
              type="button"
              class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg"
              :disabled="confirmingId === order.id"
              @click="confirmDelivery(order)"
            >
              {{
                confirmingId === order.id
                  ? "Confirming..."
                  : "Confirm Delivery"
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

const orders = ref([]);
const loading = ref(true);
const error = ref("");
const confirmingId = ref(null);

async function loadOrders() {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get("/orders");

    orders.value = Array.isArray(response.data)
      ? response.data
      : [];
  } catch (err) {
    console.error("Failed to load orders:", err);

    error.value =
      err.response?.data?.message ||
      "Failed to load orders.";
  } finally {
    loading.value = false;
  }
}

function commodityName(order) {
  return (
    order.listing?.commodity?.name ||
    order.commodity?.name ||
    "Agricultural Product"
  );
}

function formatPrice(price) {
  return Number(price || 0).toLocaleString("en-TZ");
}

function formatDate(date) {
  if (!date) {
    return "";
  }

  return new Date(date).toLocaleString("en-TZ", {
    dateStyle: "medium",
    timeStyle: "short"
  });
}

function formatStatus(status) {
  if (!status) {
    return "Unknown";
  }

  return status
    .replaceAll("_", " ")
    .replace(/\b\w/g, char => char.toUpperCase());
}

function statusClass(status) {
  switch (status) {
    case "completed":
      return "bg-green-100 text-green-700";

    case "delivered":
      return "bg-blue-100 text-blue-700";

    case "in_escrow":
      return "bg-purple-100 text-purple-700";

    case "shipped":
      return "bg-indigo-100 text-indigo-700";

    case "pending_payment":
      return "bg-yellow-100 text-yellow-700";

    case "cancelled":
    case "refunded":
      return "bg-red-100 text-red-700";

    default:
      return "bg-gray-100 text-gray-700";
  }
}

function pickupLocation(order) {
  const delivery = order.delivery || order.logistics_request;

  if (!delivery) {
    return "Not specified";
  }

  return [
    delivery.pickup_district,
    delivery.pickup_region
  ]
    .filter(Boolean)
    .join(", ") || "Not specified";
}

function deliveryLocation(order) {
  const delivery = order.delivery || order.logistics_request;

  if (!delivery) {
    return "Not specified";
  }

  return [
    delivery.delivery_district,
    delivery.delivery_region
  ]
    .filter(Boolean)
    .join(", ") || "Not specified";
}

async function confirmDelivery(order) {
  const confirmed = window.confirm(
    `Confirm that you have received Order #${order.id}?`
  );

  if (!confirmed) {
    return;
  }

  confirmingId.value = order.id;

  try {
    const response = await api.post(
      `/orders/${order.id}/confirm-delivery`
    );

    alert(
      response.data?.message ||
      "Delivery confirmed successfully."
    );

    await loadOrders();

  } catch (err) {
    console.error("Failed to confirm delivery:", err);

    alert(
      err.response?.data?.message ||
      "Failed to confirm delivery."
    );
  } finally {
    confirmingId.value = null;
  }
}

onMounted(loadOrders);
</script>
