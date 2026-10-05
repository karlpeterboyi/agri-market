<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Public header -->
    <header class="bg-green-700 text-white sticky top-0 z-20 shadow">
      <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
        <AppLogo text="Agri-market" text-class="text-xl text-white" img-class="h-9 w-9 rounded-full bg-white/10 p-0.5" />
        <div class="flex items-center gap-3">
          <select :value="locale" @change="setLocale($event.target.value)" class="bg-green-800 text-white text-xs rounded px-2 py-1 border border-green-600">
            <option v-for="l in available" :key="l.code" :value="l.code">{{ l.label }}</option>
          </select>
          <router-link to="/login" class="text-sm hover:underline">{{ t('common.login') }}</router-link>
          <router-link to="/register" class="text-sm bg-white text-green-800 px-3 py-1 rounded font-medium">{{ t('common.register') }}</router-link>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:py-10">
      <div class="text-center mb-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900">{{ t('marketplace.title') }}</h1>
        <p class="text-gray-500 mt-2 max-w-2xl mx-auto">{{ t('marketplace.subtitle') }}</p>
      </div>

      <!-- Search & filters -->
      <div class="bg-white rounded-xl shadow p-4 mb-8 flex flex-col sm:flex-row gap-3">
        <input v-model="search" @input="debouncedLoad" type="search"
          :placeholder="t('marketplace.searchPlaceholder')"
          class="flex-1 border rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 outline-none" />
        <select v-model="region" @change="load" class="border rounded-lg px-3 py-2.5 sm:w-40">
          <option value="">{{ t('common.all') }} {{ t('marketplace.region') }}</option>
          <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
        </select>
        <select v-model="sort" @change="applySort" class="border rounded-lg px-3 py-2.5 sm:w-44">
          <option value="newest">{{ t('marketplace.sortNewest') }}</option>
          <option value="price_asc">{{ t('marketplace.sortPriceLow') }}</option>
          <option value="price_desc">{{ t('marketplace.sortPriceHigh') }}</option>
        </select>
      </div>

      <!-- Category chips -->
      <div class="flex flex-wrap gap-2 mb-8">
        <button v-for="c in categoryChips" :key="c.key" @click="setCategory(c.key)"
          class="px-3 py-1.5 rounded-full text-sm border transition"
          :class="activeCategory === c.key ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-600 border-gray-200 hover:border-green-400'">
          {{ c.icon }} {{ c.label }}
        </button>
      </div>

      <div v-if="loading" class="text-center text-gray-500 py-12">{{ t('common.loading') }}</div>

      <template v-else>
        <!-- Crop / product listings -->
        <section v-if="activeCategory === 'all' || activeCategory === 'crops'" class="mb-12">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl sm:text-2xl font-bold">🌾 {{ t('marketplace.crops') }}</h2>
            <router-link to="/listings" class="text-green-700 font-semibold text-sm">{{ t('common.viewAll') }} →</router-link>
          </div>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <article v-for="item in displayedListings" :key="item.id"
              class="bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden flex flex-col">
              <div class="h-36 bg-gradient-to-br from-green-100 to-emerald-50 flex items-center justify-center text-4xl overflow-hidden">
                <img
                  v-if="listingCover(item)"
                  :src="listingCover(item)"
                  :alt="item.commodity?.name || 'Listing'"
                  class="w-full h-full object-cover"
                  @error="onImgError($event, item)"
                />
                <span v-else>{{ commodityEmoji(item) }}</span>
              </div>
              <div class="p-4 flex-1 flex flex-col">
                <h3 class="font-bold text-gray-900">{{ item.commodity?.name || 'Product' }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ item.region }}{{ item.district ? ', ' + item.district : '' }}</p>
                <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ item.description || item.grade || '' }}</p>
                <div class="mt-auto pt-3 flex items-end justify-between gap-2">
                  <div>
                    <p class="text-lg font-bold text-green-700">TZS {{ Number(item.price).toLocaleString() }}</p>
                    <p class="text-xs text-gray-400">{{ item.quantity }} {{ item.unit }}</p>
                  </div>
                  <router-link :to="`/listings/${item.id}`" class="text-sm bg-green-600 text-white px-3 py-1.5 rounded-lg font-medium whitespace-nowrap">
                    {{ t('marketplace.contact') }}
                  </router-link>
                </div>
              </div>
            </article>
          </div>
          <p v-if="!displayedListings.length" class="text-gray-400 text-sm py-6">{{ t('marketplace.noListings') }}</p>
        </section>

        
        <!-- Farm inputs -->
        <section v-if="activeCategory === 'all' || activeCategory === 'inputs'" class="mb-12">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl sm:text-2xl font-bold">🌱 {{ t('marketplace.inputs') }}</h2>
          </div>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <article v-for="item in inputListings" :key="'in-'+item.id"
              class="bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden flex flex-col">
              <div class="h-28 bg-gradient-to-br from-amber-50 to-yellow-50 flex items-center justify-center text-4xl">🌱</div>
              <div class="p-4 flex-1 flex flex-col">
                <h3 class="font-bold text-gray-900">{{ item.name }}</h3>
                <p class="text-sm text-gray-500">{{ item.brand }} · {{ item.region }}</p>
                <div class="mt-auto pt-3">
                  <p class="text-lg font-bold text-green-700">TZS {{ Number(item.price).toLocaleString() }}</p>
                  <p class="text-xs text-gray-400">{{ item.stock }} {{ item.unit }}</p>
                </div>
              </div>
            </article>
          </div>
          <p v-if="!inputListings.length" class="text-gray-400 text-sm py-4">{{ t('marketplace.noListings') }}</p>
        </section>

        <!-- Livestock preview -->

        <section v-if="activeCategory === 'all' || activeCategory === 'livestock'" class="mb-12">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl sm:text-2xl font-bold">🐄 {{ t('marketplace.livestock') }}</h2>
            <router-link to="/livestock" class="text-green-700 font-semibold text-sm">{{ t('common.viewAll') }} →</router-link>
          </div>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="n in Math.min(livestockCount, 3)" :key="n" class="bg-white rounded-xl shadow p-5">
              <p class="text-3xl mb-2">{{ ['🐔','🐐','🐄'][n-1] }}</p>
              <p class="font-semibold">{{ ['Local chicken', 'Goats', 'Dairy cows'][n-1] }}</p>
              <p class="text-sm text-gray-500">Browse livestock marketplace</p>
              <router-link to="/livestock" class="inline-block mt-3 text-green-600 text-sm font-medium">{{ t('common.viewAll') }} →</router-link>
            </div>
          </div>
        </section>

        <!-- Quick links: inputs, machinery, services -->
        <section class="grid sm:grid-cols-3 gap-4">
          <router-link to="/services" class="bg-white rounded-xl shadow p-6 hover:shadow-md transition block">
            <p class="text-3xl mb-2">🛠️</p>
            <h3 class="font-bold">{{ t('marketplace.services') }}</h3>
            <p class="text-sm text-gray-500 mt-1">Vets, agronomists, extension</p>
          </router-link>
          <router-link to="/machinery" class="bg-white rounded-xl shadow p-6 hover:shadow-md transition block">
            <p class="text-3xl mb-2">🚜</p>
            <h3 class="font-bold">{{ t('marketplace.machinery') }}</h3>
            <p class="text-sm text-gray-500 mt-1">Buy, rent or lease equipment</p>
          </router-link>
          <div class="bg-white rounded-xl shadow p-6">
            <p class="text-3xl mb-2">🌱</p>
            <h3 class="font-bold">{{ t('marketplace.inputs') }}</h3>
            <p class="text-sm text-gray-500 mt-1">Seeds, fertiliser, feed — coming via listings</p>
          </div>
        </section>
      </template>
    </div>
  </div>
