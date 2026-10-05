<template>
  <div class="max-w-6xl mx-auto p-4 sm:p-6">
    <h2 class="text-2xl sm:text-3xl font-bold mb-2">Knowledge Hub</h2>
    <p class="text-gray-500 mb-6">Courses, research, extension advice and practical learning for farmers</p>

    <div class="mb-6 flex flex-col sm:flex-row gap-2">
      <input
        v-model="q"
        @keyup.enter="search"
        placeholder="Search courses, research, officers…"
        class="flex-1 border rounded-lg px-4 py-2"
      />
      <button type="button" @click="search" class="bg-green-600 text-white px-5 py-2 rounded-lg font-medium">
        Search
      </button>
    </div>

    <div v-if="searchResults" class="mb-8 bg-white rounded-lg shadow p-4">
      <h3 class="font-semibold mb-2">Search results for “{{ searchResults.query }}”</h3>
      <ul class="space-y-1 text-sm">
        <li v-for="c in searchResults.courses || []" :key="c.id">
          <router-link :to="`/knowledge/courses/${c.id}`" class="text-green-700 hover:underline">{{ c.title }}</router-link>
        </li>
        <li v-if="!(searchResults.courses || []).length" class="text-gray-400">No courses matched.</li>
      </ul>
    </div>

    <section class="mb-10">
      <div class="flex justify-between items-center mb-3">
        <h3 class="text-xl font-semibold">Courses</h3>
        <router-link to="/knowledge/courses" class="text-green-600 text-sm font-medium">View all →</router-link>
      </div>
      <p v-if="loading" class="text-gray-400 text-sm">Loading courses…</p>
      <p v-else-if="!courses.length" class="text-gray-400 text-sm">No published courses yet.</p>
      <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <router-link
          v-for="c in courses"
          :key="c.id"
          :to="`/knowledge/courses/${c.id}`"
          class="bg-white rounded-lg shadow p-4 hover:shadow-md block"
        >
          <div class="flex gap-2 text-xs mb-2 flex-wrap">
            <span class="bg-gray-100 px-2 py-0.5 rounded">{{ c.level || 'beginner' }}</span>
            <span v-if="c.is_free !== false" class="bg-green-100 text-green-800 px-2 py-0.5 rounded">Free</span>
            <span v-if="c.featured" class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded">Featured</span>
          </div>
          <p class="font-bold">{{ c.title }}</p>
          <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ c.description }}</p>
          <p class="text-xs text-gray-400 mt-2">{{ c.category }} · {{ c.duration_minutes || '—' }} min</p>
        </router-link>
      </div>
    </section>

    <section v-if="hub?.latest_publications?.length" class="mb-10">
      <h3 class="text-xl font-semibold mb-3">Latest research</h3>
      <div class="bg-white rounded-lg shadow divide-y">
        <div v-for="p in hub.latest_publications" :key="p.id" class="p-4">
          <p class="font-medium">{{ p.title }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ p.category }} · {{ p.publication_date }}</p>
        </div>
      </div>
    </section>

    <section v-if="hub?.extension_officers?.length">
      <h3 class="text-xl font-semibold mb-3">Extension officers</h3>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="o in hub.extension_officers" :key="o.id" class="bg-white rounded-lg shadow p-4">
          <p class="font-semibold">{{ o.user?.name || 'Officer' }}</p>
          <p class="text-sm text-gray-500">{{ o.profession }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ o.region }}</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getKnowledgeHub, searchKnowledge, getCourses } from "../../api/knowledge";

const hub = ref(null);
const courses = ref([]);
const loading = ref(true);
const q = ref("");
const searchResults = ref(null);

async function load() {
  loading.value = true;
  try {
    const { data } = await getKnowledgeHub();
    hub.value = data;
    let list = data?.featured_courses || [];
    if (!list.length) {
      const res = await getCourses();
      list = res.data?.data ?? res.data ?? [];
      if (list && !Array.isArray(list) && Array.isArray(list.data)) list = list.data;
    }
    courses.value = Array.isArray(list) ? list : [];
  } catch (e) {
    try {
      const res = await getCourses();
      let list = res.data?.data ?? res.data ?? [];
      if (list && !Array.isArray(list) && Array.isArray(list.data)) list = list.data;
      courses.value = Array.isArray(list) ? list : [];
    } catch {
      courses.value = [];
    }
  } finally {
    loading.value = false;
  }
}

async function search() {
  if (!q.value || q.value.length < 2) return;
  try {
    const { data } = await searchKnowledge(q.value);
    searchResults.value = { query: q.value, ...data };
  } catch {
    searchResults.value = { query: q.value, courses: [] };
  }
}

onMounted(load);
</script>
