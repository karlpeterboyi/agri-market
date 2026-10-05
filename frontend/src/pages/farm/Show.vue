<template>
  <div class="max-w-5xl mx-auto p-4">
    <div class="mb-4">
      <router-link to="/farmer/farms" class="text-green-600 text-sm">← Back to farms</router-link>
    </div>

    <div v-if="loading" class="text-gray-500 py-10">Loading farm…</div>

    <div v-else-if="error" class="bg-red-50 text-red-700 rounded-lg p-4 space-y-2">
      <p class="font-medium">{{ error }}</p>
      <p v-if="debugHint" class="text-xs text-red-600/80 font-mono break-all">{{ debugHint }}</p>
      <button type="button" class="text-sm underline" @click="load">Retry</button>
      <router-link to="/farmer/farms" class="block text-sm underline">Return to farms</router-link>
    </div>

    <template v-else-if="farm">
      <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold">{{ farm.name }}</h2>
        <p class="text-gray-500 text-sm mt-1">
          {{ farm.region }}{{ farm.district ? ", " + farm.district : "" }}
          · {{ farm.total_area_hectares ?? "—" }} ha
          · <span class="capitalize">{{ farm.farm_type }}</span>
        </p>
        <p v-if="farm.farm_code" class="text-xs text-gray-400 mt-1">Code: {{ farm.farm_code }}</p>
      </div>

      <p class="text-xs text-gray-500 mb-4 font-mono">Farm → Field blocks → Crop cycles</p>

      <section class="mb-8">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-lg font-bold">Farm boundary</h3>
          <span v-if="farmBoundary?.area_hectares" class="text-sm text-gray-500">
            {{ farmBoundary.area_hectares }} ha mapped
          </span>
        </div>
        <BoundaryEditor
          v-if="mapReady"
          v-model="boundaryGeo"
          :center="{ lat: Number(farm.latitude) || -6.8, lng: Number(farm.longitude) || 39.2 }"
          :saving="savingBoundary"
          @save="saveFarmBoundary"
        />
        <p v-else class="text-sm text-gray-400">Map loading…</p>
      </section>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-gray-500 text-xs">Field blocks</p>
          <p class="text-2xl font-bold">{{ blocks.length }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-gray-500 text-xs">Crop cycles</p>
          <p class="text-2xl font-bold">{{ allCycles.length }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-gray-500 text-xs">Cultivated</p>
          <p class="text-2xl font-bold">{{ farm.cultivated_area_hectares || 0 }} ha</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-gray-500 text-xs">GPS</p>
          <p class="text-sm font-medium mt-1">
            {{
              farm.latitude && farm.longitude
                ? Number(farm.latitude).toFixed(4) + ", " + Number(farm.longitude).toFixed(4)
                : "Not set"
            }}
          </p>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h3 class="text-xl font-semibold">Field blocks</h3>
        <button
          type="button"
          class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold"
          @click="showBlockForm = !showBlockForm"
        >
          {{ showBlockForm ? "Cancel" : "+ Add field block" }}
        </button>
      </div>

      <form
        v-if="showBlockForm"
        class="bg-white rounded-lg shadow p-4 mb-4 grid sm:grid-cols-2 gap-3"
        @submit.prevent="addBlock"
      >
        <input v-model="blockForm.name" required placeholder="Block name *" class="border rounded px-3 py-2" />
        <input v-model="blockForm.code" placeholder="Code (e.g. A)" class="border rounded px-3 py-2" />
        <input
          v-model.number="blockForm.area_hectares"
          type="number"
          step="0.01"
          min="0.01"
          placeholder="Area (ha)"
          class="border rounded px-3 py-2"
        />
        <input v-model="blockForm.soil_type" placeholder="Soil type" class="border rounded px-3 py-2" />
        <select v-model="blockForm.irrigation_type" class="border rounded px-3 py-2">
          <option value="">Irrigation (optional)</option>
          <option value="rainfed">Rainfed</option>
          <option value="drip">Drip</option>
          <option value="sprinkler">Sprinkler</option>
          <option value="flood">Flood</option>
        </select>
        <button type="submit" :disabled="savingBlock" class="bg-green-600 text-white rounded px-4 py-2 font-semibold">
          {{ savingBlock ? "Saving…" : "Save block" }}
        </button>
        <p v-if="blockError" class="sm:col-span-2 text-red-600 text-sm">{{ blockError }}</p>
      </form>

      <div v-if="!blocks.length" class="bg-white rounded-lg shadow p-6 text-gray-400 text-sm mb-6">
        No field blocks yet. Add Block A, B, C… then attach crop cycles under each block.
      </div>

      <div v-else class="space-y-4 mb-6">
        <div v-for="block in blocks" :key="block.id" class="bg-white rounded-lg shadow overflow-hidden">
          <div class="p-4 border-b bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
              <h4 class="font-bold text-lg">
                {{ block.name }}
                <span v-if="block.code" class="text-xs font-normal text-gray-400 ml-1">{{ block.code }}</span>
              </h4>
              <p class="text-sm text-gray-500">
                {{ block.area_hectares ?? "—" }} {{ block.area_unit || "ha" }}
                <span v-if="block.soil_type"> · {{ block.soil_type }}</span>
                <span v-if="block.irrigation_type"> · {{ block.irrigation_type }}</span>
              </p>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                class="text-sm border border-green-600 text-green-700 px-3 py-1.5 rounded-lg font-medium"
                @click="blockBoundaryOpen = blockBoundaryOpen === block.id ? null : block.id"
              >
                {{ blockBoundaryOpen === block.id ? "Hide map" : "Map boundary" }}
              </button>
              <router-link
                :to="`/farmer/cycles/new?farm_id=${farm.id}&field_block_id=${block.id}`"
                class="text-sm bg-green-600 text-white px-3 py-1.5 rounded-lg font-medium"
              >
                + Crop cycle
              </router-link>
            </div>
          </div>

          <div v-if="blockBoundaryOpen === block.id && mapReady" class="p-3 border-b">
            <BoundaryEditor
              :model-value="block.boundary && block.boundary.type ? block.boundary : null"
              :center="{ lat: Number(farm.latitude) || -6.8, lng: Number(farm.longitude) || 39.2 }"
              @save="(geo) => saveBlockBoundary(block, geo)"
            />
          </div>

          <div class="divide-y">
            <div
              v-for="cycle in cyclesForBlock(block.id)"
              :key="cycle.id"
              class="p-4 flex flex-col sm:flex-row sm:justify-between gap-2"
            >
              <div>
                <p class="font-semibold">{{ cycle.crop?.name || "Crop" }} — {{ cycle.season }}</p>
                <p class="text-xs text-gray-500">
                  {{ formatDate(cycle.planting_date) }} · {{ cycle.area_hectares }} ha · {{ cycle.status }}
                </p>
              </div>
              <router-link :to="`/farmer/cycles/${cycle.id}`" class="text-sm text-green-700">View →</router-link>
            </div>
            <p v-if="!cyclesForBlock(block.id).length" class="p-4 text-sm text-gray-400">No crop cycles on this block.</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, defineAsyncComponent } from "vue";
import { useRoute } from "vue-router";
import api from "../../services/api";
import { getFarm, getFieldBlocks, createFieldBlock, getCropCycles } from "../../api/farms";
import { unwrapList } from "../../utils/apiData";

const BoundaryEditor = defineAsyncComponent(() =>
  import("../../components/BoundaryEditor.vue").catch(() => ({
    template: '<p class="text-sm text-amber-700 p-3">Map unavailable. Install leaflet or check console.</p>',
  }))
);

const route = useRoute();
const farm = ref(null);
const blocks = ref([]);
const allCycles = ref([]);
const loading = ref(true);
const error = ref("");
const debugHint = ref("");
const showBlockForm = ref(false);
const savingBlock = ref(false);
const blockError = ref("");
const blockForm = ref({
  name: "",
  code: "",
  area_hectares: null,
  soil_type: "",
  irrigation_type: "",
});
const farmBoundary = ref(null);
const boundaryGeo = ref(null);
const savingBoundary = ref(false);
const blockBoundaryOpen = ref(null);
const mapReady = ref(true);

function cyclesForBlock(blockId) {
  return allCycles.value.filter((c) => String(c.field_block_id) === String(blockId));
}

function formatDate(d) {
  return d ? String(d).slice(0, 10) : "—";
}

/** Accept Resource, nested data, or plain model */
function pickFarm(payload) {
  if (!payload) return null;
  if (payload.id) return payload;
  if (payload.data?.id) return payload.data;
  if (payload.data?.data?.id) return payload.data.data;
  return null;
}

async function load() {
  loading.value = true;
  error.value = "";
  debugHint.value = "";
  const id = route.params.id;

  try {
    const token = localStorage.getItem("token");
    if (!token) {
      error.value = "You are not logged in. Please log in again.";
      farm.value = null;
      return;
    }

    const fRes = await getFarm(id);
    const parsed = pickFarm(fRes.data);
    if (!parsed?.id) {
      error.value = "Farm response did not include an id.";
      debugHint.value = JSON.stringify(fRes.data)?.slice(0, 300);
      farm.value = null;
      return;
    }
    farm.value = parsed;

    try {
      const bRes = await getFieldBlocks({ farm_id: id, per_page: 100 });
      blocks.value = unwrapList(bRes.data);
    } catch (e) {
      console.warn("field blocks", e);
      blocks.value = Array.isArray(farm.value.field_blocks) ? farm.value.field_blocks : [];
    }

    try {
      const cRes = await getCropCycles({ farm_id: id, per_page: 100 });
      allCycles.value = unwrapList(cRes.data);
    } catch (e) {
      console.warn("cycles", e);
      allCycles.value = [];
    }

    await loadBoundary();
  } catch (e) {
    console.error("farm load", e);
    farm.value = null;
    const status = e.response?.status;
    const apiMsg = e.response?.data?.message;
    const code = e.code;
    if (apiMsg) {
      error.value = apiMsg;
    } else if (status === 401) {
      error.value = "Session expired. Please log in again.";
    } else if (status === 403) {
      error.value = "You do not own this farm.";
    } else if (status === 404) {
      error.value = "Farm not found.";
    } else if (status) {
      error.value = `Could not load farm (HTTP ${status}).`;
    } else if (code === "ERR_NETWORK") {
      error.value =
        "Cannot reach API (network). If you use a phone browser, set VITE_API_URL to your computer IP (not localhost).";
    } else {
      error.value = e.message || "Could not load farm.";
    }
    debugHint.value = [
      `url: ${e.config?.baseURL || ""}${e.config?.url || ""}`,
      `code: ${code || "—"}`,
      `status: ${status || "—"}`,
    ].join(" | ");
  } finally {
    loading.value = false;
  }
}

async function addBlock() {
  savingBlock.value = true;
  blockError.value = "";
  try {
    await createFieldBlock({
      farm_id: Number(farm.value.id),
      name: blockForm.value.name,
      code: blockForm.value.code || null,
      area_hectares: blockForm.value.area_hectares || 0.01,
      soil_type: blockForm.value.soil_type || null,
      irrigation_type: blockForm.value.irrigation_type || null,
      boundary: [],
      status: "active",
    });
    showBlockForm.value = false;
    blockForm.value = { name: "", code: "", area_hectares: null, soil_type: "", irrigation_type: "" };
    await load();
  } catch (e) {
    blockError.value =
      e.response?.data?.message ||
      Object.values(e.response?.data?.errors || {})
        .flat()
        .join(" ") ||
      "Failed to create field block";
  } finally {
    savingBlock.value = false;
  }
}

async function loadBoundary() {
  if (!farm.value?.id) return;
  try {
    const { data } = await api.get(`/farms/${farm.value.id}/boundary`);
    farmBoundary.value = data.boundary;
    boundaryGeo.value = data.boundary?.boundary || null;
  } catch {
    farmBoundary.value = null;
    boundaryGeo.value = null;
  }
}

async function saveFarmBoundary(geo) {
  savingBoundary.value = true;
  try {
    const { data } = await api.post(`/farms/${farm.value.id}/boundary`, { boundary: geo });
    farmBoundary.value = data.boundary;
    boundaryGeo.value = data.boundary?.boundary || geo;
    if (data.centroid) {
      farm.value.latitude = data.centroid.lat;
      farm.value.longitude = data.centroid.lng;
    }
  } catch (e) {
    alert(e.response?.data?.message || "Failed to save farm boundary");
  } finally {
    savingBoundary.value = false;
  }
}

async function saveBlockBoundary(block, geo) {
  try {
    const { data } = await api.post(`/field-blocks/${block.id}/boundary`, { boundary: geo });
    const idx = blocks.value.findIndex((b) => b.id === block.id);
    if (idx >= 0) {
      blocks.value[idx] = {
        ...blocks.value[idx],
        boundary: data.field_block?.boundary || geo,
        area_hectares: data.area_hectares ?? blocks.value[idx].area_hectares,
      };
    }
    blockBoundaryOpen.value = null;
  } catch (e) {
    alert(e.response?.data?.message || "Failed to save field boundary");
  }
}

onMounted(load);
</script>
