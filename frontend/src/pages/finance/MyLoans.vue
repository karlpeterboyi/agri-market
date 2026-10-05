<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:justify-between gap-3 mb-6">
      <h2 class="text-2xl sm:text-3xl font-bold">My Loan Applications</h2>
      <router-link to="/farmer/loans/products" class="bg-green-600 text-white px-4 py-2 rounded font-medium text-center">
        Browse products
      </router-link>
    </div>
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm min-w-[640px]">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="p-3">Number</th>
            <th class="p-3">Product</th>
            <th class="p-3">Amount</th>
            <th class="p-3">Period</th>
            <th class="p-3">Status</th>
            <th class="p-3">Submitted</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in applications" :key="a.id" class="border-t">
            <td class="p-3 font-mono text-xs">{{ a.application_number || a.reference || ('#' + a.id) }}</td>
            <td class="p-3">{{ productName(a) }}</td>
            <td class="p-3">TZS {{ formatMoney(a.requested_amount ?? a.amount) }}</td>
            <td class="p-3">{{ a.repayment_period_months ?? '—' }} mo</td>
            <td class="p-3">
              <span class="px-2 py-0.5 rounded text-xs" :class="statusClass(a.status)">{{ a.status }}</span>
            </td>
            <td class="p-3">{{ (a.submitted_at || '').toString().slice(0, 10) || '—' }}</td>
          </tr>
          <tr v-if="!applications.length">
            <td colspan="6" class="p-4 text-gray-400">No applications yet.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getLoanApplications } from "../../api/finance";

const applications = ref([]);

function productName(a) {
  if (!a.product) return "—";
  return typeof a.product === "string" ? a.product : a.product.name || "—";
}
function formatMoney(v) {
  const n = Number(v);
  return Number.isFinite(n) ? n.toLocaleString() : "—";
}
function statusClass(s) {
  const map = {
    draft: "bg-gray-100",
    submitted: "bg-blue-100 text-blue-800",
    under_review: "bg-yellow-100 text-yellow-800",
    approved: "bg-green-100 text-green-800",
    rejected: "bg-red-100 text-red-800",
    disbursed: "bg-emerald-100 text-emerald-800",
  };
  return map[s] || "bg-gray-100";
}

onMounted(async () => {
  try {
    const { data } = await getLoanApplications();
    // Laravel resource collection: { data: [...] } or paginated { data: { data: [...] } }
    let list = data?.data ?? data;
    if (list && !Array.isArray(list) && Array.isArray(list.data)) list = list.data;
    applications.value = Array.isArray(list) ? list : [];
  } catch {
    applications.value = [];
  }
});
</script>
