<template>
  <div class="max-w-4xl mx-auto p-4 sm:p-6">
    <h1 class="text-2xl font-bold mb-1">Finance dossier</h1>
    <p class="text-sm text-gray-500 mb-6">
      Credibility pack: wallet, marketplace settlements, loans and NMB bank links — useful for lenders, partners and government reporting.
    </p>

    <p v-if="loading" class="text-gray-400">Loading…</p>
    <p v-else-if="error" class="text-red-600">{{ error }}</p>
    <template v-else-if="dossier">
      <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-xs text-gray-500">Credibility score</p>
          <p class="text-3xl font-bold text-green-700">{{ dossier.credibility?.score }}</p>
          <p class="text-sm capitalize">{{ dossier.credibility?.band }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-xs text-gray-500">Wallet available</p>
          <p class="text-xl font-bold">TZS {{ Number(dossier.wallet?.available_balance || 0).toLocaleString() }}</p>
          <p class="text-xs text-gray-400">Pending: {{ Number(dossier.wallet?.pending_balance || 0).toLocaleString() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-xs text-gray-500">Marketplace volume</p>
          <p class="text-sm">Paid: TZS {{ Number(dossier.marketplace?.total_paid_tzs || 0).toLocaleString() }}</p>
          <p class="text-sm">Received: TZS {{ Number(dossier.marketplace?.total_received_tzs || 0).toLocaleString() }}</p>
        </div>
      </div>

      <section class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-bold mb-2">NMB bank links</h2>
        <div v-for="l in dossier.nmb_links || []" :key="l.id" class="text-sm border-t py-2">
          {{ l.account_number }} · {{ l.account_name }}
          <span class="text-xs ml-2" :class="l.verified ? 'text-green-700' : 'text-amber-700'">{{ l.verified ? 'verified' : 'unverified' }}</span>
        </div>
        <p v-if="!(dossier.nmb_links || []).length" class="text-gray-400 text-sm">
          No NMB account linked.
          <router-link to="/finance/nmb" class="text-green-700">Link now →</router-link>
        </p>
      </section>

      <section class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-bold mb-2">Loans</h2>
        <div v-for="l in dossier.loans || []" :key="l.id" class="text-sm border-t py-2 flex justify-between">
          <span>{{ l.application_number || l.id }} · {{ l.product }}</span>
          <span>{{ l.status }} · TZS {{ Number(l.requested_amount || 0).toLocaleString() }}</span>
        </div>
        <p v-if="!(dossier.loans || []).length" class="text-gray-400 text-sm">No loan applications.</p>
      </section>

      <section class="bg-white rounded-lg shadow p-4">
        <h2 class="font-bold mb-2">Recent wallet movements</h2>
        <div v-for="t in dossier.wallet_transactions || []" :key="t.id" class="text-sm border-t py-2 flex justify-between">
          <span>{{ t.description || t.type }}</span>
          <span>{{ t.type }} {{ Number(t.amount || 0).toLocaleString() }}</span>
        </div>
        <p v-if="!(dossier.wallet_transactions || []).length" class="text-gray-400 text-sm">No wallet transactions yet.</p>
      </section>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const dossier = ref(null);
const loading = ref(true);
const error = ref("");

onMounted(async () => {
  try {
    const { data } = await api.get("/finance-dossier");
    dossier.value = data;
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load dossier";
  } finally {
    loading.value = false;
  }
});
</script>
