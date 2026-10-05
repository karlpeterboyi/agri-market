<template>
  <div>
    <div class="mb-4">
      <router-link v-if="cycle?.farm_id" :to="`/farmer/farms/${cycle.farm_id}`" class="text-green-600 text-sm">
        ← Back to farm
      </router-link>
    </div>

    <div v-if="loading" class="text-gray-500">Loading…</div>
    <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded">{{ error }}</div>
    <template v-else-if="cycle">
      <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold">
          {{ cycle.crop?.name || 'Crop' }} — {{ cycle.season }}
        </h2>
        <p class="text-gray-500 mt-1">
          {{ cycle.field_block?.name || 'Block' }}
          · {{ cycle.area_hectares }} ha
          · <span class="capitalize">{{ cycle.status }}</span>
        </p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-1 overflow-x-auto border-b mb-6">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          @click="active = tab.id"
          class="px-3 py-2 text-sm whitespace-nowrap border-b-2 -mb-px"
          :class="active === tab.id ? 'border-green-600 text-green-700 font-semibold' : 'border-transparent text-gray-500'"
        >
          {{ tab.label }}
        </button>
      </div>

      <div v-if="active === 'overview'" class="bg-white rounded-lg shadow p-5 space-y-2 text-sm">
        <p><span class="text-gray-500">Planting:</span> {{ formatDate(cycle.planting_date) }}</p>
        <p><span class="text-gray-500">Expected harvest:</span> {{ formatDate(cycle.expected_harvest_date) }}</p>
        <p><span class="text-gray-500">Actual harvest:</span> {{ formatDate(cycle.actual_harvest_date) }}</p>
        <p><span class="text-gray-500">Expected yield:</span> {{ cycle.expected_yield ?? '—' }}</p>
        <p><span class="text-gray-500">Actual yield:</span> {{ cycle.actual_yield ?? '—' }}</p>
        <p><span class="text-gray-500">Farm:</span> {{ cycle.farm?.name }}</p>
      </div>

      <div v-else-if="active === 'harvest'" class="bg-white rounded-lg shadow p-5 max-w-md space-y-3">
        <h3 class="font-semibold">Record harvest</h3>
        <input v-model="harvest.actual_harvest_date" type="date" required class="w-full border rounded px-3 py-2" />
        <input v-model.number="harvest.actual_yield" type="number" step="0.01" placeholder="Actual yield" class="w-full border rounded px-3 py-2" />
        <textarea v-model="harvest.notes" placeholder="Notes" class="w-full border rounded px-3 py-2" rows="2" />
        <button type="button" @click="doHarvest" class="bg-green-600 text-white px-4 py-2 rounded font-semibold">Save harvest</button>
        <p v-if="harvestMsg" class="text-sm text-green-700">{{ harvestMsg }}</p>
      </div>

      <div v-else class="bg-white rounded-lg shadow p-6 text-gray-500 text-sm">
        <p class="font-medium text-gray-800 mb-2">{{ tabs.find(t => t.id === active)?.label }}</p>
        <p>
          Operational module for this cycle will connect tasks, inputs, irrigation, weather, diseases and expenses
          linked to <code class="text-xs bg-gray-100 px-1">crop_cycle_id = {{ cycle.id }}</code>.
        </p>
        <router-link
          v-if="active === 'tasks'"
          :to="`/farmer/activities?farm_id=${cycle.farm_id}`"
          class="inline-block mt-3 text-green-600 font-medium"
        >
          Log farm activities →
        </router-link>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "../../services/api";
import { harvestCropCycle } from "../../api/farms";
import { unwrapItem } from "../../utils/apiData";

const route = useRoute();
const cycle = ref(null);
const loading = ref(true);
const error = ref("");
const active = ref("overview");
const harvestMsg = ref("");
const harvest = ref({
  actual_harvest_date: new Date().toISOString().slice(0, 10),
  actual_yield: null,
  notes: "",
});

const tabs = [
  { id: "overview", label: "Overview" },
  { id: "planting", label: "Planting" },
  { id: "tasks", label: "Tasks" },
  { id: "inputs", label: "Inputs" },
  { id: "irrigation", label: "Irrigation" },
  { id: "weather", label: "Weather" },
  { id: "diseases", label: "Diseases" },
  { id: "expenses", label: "Expenses" },
  { id: "harvest", label: "Harvest" },
  { id: "sales", label: "Sales" },
  { id: "profit", label: "Profitability" },
];

function formatDate(d) {
  return d ? String(d).slice(0, 10) : "—";
}

async function load() {
  loading.value = true;
  error.value = "";
  try {
    const { data } = await api.get(`/crop-cycles/${route.params.id}`);
    cycle.value = unwrapItem(data) || data;
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load crop cycle";
  } finally {
    loading.value = false;
  }
}

async function doHarvest() {
  harvestMsg.value = "";
  try {
    const { data } = await harvestCropCycle(route.params.id, harvest.value);
    cycle.value = unwrapItem(data) || data;
    harvestMsg.value = "Harvest recorded.";
    active.value = "overview";
  } catch (e) {
    harvestMsg.value = e.response?.data?.message || "Harvest failed";
  }
}

onMounted(load);
</script>
