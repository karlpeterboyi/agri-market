<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">{{ t('farm.title') }}</h2>
      <div class="flex flex-wrap gap-2">
        <router-link to="/farmer/cycles/new" class="bg-white border border-green-600 text-green-700 font-semibold px-4 py-3 rounded-lg text-sm sm:text-base">
          + Crop cycle
        </router-link>
        <button @click="showForm = !showForm" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-3 rounded-lg shadow text-sm sm:text-base">
          {{ showForm ? t('common.cancel') : '+ ' + t('farm.addFarm') }}
        </button>
      </div>
    </div>

    <div v-if="showForm" class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
      <h3 class="text-lg font-semibold mb-4">{{ t('farm.addFarm') }}</h3>
      <form @submit.prevent="submitFarm" class="grid sm:grid-cols-2 gap-4">
        <input v-model="form.name" required :placeholder="t('farm.name')" class="border rounded px-3 py-2" />
        <select v-model="form.farm_type" required class="border rounded px-3 py-2">
          <option value="crop">Crop</option>
          <option value="livestock">Livestock</option>
          <option value="mixed">Mixed</option>
          <option value="horticulture">Horticulture</option>
          <option value="poultry">Poultry</option>
        </select>
        <input v-model="form.region" required :placeholder="t('common.region')" class="border rounded px-3 py-2" />
        <input v-model="form.district" required placeholder="District" class="border rounded px-3 py-2" />
        <input v-model.number="form.total_area_hectares" type="number" step="0.01" required :placeholder="t('farm.totalArea')" class="border rounded px-3 py-2" />
        <input v-model.number="form.cultivated_area_hectares" type="number" step="0.01" :placeholder="t('farm.cultivatedArea')" class="border rounded px-3 py-2" />
        <select v-model="form.ownership_type" class="border rounded px-3 py-2">
          <option value="individual">Individual</option>
          <option value="family">Family</option>
          <option value="cooperative">Cooperative</option>
        </select>
        <input v-model="form.country" placeholder="Country" class="border rounded px-3 py-2" />

        <!-- Location -->
        <div class="sm:col-span-2 border-t pt-4">
          <p class="text-sm text-gray-600 mb-2">{{ t('farm.locationHint') }}</p>
          <div class="grid sm:grid-cols-3 gap-3">
            <input v-model.number="form.latitude" type="number" step="any" :placeholder="t('farm.lat')" class="border rounded px-3 py-2" />
            <input v-model.number="form.longitude" type="number" step="any" :placeholder="t('farm.lng')" class="border rounded px-3 py-2" />
            <button type="button" @click="useMyLocation" :disabled="locating"
              class="border border-green-600 text-green-700 rounded px-3 py-2 text-sm font-medium disabled:opacity-50">
              {{ locating ? t('farm.locating') : t('farm.useMyLocation') }}
            </button>
          </div>
          <p v-if="geoError" class="text-red-500 text-xs mt-1">{{ geoError }}</p>
        </div>

        <div class="sm:col-span-2">
          <button type="submit" :disabled="saving" class="bg-green-600 text-white px-6 py-2 rounded font-semibold disabled:opacity-50">
            {{ saving ? t('common.saving') : t('common.save') }}
          </button>
        </div>
      </form>
      <p v-if="error" class="text-red-600 mt-2 text-sm">{{ error }}</p>
    </div>

    <div v-if="loading" class="text-gray-500">{{ t('common.loading') }}</div>
    <div v-else-if="farms.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
      {{ t('farm.noFarms') }}
    </div>
    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
      <div v-for="farm in farms" :key="farm.id" class="bg-white rounded-lg shadow p-5 hover:shadow-md transition">
        <div class="flex justify-between items-start gap-2">
          <h3 class="text-lg sm:text-xl font-bold text-gray-800">{{ farm.name }}</h3>
          <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded shrink-0">{{ farm.farm_type }}</span>
        </div>
        <p class="text-gray-500 text-sm mt-1">{{ farm.region }}, {{ farm.district }}</p>
        <p class="mt-2 text-sm"><span class="font-medium">Area:</span> {{ farm.total_area_hectares }} ha</p>
        <p v-if="farm.latitude && farm.longitude" class="text-xs text-gray-400 mt-1">
          📍 {{ Number(farm.latitude).toFixed(4) }}, {{ Number(farm.longitude).toFixed(4) }}
        </p>
        <div v-if="suggestions[farm.id]?.length" class="mt-3 bg-amber-50 rounded p-2 text-xs text-amber-900">
          <p class="font-semibold mb-1">{{ t('farm.suggestions') }}</p>
          <ul class="list-disc list-inside space-y-0.5">
            <li v-for="(s, i) in suggestions[farm.id]" :key="i">{{ s }}</li>
          </ul>
        </div>
        <div class="mt-4 flex flex-wrap gap-3 text-sm">
          <router-link :to="`/farmer/farms/${farm.id}`" class="text-green-600 font-medium hover:text-green-700">
            {{ t('farm.manage') }} →
          </router-link>
          <router-link :to="`/farmer/cycles/new?farm_id=${farm.id}`" class="text-green-700 font-medium hover:underline">
            + Crop cycle
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useI18n } from "../../i18n";
import { getFarms, createFarm } from "../../api/farms";
import { unwrapList } from "../../utils/apiData";

