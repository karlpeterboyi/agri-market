<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <h2 class="text-3xl font-bold">Farmer Dashboard</h2>

      <!-- Create Listing Link -->
      <router-link
        to="/farmer/listings/create"
        class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-3 rounded-lg shadow transition"
      >
        + Create New Listing
      </router-link>
    </div>

    <div class="grid md:grid-cols-4 gap-6">
      <!-- Listings -->
      <div class="bg-white rounded shadow p-6">
        <h3 class="text-gray-500">Listings</h3>
        <p class="text-4xl font-bold mt-2">
          {{ listings.length }}
        </p>

        <router-link
  to="/farmer/listings"
  class="inline-block mt-4 text-green-600 hover:text-green-700 font-medium"
>
  View Listings →
</router-link>
      </div>

      <!-- Orders -->
      <div class="bg-white rounded shadow p-6">
        <h3 class="text-gray-500">Orders</h3>
        <p class="text-4xl font-bold mt-2">
          {{ orders.length }}
        </p>

        <router-link
          to="/farmer/orders"
          class="inline-block mt-4 text-green-600 hover:text-green-700 font-medium"
        >
          View Orders →
        </router-link>
      </div>

      <!-- Wallet -->
      <div class="bg-white rounded shadow p-6">
        <h3 class="text-gray-500">Wallet</h3>

        <p class="text-4xl font-bold text-green-700 mt-2">
          TZS {{ wallet?.available_balance?.toLocaleString() || '0' }}
        </p>

        <router-link
          to="/farmer/wallet"
          class="inline-block mt-4 text-green-600 hover:text-green-700 font-medium"
        >
          View Wallet →
        </router-link>
      </div>

      <!-- Pending Withdrawals -->
      <div class="bg-white rounded shadow p-6">
        <h3 class="text-gray-500">Pending Withdrawals</h3>

        <p class="text-4xl font-bold text-yellow-600 mt-2">
          {{ pendingWithdrawals }}
        </p>

        <router-link
          to="/farmer/withdrawals"
          class="inline-block mt-4 text-green-600 hover:text-green-700 font-medium"
        >
          View Withdrawals →
        </router-link>
      </div>
    </div>

    <!-- Module shortcuts -->
    <h3 class="text-lg font-semibold mt-10 mb-4 text-gray-700">Quick access</h3>
    <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-4">
      <router-link to="/farmer/farms" class="bg-white rounded-lg shadow p-4 hover:shadow-md border-l-4 border-green-600">
        <p class="font-semibold">Farm ERP</p>
        <p class="text-xs text-gray-500 mt-1">Farms, cycles, activities</p>
      </router-link>
      <router-link to="/farmer/loans/products" class="bg-white rounded-lg shadow p-4 hover:shadow-md border-l-4 border-blue-600">
        <p class="font-semibold">Loans</p>
        <p class="text-xs text-gray-500 mt-1">Browse & apply</p>
      </router-link>
      <router-link to="/knowledge" class="bg-white rounded-lg shadow p-4 hover:shadow-md border-l-4 border-amber-500">
        <p class="font-semibold">Knowledge Hub</p>
        <p class="text-xs text-gray-500 mt-1">Courses & advice</p>
      </router-link>
      <router-link to="/farmer/subsidies" class="bg-white rounded-lg shadow p-4 hover:shadow-md border-l-4 border-purple-600">
        <p class="font-semibold">Subsidies</p>
        <p class="text-xs text-gray-500 mt-1">Government programmes</p>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const wallet = ref(null);
const listings = ref([]);
const orders = ref([]);
const pendingWithdrawals = ref(0);

onMounted(async () => {
  try {
    // Fetch wallet balance
    const walletRes = await api.get("/wallet");
    wallet.value = walletRes.data;

    // Fetch listings
    const listingsRes = await api.get("/my-listings");
    listings.value = listingsRes.data || [];

    // Fetch orders
    const ordersRes = await api.get("/farmer/orders");
    orders.value = ordersRes.data || [];

  } catch (error) {
    console.error("Failed to load dashboard data:", error);
  }
});
</script>
