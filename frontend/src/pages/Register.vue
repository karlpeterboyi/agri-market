<template>
  <div class="min-h-screen bg-gray-50 px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-6 sm:p-8">
      <div class="flex justify-center mb-3">
        <AppLogo text-class="text-xl text-gray-800" img-class="h-12 w-12 rounded-full" />
      </div>
      <div class="flex justify-between items-center mb-2">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Create account</h1>
        <select :value="locale" @change="setLocale($event.target.value)" class="text-xs border rounded px-2 py-1">
          <option v-for="l in available" :key="l.code" :value="l.code">{{ l.label }}</option>
        </select>
      </div>
      <p class="text-sm text-gray-500 mb-6">Register for Agri-market today to redefine your work in agricultural value chain</p>

      <!-- Steps -->
      <div class="flex gap-2 mb-6 text-xs font-medium">
        <span class="px-2 py-1 rounded" :class="step === 1 ? 'bg-green-600 text-white' : 'bg-gray-100'">1. Role</span>
        <span class="px-2 py-1 rounded" :class="step === 2 ? 'bg-green-600 text-white' : 'bg-gray-100'">2. Identity</span>
        <span class="px-2 py-1 rounded" :class="step === 3 ? 'bg-green-600 text-white' : 'bg-gray-100'">3. Business & bank</span>
      </div>

      <!-- Step 1: Role -->
      <div v-if="step === 1" class="space-y-3">
        <p class="text-sm text-gray-600 mb-2">Who are you on the platform?</p>
        <button
          v-for="r in roles"
          :key="r.code"
          type="button"
          @click="selectRole(r)"
          class="w-full text-left border rounded-lg p-3 hover:border-green-600 transition"
          :class="form.role === r.code ? 'border-green-600 bg-green-50' : 'border-gray-200'"
        >
          <p class="font-semibold">{{ locale === 'sw' ? r.label_sw : r.label_en }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ locale === 'sw' ? r.description_sw : r.description_en }}</p>
          <p v-if="r.verification === 'pending'" class="text-xs text-amber-700 mt-1">Requires verification before login</p>
        </button>
        <button type="button" :disabled="!form.role" class="w-full mt-4 bg-green-600 text-white py-2.5 rounded-lg font-semibold disabled:opacity-40" @click="step = 2">
          Continue
        </button>
      </div>

      <!-- Step 2: Identity -->
      <form v-else-if="step === 2" class="space-y-4" @submit.prevent="step = 3">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Full name / contact person *</label>
          <input v-model="form.name" required class="border rounded-lg px-3 py-2.5 w-full" />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Phone *</label>
          <input v-model="form.phone" required class="border rounded-lg px-3 py-2.5 w-full" placeholder="07XXXXXXXX" />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Email *</label>
          <input v-model="form.email" type="email" required class="border rounded-lg px-3 py-2.5 w-full" />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Password *</label>
          <input v-model="form.password" type="password" required minlength="6" class="border rounded-lg px-3 py-2.5 w-full" />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Confirm password *</label>
          <input v-model="form.password_confirmation" type="password" required class="border rounded-lg px-3 py-2.5 w-full" />
        </div>
        <div class="flex gap-2">
          <button type="button" class="flex-1 border rounded-lg py-2.5" @click="step = 1">Back</button>
          <button type="submit" class="flex-1 bg-green-600 text-white rounded-lg py-2.5 font-semibold">Continue</button>
        </div>
      </form>

      <!-- Step 3: Business + optional NMB -->
      <form v-else class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Business / farm name</label>
          <input v-model="form.business_name" class="border rounded-lg px-3 py-2.5 w-full" placeholder="Optional" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Region</label>
            <input v-model="form.region" class="border rounded-lg px-3 py-2.5 w-full" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">District</label>
            <input v-model="form.district" class="border rounded-lg px-3 py-2.5 w-full" />
          </div>
        </div>

        <div class="border-t pt-4">
          <p class="font-semibold text-sm mb-1">Link NMB account (recommended)</p>
          <p class="text-xs text-gray-500 mb-3">
            Linking your NMB account records settlements on-platform for credible finance reports,
            loan underwriting and government statistics. You can also link later from Finance → NMB Bank.
          </p>
          <input v-model="form.nmb_account_number" class="border rounded-lg px-3 py-2.5 w-full mb-2" placeholder="NMB account number" />
          <input v-model="form.nmb_account_name" class="border rounded-lg px-3 py-2.5 w-full" placeholder="Account name" />
        </div>

        <label class="flex items-start gap-2 text-sm">
          <input v-model="form.accept_terms" type="checkbox" class="mt-1" required />
          <span>I agree to the platform terms and acknowledge that transaction data may be used for finance dossiers and aggregated reports.</span>
        </label>

        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
        <div class="flex gap-2">
          <button type="button" class="flex-1 border rounded-lg py-2.5" @click="step = 2">Back</button>
          <button type="submit" :disabled="loading" class="flex-1 bg-green-600 text-white rounded-lg py-2.5 font-semibold disabled:opacity-50">
            {{ loading ? 'Creating…' : 'Create account' }}
          </button>
        </div>
      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        Already have an account?
        <router-link to="/login" class="text-green-700 font-medium">Login</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import AppLogo from "../components/AppLogo.vue";
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import api, { getApiBaseURL } from "../services/api";
import { useI18n } from "../i18n";

const { locale, setLocale, available } = useI18n();
const router = useRouter();
const step = ref(1);
const loading = ref(false);
const error = ref("");
const roles = ref([]);
const form = ref({
  role: "",
  name: "",
  phone: "",
  email: "",
  password: "",
  password_confirmation: "",
  business_name: "",
  region: "",
  district: "",
  nmb_account_number: "",
  nmb_account_name: "",
  accept_terms: false,
});

function selectRole(r) {
  form.value.role = r.code;
}

onMounted(async () => {
  try {
    const { data } = await api.get("/registration-roles");
    roles.value = data.roles || [];
  } catch {
    roles.value = [
      { code: "farmer", label_en: "Farmer", label_sw: "Mkulima", description_en: "", description_sw: "", verification: "auto" },
      { code: "buyer", label_en: "Buyer", label_sw: "Mnunuzi", description_en: "", description_sw: "", verification: "auto" },
    ];
  }
});

async function submit() {
  error.value = "";
  loading.value = true;
  try {
    const { data } = await api.post("/register", form.value);
    if (data.requires_activation) {
      error.value = data.message;
      loading.value = false;
      return;
    }
    if (data.token) {
      localStorage.setItem("token", data.token);
      localStorage.setItem("user", JSON.stringify(data.user));
    }
    router.push(data.home || "/");
  } catch (e) {
    const d = e.response?.data;
    const flat = d?.errors ? Object.values(d.errors).flat().join(" ") : "";
    error.value =
      d?.message ||
      flat ||
      (e.code === "ERR_NETWORK"
        ? ("Cannot reach API at " + getApiBaseURL() + ". Is Laravel on :8000 and Vite restarted?")
        : null) ||
      e.message ||
      "Registration failed";
    if (d?.error) {
      error.value += " — " + String(d.error).slice(0, 180);
    }
  } finally {
    loading.value = false;
  }
}
</script>
