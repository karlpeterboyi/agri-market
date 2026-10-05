<template>
  <div class="p-6 max-w-4xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">
          Track Order
        </h1>

        <p class="text-gray-500 mt-1">
          Order #{{ order?.id }}
        </p>
      </div>

      <router-link
        to="/buyer/orders"
        class="text-green-600 hover:text-green-700 font-medium"
      >
        ← My Orders
      </router-link>
    </div>

    <!-- Loading -->
    <div
      v-if="loading"
      class="bg-white rounded-lg shadow border p-6 text-gray-500"
    >
      Loading order...
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="bg-red-100 text-red-700 rounded-lg p-4"
    >
      {{ error }}
    </div>

    <!-- Order -->
    <div v-else-if="order" class="space-y-6">

      <!-- Order summary -->
      <div class="bg-white rounded-lg shadow border p-6">

        <h2 class="text-xl font-bold text-gray-800 mb-4">
          Order Summary
        </h2>

        <div class="grid md:grid-cols-2 gap-4">

          <div>
            <p class="text-sm text-gray-500">
              Commodity
            </p>

            <p class="font-semibold">
              {{ order.listing?.commodity?.name || "N/A" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-500">
              Farmer
            </p>

            <p class="font-semibold">
              {{ order.seller?.name || "N/A" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-500">
              Quantity
            </p>

            <p class="font-semibold">
              {{ order.quantity }} {{ order.listing?.unit || "" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-500">
              Total
            </p>

            <p class="font-semibold text-green-700">
              TZS {{ Number(order.total_amount).toLocaleString() }}
            </p>
          </div>

        </div>
      </div>

      <!-- Status -->
      <div class="bg-white rounded-lg shadow border p-6">

        <h2 class="text-xl font-bold text-gray-800 mb-6">
          Order Progress
        </h2>

        <div class="space-y-5">

          <!-- Payment -->
          <div class="flex gap-4">
            <div
              class="w-9 h-9 rounded-full flex items-center justify-center"
              :class="paymentComplete
                ? 'bg-green-600 text-white'
                : 'bg-gray-200 text-gray-500'"
            >
              ✓
            </div>

            <div>
              <h3 class="font-semibold">
                Payment
              </h3>

              <p class="text-sm text-gray-500">
                {{ paymentStatus }}
              </p>
            </div>
          </div>

          <!-- Farmer -->
          <div class="flex gap-4">
            <div
              class="w-9 h-9 rounded-full flex items-center justify-center"
              :class="farmerProcessing
                ? 'bg-green-600 text-white'
                : 'bg-gray-200 text-gray-500'"
            >
              ✓
            </div>

            <div>
              <h3 class="font-semibold">
                Farmer Processing
              </h3>

              <p class="text-sm text-gray-500">
                {{ farmerStatus }}
              </p>
            </div>
          </div>

          <!-- Transport -->
          <div class="flex gap-4">
            <div
              class="w-9 h-9 rounded-full flex items-center justify-center"
              :class="transportActive
                ? 'bg-green-600 text-white'
                : 'bg-gray-200 text-gray-500'"
            >
              🚚
            </div>

            <div>
              <h3 class="font-semibold">
                Transport
              </h3>

              <p class="text-sm text-gray-500">
                {{ transportStatus }}
              </p>
            </div>
          </div>

          <!-- Delivery -->
          <div class="flex gap-4">
            <div
              class="w-9 h-9 rounded-full flex items-center justify-center"
              :class="deliveryComplete
                ? 'bg-green-600 text-white'
                : 'bg-gray-200 text-gray-500'"
            >
              ✓
            </div>

            <div>
              <h3 class="font-semibold">
                Delivery
              </h3>

              <p class="text-sm text-gray-500">
                {{ deliveryStatus }}
              </p>
            </div>
          </div>

          <!-- Completed -->
          <div class="flex gap-4">
            <div
              class="w-9 h-9 rounded-full flex items-center justify-center"
              :class="order.status === 'completed'
                ? 'bg-green-600 text-white'
                : 'bg-gray-200 text-gray-500'"
            >
              ✓
            </div>

            <div>
              <h3 class="font-semibold">
                Completed
              </h3>

              <p class="text-sm text-gray-500">
                {{ order.status === "completed"
                  ? "Order completed successfully."
                  : "Waiting for order completion." }}
              </p>
            </div>
          </div>

        </div>
      </div>

      <!-- Logistics -->
      <div
        v-if="order.logistics_request"
        class="bg-white rounded-lg shadow border p-6"
      >

        <h2 class="text-xl font-bold text-gray-800 mb-4">
          Delivery Information
        </h2>

        <div class="grid md:grid-cols-2 gap-4">

          <div>
            <p class="text-sm text-gray-500">
              Pickup
            </p>

            <p class="font-medium">
              {{ order.logistics_request.pickup_region }},
              {{ order.logistics_request.pickup_district }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-500">
              Destination
            </p>

            <p class="font-medium">
              {{ order.logistics_request.delivery_region }},
              {{ order.logistics_request.delivery_district }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-500">
              Logistics Status
            </p>

            <p class="font-semibold">
              {{ order.logistics_request.status }}
            </p>
          </div>

          <div v-if="order.logistics_request.assignment">

            <p class="text-sm text-gray-500">
              Transport Status
            </p>

            <p class="font-semibold">
              {{ order.logistics_request.assignment.status }}
            </p>

          </div>

        </div>
      </div>

      <!-- Confirm delivery -->
      <div
        v-if="order.status === 'in_escrow'"
        class="bg-yellow-50 border border-yellow-200 rounded-lg p-6"
      >

        <h2 class="text-lg font-bold text-yellow-800">
          Delivery Confirmation
        </h2>

        <p class="text-sm text-yellow-700 mt-2">
          If you have received your agricultural products,
          confirm delivery to release the escrow payment to the farmer.
        </p>

        <button
          @click="confirmDelivery"
          :disabled="confirming"
          class="mt-4 bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg font-semibold disabled:opacity-50"
        >
          {{ confirming ? "Confirming..." : "Confirm Delivery" }}
        </button>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "../../services/api";

const route = useRoute();

const order = ref(null);
const loading = ref(true);
const error = ref("");
const confirming = ref(false);

const paymentComplete = computed(() => {
  return ["paid", "in_escrow"].includes(order.value?.payment?.status);
});

const paymentStatus = computed(() => {
  const status = order.value?.payment?.status;

  if (status === "paid") return "Payment received.";
  if (status === "pending") return "Payment pending.";
  return status || "Payment not available.";
});

const farmerProcessing = computed(() => {
  return [
    "in_escrow",
    "shipped",
    "delivered",
    "completed"
  ].includes(order.value?.status);
});

const farmerStatus = computed(() => {
  if (order.value?.status === "pending_payment") {
    return "Waiting for payment.";
  }

  if (order.value?.status === "in_escrow") {
    return "Payment secured. Farmer is processing the order.";
  }

  return "Order is being processed.";
});

const transportActive = computed(() => {
  const status = order.value?.logistics_request?.status;

  return [
    "assigned",
    "in_transit",
    "delivered"
  ].includes(status);
});

const transportStatus = computed(() => {
  const logistics = order.value?.logistics_request;

  if (!logistics) {
    return "Logistics request not available.";
  }

  if (logistics.status === "pending") {
    return "Waiting for transport assignment.";
  }

  if (logistics.status === "assigned") {
    return "Transport has been assigned.";
  }

  if (logistics.status === "in_transit") {
    return "Your order is in transit.";
  }

  if (logistics.status === "delivered") {
    return "Transport has delivered the order.";
  }

  return logistics.status;
});

const deliveryComplete = computed(() => {
  return [
    "delivered",
    "completed"
  ].includes(order.value?.status)
  || order.value?.logistics_request?.status === "delivered";
});

const deliveryStatus = computed(() => {
  if (order.value?.logistics_request?.status === "delivered") {
    return "Your order has been delivered.";
  }

  return "Waiting for delivery.";
});

async function loadOrder() {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get("/orders");

    const found = response.data.find(
      item => Number(item.id) === Number(route.params.id)
    );

    if (!found) {
      error.value = "Order not found.";
      return;
    }

    order.value = found;

  } catch (err) {
    console.error("Failed to load order:", err);

    error.value =
      err.response?.data?.message ||
      "Failed to load order.";

  } finally {
    loading.value = false;
  }
}

async function confirmDelivery() {
  if (!order.value) return;

  confirming.value = true;

  try {
    const response = await api.post(
      `/orders/${order.value.id}/confirm-delivery`
    );

    alert(
      response.data.message ||
      "Delivery confirmed successfully."
    );

    await loadOrder();

  } catch (err) {
    console.error("Failed to confirm delivery:", err);

    alert(
      err.response?.data?.message ||
      "Failed to confirm delivery."
    );

  } finally {
    confirming.value = false;
  }
}

onMounted(loadOrder);
</script>
