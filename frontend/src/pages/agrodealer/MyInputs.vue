<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:justify-between gap-3 mb-6">
      <h2 class="text-2xl font-bold">My Input Listings</h2>
      <button type="button" @click="showForm=!showForm" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
        {{ showForm ? 'Cancel' : '+ Add input' }}
      </button>
    </div>

    <form v-if="showForm" @submit.prevent="save" class="bg-white rounded-lg shadow p-4 mb-6 grid sm:grid-cols-2 gap-3">
      <input v-model="form.name" required placeholder="Product name *" class="border rounded px-3 py-2" />
      <input v-model="form.brand" placeholder="Brand" class="border rounded px-3 py-2" />
      <input v-model.number="form.price" type="number" required min="0" placeholder="Price (TZS) *" class="border rounded px-3 py-2" />
      <input v-model.number="form.stock" type="number" required min="0" placeholder="Stock *" class="border rounded px-3 py-2" />
      <input v-model="form.unit" required placeholder="Unit (kg, bag, L) *" class="border rounded px-3 py-2" />
      <input v-model="form.region" placeholder="Region" class="border rounded px-3 py-2" />
      <input v-model="form.district" placeholder="District" class="border rounded px-3 py-2" />
      <textarea v-model="form.description" placeholder="Description" class="border rounded px-3 py-2 sm:col-span-2" rows="2" />
      <p v-if="error" class="sm:col-span-2 text-red-600 text-sm whitespace-pre-wrap">{{ error }}</p>
      <button type="submit" :disabled="saving" class="sm:col-span-2 bg-green-600 text-white rounded py-2 font-semibold disabled:opacity-50">
        {{ saving ? 'Saving…' : 'Publish to marketplace' }}
      </button>
    </form>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm min-w-[600px]">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="p-3">Name</th>
            <th class="p-3">Price</th>
            <th class="p-3">Stock</th>
            <th class="p-3">Region</th>
            <th class="p-3">Status</th>
            <th class="p-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="i in items" :key="i.id" class="border-t">
            <td class="p-3 font-medium">{{ i.name }}</td>
            <td class="p-3">TZS {{ Number(i.price).toLocaleString() }}</td>
            <td class="p-3">{{ i.stock }} {{ i.unit }}</td>
            <td class="p-3">{{ i.region }}</td>
            <td class="p-3">{{ i.status }}</td>
            <td class="p-3"><button type="button" @click="remove(i)" class="text-red-600 text-xs">Delete</button></td>
          </tr>
          <tr v-if="!items.length">
            <td colspan="6" class="p-4 text-gray-400">No inputs yet. Add one to appear on the marketplace.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const items = ref([]);
const showForm = ref(false);
const saving = ref(false);
const error = ref("");
const blank = () => ({
  name: "",
  brand: "",
  price: null,
  stock: null,
  unit: "kg",
  region: "",
  district: "",
  description: "",
  status: "available",
});
const form = ref(blank());

async function load() {
  try {
    const { data } = await api.get("/my-input-listings");
    items.value = Array.isArray(data) ? data : data.data || [];
  } catch (e) {
    items.value = [];
    console.error(e);
  }
}

async function save() {
  saving.value = true;
  error.value = "";
  try {
    await api.post("/input-listings", {
      ...form.value,
      region: form.value.region || "Tanzania",
      district: form.value.district || "Unknown",
      description: form.value.description || form.value.name,
    });
    showForm.value = false;
    form.value = blank();
    await load();
  } catch (e) {
    const d = e.response?.data;
    const errs = d?.errors;
    error.value =
      d?.message ||
      (errs ? Object.entries(errs).map(([k, v]) => `${k}: ${[].concat(v).join(", ")}`).join("\n") : null) ||
      "Failed to save input listing";
  } finally {
    saving.value = false;
  }
}

async function remove(i) {
  if (!confirm("Delete?")) return;
  try {
    await api.delete(`/input-listings/${i.id}`);
    await load();
  } catch (e) {
    alert(e.response?.data?.message || "Delete failed");
  }
}

onMounted(load);
</script>
