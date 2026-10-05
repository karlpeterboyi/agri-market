<template>
  <div class="p-6 max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">
      Farmer Withdrawals
    </h1>

    <div class="grid md:grid-cols-2 gap-8">

      <!-- Withdrawal Form -->
      <div class="bg-white rounded-lg shadow border p-6">

        <h2 class="text-xl font-semibold mb-4">
          Request Withdrawal
        </h2>

        <div
          v-if="message"
          class="mb-4 p-3 rounded bg-green-100 text-green-700"
        >
          {{ message }}
        </div>

        <div
          v-if="error"
          class="mb-4 p-3 rounded bg-red-100 text-red-700"
        >
          {{ error }}
        </div>

        <form
          @submit.prevent="handleWithdraw"
          class="space-y-4"
        >

          <div>
            <label class="block mb-1 font-medium">
              Amount (TZS)
            </label>

            <input
              v-model.number="form.amount"
              type="number"
              min="1000"
              required
              placeholder="50000"
              class="w-full border rounded p-2"
            >
          </div>

          <div>
            <label class="block mb-1 font-medium">
              Phone Number
            </label>

            <input
              v-model="form.phone"
              type="tel"
              required
              placeholder="0712345678"
              class="w-full border rounded p-2"
            >
          </div>

          <button
            type="submit"
            :disabled="submitting"
            class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded disabled:opacity-50"
          >
            {{ submitting ? "Submitting..." : "Request Withdrawal" }}
          </button>

        </form>

      </div>

      <!-- Withdrawal History -->
      <div class="bg-white rounded-lg shadow border p-6">

        <h2 class="text-xl font-semibold mb-4">
          Withdrawal History
        </h2>

        <div
          v-if="loading"
          class="text-gray-500"
        >
          Loading...
        </div>

        <div
          v-else-if="withdrawals.length === 0"
          class="text-gray-500"
        >
          No withdrawals yet.
        </div>

        <div
          v-else
          class="space-y-3"
        >

          <div
            v-for="item in withdrawals"
            :key="item.id"
            class="border rounded p-4 flex justify-between items-center"
          >

            <div>

              <div class="font-semibold text-lg">
                TZS {{ Number(item.amount).toLocaleString() }}
              </div>

              <div class="text-gray-600 text-sm">
                {{ item.phone }}
              </div>

              <div
                v-if="item.created_at"
                class="text-xs text-gray-400 mt-1"
              >
                {{ new Date(item.created_at).toLocaleString() }}
              </div>

            </div>

            <span
              class="px-3 py-1 rounded-full text-sm font-semibold"
              :class="{
                'bg-yellow-100 text-yellow-700': item.status === 'pending',
                'bg-blue-100 text-blue-700': item.status === 'approved',
                'bg-green-100 text-green-700': item.status === 'paid',
                'bg-red-100 text-red-700': item.status === 'rejected'
              }"
            >
              {{ item.status }}
            </span>

          </div>

        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const withdrawals = ref([]);
const loading = ref(true);
const submitting = ref(false);

const message = ref("");
const error = ref("");

const form = ref({
  amount: null,
  phone: ""
});

async function fetchWithdrawals() {
  loading.value = true;

  try {
    const res = await api.get("/withdrawals");
    withdrawals.value = res.data;
  } catch (err) {
    console.error(err);
    error.value = "Failed to load withdrawals.";
  } finally {
    loading.value = false;
  }
}

async function handleWithdraw() {
  submitting.value = true;
  message.value = "";
  error.value = "";

  try {
    await api.post("/withdrawals", {
      amount: form.value.amount,
      phone: form.value.phone
    });

    message.value = "Withdrawal request submitted successfully.";

    form.value = {
      amount: null,
      phone: ""
    };

    await fetchWithdrawals();

  } catch (err) {
    console.error(err);

    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)
        .flat()
        .join("\n");
    } else {
      error.value =
        err.response?.data?.message ??
        "Withdrawal request failed.";
    }
  } finally {
    submitting.value = false;
  }
}

onMounted(fetchWithdrawals);
</script>