</template>

<script setup>
import AppLogo from "../components/AppLogo.vue";
import { ref, computed, onMounted } from "vue";
import { useI18n } from "../i18n";
import api from "../services/api";
import { listingCover as listingCoverFn } from "../utils/mediaUrl";

const { t, locale, setLocale, available } = useI18n();

const listings = ref([]);
const inputListings = ref([]);
const loading = ref(true);
const search = ref("");
const region = ref("");
const sort = ref("newest");
const activeCategory = ref("all");
const livestockCount = ref(3);
let debounceTimer = null;

const regions = ["Morogoro", "Arusha", "Mbeya", "Dar es Salaam", "Iringa", "Mwanza", "Kilimanjaro", "Dodoma"];

const categoryChips = computed(() => [
  { key: "all", icon: "🏪", label: t("common.all") },
  { key: "crops", icon: "🌾", label: t("marketplace.crops") },
  { key: "inputs", icon: "🌱", label: t("marketplace.inputs") },
  { key: "livestock", icon: "🐄", label: t("marketplace.livestock") },
  { key: "services", icon: "🛠️", label: t("marketplace.services") },
  { key: "machinery", icon: "🚜", label: t("marketplace.machinery") },
]);

const displayedListings = computed(() => {
  let list = [...listings.value];
  if (sort.value === "price_asc") list.sort((a, b) => Number(a.price) - Number(b.price));
  if (sort.value === "price_desc") list.sort((a, b) => Number(b.price) - Number(a.price));
  return list;
});

function listingCover(item) {
  return listingCoverFn(item);
}
function onImgError(e, item) {
  e.target.style.display = "none";
  // fall back to emoji by clearing would need state; hide img is enough if sibling exists
}
function commodityEmoji(item) {
  const name = (item.commodity?.name || "").toLowerCase();
  if (name.includes("maize") || name.includes("mahindi")) return "🌽";
  if (name.includes("rice") || name.includes("mchele")) return "🍚";
  if (name.includes("tomato") || name.includes("nyanya")) return "🍅";
  if (name.includes("bean")) return "🫘";
  if (name.includes("coffee")) return "☕";
  if (name.includes("banana")) return "🍌";
  if (name.includes("onion")) return "🧅";
  return "🌿";
}

function setCategory(key) {
  activeCategory.value = key;
  if (key === "services") window.location.href = "/services";
  if (key === "machinery") window.location.href = "/machinery";
}

function debouncedLoad() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(load, 300);
}

function applySort() {
  /* computed handles sort */
}

async function load() {
  loading.value = true;
  try {
    const [listingsRes, inputsRes] = await Promise.all([
      api.get("/listings"),
      api.get("/input-listings").catch(() => ({ data: [] })),
    ]);
    let list = listingsRes.data.data || listingsRes.data || [];
    let inputs = inputsRes.data.data || inputsRes.data || [];
    if (search.value) {
      const q = search.value.toLowerCase();
      list = list.filter(
        (i) =>
          (i.commodity?.name || "").toLowerCase().includes(q) ||
          (i.description || "").toLowerCase().includes(q) ||
          (i.region || "").toLowerCase().includes(q)
      );
      inputs = inputs.filter(
        (i) =>
          (i.name || "").toLowerCase().includes(q) ||
          (i.brand || "").toLowerCase().includes(q) ||
          (i.region || "").toLowerCase().includes(q)
      );
    }
    if (region.value) {
      list = list.filter((i) => (i.region || "").toLowerCase() === region.value.toLowerCase());
      inputs = inputs.filter((i) => (i.region || "").toLowerCase() === region.value.toLowerCase());
    }
    listings.value = list;
    inputListings.value = inputs;
  } catch {
    listings.value = [];
    inputListings.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
