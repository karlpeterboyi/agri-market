<template>
  <div class="max-w-6xl mx-auto p-4 sm:p-6">
    <h1 class="text-2xl sm:text-3xl font-bold mb-2">Financial Institution Portal</h1>
    <p class="text-gray-500 mb-6 text-sm">
      Register your bank, MFI, SACCO or insurer. Publish loan products (equipment, working capital, agribusiness)
      and insurance products (crop, livestock, weather). Farmers apply online.
    </p>

    <div v-if="!institution" class="bg-white rounded-xl shadow p-6 mb-8">
      <h2 class="font-bold text-lg mb-4">Register your institution</h2>
      <form @submit.prevent="saveInstitution" class="grid sm:grid-cols-2 gap-3">
        <input v-model="fiForm.name" required placeholder="Institution name *" class="border rounded px-3 py-2" />
        <select v-model="fiForm.institution_type" class="border rounded px-3 py-2 bg-white">
          <option value="bank">Bank</option>
          <option value="mfi">Microfinance (MFI)</option>
          <option value="sacco">SACCO / Cooperative</option>
          <option value="insurer">Insurance company</option>
          <option value="fintech">Fintech</option>
          <option value="government">Government fund</option>
        </select>
        <input v-model="fiForm.email" type="email" placeholder="Email" class="border rounded px-3 py-2" />
        <input v-model="fiForm.phone" placeholder="Phone" class="border rounded px-3 py-2" />
        <input v-model="fiForm.website" placeholder="Website" class="border rounded px-3 py-2 sm:col-span-2" />
        <textarea v-model="fiForm.description" placeholder="About your institution" class="border rounded px-3 py-2 sm:col-span-2" rows="2" />
        <p v-if="error" class="sm:col-span-2 text-red-600 text-sm">{{ error }}</p>
        <button type="submit" class="sm:col-span-2 bg-green-700 text-white rounded py-2 font-semibold">Register institution</button>
      </form>
    </div>

    <template v-else>
      <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-500">Institution</p>
          <p class="font-bold">{{ institution.name }}</p>
          <p class="text-xs text-gray-400">{{ institution.institution_type }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-500">Loan products</p>
          <p class="text-2xl font-bold">{{ stats.loan_products ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-500">Insurance products</p>
          <p class="text-2xl font-bold">{{ stats.insurance_products ?? 0 }}</p>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-8">
        <!-- Loans -->
        <div>
          <h2 class="font-bold text-lg mb-3">Loan products</h2>
          <form @submit.prevent="saveLoan" class="bg-white rounded-lg shadow p-4 grid gap-2 mb-4">
            <input v-model="loan.name" required placeholder="Product name * e.g. Tractor finance" class="border rounded px-3 py-2" />
            <select v-model="loan.loan_type" class="border rounded px-3 py-2 bg-white">
              <option value="equipment">Equipment loan</option>
              <option value="input_credit">Input credit</option>
              <option value="working_capital">Working capital</option>
              <option value="warehouse_receipt">Warehouse receipt</option>
              <option value="agribusiness">Agribusiness</option>
              <option value="livestock">Livestock</option>
            </select>
            <div class="grid grid-cols-2 gap-2">
              <input v-model.number="loan.minimum_amount" type="number" required placeholder="Min amount" class="border rounded px-3 py-2" />
              <input v-model.number="loan.maximum_amount" type="number" required placeholder="Max amount" class="border rounded px-3 py-2" />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <input v-model.number="loan.interest_rate" type="number" step="0.01" required placeholder="Interest % p.a." class="border rounded px-3 py-2" />
              <input v-model.number="loan.maximum_duration_months" type="number" required placeholder="Max months" class="border rounded px-3 py-2" />
            </div>
            <input v-model.number="loan.minimum_duration_months" type="number" placeholder="Min months" class="border rounded px-3 py-2" />
            <textarea v-model="loan.description" placeholder="Description" class="border rounded px-3 py-2" rows="2" />
            <p v-if="loanError" class="text-red-600 text-sm">{{ loanError }}</p>
            <button type="submit" class="bg-green-700 text-white rounded py-2 font-semibold">Publish loan product</button>
          </form>
          <div class="bg-white rounded-lg shadow divide-y">
            <div v-for="p in loans" :key="p.id" class="p-3 text-sm">
              <p class="font-semibold">{{ p.name }}</p>
              <p class="text-gray-500">{{ p.loan_type }} · {{ p.interest_rate }}% · TZS {{ Number(p.minimum_amount).toLocaleString() }}–{{ Number(p.maximum_amount).toLocaleString() }}</p>
            </div>
            <p v-if="!loans.length" class="p-3 text-gray-400 text-sm">No loan products yet.</p>
          </div>
        </div>

        <!-- Insurance -->
        <div>
          <h2 class="font-bold text-lg mb-3">Insurance products</h2>
          <form @submit.prevent="saveInsurance" class="bg-white rounded-lg shadow p-4 grid gap-2 mb-4">
            <input v-model="ins.name" required placeholder="Product name * e.g. Maize crop cover" class="border rounded px-3 py-2" />
            <select v-model="ins.insurance_type" class="border rounded px-3 py-2 bg-white">
              <option value="crop">Crop</option>
              <option value="livestock">Livestock</option>
              <option value="equipment">Equipment</option>
              <option value="weather">Weather index</option>
              <option value="multi">Multi-peril</option>
              <option value="life">Life / health</option>
            </select>
            <input v-model.number="ins.premium_rate" type="number" step="0.001" placeholder="Premium rate (e.g. 0.05 = 5%)" class="border rounded px-3 py-2" />
            <input v-model.number="ins.maximum_cover" type="number" placeholder="Max cover (TZS)" class="border rounded px-3 py-2" />
            <textarea v-model="ins.description" placeholder="Description" class="border rounded px-3 py-2" rows="2" />
            <p v-if="insError" class="text-red-600 text-sm">{{ insError }}</p>
            <button type="submit" class="bg-green-700 text-white rounded py-2 font-semibold">Publish insurance product</button>
          </form>
          <div class="bg-white rounded-lg shadow divide-y">
            <div v-for="p in insurance" :key="p.id" class="p-3 text-sm">
              <p class="font-semibold">{{ p.name }}</p>
              <p class="text-gray-500">{{ p.insurance_type }} · rate {{ p.premium_rate ?? '—' }}</p>
            </div>
            <p v-if="!insurance.length" class="p-3 text-gray-400 text-sm">No insurance products yet.</p>
          </div>
        </div>
      </div>
      <!-- Applications inbox -->
      <div class="mt-10">
        <h2 class="font-bold text-lg mb-3">Applications inbox</h2>
        <p class="text-sm text-gray-500 mb-3">Review farmer applications for your loan products.</p>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
          <table class="w-full text-sm min-w-[700px]">
            <thead class="bg-gray-50 text-left">
              <tr>
                <th class="p-3">Ref</th>
                <th class="p-3">Applicant</th>
                <th class="p-3">Product</th>
                <th class="p-3">Amount</th>
                <th class="p-3">Status</th>
                <th class="p-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="a in applications" :key="a.id" class="border-t">
                <td class="p-3 font-mono text-xs">{{ a.application_number || a.reference || a.id }}</td>
                <td class="p-3">{{ a.applicant?.name || '—' }}</td>
                <td class="p-3">{{ a.product?.name || a.product || '—' }}</td>
                <td class="p-3">TZS {{ Number((a.requested_amount != null ? a.requested_amount : a.amount) || 0).toLocaleString() }}</td>
                <td class="p-3">{{ a.status }}</td>
                <td class="p-3 space-x-2 whitespace-nowrap">
                  <button v-if="a.status==='submitted'" type="button" class="text-xs text-blue-700" @click="reviewApp(a)">Review</button>
                  <button v-if="['submitted','under_review'].includes(a.status)" type="button" class="text-xs text-green-700" @click="approveApp(a)">Approve</button>
                  <button v-if="['submitted','under_review'].includes(a.status)" type="button" class="text-xs text-red-600" @click="rejectApp(a)">Reject</button>
                </td>
              </tr>
              <tr v-if="!applications.length"><td colspan="6" class="p-4 text-gray-400">No applications yet.</td></tr>
            </tbody>
          </table>
        </div>
        <p v-if="appMsg" class="text-sm text-green-700 mt-2">{{ appMsg }}</p>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const institution = ref(null);
const stats = ref({});
const loans = ref([]);
const insurance = ref([]);
const applications = ref([]);
const appMsg = ref("");
const error = ref("");
const loanError = ref("");
const insError = ref("");

const fiForm = ref({
  name: "",
  institution_type: "mfi",
  email: "",
  phone: "",
  website: "",
  description: "",
});
const loan = ref({
  name: "",
  loan_type: "equipment",
  minimum_amount: 500000,
  maximum_amount: 50000000,
  interest_rate: 18,
  minimum_duration_months: 3,
  maximum_duration_months: 36,
  description: "",
});
const ins = ref({
  name: "",
  insurance_type: "crop",
  premium_rate: 0.05,
  maximum_cover: null,
  description: "",
});

async function loadApplications() {
  appMsg.value = "";
  try {
    const { data } = await api.get("/loan-applications");
    let list = data?.data ?? data;
    if (list && !Array.isArray(list) && Array.isArray(list.data)) list = list.data;
    applications.value = Array.isArray(list) ? list : [];
    if (!applications.value.length) {
      appMsg.value = "No submitted applications yet. Farmers must apply and submit a loan.";
    }
  } catch (e) {
    applications.value = [];
    appMsg.value = e.response?.data?.message || "Failed to load applications (check financier login).";
  }
}

async function reviewApp(a) {
  appMsg.value = "";
  try {
    await api.post(`/loan-applications/${a.id}/review`);
    appMsg.value = "Moved to under review";
    await loadApplications();
  } catch (e) {
    appMsg.value = e.response?.data?.message || "Review failed";
  }
}
async function approveApp(a) {
  appMsg.value = "";
  try {
    await api.post(`/loan-applications/${a.id}/approve`);
    appMsg.value = "Approved";
    await loadApplications();
  } catch (e) {
    appMsg.value = e.response?.data?.message || "Approve failed";
  }
}
async function rejectApp(a) {
  const remarks = prompt("Rejection reason?", "Does not meet criteria");
  if (remarks == null) return;
  appMsg.value = "";
  try {
    await api.post(`/loan-applications/${a.id}/reject`, { remarks });
    appMsg.value = "Rejected";
    await loadApplications();
  } catch (e) {
    appMsg.value = e.response?.data?.message || "Reject failed";
  }
}

async function load() {
  try {
    const { data } = await api.get("/finance/dashboard");
    institution.value = data.institution;
    stats.value = data.stats || {};
    loans.value = data.loan_products || [];
    insurance.value = data.insurance_products || [];
    if (Array.isArray(data.applications) && data.applications.length) {
      applications.value = data.applications;
    }
    await loadApplications();
  } catch (e) {
    if (e.response?.status === 404) {
      institution.value = null;
    }
  }
}

async function saveInstitution() {
  error.value = "";
  try {
    await api.post("/finance/institution", fiForm.value);
    await load();
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to register";
  }
}

async function saveLoan() {
  loanError.value = "";
  try {
    await api.post("/loan-products", loan.value);
    loan.value.name = "";
    await load();
  } catch (e) {
    loanError.value = e.response?.data?.message || JSON.stringify(e.response?.data?.errors || e.message);
  }
}

async function saveInsurance() {
  insError.value = "";
  try {
    await api.post("/insurance-products", ins.value);
    ins.value.name = "";
    await load();
  } catch (e) {
    insError.value = e.response?.data?.message || JSON.stringify(e.response?.data?.errors || e.message);
  }
}

onMounted(load);
</script>
