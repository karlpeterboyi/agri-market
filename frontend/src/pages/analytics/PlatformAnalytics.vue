<template>
  <div>
    <h2 class="text-3xl font-bold mb-6">Platform Analytics</h2>
    <div v-if="loading" class="text-gray-500">Loading…</div>
    <template v-else-if="overview">
      <div class="grid md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded shadow p-5">
          <p class="text-gray-500 text-sm">Users</p>
          <p class="text-3xl font-bold">{{ overview.users?.total }}</p>
          <p class="text-xs text-gray-400">{{ overview.users?.farmers }} farmers · {{ overview.users?.buyers }} buyers</p>
        </div>
        <div class="bg-white rounded shadow p-5">
          <p class="text-gray-500 text-sm">Farms</p>
          <p class="text-3xl font-bold">{{ overview.farms?.total }}</p>
          <p class="text-xs text-gray-400">{{ overview.farms?.cultivated_area_ha }} ha cultivated</p>
        </div>
        <div class="bg-white rounded shadow p-5">
          <p class="text-gray-500 text-sm">GMV</p>
          <p class="text-3xl font-bold text-green-700">TZS {{ Number(overview.marketplace?.gmv || 0).toLocaleString() }}</p>
          <p class="text-xs text-gray-400">{{ overview.marketplace?.orders }} orders</p>
        </div>
        <div class="bg-white rounded shadow p-5">
          <p class="text-gray-500 text-sm">Loans approved</p>
          <p class="text-3xl font-bold">{{ overview.finance?.loans_approved }}</p>
          <p class="text-xs text-gray-400">{{ overview.finance?.loan_applications }} applications</p>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded shadow p-5">
          <h3 class="font-semibold mb-3">Marketplace</h3>
          <p class="text-sm">Active listings: <strong>{{ overview.marketplace?.active_listings }}</strong></p>
          <p class="text-sm">Escrow balance: <strong>TZS {{ Number(overview.marketplace?.escrow_balance || 0).toLocaleString() }}</strong></p>
        </div>
        <div class="bg-white rounded shadow p-5">
          <h3 class="font-semibold mb-3">Government</h3>
          <p class="text-sm">Subsidy applications: <strong>{{ overview.government?.subsidy_applications }}</strong></p>
          <p class="text-sm">Approved: <strong>{{ overview.government?.subsidy_approved }}</strong> · Disbursed: <strong>{{ overview.government?.subsidy_disbursed }}</strong></p>
        </div>
      </div>
    </template>
    <p v-else class="text-gray-400">Unable to load platform analytics (admin/government role required).</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getPlatformOverview } from "../../api/analytics";

const overview = ref(null);
const loading = ref(true);

onMounted(async () => {
  try {
    const { data } = await getPlatformOverview();
    overview.value = data.overview || data;
  } catch {
    overview.value = null;
  } finally {
    loading.value = false;
  }
});
</script>
