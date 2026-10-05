<template>
  <div class="max-w-lg mx-auto p-4">
    <h1 class="text-2xl font-bold mb-1">Course payment</h1>
    <p class="text-sm text-gray-500 mb-6">Pay with Pesapal (mobile money / card) to unlock course materials.</p>

    <div v-if="loading" class="text-gray-400">Loading…</div>
    <div v-else-if="error" class="text-red-600 text-sm mb-4">{{ error }}</div>

    <div v-if="course" class="bg-white rounded-xl shadow p-5 mb-6">
      <p class="font-semibold text-lg">{{ course.title }}</p>
      <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ course.description }}</p>
      <p class="text-2xl font-bold text-green-700 mt-4">
        TZS {{ Number(course.price || 0).toLocaleString() }}
      </p>
    </div>

    <form v-if="course && !paid" class="bg-white rounded-xl shadow p-5 space-y-4" @submit.prevent="pay">
      <div>
        <label class="block text-sm text-gray-600 mb-1">Phone (M-Pesa / Tigo / Airtel)</label>
        <input v-model="phone" required type="tel" placeholder="07XXXXXXXX" class="w-full border rounded-lg px-3 py-2.5" />
      </div>
      <button type="submit" :disabled="paying" class="w-full bg-green-600 text-white font-semibold py-2.5 rounded-lg disabled:opacity-50">
        {{ paying ? "Redirecting to Pesapal…" : "Pay with Pesapal" }}
      </button>
      <p class="text-xs text-gray-400">You will be redirected to Pesapal. After payment, course access unlocks automatically.</p>
      <p v-if="msg" class="text-sm" :class="msgErr ? 'text-red-600' : 'text-green-700'">{{ msg }}</p>
    </form>

    <div v-if="paid" class="bg-green-50 text-green-800 rounded-xl p-5">
      <p class="font-semibold">Payment confirmed</p>
      <p class="text-sm mt-1">You can now access the course materials.</p>
      <router-link :to="`/knowledge/courses/${courseId}`" class="inline-block mt-3 text-green-800 font-semibold underline">
        Go to course →
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../../services/api";

const route = useRoute();
const router = useRouter();
const courseId = route.params.id;
const course = ref(null);
const phone = ref("");
const loading = ref(true);
const paying = ref(false);
const error = ref("");
const msg = ref("");
const msgErr = ref(false);
const paid = ref(false);

onMounted(async () => {
  try {
    const { data } = await api.get(`/training-courses/${courseId}`);
    course.value = data.course || data;
    const en = data.enrollment;
    if (en && (en.payment_status === "paid" || en.status === "enrolled" || en.status === "completed")) {
      paid.value = true;
    }
    // prefill phone from profile if available
    try {
      const me = await api.get("/profile");
      phone.value = me.data?.phone || "";
    } catch {}
  } catch (e) {
    error.value = e.response?.data?.message || "Could not load course";
  } finally {
    loading.value = false;
  }
});

async function pay() {
  paying.value = true;
  msg.value = "";
  try {
    const { data } = await api.post(`/training-courses/${courseId}/pay`, { phone: phone.value });
    if (data.access_granted) {
      paid.value = true;
      msgErr.value = false;
      msg.value = "Already paid";
      return;
    }
    const url = data.redirect_url;
    if (url) {
      window.location.href = url;
      return;
    }
    msgErr.value = true;
    msg.value = "No Pesapal redirect URL returned";
  } catch (e) {
    msgErr.value = true;
    msg.value = e.response?.data?.message || "Payment start failed";
  } finally {
    paying.value = false;
  }
}
</script>
