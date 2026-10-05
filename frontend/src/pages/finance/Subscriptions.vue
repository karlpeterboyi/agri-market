<template>
  <div class="max-w-3xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-1">Subscriptions</h1>
    <p class="text-sm text-gray-500 mb-4">
      Farmers list produce for free. Buyers, agrodealers, providers, transporters, processors and other commercial roles need an active plan.
    </p>

    <div v-if="mine?.farmer_exempt" class="bg-green-50 text-green-800 rounded-lg p-4 mb-6 text-sm">
      Your role is farmer — you are exempt from listing subscription fees.
    </div>
    <div v-else-if="mine?.active" class="bg-green-50 text-green-800 rounded-lg p-4 mb-6 text-sm">
      Active until {{ formatDate(mine.current?.expires_at) }}
    </div>
    <div v-else class="bg-amber-50 text-amber-900 rounded-lg p-4 mb-6 text-sm">
      No active subscription. Choose a plan below to activate (sandbox).
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div v-for="p in plans" :key="p.code || p.id || p.name" class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold">{{ p.name }}</h3>
        <p class="text-xs text-gray-500 mb-2">For: {{ p.role || "commercial" }}</p>
        <p class="text-xl font-semibold text-green-700 mb-2">
          TZS {{ Number(priceOf(p)).toLocaleString() }}
          <span class="text-sm font-normal text-gray-500">/ {{ p.period || "month" }}</span>
        </p>
        <ul class="text-sm text-gray-600 mb-3 list-disc pl-4">
          <li v-for="(f, i) in featuresOf(p)" :key="i">{{ f }}</li>
        </ul>
        <button
          type="button"
          class="w-full bg-green-600 text-white py-2 rounded font-semibold text-sm disabled:opacity-50"
          :disabled="activating === planKey(p) || !planKey(p)"
          @click="activate(p)"
        >
          {{ activating === planKey(p) ? "…" : "Activate (sandbox)" }}
        </button>
      </div>
    </div>
    <p v-if="!plans.length" class="text-gray-400 text-sm mt-4">No plans available.</p>
    <p v-if="msg" class="mt-4 text-sm" :class="msgErr ? 'text-red-600' : 'text-green-700'">{{ msg }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const plans = ref([]);
const mine = ref(null);
const activating = ref("");
const msg = ref("");
const msgErr = ref(false);

function planKey(p) {
  return p.code || p.slug || (p.id != null ? String(p.id) : "");
}

function priceOf(p) {
  if (p.price_tzs != null && Number(p.price_tzs) > 0) return Number(p.price_tzs);
  if (p.price != null) return Number(p.price);
  const prices = p.prices || [];
  if (prices.length) {
    const row = prices.find((x) => x.active) || prices[0];
    return Number(row.price ?? row.amount ?? 0);
  }
  return 0;
}

function featuresOf(p) {
  if (Array.isArray(p.features) && p.features.length) return p.features;
  if (typeof p.features === "string") return [p.features];
  return p.description ? [p.description] : [];
}

function formatDate(d) {
  if (!d) return "—";
  return String(d).slice(0, 10);
}

async function load() {
  try {
    const [p, m] = await Promise.all([
      api.get("/subscription-plans"),
      api.get("/my-subscription"),
    ]);
    const raw = p.data.data || p.data || [];
    plans.value = Array.isArray(raw) ? raw : [];
    mine.value = m.data;
  } catch (e) {
    msgErr.value = true;
    msg.value = e.response?.data?.message || "Failed to load plans";
  }
}

async function activate(p) {
  const code = p.code || p.slug || null;
  const key = planKey(p);
  activating.value = key;
  msg.value = "";
  try {
    const body = { months: 1 };
    if (code) body.plan_code = code;
    if (p.id) body.plan_id = p.id;
    const { data } = await api.post("/subscribe", body);
    msgErr.value = false;
    msg.value = data.message || "Activated";
    await load();
  } catch (e) {
    msgErr.value = true;
    const errs = e.response?.data?.errors;
    const flat = errs ? Object.values(errs).flat().join(" ") : "";
    msg.value = e.response?.data?.message || flat || "Activation failed";
  } finally {
    activating.value = "";
  }
}

onMounted(load);
</script>
