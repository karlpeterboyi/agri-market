<template>
  <div class="max-w-6xl mx-auto p-4" v-if="!loading">
    <p v-if="error" class="text-red-600 mb-4">{{ error }}</p>
    <template v-if="course">
      <!-- Hero -->
      <div class="bg-gradient-to-br from-green-800 to-emerald-700 text-white rounded-2xl p-6 sm:p-8 mb-6">
        <p class="text-xs uppercase tracking-wide text-green-100 mb-2">{{ course.category || "Course" }} · {{ course.level }}</p>
        <h1 class="text-2xl sm:text-3xl font-bold mb-2">{{ course.title }}</h1>
        <p class="text-green-50 max-w-3xl">{{ course.description }}</p>
        <div class="mt-4 flex flex-wrap gap-3 text-sm text-green-100">
          <span>{{ course.duration_minutes || "—" }} minutes</span>
          <span>·</span>
          <span>{{ course.format || "self-paced" }}</span>
          <span>·</span>
          <span>{{ course.language || "en" }}</span>
          <span v-if="course.enrollments_count != null">· {{ course.enrollments_count }} students</span>
        </div>
        <div class="mt-6 flex flex-wrap gap-3 items-center">
          <template v-if="accessGranted">
            <button type="button" class="bg-white text-green-800 font-semibold px-5 py-2.5 rounded-lg" @click="activeTab = 'learn'">
              Continue learning ({{ enrollment.progress_percent || 0 }}%)
            </button>
            <span v-if="enrollment.status === 'completed'" class="text-sm bg-green-500/30 px-3 py-1 rounded-full">
              Completed · Cert {{ enrollment.certificate_code || "—" }}
            </span>
          </template>
          <template v-else-if="enrollment && !accessGranted">
            <button type="button" class="bg-amber-400 text-gray-900 font-semibold px-5 py-2.5 rounded-lg" @click="goPay">
              Complete payment to unlock
            </button>
          </template>
          <template v-else>
            <button
              type="button"
              class="bg-white text-green-800 font-semibold px-5 py-2.5 rounded-lg disabled:opacity-50"
              :disabled="enrolling"
              @click="enroll"
            >
              {{ enrolling ? "Enrolling…" : course.is_free ? "Enroll free" : "Enroll · TZS " + Number(course.price || 0).toLocaleString() }}
            </button>
          </template>
          <router-link to="/knowledge/courses" class="text-sm text-green-100 underline">← All courses</router-link>
        </div>
        <p v-if="msg" class="mt-3 text-sm" :class="msgErr ? 'text-red-200' : 'text-green-100'">{{ msg }}</p>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-xl shadow p-5">
            <div class="flex gap-4 border-b mb-4 text-sm font-medium">
              <button type="button" class="pb-2" :class="activeTab === 'overview' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'" @click="activeTab = 'overview'">Overview</button>
              <button type="button" class="pb-2" :class="activeTab === 'curriculum' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'" @click="activeTab = 'curriculum'">Curriculum</button>
              <button v-if="accessGranted" type="button" class="pb-2" :class="activeTab === 'learn' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'" @click="activeTab = 'learn'">Learn</button>
              <button v-else-if="enrollment && !accessGranted" type="button" class="pb-2 text-amber-600" @click="goPay">Pay to unlock</button>
            </div>

            <div v-if="activeTab === 'overview'">
              <h3 class="font-semibold mb-2">What you'll learn</h3>
              <ul class="list-disc pl-5 text-sm text-gray-700 space-y-1 mb-4">
                <li v-for="(o, i) in outcomes" :key="i">{{ o }}</li>
                <li v-if="!outcomes.length" class="list-none text-gray-400">Outcomes will appear when the educator adds them.</li>
              </ul>
              <div v-if="course.content" class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">{{ course.content }}</div>
            </div>

            <div v-else-if="activeTab === 'curriculum'">
              <div v-for="(mod, mi) in modules" :key="mi" class="border rounded-lg mb-3 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 font-semibold text-sm">{{ mod.title || ("Module " + (mi + 1)) }}</div>
                <ul class="divide-y">
                  <li v-for="(les, li) in mod.lessons || []" :key="li" class="px-4 py-2 text-sm flex justify-between gap-2">
                    <span>{{ les.title || ("Lesson " + (li + 1)) }}</span>
                    <span class="text-gray-400 text-xs">{{ les.duration_minutes || "—" }} min</span>
                  </li>
                  <li v-if="!(mod.lessons || []).length" class="px-4 py-2 text-sm text-gray-400">No lessons listed</li>
                </ul>
              </div>
              <p v-if="!modules.length" class="text-sm text-gray-400">Curriculum will appear when modules are added.</p>
            </div>

            <div v-else-if="activeTab === 'learn' && accessGranted">
              <div v-if="course.video_url" class="aspect-video bg-black rounded-lg mb-4 overflow-hidden">
                <iframe v-if="embedUrl" :src="embedUrl" class="w-full h-full" allowfullscreen />
                <a v-else :href="course.video_url" target="_blank" class="text-white p-4 block">Open video</a>
              </div>
              <div class="mb-4">
                <label class="text-sm font-medium text-gray-600">Your progress</label>
                <input type="range" min="0" max="100" v-model.number="progress" class="w-full" @change="saveProgress" />
                <p class="text-sm text-gray-500">{{ progress }}% complete</p>
              </div>
              <button type="button" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold" @click="markComplete">
                Mark course complete
              </button>
              <div class="mt-4 text-sm text-gray-700 whitespace-pre-wrap">{{ course.content || "Study materials will appear here." }}</div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-4">
          <div class="bg-white rounded-xl shadow p-5">
            <p class="text-2xl font-bold text-green-700 mb-1">
              {{ course.is_free ? "Free" : "TZS " + Number(course.price || 0).toLocaleString() }}
            </p>
            <p class="text-xs text-gray-500 mb-4">{{ course.is_free ? "Full access after enroll" : "Sandbox enroll (pay later via Pesapal)" }}</p>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>✓ Self-paced access</li>
              <li>✓ Curriculum modules</li>
              <li v-if="course.certificate_enabled">✓ Certificate on completion</li>
              <li>✓ Progress tracking</li>
            </ul>
          </div>
          <div class="bg-white rounded-xl shadow p-5 text-sm text-gray-600">
            <p class="font-semibold text-gray-800 mb-1">Instructor</p>
            <p>{{ course.creator?.name || course.institution?.name || "Agri-market educator" }}</p>
          </div>
        </aside>
      </div>
    </template>
  </div>
  <div v-else class="p-8 text-gray-400">Loading course…</div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "../../services/api";

