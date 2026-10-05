<template>
  <div>
    <h2 class="text-2xl sm:text-3xl font-bold mb-2">AI Advisor</h2>
    <p class="text-gray-500 mb-6">Get farm-specific recommendations (demo rule-based engine)</p>

    <div v-if="loadError" class="bg-red-50 text-red-700 text-sm rounded-lg p-3 mb-4">{{ loadError }}</div>
    <div v-if="!loading && !farms.length" class="bg-amber-50 text-amber-900 text-sm rounded-lg p-4 mb-4">
      No farms found for your account.
      <router-link to="/farmer/farms" class="text-green-700 font-medium underline ml-1">Create a farm first →</router-link>
    </div>

    <form @submit.prevent="generate" class="bg-white rounded-lg shadow p-4 sm:p-6 mb-8 grid sm:grid-cols-3 gap-4">
      <select v-model="form.farm_id" required class="border rounded px-3 py-2 bg-white">
        <option disabled value="">Select farm</option>
        <option v-for="f in farms" :key="f.id" :value="String(f.id)">{{ f.name }}</option>
      </select>
      <select v-model="form.category" class="border rounded px-3 py-2 bg-white">
        <option value="general">General</option>
        <option value="pest">Pest</option>
        <option value="soil">Soil</option>
        <option value="weather">Weather</option>
        <option value="market">Market</option>
      </select>
      <button type="submit" :disabled="busy || !farms.length" class="bg-green-600 text-white rounded font-semibold disabled:opacity-50 py-2">
        {{ busy ? 'Generating…' : 'Generate recommendation' }}
      </button>
    </form>

    <div class="space-y-4">
      <div v-for="r in recommendations" :key="r.id" class="bg-white rounded-lg shadow p-5">
        <div class="flex justify-between items-start gap-2">
          <h3 class="font-bold">{{ r.title }}</h3>
          <span class="text-xs px-2 py-0.5 rounded shrink-0" :class="r.priority === 'high' ? 'bg-red-100 text-red-800' : 'bg-gray-100'">{{ r.priority }}</span>
        </div>
        <p class="text-sm text-gray-600 mt-2">{{ r.recommendation }}</p>
        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-400">
          <span>{{ r.category }}</span>
          <span>Confidence: {{ Math.round((r.confidence || 0) * 100) }}%</span>
          <button v-if="!r.accepted" @click="accept(r)" class="text-green-600 font-medium">Accept</button>
          <span v-else class="text-green-700">Accepted</span>
        </div>
      </div>
      <p v-if="!recommendations.length && !loading" class="text-gray-400">No recommendations yet. Generate one above.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getFarms } from "../../api/farms";
import { getAIRecommendations, generateAIRecommendation, acceptAIRecommendation } from "../../api/knowledge";
import { unwrapList } from "../../utils/apiData";

const farms = ref([]);
const recommendations = ref([]);
const busy = ref(false);
const loading = ref(true);
const loadError = ref("");
const form = ref({ farm_id: "", category: "general" });

async function load() {
  loading.value = true;
  loadError.value = "";
  try {
    const [fRes, rRes] = await Promise.all([
      getFarms({ per_page: 100 }),
      getAIRecommendations().catch(() => ({ data: [] })),
    ]);
    farms.value = unwrapList(fRes.data);
    recommendations.value = unwrapList(rRes.data);
    if (farms.value.length && !form.value.farm_id) {
      form.value.farm_id = String(farms.value[0].id);
    }
  } catch (e) {
    loadError.value = e.response?.data?.message || "Failed to load farms. Check you are logged in as a farmer.";
    farms.value = [];
  } finally {
    loading.value = false;
  }
}

async function generate() {
  if (!form.value.farm_id) return;
  busy.value = true;
  try {
    const farm = farms.value.find((f) => String(f.id) === String(form.value.farm_id));
    await generateAIRecommendation({
      farm_id: Number(form.value.farm_id),
      category: form.value.category,
      context: {
        latitude: farm?.latitude,
        longitude: farm?.longitude,
        region: farm?.region,
      },
    });
    await load();
  } catch (e) {
    loadError.value = e.response?.data?.message || "Failed to generate recommendation";
  } finally {
    busy.value = false;
  }
}

async function accept(r) {
  await acceptAIRecommendation(r.id);
  await load();
}

onMounted(load);
</script>
