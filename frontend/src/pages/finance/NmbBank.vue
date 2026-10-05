<template>
  <div class="max-w-4xl mx-auto p-4 sm:p-6">
    <h1 class="text-2xl font-bold mb-1">NMB Bank (Open Banking)</h1>
    <p class="text-sm text-gray-500 mb-6">
      Agri-fintech bridge to NMB OBP sandbox — accounts, balances, customer onboarding, and payments
      for loan disbursement and seller payouts.
    </p>

    <div class="bg-white rounded-lg shadow p-4 mb-6 text-sm">
      <p><span class="text-gray-500">Status:</span>
        <strong>{{ status?.mock ? 'Mock mode (set NMB_OBP_* in .env for live sandbox)' : 'Live sandbox' }}</strong>
      </p>
      <p class="mt-1"><span class="text-gray-500">Bank ID:</span> {{ status?.bank_id }}</p>
      <p class="mt-1"><span class="text-gray-500">API:</span> {{ status?.base_url }} / {{ status?.api_version }}</p>
      <a v-if="status?.docs" :href="status.docs" target="_blank" class="text-green-700 text-xs underline mt-2 inline-block">OBP docs →</a>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 mb-8">
      <button type="button" class="bg-green-700 text-white rounded-lg py-2 font-semibold" @click="loadAccounts">Refresh accounts</button>
      <button type="button" class="border border-green-700 text-green-800 rounded-lg py-2 font-semibold" @click="onboard">Onboard as NMB customer</button>
    </div>
    <p v-if="msg" class="text-sm mb-4" :class="msgError ? 'text-red-600' : 'text-green-700'">{{ msg }}</p>

    <h2 class="font-bold mb-2">Accounts & balances</h2>
    <div class="bg-white rounded-lg shadow divide-y mb-8">
      <div v-for="a in accounts" :key="a.id || a.account_id" class="p-4 text-sm">
        <p class="font-semibold">{{ a.label || a.id || a.account_id }}</p>
        <p class="text-gray-500">{{ balanceText(a) }}</p>
        <button type="button" class="text-green-700 text-xs mt-2" @click="loadTx(a.id || a.account_id)">View transactions</button>
      </div>
      <p v-if="!accounts.length" class="p-4 text-gray-400">No accounts loaded yet.</p>
    </div>

    <h2 class="font-bold mb-2">Link your NMB account number</h2>
    <form @submit.prevent="link" class="bg-white rounded-lg shadow p-4 grid sm:grid-cols-2 gap-3 mb-8">
      <input v-model="linkForm.account_number" required placeholder="Account number *" class="border rounded px-3 py-2" />
      <input v-model="linkForm.account_name" placeholder="Account name" class="border rounded px-3 py-2" />
      <button type="submit" class="sm:col-span-2 bg-gray-900 text-white rounded py-2 font-semibold">Save link</button>
    </form>

    <div v-if="transactions.length" class="mb-8">
      <h2 class="font-bold mb-2">Recent transactions</h2>
      <div class="bg-white rounded-lg shadow divide-y text-sm">
        <div v-for="t in transactions" :key="t.id" class="p-3">
          <p class="font-medium">{{ t.details?.description || t.id }}</p>
          <p class="text-gray-500">{{ t.details?.value?.amount }} {{ t.details?.value?.currency }}</p>
        </div>
      </div>
    </div>

    <h2 class="font-bold mb-2">Sandbox payment (demo)</h2>
    <form @submit.prevent="pay" class="bg-white rounded-lg shadow p-4 grid sm:grid-cols-2 gap-3">
      <input v-model="payForm.from_account_id" required placeholder="From account id *" class="border rounded px-3 py-2" />
      <input v-model="payForm.to_account_id" placeholder="To account id (ACCOUNT type)" class="border rounded px-3 py-2" />
      <input v-model.number="payForm.amount" type="number" required min="1" placeholder="Amount TZS *" class="border rounded px-3 py-2" />
      <input v-model="payForm.description" placeholder="Description" class="border rounded px-3 py-2" />
      <button type="submit" class="sm:col-span-2 bg-green-700 text-white rounded py-2 font-semibold">Send payment request</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const status = ref(null);
const accounts = ref([]);
const transactions = ref([]);
const msg = ref("");
const msgError = ref(false);
const linkForm = ref({ account_number: "", account_name: "" });
const payForm = ref({ from_account_id: "mkulima-escrow-001", to_account_id: "farmer-acc-1", amount: 10000, description: "MkulimaHub demo transfer" });

function balanceText(a) {
  const b = a.balance;
  if (!b) return "—";
  if (typeof b === "object") return `${b.amount || ""} ${b.currency || "TZS"}`;
  return String(b);
}

async function loadStatus() {
  const { data } = await api.get("/nmb/status");
  status.value = data;
}

async function loadAccounts() {
  msg.value = "";
  try {
    const { data } = await api.get("/nmb/accounts");
    accounts.value = data.accounts || data || [];
    if (accounts.value[0]) {
      payForm.value.from_account_id = accounts.value[0].id || accounts.value[0].account_id || payForm.value.from_account_id;
    }
  } catch (e) {
    msgError.value = true;
    msg.value = e.response?.data?.message || e.message;
  }
}

async function loadTx(accountId) {
  try {
    const { data } = await api.get("/nmb/transactions", { params: { account_id: accountId } });
    transactions.value = data.transactions || [];
  } catch (e) {
    msgError.value = true;
    msg.value = e.response?.data?.message || "Failed to load transactions";
  }
}

async function onboard() {
  msgError.value = false;
  try {
    const { data } = await api.post("/nmb/customers", {});
    msg.value = data.message + (data.mock ? " [mock]" : "");
  } catch (e) {
    msgError.value = true;
    msg.value = e.response?.data?.message || "Onboard failed";
  }
}

async function link() {
  msgError.value = false;
  try {
    await api.post("/nmb/link-account", linkForm.value);
    msg.value = "Account linked";
    linkForm.value = { account_number: "", account_name: "" };
  } catch (e) {
    msgError.value = true;
    msg.value = e.response?.data?.message || "Link failed";
  }
}

async function pay() {
  msgError.value = false;
  try {
    const { data } = await api.post("/nmb/pay", {
      ...payForm.value,
      purpose: "other",
      currency: "TZS",
    });
    msg.value = data.message + " · " + (data.transaction_request?.id || "") + (data.mock ? " [mock]" : "");
  } catch (e) {
    msgError.value = true;
    msg.value = e.response?.data?.message || "Payment failed";
  }
}

onMounted(async () => {
  try {
    await loadStatus();
    await loadAccounts();
  } catch (_) {}
});
</script>
