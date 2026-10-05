<template>
  <div class="max-w-3xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-1">My trucks</h1>
    <p class="text-sm text-gray-500 mb-6">
      List vehicles with price per km (or mile). Buyers get quotes after order confirmation:
      distance × rate = transport cost.
    </p>

    <form class="bg-white rounded-lg shadow p-4 mb-6 grid sm:grid-cols-2 gap-3" @submit.prevent="save">
      <input v-model="form.registration_number" required placeholder="Registration *" class="border rounded px-3 py-2" />
      <input v-model="form.name" placeholder="Name (e.g. Fuso 1)" class="border rounded px-3 py-2" />
      <select v-model="form.vehicle_type" required class="border rounded px-3 py-2">
        <option value="truck">Truck</option>
        <option value="pickup">Pickup</option>
        <option value="van">Van</option>
        <option value="trailer">Trailer</option>
        <option value="bike">Bike</option>
      </select>
      <input v-model.number="form.capacity" type="number" step="0.1" required placeholder="Capacity (tons)" class="border rounded px-3 py-2" />
      <input v-model.number="form.price_per_km" type="number" step="100" placeholder="Price per km (TZS)" class="border rounded px-3 py-2" />
      <input v-model.number="form.price_per_mile" type="number" step="100" placeholder="Price per mile (TZS)" class="border rounded px-3 py-2" />
      <input v-model="form.base_region" placeholder="Base region" class="border rounded px-3 py-2" />
      <input v-model="form.base_district" placeholder="Base district" class="border rounded px-3 py-2" />
      <input v-model.number="form.base_latitude" type="number" step="any" placeholder="Base latitude" class="border rounded px-3 py-2" />
      <input v-model.number="form.base_longitude" type="number" step="any" placeholder="Base longitude" class="border rounded px-3 py-2" />
      <button type="submit" class="sm:col-span-2 bg-green-600 text-white py-2 rounded font-semibold" :disabled="saving">
        {{ saving ? "Saving…" : "Add truck" }}
      </button>
      <p v-if="error" class="sm:col-span-2 text-red-600 text-sm">{{ error }}</p>
    </form>

    <div v-for="v in vehicles" :key="v.id" class="bg-white rounded-lg shadow p-4 mb-3 flex justify-between gap-3">
      <div>
        <p class="font-bold">{{ v.name || v.registration_number }} · {{ v.vehicle_type }}</p>
        <p class="text-sm text-gray-500">
          {{ v.capacity }} t ·
          TZS {{ Number(v.price_per_km || 0).toLocaleString() }}/km
          <span v-if="v.price_per_mile">(≈ {{ Number(v.price_per_mile).toLocaleString() }}/mi)</span>
        </p>
        <p class="text-xs text-gray-400">{{ v.base_region }} {{ v.base_district }} · {{ v.available ? "Available" : "Unavailable" }}</p>
      </div>
      <button type="button" class="text-red-600 text-sm" @click="remove(v)">Delete</button>
    </div>
    <p v-if="!vehicles.length && !loading" class="text-gray-400 text-sm">No trucks yet.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const vehicles = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref("");
const form = ref({
  registration_number: "",
  name: "",
  vehicle_type: "truck",
  capacity: 5,
  price_per_km: 1500,
  price_per_mile: null,
  base_region: "",
  base_district: "",
  base_latitude: null,
  base_longitude: null,
});

async function load() {
  loading.value = true;
  try {
    const { data } = await api.get("/my-trucks");
    vehicles.value = data.vehicles || [];
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load trucks";
  } finally {
    loading.value = false;
  }
}

async function save() {
  saving.value = true;
  error.value = "";
  try {
    await api.post("/my-trucks", form.value);
    form.value.registration_number = "";
    form.value.name = "";
    await load();
  } catch (e) {
    error.value =
      e.response?.data?.message ||
      (e.response?.status === 402 ? "Subscribe first to list trucks." : "Save failed");
  } finally {
    saving.value = false;
  }
}

async function remove(v) {
  if (!confirm("Delete this truck?")) return;
  await api.delete(`/my-trucks/${v.id}`);
  await load();
}

onMounted(load);
</script>
