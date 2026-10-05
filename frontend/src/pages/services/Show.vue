<template>
  <div class="max-w-7xl mx-auto p-4 sm:p-6" v-if="service">
    <div class="text-sm text-gray-500 mb-5">
      <router-link to="/services" class="text-green-700">Services</router-link>
      / {{ titleOf(service) }}
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2">
        <img
          :src="cover(service)"
          class="w-full h-64 sm:h-[420px] object-cover rounded-xl shadow"
          alt=""
        />
        <div class="mt-8">
          <h1 class="text-3xl sm:text-4xl font-bold">{{ titleOf(service) }}</h1>
          <p class="text-gray-500 mt-2">{{ service.category?.name }}</p>
          <p class="text-green-700 font-bold text-xl mt-3">{{ priceLabel(service) }}</p>
        </div>
        <div class="mt-8">
          <h2 class="text-xl font-bold mb-3">About this service</h2>
          <p class="text-gray-700 leading-7">{{ service.description || 'No description.' }}</p>
        </div>
        <div class="mt-8 grid sm:grid-cols-2 gap-4 text-sm">
          <div><span class="font-semibold">Region</span><div>{{ service.region || '—' }}</div></div>
          <div><span class="font-semibold">District</span><div>{{ service.district || '—' }}</div></div>
          <div><span class="font-semibold">Phone</span><div>{{ service.phone || service.mobile || '—' }}</div></div>
          <div><span class="font-semibold">Provider</span><div>{{ service.provider?.name || '—' }}</div></div>
        </div>

        <div v-if="service.packages?.length" class="mt-10">
          <h2 class="text-xl font-bold mb-3">Packages</h2>
          <div class="space-y-2">
            <div v-for="p in service.packages" :key="p.id" class="border rounded-lg p-3 flex justify-between">
              <span>{{ p.name }} <span class="text-gray-400 text-sm">{{ p.duration }}</span></span>
              <span class="font-semibold text-green-700">TZS {{ Number(p.price).toLocaleString() }}</span>
            </div>
          </div>
        </div>
      </div>

      <div>
        <div class="bg-white rounded-xl shadow p-5 sticky top-4">
          <p class="font-bold text-lg mb-2">Book this service</p>
          <p class="text-sm text-gray-500 mb-4">Contact the provider or place a booking request.</p>
          <router-link
            v-if="auth.authenticated"
            :to="'/services/' + service.id + '/book'"
            class="block text-center bg-green-700 text-white py-2.5 rounded-lg font-semibold"
          >
            Request booking
          </router-link>
          <router-link
            v-else
            to="/login"
            class="block text-center bg-green-700 text-white py-2.5 rounded-lg font-semibold"
          >
            Login to book
          </router-link>
          <a
            v-if="service.phone"
            :href="'tel:' + service.phone"
            class="block text-center border mt-3 py-2.5 rounded-lg text-sm"
          >
            Call {{ service.phone }}
          </a>
        </div>
      </div>
    </div>
  </div>
  <div v-else-if="loading" class="p-8 text-gray-500">Loading…</div>
  <div v-else class="p-8 text-red-600">{{ error || 'Service not found' }}</div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "../../services/api";
import { useAuthStore } from "../../stores/auth";

const route = useRoute();
const auth = useAuthStore();
const service = ref(null);
const loading = ref(true);
const error = ref("");

function titleOf(s) {
  return s?.title || s?.business_name || "Service";
}
function cover(s) {
  const path = s?.cover_photo || s?.cover_image;
  if (!path) return "https://placehold.co/1200x650?text=Service";
  if (String(path).startsWith("http")) return path;
  return `/storage/${path}`;
}
function priceLabel(s) {
  const p = s?.price ?? s?.starting_price ?? 0;
  return `TZS ${Number(p).toLocaleString()}`;
}

onMounted(async () => {
  loading.value = true;
  try {
    const res = await api.get(`/services/${route.params.id}`);
    service.value = res.data?.data || res.data;
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load service";
  } finally {
    loading.value = false;
  }
});
</script>
