<template>
  <div>

    <div
      v-if="loading"
      class="text-gray-500"
    >
      Loading livestock...
    </div>

    <div
      v-else
      class="space-y-4"
    >

      <div
        v-for="item in livestock"
        :key="item.id"
        class="border rounded-lg p-4 hover:shadow-md transition"
      >

        <div class="flex justify-between items-start">

          <div>

            <h3 class="font-bold text-lg">
              {{ item.title }}
            </h3>

            <p class="text-gray-600 text-sm">
              {{ item.species?.name }}
            </p>

            <p class="text-gray-500 text-sm">
              {{ item.region }}
            </p>

          </div>

          <div class="text-green-700 font-bold">
            TZS {{ Number(item.price).toLocaleString() }}
          </div>

        </div>

        <router-link
          :to="`/livestock/${item.id}`"
          class="inline-block mt-3 text-green-700 font-semibold"
        >
          View Details →
        </router-link>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";

const livestock = ref([]);
const loading = ref(true);

async function load() {
    try {
        const res = await api.get("/livestock");

        livestock.value = res.data.slice(0, 4);

    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>