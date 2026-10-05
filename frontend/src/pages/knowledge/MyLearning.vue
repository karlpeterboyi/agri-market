<template>
  <div class="max-w-4xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-2">My learning</h1>
    <p class="text-sm text-gray-500 mb-6">Courses you are enrolled in</p>
    <p v-if="loading" class="text-gray-400">Loading…</p>
    <p v-else-if="error" class="text-red-600">{{ error }}</p>
    <div v-else class="space-y-3">
      <router-link
        v-for="e in items"
        :key="e.id"
        :to="`/knowledge/courses/${e.course?.id || e.training_course_id}`"
        class="block bg-white rounded-lg shadow p-4 hover:shadow-md"
      >
        <p class="font-semibold">{{ e.course?.title || "Course #" + e.training_course_id }}</p>
        <p class="text-sm text-gray-500">{{ e.status }} · {{ e.progress_percent || 0 }}% · enrolled {{ (e.enrolled_at || "").toString().slice(0, 10) }}</p>
        <div class="mt-2 h-2 bg-gray-100 rounded overflow-hidden">
          <div class="h-full bg-green-600" :style="{ width: (e.progress_percent || 0) + '%' }" />
        </div>
      </router-link>
      <p v-if="!items.length" class="text-gray-400 text-sm">
        No enrollments yet.
        <router-link to="/knowledge/courses" class="text-green-700">Browse courses</router-link>
      </p>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";
const items = ref([]);
const loading = ref(true);
const error = ref("");
onMounted(async () => {
  try {
    const { data } = await api.get("/my-course-enrollments");
    items.value = data.data || data || [];
    if (!Array.isArray(items.value)) items.value = [];
  } catch (e) {
    error.value = e.response?.data?.message || "Login required to view enrollments";
  } finally {
    loading.value = false;
  }
});
</script>
