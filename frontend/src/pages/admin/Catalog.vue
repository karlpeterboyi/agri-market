<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Master catalogs</h1>
    <div class="flex gap-2 mb-4">
      <button type="button" class="px-3 py-1.5 rounded text-sm border" :class="kind==='crops'?'bg-green-700 text-white':''" @click="kind='crops'; load()">Crops</button>
      <button type="button" class="px-3 py-1.5 rounded text-sm border" :class="kind==='commodities'?'bg-green-700 text-white':''" @click="kind='commodities'; load()">Commodities</button>
    </div>
    <form @submit.prevent="create" class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap gap-2">
      <input v-model="form.name" required placeholder="Name *" class="border rounded px-3 py-2 flex-1 min-w-[160px]" />
      <input v-model="form.category" placeholder="Category" class="border rounded px-3 py-2 w-40" />
      <button class="bg-green-600 text-white px-4 py-2 rounded font-semibold">Add</button>
    </form>
    <p v-if="error" class="text-red-600 text-sm mb-2">{{ error }}</p>
    <ul class="bg-white rounded-lg shadow divide-y">
      <li v-for="item in items" :key="item.id" class="p-3 flex justify-between gap-2">
        <span>{{ item.name }} <span class="text-gray-400 text-sm">{{ item.category || item.scientific_name }}</span></span>
        <button class="text-red-600 text-xs" @click="remove(item)">Delete</button>
      </li>
      <li v-if="!items.length" class="p-4 text-gray-400 text-sm">Empty catalog.</li>
    </ul>
  </div>
</template>
<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";
const kind = ref("crops");
const items = ref([]);
const error = ref("");
const form = ref({ name: "", category: "" });
async function load() {
  error.value = "";
  try {
    const { data } = await api.get(`/admin/catalog/${kind.value}`);
    items.value = Array.isArray(data) ? data : data.data || [];
  } catch (e) {
    error.value = e.response?.data?.message || "Failed";
    items.value = [];
  }
}
async function create() {
  error.value = "";
  try {
    const payload = { name: form.value.name };
    if (kind.value === "crops") payload.category = form.value.category;
    else payload.description = form.value.category;
    await api.post(`/admin/catalog/${kind.value}`, payload);
    form.value = { name: "", category: "" };
    await load();
  } catch (e) {
    error.value = e.response?.data?.message || "Create failed";
  }
}
async function remove(item) {
  if (!confirm("Delete?")) return;
  await api.delete(`/admin/catalog/${kind.value}/${item.id}`);
  await load();
}
onMounted(load);
</script>
