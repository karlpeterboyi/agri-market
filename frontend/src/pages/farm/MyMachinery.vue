<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:justify-between gap-3 mb-6">
      <div>
        <h2 class="text-2xl font-bold">{{ t('machinery.myTitle') }}</h2>
        <p class="text-xs text-gray-500 mt-1">
          {{ t('machinery.farmerHint') }} · {{ t('common.role') }}: <strong>{{ auth.role || '—' }}</strong>
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <router-link to="/machinery" class="text-green-600 text-sm font-medium self-center">{{ t('machinery.publicMarket') }} →</router-link>
        <button type="button" @click="showForm = !showForm" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
          {{ showForm ? t('common.cancel') : '+ ' + t('machinery.list') }}
        </button>
      </div>
    </div>

    <form v-if="showForm" @submit.prevent="save" class="bg-white rounded-lg shadow p-4 mb-6 grid sm:grid-cols-2 gap-3">
      <select v-model="form.machinery_category_id" required class="border rounded px-3 py-2 bg-white" @change="onCategory">
        <option disabled value="">Category *</option>
        <option v-for="c in categories" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
      </select>
      <select v-model="form.machinery_brand_id" required class="border rounded px-3 py-2 bg-white" @change="onBrand">
        <option disabled value="">Brand *</option>
        <option v-for="b in brands" :key="b.id" :value="String(b.id)">{{ b.name }}</option>
      </select>
      <select v-model="form.machinery_model_id" required class="border rounded px-3 py-2 bg-white sm:col-span-2">
        <option disabled value="">Model *</option>
        <option v-for="m in filteredModels" :key="m.id" :value="String(m.id)">{{ m.name }}</option>
      </select>
      <input v-model="form.title" required :placeholder="t('machinery.titlePlaceholder')" class="border rounded px-3 py-2 sm:col-span-2" />
      <textarea v-model="form.description" required :placeholder="t('common.description') + ' *'" class="border rounded px-3 py-2 sm:col-span-2" rows="2" />
      <input v-model.number="form.manufacture_year" type="number" min="1950" max="2100" placeholder="Year" class="border rounded px-3 py-2" />
      <input v-model.number="form.horsepower" type="number" min="0" placeholder="HP" class="border rounded px-3 py-2" />
      <input v-model.number="form.sale_price" type="number" min="0" :placeholder="t('machinery.salePrice')" class="border rounded px-3 py-2" />
      <input v-model.number="form.rental_price" type="number" min="0" :placeholder="t('machinery.rentalPrice')" class="border rounded px-3 py-2" />
      <select v-model="form.rental_period" class="border rounded px-3 py-2 bg-white">
        <option value="">Rental period</option>
        <option value="hour">Hour</option>
        <option value="day">Day</option>
        <option value="week">Week</option>
        <option value="month">Month</option>
      </select>
      <select v-model="form.condition" class="border rounded px-3 py-2 bg-white">
        <option value="Used">Used</option>
        <option value="New">New</option>
        <option value="Refurbished">Refurbished</option>
      </select>
      <label class="flex items-center gap-2 text-sm"><input v-model="form.for_sale" type="checkbox" /> {{ t('machinery.forSale') }}</label>
      <label class="flex items-center gap-2 text-sm"><input v-model="form.for_rent" type="checkbox" /> {{ t('machinery.forRent') }}</label>
      <label class="flex items-center gap-2 text-sm sm:col-span-2"><input v-model="form.operator_included" type="checkbox" /> Operator included</label>
      <input v-model="form.region" required :placeholder="t('common.region') + ' *'" class="border rounded px-3 py-2" />
      <input v-model="form.district" required :placeholder="t('common.district') + ' *'" class="border rounded px-3 py-2" />
      <input v-model="form.ward" placeholder="Ward" class="border rounded px-3 py-2" />
      <input v-model="form.village" placeholder="Village" class="border rounded px-3 py-2" />
      <select v-model="form.status" class="border rounded px-3 py-2 bg-white sm:col-span-2">
        <option value="active">active</option>
        <option value="draft">draft</option>
        <option value="maintenance">maintenance</option>
      </select>
      <p v-if="error" class="sm:col-span-2 text-red-600 text-sm whitespace-pre-wrap">{{ error }}</p>
      <button type="submit" :disabled="saving" class="sm:col-span-2 bg-green-600 text-white rounded py-2 font-semibold disabled:opacity-50">
        {{ saving ? t('common.saving') : t('machinery.publish') }}
      </button>
    </form>

    <div class="bg-white rounded-lg shadow divide-y">
      <div v-for="m in items" :key="m.id" class="p-4 flex flex-col sm:flex-row sm:justify-between gap-2">
        <div>
          <p class="font-semibold">{{ m.title || ('#' + m.id) }}</p>
          <p class="text-sm text-gray-500">
            {{ m.brand?.name }} {{ m.model?.name }}
            · {{ m.region }}{{ m.district ? ', ' + m.district : '' }}
            · {{ m.status }}
            <span v-if="m.sale_price"> · {{ t('machinery.sale') }} TZS {{ Number(m.sale_price).toLocaleString() }}</span>
            <span v-if="m.rental_price"> · {{ t('machinery.rent') }} TZS {{ Number(m.rental_price).toLocaleString() }}</span>
          </p>
        </div>
        <button type="button" class="text-red-600 text-sm" @click="remove(m)">{{ t('common.delete') }}</button>
      </div>
      <p v-if="!items.length" class="p-4 text-gray-400 text-sm">{{ t('machinery.empty') }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../../services/api";
import { unwrapList } from "../../utils/apiData";
import { useAuthStore } from "../../stores/auth";
import { useI18n } from "../../i18n";

const { t } = useI18n();
const auth = useAuthStore();
const items = ref([]);
const categories = ref([]);
const brands = ref([]);
const models = ref([]);
const showForm = ref(false);
const saving = ref(false);
const error = ref("");

const blank = () => ({
  machinery_category_id: "",
  machinery_brand_id: "",
  machinery_model_id: "",
  title: "",
  description: "",
  manufacture_year: null,
  horsepower: null,
  sale_price: null,
  rental_price: null,
  rental_period: "day",
  condition: "Used",
  for_sale: false,
  for_rent: true,
  operator_included: false,
  region: "",
  district: "",
  ward: "",
  village: "",
  status: "active",
});
const form = ref(blank());

const filteredModels = computed(() => {
  return models.value.filter((m) => {
    const brandOk = !form.value.machinery_brand_id || String(m.machinery_brand_id) === String(form.value.machinery_brand_id);
    const catOk = !form.value.machinery_category_id || String(m.machinery_category_id) === String(form.value.machinery_category_id);
    return brandOk && catOk;
  });
});

function onCategory() {
  form.value.machinery_model_id = "";
}
function onBrand() {
  form.value.machinery_model_id = "";
}

async function loadTaxonomy() {
  const [c, b, m] = await Promise.all([
    api.get("/machinery-categories"),
    api.get("/machinery-brands"),
    api.get("/machinery-models"),
  ]);
  categories.value = unwrapList(c.data);
  brands.value = unwrapList(b.data);
  models.value = unwrapList(m.data);
  if (!categories.value.length || !brands.value.length || !models.value.length) {
    error.value = "Taxonomy empty. Run: php artisan db:seed --class=MachineryCategorySeeder && php artisan db:seed --class=MachineryBrandSeeder && php artisan db:seed --class=MachineryModelSeeder";
  }
}

async function load() {
  try {
    const { data } = await api.get("/machinery-my-listings");
    items.value = unwrapList(data);
    if (!items.value.length && Array.isArray(data)) items.value = data;
  } catch {
    items.value = [];
  }
}

async function save() {
  saving.value = true;
  error.value = "";
  try {
    try { await auth.loadProfile(); } catch (_) {}
    const payload = {
      ...form.value,
      machinery_category_id: Number(form.value.machinery_category_id),
      machinery_brand_id: Number(form.value.machinery_brand_id),
      machinery_model_id: Number(form.value.machinery_model_id),
      description: form.value.description || form.value.title,
    };
    await api.post("/machinery", payload);
    showForm.value = false;
    form.value = blank();
    await load();
  } catch (e) {
    const d = e.response?.data;
    const errs = d?.errors;
    error.value =
      (d?.message || "") +
      (d?.your_role ? `\n(${t("common.role")}: ${d.your_role})` : "") +
      (errs ? "\n" + Object.entries(errs).map(([k, v]) => `${k}: ${[].concat(v).join(", ")}`).join("\n") : "");
    if (!error.value) error.value = t("common.error");
  } finally {
    saving.value = false;
  }
}

async function remove(m) {
  if (!confirm("Delete listing?")) return;
  try {
    await api.delete(`/machinery/${m.id}`);
    await load();
  } catch (e) {
    alert(e.response?.data?.message || "Delete failed");
  }
}

onMounted(async () => {
  try { await auth.loadProfile(); } catch (_) {}
  await Promise.all([loadTaxonomy(), load()]);
});
</script>
