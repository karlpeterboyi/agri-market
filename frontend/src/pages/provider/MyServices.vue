<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:justify-between gap-3 mb-4">
      <div>
        <h2 class="text-2xl font-bold">My Services</h2>
        <p class="text-sm text-gray-500 mt-1">
          <strong>Service</strong> = what you sell (title + base price). It appears on the public marketplace immediately.
          <strong>Packages</strong> are optional price tiers (e.g. Half day / Full day) under a service — not required.
        </p>
      </div>
      <button type="button" @click="showForm = !showForm" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold h-fit">
        {{ showForm ? 'Cancel' : '+ New service' }}
      </button>
    </div>

    <form v-if="showForm" @submit.prevent="save" class="bg-white rounded-lg shadow p-4 mb-6 grid sm:grid-cols-2 gap-3">
      <input v-model="form.title" required placeholder="Service title * e.g. Corn shelling" class="border rounded px-3 py-2 sm:col-span-2" />
      <input v-model.number="form.price" type="number" required min="0" placeholder="Base price (TZS) *" class="border rounded px-3 py-2" />
      <select v-model="form.pricing_type" class="border rounded px-3 py-2 bg-white">
        <option value="fixed">Fixed</option>
        <option value="starting_from">Starting from</option>
        <option value="per_hour">Per hour</option>
        <option value="per_acre">Per acre</option>
      </select>
      <input v-model="form.region" placeholder="Region" class="border rounded px-3 py-2" />
      <input v-model="form.district" placeholder="District" class="border rounded px-3 py-2" />
      <input v-model="form.phone" placeholder="Phone" class="border rounded px-3 py-2 sm:col-span-2" />
      <textarea v-model="form.description" placeholder="Description" class="border rounded px-3 py-2 sm:col-span-2" rows="2" />
      <p v-if="error" class="sm:col-span-2 text-red-600 text-sm whitespace-pre-wrap">{{ error }}</p>
      <button type="submit" :disabled="saving" class="sm:col-span-2 bg-green-600 text-white rounded py-2 font-semibold disabled:opacity-50">
        {{ saving ? 'Publishing…' : 'Publish service' }}
      </button>
    </form>

    <div class="bg-white rounded-lg shadow divide-y mb-10">
      <div v-for="s in items" :key="s.id" class="p-4">
        <div class="flex flex-col sm:flex-row sm:justify-between gap-2">
          <div>
            <p class="font-semibold">{{ s.title }}</p>
            <p class="text-sm text-gray-500">
              Base: TZS {{ Number(s.price || 0).toLocaleString() }} · {{ s.pricing_type || 'fixed' }} · {{ s.status || 'active' }}
              <span v-if="s.region"> · {{ s.region }}</span>
            </p>
            <p v-if="s.packages?.length" class="text-xs text-gray-400 mt-1">
              Packages: {{ s.packages.map(p => p.name + ' (TZS ' + Number(p.price).toLocaleString() + ')').join(', ') }}
            </p>
          </div>
          <button type="button" @click="remove(s)" class="text-red-600 text-sm">Delete</button>
        </div>
      </div>
      <p v-if="!items.length" class="p-4 text-gray-400 text-sm">No services yet. Publish one — packages are optional.</p>
    </div>

    <h3 class="text-lg font-bold mb-2">Optional packages</h3>
    <p class="text-sm text-gray-500 mb-3">Add only if you need multiple price options for one service.</p>
    <form @submit.prevent="savePackage" class="bg-white rounded-lg shadow p-4 mb-4 grid sm:grid-cols-2 gap-3">
      <select v-model="pkg.service_id" required class="border rounded px-3 py-2 bg-white">
        <option disabled value="">Select service *</option>
        <option v-for="s in items" :key="s.id" :value="s.id">{{ s.title }}</option>
      </select>
      <input v-model="pkg.name" required placeholder="Package name * e.g. Full day" class="border rounded px-3 py-2" />
      <input v-model.number="pkg.price" type="number" required min="0" placeholder="Package price *" class="border rounded px-3 py-2" />
      <input v-model="pkg.duration" placeholder="Duration e.g. 8 hours" class="border rounded px-3 py-2" />
      <p v-if="pkgError" class="sm:col-span-2 text-red-600 text-sm">{{ pkgError }}</p>
      <button type="submit" class="sm:col-span-2 bg-gray-800 text-white rounded py-2 font-semibold">Add package</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const items = ref([]);
const showForm = ref(false);
const saving = ref(false);
const error = ref("");
const pkgError = ref("");
const form = ref({
  title: "",
  price: null,
  description: "",
  pricing_type: "fixed",
  phone: "",
  region: "",
  district: "",
});
const pkg = ref({ service_id: "", name: "", price: null, duration: "" });

async function load() {
  try {
    const { data } = await api.get("/provider/services");
    const list = data?.data ?? data;
    items.value = Array.isArray(list) ? list : [];
  } catch (e) {
    console.error(e);
    items.value = [];
  }
}

async function save() {
  saving.value = true;
  error.value = "";
  try {
    const { data } = await api.post("/services", {
      ...form.value,
      description: form.value.description || form.value.title,
      create_default_package: true,
    });
    showForm.value = false;
    form.value = { title: "", price: null, description: "", pricing_type: "fixed", phone: "", region: "", district: "" };
    await load();
    if (!items.value.length && data?.data) {
      items.value = [data.data];
    }
  } catch (e) {
    const d = e.response?.data;
    const errs = d?.errors;
    error.value =
      (d?.message || "") +
      (d?.your_role ? ` (role: ${d.your_role})` : "") +
      (errs ? "\n" + Object.entries(errs).map(([k, v]) => `${k}: ${[].concat(v).join(", ")}`).join("\n") : "");
    if (!error.value.trim()) error.value = "Failed to save service";
  } finally {
    saving.value = false;
  }
}

async function remove(s) {
  if (!confirm("Delete this service?")) return;
  try {
    await api.delete(`/services/${s.id}`);
    await load();
  } catch (e) {
    alert(e.response?.data?.message || "Delete failed");
  }
}

async function savePackage() {
  pkgError.value = "";
  try {
    await api.post("/service-packages", {
      service_id: Number(pkg.value.service_id),
      name: pkg.value.name,
      price: pkg.value.price,
      duration: pkg.value.duration || null,
      description: pkg.value.name,
      active: true,
    });
    pkg.value = { service_id: "", name: "", price: null, duration: "" };
    await load();
  } catch (e) {
    pkgError.value = e.response?.data?.message || "Failed to save package";
  }
}

onMounted(load);
</script>
