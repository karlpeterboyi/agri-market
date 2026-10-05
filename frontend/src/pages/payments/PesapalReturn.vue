<template>
  <div class="max-w-md mx-auto p-8 text-center">
    <h1 class="text-xl font-bold mb-2">Confirming payment…</h1>
    <p class="text-sm text-gray-500 mb-4">{{ status }}</p>
    <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
    <router-link v-if="doneLink" :to="doneLink" class="text-green-700 font-semibold underline">Continue →</router-link>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../../services/api";

const route = useRoute();
const router = useRouter();
const status = ref("Please wait");
const error = ref("");
const doneLink = ref("");

onMounted(async () => {
  try {
    const params = {
      OrderTrackingId: route.query.OrderTrackingId || route.query.orderTrackingId,
      payment_id: route.query.payment_id,
    };
    const { data } = await api.post("/payments/confirm-return", params);
    status.value = data.message || "Payment confirmed";
    if (data.access_granted && data.enrollment) {
      const cid = data.enrollment.training_course_id;
      doneLink.value = `/knowledge/courses/${cid}?paid=1`;
      setTimeout(() => router.replace(doneLink.value), 1200);
    } else if (data.payment?.payment_type === "order") {
      doneLink.value = "/buyer/orders";
      setTimeout(() => router.replace(doneLink.value), 1200);
    } else {
      doneLink.value = "/knowledge/my-learning";
    }
  } catch (e) {
    error.value = e.response?.data?.message || e.message || "Confirmation failed";
    status.value = "Could not confirm payment";
    doneLink.value = "/knowledge/courses";
  }
});
</script>
