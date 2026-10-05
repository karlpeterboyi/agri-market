<template>
  <div class="max-w-lg">
    <h2 class="text-2xl sm:text-3xl font-bold mb-6">{{ t('gov.registrations') }}</h2>
    <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-4 sm:p-6 space-y-4">
      <select v-model="form.registration_type" required class="w-full border rounded px-3 py-2">
        <option value="farm">Farm</option>
        <option value="trader">Trader</option>
        <option value="input_dealer">Input dealer</option>
        <option value="processor">Processor</option>
        <option value="cooperative">Cooperative</option>
      </select>
      <input v-model="form.business_name" placeholder="Business / farm name" class="w-full border rounded px-3 py-2" />
      <input v-model="form.region" required :placeholder="t('common.region')" class="w-full border rounded px-3 py-2" />
      <input v-model="form.district" required placeholder="District" class="w-full border rounded px-3 py-2" />
      <input v-model="form.ward" placeholder="Ward" class="w-full border rounded px-3 py-2" />
      <p v-if="msg" class="text-sm text-green-700">{{ msg }}</p>
      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
      <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded font-semibold">{{ t('common.save') }}</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "../../i18n";
import { createRegistration } from "../../api/government";

const { t } = useI18n();
const form = ref({
  registration_type: "farm",
  business_name: "",
  region: "",
  district: "",
  ward: "",
});
const msg = ref("");
const error = ref("");

async function submit() {
  error.value = "";
  try {
    await createRegistration(form.value);
    msg.value = "Registration submitted for review.";
  } catch (e) {
    error.value = e.response?.data?.message || t('common.error');
  }
}
</script>