const { t } = useI18n();
const farms = ref([]);
const loading = ref(true);
const showForm = ref(false);
const saving = ref(false);
const locating = ref(false);
const error = ref("");
const geoError = ref("");
const suggestions = ref({});

const form = ref({
  name: "",
  farm_type: "crop",
  ownership_type: "individual",
  country: "Tanzania",
  region: "",
  district: "",
  total_area_hectares: null,
  cultivated_area_hectares: null,
  latitude: null,
  longitude: null,
});

function buildSuggestions(farm) {
  const tips = [];
  const region = (farm.region || "").toLowerCase();
  if (region.includes("morogoro") || region.includes("mbeya") || region.includes("iringa")) {
    tips.push("Strong maize & rice zones — consider certified seed for next season.");
  }
  if (region.includes("arusha") || region.includes("kilimanjaro")) {
    tips.push("Horticulture & dairy potential — check Knowledge Hub courses on vegetables.");
  }
  if (region.includes("mwanza") || region.includes("mara")) {
    tips.push("Lake zone — explore aquaculture & cassava value chains.");
  }
  if (farm.latitude && farm.longitude) {
    tips.push("Coordinates set — AI Advisor can use location context for weather & pest tips.");
    tips.push("Link crop cycles to this farm for location-aware yield tracking.");
  } else {
    tips.push("Add GPS coordinates for better weather and market suggestions.");
  }
  if ((farm.irrigated_area_hectares || 0) === 0 && (farm.total_area_hectares || 0) > 2) {
    tips.push("Consider water-harvesting or small-scale irrigation for climate resilience.");
  }
  return tips.slice(0, 3);
}

async function load() {
  loading.value = true;
  try {
    const { data } = await getFarms();
    farms.value = unwrapList(data);
    const map = {};
    for (const f of farms.value) {
      map[f.id] = buildSuggestions(f);
    }
    suggestions.value = map;
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load farms";
  } finally {
    loading.value = false;
  }
}

function useMyLocation() {
  geoError.value = "";
  if (!navigator.geolocation) {
    geoError.value = "Geolocation not supported on this device.";
    return;
  }
  locating.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      form.value.latitude = Number(pos.coords.latitude.toFixed(6));
      form.value.longitude = Number(pos.coords.longitude.toFixed(6));
      locating.value = false;
    },
    (err) => {
      geoError.value = err.message || "Unable to get location";
      locating.value = false;
    },
    { enableHighAccuracy: true, timeout: 15000 }
  );
}

async function submitFarm() {
  saving.value = true;
  error.value = "";
  try {
    await createFarm(form.value);
    showForm.value = false;
    form.value = {
      name: "", farm_type: "crop", ownership_type: "individual", country: "Tanzania",
      region: "", district: "", total_area_hectares: null, cultivated_area_hectares: null,
      latitude: null, longitude: null,
    };
    await load();
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to create farm";
  } finally {
    saving.value = false;
  }
}

onMounted(load);
</script>