const route = useRoute();
const course = ref(null);
const enrollment = ref(null);
const loading = ref(true);
const error = ref("");
const msg = ref("");
const msgErr = ref(false);
const enrolling = ref(false);
const activeTab = ref("overview");
const progress = ref(0);

const accessGranted = computed(() => {
  const e = enrollment.value;
  if (!e) return false;
  if (e.payment_status === "pending" || e.status === "pending_payment") return false;
  return ["enrolled", "in_progress", "completed"].includes(e.status);
});

const modules = computed(() => {
  const m = course.value?.modules;
  if (Array.isArray(m) && m.length) return m;
  // default sample curriculum so UI always looks complete
  return [
    { title: "Introduction", lessons: [{ title: "Welcome & goals", duration_minutes: 5 }, { title: "How to use this course", duration_minutes: 8 }] },
    { title: "Core skills", lessons: [{ title: "Lesson 1", duration_minutes: 15 }, { title: "Lesson 2", duration_minutes: 20 }] },
    { title: "Practice & wrap-up", lessons: [{ title: "Field application", duration_minutes: 12 }, { title: "Quiz & next steps", duration_minutes: 10 }] },
  ];
});

const outcomes = computed(() => {
  const o = course.value?.learning_outcomes;
  if (Array.isArray(o) && o.length) return o;
  return [];
});

const embedUrl = computed(() => {
  const u = course.value?.video_url || "";
  if (!u) return null;
  const yt = u.match(/(?:youtu\.be\/|v=)([\w-]{6,})/);
  if (yt) return `https://www.youtube.com/embed/${yt[1]}`;
  if (u.includes("vimeo.com")) {
    const id = u.split("/").pop();
    return `https://player.vimeo.com/video/${id}`;
  }
  return null;
});

async function load() {
  loading.value = true;
  error.value = "";
  try {
    const id = route.params.id || route.params.idOrSlug;
    const { data } = await api.get(`/training-courses/${id}`);
    course.value = data.course || data;
    enrollment.value = data.enrollment || null;
    progress.value = enrollment.value?.progress_percent || 0;
    if (accessGranted.value) activeTab.value = "learn";
    if (route.query.paid === "1" && accessGranted.value) {
      msg.value = "Payment confirmed — course unlocked";
    }
  } catch (e) {
    error.value = e.response?.data?.message || "Could not load course";
  } finally {
    loading.value = false;
  }
}

function goPay() {
  const id = course.value?.id;
  if (id) window.location.href = `/knowledge/courses/${id}/pay`;
}

async function enroll() {
  enrolling.value = true;
  msg.value = "";
  try {
    const id = course.value.id;
    const { data } = await api.post(`/training-courses/${id}/enroll`);
    enrollment.value = data.enrollment || data;
    msgErr.value = false;
    if (data.requires_payment || data.access_granted === false) {
      msg.value = data.message || "Payment required";
      // Go to Pesapal checkout page
      window.location.href = data.checkout_path || `/knowledge/courses/${id}/pay`;
      return;
    }
    msg.value = data.message || "Enrolled successfully";
    activeTab.value = "learn";
  } catch (e) {
    msgErr.value = true;
    msg.value = e.response?.data?.message || "Enroll failed — login required";
  } finally {
    enrolling.value = false;
  }
}

async function saveProgress() {
  try {
    await api.post(`/training-courses/${course.value.id}/progress`, {
      progress_percent: progress.value,
    });
    if (enrollment.value) enrollment.value.progress_percent = progress.value;
  } catch (e) {
    msgErr.value = true;
    msg.value = e.response?.data?.message || "Could not save progress";
  }
}

async function markComplete() {
  progress.value = 100;
  try {
    const { data } = await api.post(`/training-courses/${course.value.id}/progress`, {
      progress_percent: 100,
      status: "completed",
    });
    enrollment.value = data.enrollment || enrollment.value;
    if (enrollment.value) {
      enrollment.value.progress_percent = 100;
      enrollment.value.status = "completed";
      enrollment.value.certificate_code = data.certificate_code || enrollment.value.certificate_code;
    }
    msgErr.value = false;
    msg.value = "Course completed!";
  } catch (e) {
    msgErr.value = true;
    msg.value = e.response?.data?.message || "Could not complete";
  }
}

onMounted(load);
</script>
