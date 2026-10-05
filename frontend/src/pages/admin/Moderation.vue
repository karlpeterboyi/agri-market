<template>
  <div>
    <h1 class="text-2xl font-bold mb-2">{{ t('admin.moderation') }}</h1>
    <p class="text-sm text-gray-500 mb-4">Admin can CRUD all marketplace items. Machinery follows schema FKs.</p>

    <div class="flex flex-wrap gap-2 mb-4">
      <button v-for="titem in tabs" :key="titem.id" type="button" @click="tab=titem.id; load()"
        class="px-3 py-1.5 rounded text-sm border"
        :class="tab===titem.id ? 'bg-green-700 text-white border-green-700' : 'bg-white'">
        {{ titem.label }}
      </button>
      <button type="button" class="ml-auto text-sm underline" @click="load">{{ t('common.refresh') }}</button>
    </div>

    <form v-if="tab==='machinery'" @submit.prevent="createMachinery" class="bg-white rounded-lg shadow p-4 mb-4 grid sm:grid-cols-2 gap-3">
      <p class="sm:col-span-2 text-sm font-semibold text-green-800">Create machinery (admin)</p>
      <select v-model="machForm.machinery_category_id" required class="border rounded px-3 py-2 bg-white">
        <option disabled value="">Category *</option>
        <option v-for="c in categories" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
      </select>
      <select v-model="machForm.machinery_brand_id" required class="border rounded px-3 py-2 bg-white" @change="machForm.machinery_model_id=''">
        <option disabled value="">Brand *</option>
        <option v-for="b in brands" :key="b.id" :value="String(b.id)">{{ b.name }}</option>
      </select>
      <select v-model="machForm.machinery_model_id" required class="border rounded px-3 py-2 bg-white sm:col-span-2">
        <option disabled value="">Model *</option>
        <option v-for="m in adminModels" :key="m.id" :value="String(m.id)">{{ m.name }}</option>
      </select>
      <input v-model="machForm.title" required placeholder="Title *" class="border rounded px-3 py-2 sm:col-span-2" />
      <textarea v-model="machForm.description" required placeholder="Description *" class="border rounded px-3 py-2 sm:col-span-2" rows="2" />
      <input v-model.number="machForm.sale_price" type="number" placeholder="Sale price" class="border rounded px-3 py-2" />
      <input v-model.number="machForm.rental_price" type="number" placeholder="Rental price" class="border rounded px-3 py-2" />
      <input v-model="machForm.region" required placeholder="Region *" class="border rounded px-3 py-2" />
      <input v-model="machForm.district" required placeholder="District *" class="border rounded px-3 py-2" />
      <button type="submit" class="sm:col-span-2 bg-green-600 text-white rounded py-2 font-semibold">{{ t('common.create') }}</button>
      <p v-if="createError" class="sm:col-span-2 text-red-600 text-sm whitespace-pre-wrap">{{ createError }}</p>
    </form>

    <p v-if="error" class="text-red-600 text-sm mb-3">{{ error }}</p>
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm min-w-[640px]">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-2">Item</th>
            <th class="p-2">Owner</th>
            <th class="p-2">Price / status</th>
            <th class="p-2">{{ t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="tab+row.id" class="border-t">
            <td class="p-2 font-medium">{{ label(row) }}</td>
            <td class="p-2">{{ owner(row) }}</td>
            <td class="p-2">{{ price(row) }} · {{ row.status || '—' }}</td>
            <td class="p-2 whitespace-nowrap">
              <button class="text-xs text-red-600" @click="remove(row)">{{ t('common.delete') }}</button>
            </td>
          </tr>
          <tr v-if="!rows.length"><td colspan="4" class="p-4 text-gray-400">{{ t('common.noData') }}</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../../services/api";
import { unwrapList } from "../../utils/apiData";
import { useI18n } from "../../i18n";
const { t } = useI18n();
const tabs = [
  { id: "products", label: "Produce" },
  { id: "inputs", label: "Inputs" },
  { id: "machinery", label: "Machinery" },
  { id: "services", label: "Services" },
];
const tab = ref("machinery");
const rows = ref([]);
const error = ref("");
const createError = ref("");
const categories = ref([]);
const brands = ref([]);
const models = ref([]);
const machForm = ref({
  machinery_category_id: "",
  machinery_brand_id: "",
  machinery_model_id: "",
  title: "",
  description: "",
  sale_price: null,
  rental_price: null,
  region: "",
  district: "",
});

const adminModels = computed(() =>
  models.value.filter((m) => {
    const brandOk = !machForm.value.machinery_brand_id || String(m.machinery_brand_id) === String(machForm.value.machinery_brand_id);
    const catOk = !machForm.value.machinery_category_id || String(m.machinery_category_id) === String(machForm.value.machinery_category_id);
    return brandOk && catOk;
  })
);

function label(r) {
  return r.title || r.name || r.commodity?.name || `#${r.id}`;
}
function owner(r) {
  return r.seller?.name || r.provider?.name || r.owner?.name || "—";
}
function price(r) {
  const p = r.price ?? r.sale_price ?? r.rental_price;
  return p != null ? `TZS ${Number(p).toLocaleString()}` : "—";
}
async function loadTaxonomy() {
  try {
    const [c, b, m] = await Promise.all([
      api.get("/machinery-categories"),
      api.get("/machinery-brands"),
      api.get("/machinery-models"),
    ]);
    categories.value = unwrapList(c.data);
    brands.value = unwrapList(b.data);
    models.value = unwrapList(m.data);
  } catch (_) {}
}
async function load() {
  error.value = "";
  try {
    const { data } = await api.get(`/admin/moderation/${tab.value}`);
    rows.value = Array.isArray(data) ? data : data.data || [];
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load";
    rows.value = [];
  }
}
async function remove(row) {
  if (!confirm("Delete?")) return;
  await api.delete(`/admin/moderation/${tab.value}/${row.id}`);
  await load();
}
async function createMachinery() {
  createError.value = "";
  try {
    await api.post("/admin/moderation/machinery", {
      machinery_category_id: Number(machForm.value.machinery_category_id),
      machinery_brand_id: Number(machForm.value.machinery_brand_id),
      machinery_model_id: Number(machForm.value.machinery_model_id),
      title: machForm.value.title,
      description: machForm.value.description || machForm.value.title,
      sale_price: machForm.value.sale_price,
      rental_price: machForm.value.rental_price,
      region: machForm.value.region,
      district: machForm.value.district,
      for_sale: !!machForm.value.sale_price,
      for_rent: true,
      status: "active",
      condition: "Used",
    });
    machForm.value = {
      machinery_category_id: "",
      machinery_brand_id: "",
      machinery_model_id: "",
      title: "",
      description: "",
      sale_price: null,
      rental_price: null,
      region: "",
      district: "",
    };
    await load();
  } catch (e) {
    const d = e.response?.data;
    const errs = d?.errors;
    createError.value =
      d?.message ||
      (errs ? Object.entries(errs).map(([k, v]) => `${k}: ${[].concat(v).join(", ")}`).join("\n") : "Create failed");
  }
}
onMounted(async () => {
  await loadTaxonomy();
  await load();
});
</script>
