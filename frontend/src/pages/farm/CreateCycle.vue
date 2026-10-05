<template>
  <div class="max-w-xl mx-auto">
    <router-link :to="backLink" class="text-green-600 text-sm">← Back</router-link>
    <h2 class="text-2xl sm:text-3xl font-bold mt-2 mb-2">New crop cycle</h2>
    <p class="text-sm text-gray-500 mb-6">Architecture: Farm → Field block → Crop cycle → Crop</p>

    <div v-if="!farms.length && !loading" class="bg-amber-50 text-amber-900 text-sm rounded p-4 mb-4">
      No farms. <router-link to="/farmer/farms" class="underline font-medium">Add a farm</router-link> first.
    </div>
    <div v-if="farms.length && !blocks.length && form.farm_id" class="bg-amber-50 text-amber-900 text-sm rounded p-4 mb-4">
      This farm has no field blocks.
      <router-link :to="`/farmer/farms/${form.farm_id}`" class="underline font-medium">Add a field block</router-link> first.
    </div>
    <div v-if="!crops.length && !loading" class="bg-amber-50 text-amber-900 text-sm rounded p-4 mb-4">
      No crops seeded. Run: <code class="text-xs">php artisan db:seed --class=CropSeeder</code>
    </div>

    <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-4 sm:p-6 space-y-4">
      <div>
        <label class="block text-sm text-gray-600 mb-1">Farm</label>
        <select v-model="form.farm_id" required class="w-full border rounded px-3 py-2 bg-white" @change="onFarmChange">
          <option disabled value="">Select farm</option>
          <option v-for="f in farms" :key="f.id" :value="String(f.id)">{{ f.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Field block *</label>
        <select v-model="form.field_block_id" required class="w-full border rounded px-3 py-2 bg-white">
          <option disabled value="">Select field block</option>
          <option v-for="b in blocks" :key="b.id" :value="String(b.id)">
            {{ b.name }} ({{ b.area_hectares }} ha)
          </option>
        </select>
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Crop *</label>
        <select v-model="form.crop_id" required class="w-full border rounded px-3 py-2 bg-white">
          <option disabled value="">Select crop</option>
          <option v-for="c in crops" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Season *</label>
        <input v-model="form.season" required placeholder="e.g. Masika 2026" class="w-full border rounded px-3 py-2" />
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Planting date *</label>
          <input v-model="form.planting_date" type="date" required class="w-full border rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Expected harvest</label>
          <input v-model="form.expected_harvest_date" type="date" class="w-full border rounded px-3 py-2" />
        </div>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Area (ha) *</label>
          <input v-model.number="form.area_hectares" type="number" step="0.01" required min="0.01" class="w-full border rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Expected yield</label>
          <input v-model.number="form.expected_yield" type="number" step="0.01" class="w-full border rounded px-3 py-2" />
        </div>
      </div>
      <div>
        <label class="block text-sm text-gray-600 mb-1">Status</label>
        <select v-model="form.status" class="w-full border rounded px-3 py-2 bg-white">
          <option value="planned">Planned</option>
          <option value="planted">Planted</option>
          <option value="growing">Growing</option>
        </select>
      </div>
      <p v-if="error" class="text-red-600 text-sm whitespace-pre-wrap">{{ error }}</p>
      <button type="submit" :disabled="saving || !blocks.length || !crops.length"
        class="w-full sm:w-auto bg-green-600 text-white px-6 py-2.5 rounded-lg font-semibold disabled:opacity-50">
        {{ saving ? 'Saving…' : 'Create crop cycle' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { getFarms, getFieldBlocks, createCropCycle } from "../../api/farms";
import api from "../../services/api";
import { unwrapList } from "../../utils/apiData";

const route = useRoute();
const router = useRouter();

const farms = ref([]);
const blocks = ref([]);
const crops = ref([]);
const saving = ref(false);
const loading = ref(true);
const error = ref("");

const form = ref({
  farm_id: route.query.farm_id ? String(route.query.farm_id) : "",
  field_block_id: route.query.field_block_id ? String(route.query.field_block_id) : "",
  crop_id: "",
  season: "",
  planting_date: "",
  expected_harvest_date: "",
  area_hectares: null,
  expected_yield: null,
  status: "planned",
});

const backLink = computed(() =>
  form.value.farm_id ? `/farmer/farms/${form.value.farm_id}` : "/farmer/farms"
);

async function loadBlocks() {
  if (!form.value.farm_id) {
    blocks.value = [];
    return;
  }
  const { data } = await getFieldBlocks({ farm_id: form.value.farm_id, per_page: 100 });
  blocks.value = unwrapList(data);
  if (form.value.field_block_id && !blocks.value.find((b) => String(b.id) === form.value.field_block_id)) {
    form.value.field_block_id = "";
  }
  if (!form.value.field_block_id && blocks.value.length === 1) {
    form.value.field_block_id = String(blocks.value[0].id);
  }
  // default area from block
  const b = blocks.value.find((x) => String(x.id) === form.value.field_block_id);
  if (b && !form.value.area_hectares) form.value.area_hectares = Number(b.area_hectares);
}

async function onFarmChange() {
  form.value.field_block_id = "";
  await loadBlocks();
}

async function submit() {
  saving.value = true;
  error.value = "";
  try {
    const payload = {
      field_block_id: Number(form.value.field_block_id),
      farm_id: Number(form.value.farm_id),
      crop_id: Number(form.value.crop_id),
      season: form.value.season,
      planting_date: form.value.planting_date,
      expected_harvest_date: form.value.expected_harvest_date || null,
      area_hectares: form.value.area_hectares,
      expected_yield: form.value.expected_yield,
      status: form.value.status,
    };
    const { data } = await createCropCycle(payload);
    const cycle = data.data || data;
    const id = cycle.id;
    if (id) {
      router.push(`/farmer/cycles/${id}`);
    } else {
      router.push(`/farmer/farms/${form.value.farm_id}`);
    }
  } catch (e) {
    const errs = e.response?.data?.errors;
    error.value =
      e.response?.data?.message ||
      (errs ? Object.entries(errs).map(([k, v]) => `${k}: ${v}`).join("\n") : null) ||
      "Failed to save crop cycle";
  } finally {
    saving.value = false;
  }
}

onMounted(async () => {
  loading.value = true;
  try {
    const [f, c] = await Promise.all([
      getFarms({ per_page: 100 }),
      api.get("/crops", { params: { per_page: 100 } }).catch(() => ({ data: [] })),
    ]);
    farms.value = unwrapList(f.data);
    crops.value = unwrapList(c.data);
    if (!form.value.farm_id && farms.value.length) {
      form.value.farm_id = String(farms.value[0].id);
    }
    await loadBlocks();
  } finally {
    loading.value = false;
  }
});
</script>
