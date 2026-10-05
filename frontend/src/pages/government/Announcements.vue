<template>
  <div>
    <h2 class="text-3xl font-bold mb-6">Government Announcements</h2>
    <div class="space-y-4">
      <div v-for="a in items" :key="a.id" class="bg-white rounded-lg shadow p-5">
        <div class="flex justify-between items-start gap-2">
          <h3 class="font-bold text-lg">{{ a.title }}</h3>
          <span class="text-xs px-2 py-0.5 rounded shrink-0" :class="a.priority === 'high' || a.priority === 'urgent' ? 'bg-red-100 text-red-800' : 'bg-gray-100'">{{ a.priority }}</span>
        </div>
        <p class="text-sm text-gray-500 mt-1">{{ a.source_organisation }} · {{ a.published_at?.slice?.(0, 10) }}</p>
        <p class="mt-2 text-gray-700">{{ a.summary }}</p>
        <span v-if="a.category" class="inline-block mt-2 text-xs bg-green-50 text-green-800 px-2 py-0.5 rounded">{{ a.category }}</span>
      </div>
      <p v-if="!items.length" class="text-gray-400">No announcements.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getAnnouncements } from "../../api/government";
const items = ref([]);
onMounted(async () => {
  const { data } = await getAnnouncements();
  items.value = data.data || data;
});
</script>
