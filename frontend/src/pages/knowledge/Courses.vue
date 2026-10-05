<template>
  <div class="max-w-6xl mx-auto p-4">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Online courses</h1>
        <p class="text-sm text-gray-500 mt-1">Learn farming, livestock, finance and agribusiness — self-paced.</p>
      </div>
      <router-link to="/knowledge/my-learning" class="text-sm text-green-700 font-semibold">My learning →</router-link>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-6 flex flex-col sm:flex-row gap-3">
      <input v-model="search" type="search" placeholder="Search courses..." class="flex-1 border rounded-lg px-3 py-2" @input="debounced" />
      <select v-model="filter" class="border rounded-lg px-3 py-2">
        <option v-for="c in categories" :key="c || 'all'" :value="c">{{ c || "All categories" }}</option>
      </select>
      <select v-model="level" class="border rounded-lg px-3 py-2">
        <option value="">All levels</option>
        <option value="beginner">Beginner</option>
        <option value="intermediate">Intermediate</option>
        <option value="advanced">Advanced</option>
      </select>
    </div>

    <p v-if="loading" class="text-gray-400">Loading courses…</p>
    <p v-else-if="error" class="text-red-600 text-sm">{{ error }}</p>
    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <router-link
        v-for="course in filtered"
        :key="course.id"
        :to="`/knowledge/courses/${course.slug || course.id}`"
        class="bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden flex flex-col"
      >
        <div class="h-36 bg-gradient-to-br from-green-700 to-emerald-600 text-white flex items-end p-4">
          <div>
            <span class="text-xs bg-white/20 px-2 py-0.5 rounded">{{ course.level || "Course" }}</span>
            <h3 class="font-bold text-lg mt-2 line-clamp-2">{{ course.title }}</h3>
          </div>
        </div>
        <div class="p-4 flex-1 flex flex-col">
          <p class="text-sm text-gray-600 line-clamp-2 flex-1">{{ course.description }}</p>
          <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
            <span>{{ course.duration_minutes || "—" }} min · {{ course.enrollments_count || 0 }} students</span>
            <span class="font-semibold text-green-700">{{ course.is_free ? "Free" : "TZS " + Number(course.price || 0).toLocaleString() }}</span>
          </div>
        </div>
      </router-link>
      <p v-if="!filtered.length" class="text-gray-400 col-span-full">No published courses yet.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { getCourses } from "../../api/knowledge";

const courses = ref([]);
const filter = ref("");
const level = ref("");
const search = ref("");
const loading = ref(true);
const error = ref("");
const categories = ["", "crop_production", "livestock", "finance", "pest_disease", "climate", "business"];
let t = null;

const filtered = computed(() => {
  let list = courses.value;
  if (filter.value) list = list.filter((c) => c.category === filter.value);
  if (level.value) list = list.filter((c) => c.level === level.value);
  if (search.value) {
    const q = search.value.toLowerCase();
    list = list.filter(
      (c) =>
        (c.title || "").toLowerCase().includes(q) ||
        (c.description || "").toLowerCase().includes(q)
    );
  }
  return list;
});

function debounced() {
  clearTimeout(t);
  t = setTimeout(() => {}, 200);
}

onMounted(async () => {
  loading.value = true;
  try {
    const { data } = await getCourses();
    let list = data?.data ?? data;
    if (list && !Array.isArray(list) && Array.isArray(list.data)) list = list.data;
    courses.value = Array.isArray(list) ? list : [];
  } catch (e) {
    error.value = e.response?.data?.message || "Failed to load courses";
  } finally {
    loading.value = false;
  }
});
</script>
