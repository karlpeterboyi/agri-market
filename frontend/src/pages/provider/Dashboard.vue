<template>
  <div>
    <h2 class="text-2xl sm:text-3xl font-bold mb-2">Service Provider Dashboard</h2>
    <p class="text-gray-500 mb-6">Publish services, win bookings, and get paid through escrow.</p>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-8">
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Services</p>
        <p class="text-2xl font-bold">{{ stats.services }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Subscription</p>
        <p class="text-lg font-bold" :class="subActive ? 'text-green-700' : 'text-amber-600'">
          {{ subActive ? "Active" : "Required" }}
        </p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs text-gray-500">Wallet (TZS)</p>
        <p class="text-lg font-bold">{{ Number(stats.wallet || 0).toLocaleString() }}</p>
      </div>
    </div>

    <p class="text-sm font-semibold text-gray-600 mb-3">Core flows</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
      <router-link to="/finance/subscriptions" class="card-link">
        <p class="font-semibold">1. Activate subscription</p>
        <p class="text-sm text-gray-500 mt-1">Required to publish services</p>
      </router-link>
      <router-link to="/provider/services" class="card-link">
        <p class="font-semibold">2. My services</p>
        <p class="text-sm text-gray-500 mt-1">Create and manage offerings</p>
      </router-link>
      <router-link to="/provider/bookings" class="card-link">
        <p class="font-semibold">3. Bookings</p>
        <p class="text-sm text-gray-500 mt-1">Accept and complete jobs</p>
      </router-link>
      <router-link to="/provider/profile" class="card-link">
        <p class="font-semibold">Provider profile</p>
        <p class="text-sm text-gray-500 mt-1">Public trust profile</p>
      </router-link>
      <router-link to="/marketplace" class="card-link">
        <p class="font-semibold">Marketplace</p>
        <p class="text-sm text-gray-500 mt-1">Discover farm demand</p>
      </router-link>
      <router-link to="/knowledge" class="card-link">
        <p class="font-semibold">Knowledge Hub</p>
        <p class="text-sm text-gray-500 mt-1">Upskill and train clients</p>
      </router-link>
    </div>

    <div v-if="services.length" class="mt-4 bg-white rounded-lg shadow divide-y">
      <div v-for="s in services" :key="s.id" class="p-4 flex justify-between gap-2">
        <div>
          <p class="font-semibold">{{ s.title }}</p>
          <p class="text-sm text-gray-500">TZS {{ Number(s.price || 0).toLocaleString() }} · {{ s.status }}</p>
        </div>
        <router-link :to="'/services/' + s.id" class="text-green-600 text-sm">View</router-link>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";
const services = ref([]);
const stats = ref({ services: 0, wallet: 0 });
const subActive = ref(false);
onMounted(async () => {
  try {
    const [sv, w, s] = await Promise.allSettled([
      api.get("/provider/services"),
      api.get("/wallet"),
      api.get("/my-subscription"),
    ]);
    if (sv.status === "fulfilled") {
      const d = sv.value.data?.data ?? sv.value.data;
      services.value = Array.isArray(d) ? d : [];
      stats.value.services = services.value.length;
    }
    if (w.status === "fulfilled") {
      const ww = w.value.data?.data || w.value.data || {};
      stats.value.wallet = ww.available_balance ?? ww.balance ?? 0;
    }
    if (s.status === "fulfilled") subActive.value = !!s.value.data?.active;
  } catch {}
});
</script>
<style scoped>
.card-link { display:block; background:#fff; border-radius:.5rem; box-shadow:0 1px 3px rgb(0 0 0/.08); padding:1.25rem; }
.card-link:hover { box-shadow:0 4px 12px rgb(0 0 0/.1); }
</style>
