<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <h2 class="text-3xl font-bold">Farm Activities</h2>
      <button @click="showForm = !showForm" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-3 rounded-lg">+ Log Activity</button>
    </div>

    <form v-if="showForm" @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 mb-6 grid md:grid-cols-2 gap-4">
      <select v-model="form.farm_id" required class="border rounded px-3 py-2">
        <option disabled value="">Select farm</option>
        <option v-for="f in farms" :key="f.id" :value="f.id">{{ f.name }}</option>
      </select>
      <select v-model="form.activity_type" required class="border rounded px-3 py-2">
        <option value="planting">Planting</option>
        <option value="weeding">Weeding</option>
        <option value="fertilizing">Fertilizing</option>
        <option value="spraying">Spraying</option>
        <option value="irrigation">Irrigation</option>
        <option value="harvesting">Harvesting</option>
        <option value="scouting">Scouting</option>
        <option value="other">Other</option>
      </select>
      <input v-model="form.title" required placeholder="Title" class="border rounded px-3 py-2 md:col-span-2" />
      <input v-model="form.activity_date" type="date" required class="border rounded px-3 py-2" />
      <input v-model.number="form.cost" type="number" step="0.01" placeholder="Input cost (TZS)" class="border rounded px-3 py-2" />
      <input v-model.number="form.labour_cost" type="number" step="0.01" placeholder="Labour cost (TZS)" class="border rounded px-3 py-2" />
      <textarea v-model="form.description" placeholder="Notes" class="border rounded px-3 py-2 md:col-span-2" rows="2" />
      <button type="submit" class="bg-green-600 text-white rounded px-6 py-2 font-semibold md:col-span-2">Save activity</button>
    </form>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="p-3">Date</th>
            <th class="p-3">Type</th>
            <th class="p-3">Title</th>
            <th class="p-3">Farm</th>
            <th class="p-3">Cost</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in activities" :key="a.id" class="border-t">
            <td class="p-3">{{ a.activity_date?.slice?.(0, 10) || a.activity_date }}</td>
            <td class="p-3">{{ a.activity_type }}</td>
            <td class="p-3">{{ a.title }}</td>
            <td class="p-3">{{ a.farm?.name || '—' }}</td>
            <td class="p-3">{{ formatMoney((a.cost || 0) + (a.labour_cost || 0)) }}</td>
          </tr>
          <tr v-if="!activities.length"><td colspan="5" class="p-4 text-gray-400">No activities logged yet.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getFarms, getFarmActivities, createFarmActivity } from "../../api/farms";

const farms = ref([]);
const activities = ref([]);
const showForm = ref(false);
const form = ref({
  farm_id: "",
  activity_type: "planting",
  title: "",
  activity_date: new Date().toISOString().slice(0, 10),
  cost: null,
  labour_cost: null,
  description: "",
});

function formatMoney(n) {
  return "TZS " + Number(n || 0).toLocaleString();
}

async function load() {
  const [f, a] = await Promise.all([getFarms(), getFarmActivities()]);
  farms.value = f.data.data || f.data;
  activities.value = a.data.data || a.data;
}

async function submit() {
  await createFarmActivity(form.value);
  showForm.value = false;
  form.value.title = "";
  form.value.description = "";
  await load();
}

onMounted(load);
</script>
