<template>
  <div>
    <h2 class="text-3xl font-bold mb-6">My Farm Analytics</h2>
    <div v-if="loading" class="text-gray-500">Loading…</div>
    <template v-else-if="data">
      <div class="grid md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded shadow p-5">
          <p class="text-gray-500 text-sm">Crop cycles</p>
          <p class="text-3xl font-bold">{{ data.production?.total_cycles || 0 }}</p>
        </div>
        <div class="bg-white rounded shadow p-5">
          <p class="text-gray-500 text-sm">Total area</p>
          <p class="text-3xl font-bold">{{ data.production?.total_area_ha || 0 }} ha</p>
        </div>
        <div class="bg-white rounded shadow p-5">
          <p class="text-gray-500 text-sm">Est. carbon (tCO₂e)</p>
          <p class="text-3xl font-bold text-green-700">{{ data.esg?.estimated_carbon_sequestration_tco2e || 0 }}</p>
        </div>
      </div>

      <section class="mb-8">
        <h3 class="text-xl font-semibold mb-3">Production by crop</h3>
        <div class="bg-white rounded shadow overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left"><tr>
              <th class="p-3">Crop</th><th class="p-3">Cycles</th><th class="p-3">Area (ha)</th><th class="p-3">Expected yield</th><th class="p-3">Actual yield</th>
            </tr></thead>
            <tbody>
              <tr v-for="(v, crop) in (data.production?.crop_summary || {})" :key="crop" class="border-t">
                <td class="p-3 font-medium">{{ crop }}</td>
                <td class="p-3">{{ v.cycles }}</td>
                <td class="p-3">{{ v.area_ha }}</td>
                <td class="p-3">{{ v.expected_yield }}</td>
                <td class="p-3">{{ v.actual_yield }}</td>
              </tr>
              <tr v-if="!Object.keys(data.production?.crop_summary || {}).length">
                <td colspan="5" class="p-4 text-gray-400">No crop cycle data yet. Add farms and cycles in Farm ERP.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section>
        <h3 class="text-xl font-semibold mb-3">Costs by activity</h3>
        <div class="bg-white rounded shadow overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left"><tr>
              <th class="p-3">Activity</th><th class="p-3">Count</th><th class="p-3">Input cost</th><th class="p-3">Labour</th><th class="p-3">Total</th>
            </tr></thead>
            <tbody>
              <tr v-for="c in (data.production?.cost_by_activity || [])" :key="c.activity_type" class="border-t">
                <td class="p-3">{{ c.activity_type }}</td>
                <td class="p-3">{{ c.count }}</td>
                <td class="p-3">{{ format(c.input_cost) }}</td>
                <td class="p-3">{{ format(c.labour_cost) }}</td>
                <td class="p-3 font-medium">{{ format(c.total_cost) }}</td>
              </tr>
              <tr v-if="!(data.production?.cost_by_activity || []).length">
                <td colspan="5" class="p-4 text-gray-400">No activity costs logged yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getFarmerDashboard } from "../../api/analytics";

const data = ref(null);
const loading = ref(true);

function format(n) {
  return "TZS " + Number(n || 0).toLocaleString();
}

onMounted(async () => {
  try {
    const res = await getFarmerDashboard();
    data.value = res.data;
  } catch (e) {
    data.value = { production: {}, esg: {} };
  } finally {
    loading.value = false;
  }
});
</script>
