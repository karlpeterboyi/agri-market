<template>
  <div>
    <h2 class="text-2xl sm:text-3xl font-bold mb-6">{{ t('gov.reviewTitle') }}</h2>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 border-b overflow-x-auto">
      <button v-for="tab in tabs" :key="tab.id" @click="active = tab.id"
        class="px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 -mb-px"
        :class="active === tab.id ? 'border-green-600 text-green-700' : 'border-transparent text-gray-500'">
        {{ tab.label }}
      </button>
    </div>

    <!-- Subsidy applications -->
    <div v-if="active === 'subsidies'" class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm min-w-[700px]">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="p-3">#</th>
            <th class="p-3">{{ t('gov.applicant') }}</th>
            <th class="p-3">{{ t('gov.programme') }}</th>
            <th class="p-3">{{ t('inventory.quantity') }}</th>
            <th class="p-3">Status</th>
            <th class="p-3">{{ t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in subsidyApps" :key="a.id" class="border-t">
            <td class="p-3 font-mono text-xs">{{ a.application_number }}</td>
            <td class="p-3">{{ a.applicant?.name || a.user_id }}</td>
            <td class="p-3">{{ a.program?.name }}</td>
            <td class="p-3">{{ a.requested_units }}</td>
            <td class="p-3"><span class="text-xs px-2 py-0.5 rounded bg-gray-100">{{ a.status }}</span></td>
            <td class="p-3 space-x-2">
              <template v-if="['submitted', 'under_review'].includes(a.status)">
                <button @click="reviewSubsidy(a, 'approved')" class="text-green-600 text-xs font-medium">{{ t('gov.approve') }}</button>
                <button @click="reviewSubsidy(a, 'rejected')" class="text-red-600 text-xs font-medium">{{ t('gov.reject') }}</button>
              </template>
              <button v-if="a.status === 'approved'" @click="disburseSubsidy(a)" class="text-emerald-600 text-xs font-medium">{{ t('gov.disburse') }}</button>
            </td>
          </tr>
          <tr v-if="!subsidyApps.length"><td colspan="6" class="p-4 text-gray-400">{{ t('common.noData') }}</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Registrations -->
    <div v-if="active === 'registrations'" class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm min-w-[700px]">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="p-3">#</th>
            <th class="p-3">{{ t('gov.type') }}</th>
            <th class="p-3">{{ t('gov.applicant') }}</th>
            <th class="p-3">{{ t('common.region') }}</th>
            <th class="p-3">Status</th>
            <th class="p-3">{{ t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in registrations" :key="r.id" class="border-t">
            <td class="p-3 font-mono text-xs">{{ r.registration_number }}</td>
            <td class="p-3">{{ r.registration_type }}</td>
            <td class="p-3">{{ r.user?.name || r.business_name || r.user_id }}</td>
            <td class="p-3">{{ r.region }} / {{ r.district }}</td>
            <td class="p-3"><span class="text-xs px-2 py-0.5 rounded bg-gray-100">{{ r.status }}</span></td>
            <td class="p-3 space-x-2">
              <template v-if="r.status === 'pending'">
                <button @click="reviewReg(r, 'approved')" class="text-green-600 text-xs font-medium">{{ t('gov.approve') }}</button>
                <button @click="reviewReg(r, 'rejected')" class="text-red-600 text-xs font-medium">{{ t('gov.reject') }}</button>
              </template>
            </td>
          </tr>
          <tr v-if="!registrations.length"><td colspan="6" class="p-4 text-gray-400">{{ t('common.noData') }}</td></tr>
        </tbody>
      </table>
    </div>

    <p v-if="msg" class="mt-4 text-sm text-green-700">{{ msg }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useI18n } from "../../i18n";
import api from "../../services/api";
import { getSubsidyApplications, getRegistrations } from "../../api/government";

const { t } = useI18n();
const active = ref("subsidies");
const subsidyApps = ref([]);
const registrations = ref([]);
const msg = ref("");

const tabs = computed(() => [
  { id: "subsidies", label: t('gov.subsidyApps') },
  { id: "registrations", label: t('gov.registrations') },
]);

async function load() {
  const [s, r] = await Promise.all([
    getSubsidyApplications(),
    getRegistrations(),
  ]);
  subsidyApps.value = s.data.data || s.data;
  registrations.value = r.data.data || r.data;
}

async function reviewSubsidy(a, status) {
  const notes = status === 'rejected' ? prompt(t('gov.rejectionReason')) : null;
  if (status === 'rejected' && notes === null) return;
  await api.post(`/subsidy-applications/${a.id}/review`, { status, review_notes: notes });
  msg.value = t('gov.updated');
  await load();
}

async function disburseSubsidy(a) {
  await api.post(`/subsidy-applications/${a.id}/disburse`);
  msg.value = t('gov.disbursed');
  await load();
}

async function reviewReg(r, status) {
  await api.post(`/agricultural-registrations/${r.id}/review`, { status });
  msg.value = t('gov.updated');
  await load();
}

onMounted(load);
</script>
