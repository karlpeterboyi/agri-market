<template>
  <div class="max-w-xl">
    <h2 class="text-3xl font-bold mb-6">Apply for Loan</h2>
    <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-4">
      <div>
        <label class="block text-sm text-gray-600 mb-1">Loan product</label>
        <select v-model="form.loan_product_id" required class="w-full border rounded px-3 py-2">
          <option disabled value="">Select product</option>
          <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.interest_rate }}%)</option>
        </select>
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Requested amount (TZS)</label>
        <input v-model.number="form.requested_amount" type="number" required min="1000" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Repayment period (months)</label>
        <input v-model.number="form.repayment_period_months" type="number" required min="1" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Purpose</label>
        <textarea v-model="form.purpose" required rows="3" class="w-full border rounded px-3 py-2" placeholder="e.g. Buy fertiliser and seed for maize season" />
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Farm size (optional)</label>
        <div class="flex gap-2">
          <input v-model.number="form.farm_size" type="number" step="0.1" class="border rounded px-3 py-2 flex-1" />
          <select v-model="form.farm_size_unit" class="border rounded px-3 py-2">
            <option value="hectares">hectares</option>
            <option value="acres">acres</option>
          </select>
        </div>
      </div>
      <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
      <p v-if="success" class="text-green-600 text-sm">{{ success }}</p>
      <button type="submit" :disabled="saving" class="bg-green-600 text-white px-6 py-2 rounded font-semibold disabled:opacity-50">
        {{ saving ? 'Submitting…' : 'Create application' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { getLoanProducts, createLoanApplication, submitLoanApplication } from "../../api/finance";

const route = useRoute();
const router = useRouter();
const products = ref([]);
const saving = ref(false);
const error = ref("");
const success = ref("");
const form = ref({
  loan_product_id: route.query.product || "",
  requested_amount: null,
  repayment_period_months: 6,
  purpose: "",
  farm_size: null,
  farm_size_unit: "hectares",
});

onMounted(async () => {
  const { data } = await getLoanProducts();
  products.value = data.data || data;
});

async function submit() {
  saving.value = true;
  error.value = "";
  success.value = "";
  try {
    const { data } = await createLoanApplication(form.value);
    const app = data.data || data;
    await submitLoanApplication(app.id);
    success.value = "Application created and submitted.";
    setTimeout(() => router.push("/farmer/loans"), 1200);
  } catch (e) {
    const d = e.response?.data;
    error.value = d?.message
      || (d?.errors ? Object.values(d.errors).flat().join("\n") : null)
      || e.message
      || "Failed to submit application";
  } finally {
    saving.value = false;
  }
}
</script>
