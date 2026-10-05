<template>
  <div class="p-6 max-w-4xl mx-auto">

    <button
      @click="$router.back()"
      class="mb-6 text-blue-600 hover:underline"
    >
      ← Back to Orders
    </button>

    <div v-if="loading" class="text-gray-500">
      Loading order...
    </div>

    <div v-else-if="error" class="text-red-600">
      {{ error }}
    </div>

    <div v-else-if="order" class="space-y-6">

      <!-- Order Header -->
      <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-start gap-4">
          <div>
            <h1 class="text-2xl font-bold">
              Order #{{ order.id }}
            </h1>

            <p class="text-gray-500 mt-1">
              {{ formatDate(order.created_at) }}
            </p>
          </div>

          <span
            class="px-3 py-1 rounded-full text-sm font-semibold"
            :class="statusClass(order.status)"
          >
            {{ formatStatus(order.status) }}
          </span>
        </div>
      </div>

      <!-- Product -->
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">
          Product
        </h2>

        <div class="space-y-2">
          <p>
            <strong>Commodity:</strong>
            {{ order.listing?.commodity?.name ?? "N/A" }}
          </p>

          <p>
            <strong>Quantity:</strong>
            {{ order.quantity }}
            {{ order.listing?.unit ?? "" }}
          </p>

          <p>
            <strong>Price:</strong>
            TZS {{ Number(order.price).toLocaleString() }}
          </p>

          <p class="text-lg">
            <strong>Total:</strong>
            TZS {{ Number(order.total_amount).toLocaleString() }}
          </p>

          <p>
            <strong>Farmer:</strong>
            {{ order.seller?.name ?? "Unknown Farmer" }}
          </p>
        </div>
      </div>

      <!-- Payment -->
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">
          Payment
        </h2>

        <div v-if="order.payment" class="space-y-2">
          <p>
            <strong>Method:</strong>
            {{ order.payment.method }}
          </p>

          <p>
            <strong>Amount:</strong>
            TZS {{ Number(order.payment.amount).toLocaleString() }}
          </p>

          <p>
            <strong>Status:</strong>
            {{ formatStatus(order.payment.status) }}
          </p>

          <p v-if="order.payment.transaction_ref">
            <strong>Transaction:</strong>
            {{ order.payment.transaction_ref }}
          </p>
        </div>

        <p v-else class="text-gray-500">
          No payment record found.
        </p>
      </div>

      <!-- Delivery -->
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">
          Delivery & Logistics
        </h2>

        <div
          v-if="order.logistics_request"
          class="space-y-3"
        >

          <p>
            <strong>Pickup:</strong>
            {{ order.logistics_request.pickup_region }},
            {{ order.logistics_request.pickup_district }}
          </p>

          <p>
            <strong>Delivery:</strong>
            {{ order.logistics_request.delivery_region }},
            {{ order.logistics_request.delivery_district }}
          </p>

          <p>
            <strong>Logistics Status:</strong>
            {{ formatStatus(order.logistics_request.status) }}
          </p>

          <div
            v-if="order.logistics_request.assignment"
            class="mt-4 p-4 bg-gray-50 rounded"
          >
            <h3 class="font-semibold mb-2">
              Transport Assignment
            </h3>

            <p>
              <strong>Status:</strong>
              {{ formatStatus(
                order.logistics_request.assignment.status
              ) }}
            </p>
          </div>

          <div
            v-else
            class="mt-4 text-gray-500"
          >
            Transporter has not yet been assigned.
          </div>

        </div>

        <p v-else class="text-gray-500">
          No logistics information found.
        </p>
      </div>

      <!-- Delivery Confirmation -->
      <div
        v-if="order.status === 'in_escrow'"
        class="bg-green-50 border border-green-200 rounded-lg p-6"
      >
        <h2 class="text-xl font-bold text-green-800">
          Delivery Confirmation
        </h2>

        <p class="mt-2 text-green-700">
          Your payment is currently held in escrow.
          Confirm delivery after you receive the goods.
        </p>

        <button
          @click="confirmDelivery"
          :disabled="confirming"
          class="mt-4 bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700 disabled:opacity-50"
        >
          {{ confirming ? "Confirming..." : "Confirm Delivery" }}
        </button>
      </div>

    </div>

    <div v-else class="text-gray-500">
      Order not found.
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../../services/api";

const route = useRoute();
const router = useRouter();

const order = ref(null);
const loading = ref(true);
const error = ref(null);
const confirming = ref(false);

async function loadOrder() {
  try {
    const res = await api.get("/orders");

    const orders = Array.isArray(res.data)
      ? res.data
      : [];

    order.value = orders.find(
      item => Number(item.id) === Number(route.params.id)
    );

    if (!order.value) {
      error.value = "Order not found.";
    }

  } catch (err) {
    console.error("Failed to load order:", err);
    error.value = "Failed to load order.";
  } finally {
    loading.value = false;
  }
}

async function confirmDelivery() {
  if (!order.value) return;

  if (!confirm(
    "Confirm that you have received this order?"
  )) {
    return;
  }

  confirming.value = true;

  try {
    await api.post(
      `/orders/${order.value.id}/confirm-delivery`
    );

    alert(
      "Delivery confirmed. Seller has been paid."
    );

    await loadOrder();

  } catch (err) {
    console.error(
      "Failed to confirm delivery:",
      err
    );

    alert(
      err.response?.data?.message ??
      "Failed to confirm delivery."
    );

  } finally {
    confirming.value = false;
  }
}

function formatStatus(status) {
  if (!status) return "Unknown";

  return status
    .replaceAll("_", " ")
    .replace(/\b\w/g, char => char.toUpperCase());
}

function statusClass(status) {
  switch (status) {
    case "completed":
      return "bg-green-100 text-green-800";

    case "delivered":
      return "bg-blue-100 text-blue-800";

    case "in_escrow":
      return "bg-yellow-100 text-yellow-800";

    case "cancelled":
    case "refunded":
      return "bg-red-100 text-red-800";

    case "pending_payment":
      return "bg-orange-100 text-orange-800";

    default:
      return "bg-gray-100 text-gray-800";
  }
}

function formatDate(date) {
  if (!date) return "N/A";

  return new Date(date).toLocaleString();
}

onMounted(loadOrder);
</script>
