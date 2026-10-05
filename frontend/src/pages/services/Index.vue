<template>
  <div class="max-w-7xl mx-auto p-4 sm:p-6">
    <h1 class="text-3xl sm:text-4xl font-bold mb-2">Agricultural Services</h1>
    <p class="text-gray-600 mb-6">Find trusted agricultural service providers across Tanzania.</p>

    <input
      v-model="search"
      placeholder="Search services..."
      class="w-full border rounded-lg p-3 mb-6"
    />

    <div class="flex flex-wrap gap-2 mb-8">
      <button
        type="button"
        @click="selectedCategory = null"
        class="border rounded-lg px-4 py-2 text-sm"
        :class="!selectedCategory ? 'bg-green-700 text-white border-green-700' : 'hover:bg-green-50'"
      >
        All
      </button>
      <button
        v-for="category in categories"
        :key="category.id"
        type="button"
        @click="selectedCategory = category.id"
        class="border rounded-lg px-4 py-2 text-sm"
        :class="selectedCategory === category.id ? 'bg-green-700 text-white border-green-700' : 'hover:bg-green-50'"
      >
        {{ category.name }}
      </button>
    </div>

    <p v-if="loading" class="text-gray-500">Loading services…</p>
    <p v-else-if="error" class="text-red-600 text-sm mb-4">{{ error }}</p>
    <p v-else-if="!filteredServices.length" class="text-gray-400">No services found. Providers can publish from My Services.</p>

    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6">
      <div
        v-for="service in filteredServices"
        :key="service.id"
        class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden"
      >
        <img
          :src="cover(service)"
          class="w-full h-48 object-cover"
          alt=""
        />
        <div class="p-5">
          <h2 class="font-bold text-xl">{{ titleOf(service) }}</h2>
          <p class="text-gray-500 text-sm">{{ service.category?.name || 'Service' }}</p>
          <p class="mt-2 text-sm text-gray-600">
            {{ service.region || 'Tanzania' }}{{ service.district ? ', ' + service.district : '' }}
          </p>
          <p class="font-bold text-green-700 mt-2">
            {{ priceLabel(service) }}
          </p>
          <p v-if="service.provider?.name" class="text-xs text-gray-400 mt-1">by {{ service.provider.name }}</p>
          <router-link
            :to="'/services/' + service.id"
            class="mt-4 inline-block bg-green-700 text-white px-4 py-2 rounded text-sm font-medium"
          >
            View Service
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../../services/api";
import { unwrapList } from "../../utils/apiData";

const services = ref([]);
const categories = ref([]);
const search = ref("");
const selectedCategory = ref(null);
const loading = ref(true);
const error = ref("");

function titleOf(s) {
  return s.title || s.business_name || "Service";
}
function cover(s) {
  const path = s.cover_photo || s.cover_image;
  if (!path) return "https://placehold.co/600x400?text=Service";
  if (String(path).startsWith("http")) return path;
  return `/storage/${path}`;
}
function priceLabel(s) {
  const p = s.price ?? s.starting_price ?? 0;
  const type = s.pricing_type || "fixed";
  const prefix = type === "starting_from" ? "From " : "";
  return `${prefix}TZS ${Number(p).toLocaleString()}`;
}

const filteredServices = computed(() => {
  return services.value.filter((service) => {
    const name = titleOf(service).toLowerCase();
    const matchesSearch = !search.value || name.includes(search.value.toLowerCase());
    const matchesCategory =
      !selectedCategory.value ||
      Number(service.service_category_id) === Number(selectedCategory.value);
    return matchesSearch && matchesCategory;
  });
});

onMounted(async () => {
  loading.value = true;
  error.value = "";
  try {
    const [servicesRes, categoryRes] = await Promise.all([
      api.get("/services"),
      api.get("/service-categories"),
    ]);
    services.value = unwrapList(servicesRes.data);
    // paginator: { data: [...] }
    if (!services.value.length && Array.isArray(servicesRes.data?.data)) {
      services.value = servicesRes.data.data;
    }
    categories.value = unwrapList(categoryRes.data);
    if (!categories.value.length && Array.isArray(categoryRes.data)) {
      categories.value = categoryRes.data;
    }
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load services";
    services.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
