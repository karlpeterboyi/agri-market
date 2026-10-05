<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const wallet = ref(null);
const loading = ref(true);
const error = ref(false);

async function loadWallet() {
  try {
    const response = await api.get("/wallet");
    wallet.value = response.data;
  } catch (err) {
    console.error("Failed to fetch wallet:", err);
    error.value = true;
  } finally {
    loading.value = false;
  }
}

onMounted(loadWallet);
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">My Wallet</h1>

    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold mb-1">Available Balance</h2>

      <p v-if="loading" class="text-4xl font-bold text-gray-400 mt-3">
        Loading...
      </p>
      
      <p v-else-if="error" class="text-4xl font-bold text-red-600 mt-3">
        Error loading balance
      </p>
      
      <p v-else class="text-4xl font-bold text-green-600 mt-3">
        TZS {{ wallet?.available_balance?.toLocaleString() || '0.00' }}
      </p>

      <button
        class="mt-6 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-medium disabled:opacity-50"
        :disabled="loading || error"
        @click="$router.push('/farmer/withdrawals')"
      >
        Withdraw Funds
      </button>
    </div>
  </div>
</template>