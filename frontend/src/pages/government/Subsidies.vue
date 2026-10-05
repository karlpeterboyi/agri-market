<template>
  <div>
    <h2 class="text-3xl font-bold mb-6">Subsidy Programmes</h2>
    <div class="grid md:grid-cols-2 gap-6 mb-10">
      <div v-for="p in programs" :key="p.id" class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold">{{ p.name }}</h3>
        <p class="text-sm text-gray-500 mt-1">{{ p.program_type }} · {{ p.implementing_agency }}</p>
        <p class="mt-2 text-sm">{{ p.description }}</p>
        <p class="mt-3 text-sm"><strong>Value:</strong> TZS {{ Number(p.unit_value || 0).toLocaleString() }} / {{ p.unit_label }}
          <span v-if="p.max_units_per_farmer"> (max {{ p.max_units_per_farmer }})</span>
        </p>
        <p class="text-xs text-gray-400 mt-1">Apply by {{ p.application_end }}</p>
        <button @click="openApply(p)" class="mt-4 bg-green-600 text-white px-4 py-2 rounded font-medium text-sm">Apply</button>
      </div>
    </div>

    <h3 class="text-xl font-semibold mb-3">My applications</h3>
    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left"><tr>
          <th class="p-3">Number</th><th class="p-3">Programme</th><th class="p-3">Units</th><th class="p-3">Status</th><th class="p-3"></th>
        </tr></thead>
        <tbody>
          <tr v-for="a in applications" :key="a.id" class="border-t">
            <td class="p-3 font-mono text-xs">{{ a.application_number }}</td>
            <td class="p-3">{{ a.program?.name }}</td>
            <td class="p-3">{{ a.requested_units }}</td>
            <td class="p-3">{{ a.status }}</td>
            <td class="p-3">
              <button v-if="a.status === 'draft'" @click="submit(a)" class="text-green-600 text-xs font-medium">Submit</button>
            </td>
          </tr>
          <tr v-if="!applications.length"><td colspan="5" class="p-4 text-gray-400">No applications yet.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Simple apply modal -->
    <div v-if="applying" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
      <form @submit.prevent="createApp" class="bg-white rounded-lg p-6 w-full max-w-md space-y-4">
        <h3 class="font-bold text-lg">Apply: {{ applying.name }}</h3>
        <input v-model.number="units" type="number" min="1" :max="applying.max_units_per_farmer || 99" required class="w-full border rounded px-3 py-2" placeholder="Units" />
        <textarea v-model="purpose" class="w-full border rounded px-3 py-2" rows="2" placeholder="Purpose (optional)" />
        <div class="flex gap-2 justify-end">
          <button type="button" @click="applying = null" class="px-4 py-2 border rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Create draft</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getSubsidyPrograms, getSubsidyApplications, createSubsidyApplication, submitSubsidyApplication } from "../../api/government";

const programs = ref([]);
const applications = ref([]);
const applying = ref(null);
const units = ref(1);
const purpose = ref("");

async function load() {
  const [p, a] = await Promise.all([getSubsidyPrograms(), getSubsidyApplications()]);
  programs.value = p.data.data || p.data;
  applications.value = a.data.data || a.data;
}

function openApply(p) {
  applying.value = p;
  units.value = 1;
  purpose.value = "";
}

async function createApp() {
  await createSubsidyApplication({
    subsidy_program_id: applying.value.id,
    requested_units: units.value,
    purpose: purpose.value || null,
  });
  applying.value = null;
  await load();
}

async function submit(a) {
  await submitSubsidyApplication(a.id);
  await load();
}

onMounted(load);
</script>
