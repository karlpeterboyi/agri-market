<template>
  <div class="max-w-5xl mx-auto p-4 sm:p-6">
    <h1 class="text-2xl sm:text-3xl font-bold mb-2">Knowledge & Education Portal</h1>
    <p class="text-gray-500 mb-6 text-sm">
      For universities, research institutes and extension educators. Publish short courses
      (video, self-paced, live) and manage your institution profile.
    </p>

    <div v-if="!institution" class="bg-white rounded-xl shadow p-6 mb-8">
      <h2 class="font-bold text-lg mb-4">Register research / education institution</h2>
      <form @submit.prevent="saveInstitution" class="grid sm:grid-cols-2 gap-3">
        <input v-model="instForm.name" required placeholder="Institution name *" class="border rounded px-3 py-2" />
        <input v-model="instForm.acronym" placeholder="Acronym e.g. SUA" class="border rounded px-3 py-2" />
        <select v-model="instForm.institution_type" class="border rounded px-3 py-2 bg-white">
          <option value="university">University</option>
          <option value="research">Research institute</option>
          <option value="government">Government / extension</option>
          <option value="ngo">NGO</option>
          <option value="private">Private training provider</option>
        </select>
        <input v-model="instForm.region" placeholder="Region" class="border rounded px-3 py-2" />
        <input v-model="instForm.email" type="email" placeholder="Email" class="border rounded px-3 py-2" />
        <input v-model="instForm.website" placeholder="Website" class="border rounded px-3 py-2" />
        <textarea v-model="instForm.description" placeholder="About" class="border rounded px-3 py-2 sm:col-span-2" rows="2" />
        <p v-if="error" class="sm:col-span-2 text-red-600 text-sm">{{ error }}</p>
        <button type="submit" class="sm:col-span-2 bg-green-700 text-white rounded py-2 font-semibold">Register</button>
      </form>
      <p class="text-xs text-gray-400 mt-3">You can still publish courses as an educator without an institution.</p>
    </div>

    <div v-else class="bg-white rounded-lg shadow p-4 mb-6">
      <p class="font-bold">{{ institution.name }} <span class="text-gray-400 text-sm font-normal">({{ institution.institution_type }})</span></p>
      <p class="text-sm text-gray-500">{{ stats.courses || 0 }} courses · {{ stats.published_courses || 0 }} published</p>
    </div>

    <h2 class="font-bold text-lg mb-3">Publish a short course</h2>
    <form @submit.prevent="saveCourse" class="bg-white rounded-xl shadow p-4 grid sm:grid-cols-2 gap-3 mb-8">
      <input v-model="course.title" required placeholder="Course title *" class="border rounded px-3 py-2 sm:col-span-2" />
      <select v-model="course.category" class="border rounded px-3 py-2 bg-white">
        <option value="crop_production">Crop production</option>
        <option value="livestock">Livestock</option>
        <option value="finance">Agri-finance</option>
        <option value="pest_disease">Pest & disease</option>
        <option value="climate">Climate & soil</option>
        <option value="business">Agribusiness</option>
      </select>
      <select v-model="course.format" class="border rounded px-3 py-2 bg-white">
        <option value="self_paced">Self-paced</option>
        <option value="video">Video series</option>
        <option value="live">Live / webinar</option>
        <option value="hybrid">Hybrid</option>
        <option value="downloadable">Downloadable pack</option>
      </select>
      <select v-model="course.level" class="border rounded px-3 py-2 bg-white">
        <option value="beginner">Beginner</option>
        <option value="intermediate">Intermediate</option>
        <option value="advanced">Advanced</option>
      </select>
      <input v-model.number="course.duration_minutes" type="number" placeholder="Duration (minutes)" class="border rounded px-3 py-2" />
      <input v-model="course.video_url" placeholder="Video URL (YouTube/Vimeo)" class="border rounded px-3 py-2 sm:col-span-2" />
      <label class="flex items-center gap-2 text-sm"><input v-model="course.is_free" type="checkbox" /> Free course</label>
      <input v-if="!course.is_free" v-model.number="course.price" type="number" placeholder="Price (TZS)" class="border rounded px-3 py-2" />
      <textarea v-model="course.description" required placeholder="Description *" class="border rounded px-3 py-2 sm:col-span-2" rows="3" />
      <textarea v-model="course.modules_text" placeholder="Modules (one per line: Module title | Lesson 1; Lesson 2)" class="border rounded px-3 py-2 sm:col-span-2" rows="3" />
      <label class="flex items-center gap-2 text-sm sm:col-span-2"><input v-model="course.is_published" type="checkbox" /> Publish immediately (visible on Knowledge Hub)</label>
      <p v-if="courseError" class="sm:col-span-2 text-red-600 text-sm">{{ courseError }}</p>
      <button type="submit" class="sm:col-span-2 bg-green-700 text-white rounded py-2 font-semibold">Save course</button>
    </form>

    <div class="bg-white rounded-lg shadow divide-y">
      <div v-for="c in courses" :key="c.id" class="p-4 flex justify-between gap-2">
        <div>
          <p class="font-semibold">{{ c.title }}</p>
          <p class="text-sm text-gray-500">{{ c.format }} · {{ c.level }} · {{ c.is_published ? 'Published' : 'Draft' }}</p>
        </div>
        <router-link :to="'/knowledge/courses/' + c.id" class="text-green-600 text-sm">View</router-link>
      </div>
      <p v-if="!courses.length" class="p-4 text-gray-400 text-sm">No courses yet.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const institution = ref(null);
const stats = ref({});
const courses = ref([]);
const error = ref("");
const courseError = ref("");

const instForm = ref({
  name: "",
  acronym: "",
  institution_type: "university",
  region: "",
  email: "",
  website: "",
  description: "",
});
const course = ref({
  title: "",
  category: "crop_production",
  format: "self_paced",
  level: "beginner",
  duration_minutes: 60,
  video_url: "",
  description: "",
  is_free: true,
  price: 0,
  modules_text: '',
  is_published: true,
  language: "sw",
});

async function load() {
  try {
    const { data } = await api.get("/knowledge/dashboard");
    institution.value = data.institution;
    stats.value = data.stats || {};
    courses.value = data.courses || [];
  } catch {
    courses.value = [];
  }
}

async function saveInstitution() {
  error.value = "";
  try {
    await api.post("/knowledge/institution", instForm.value);
    await load();
  } catch (e) {
    error.value = e.response?.data?.message || "Failed";
  }
}

async function saveCourse() {
  courseError.value = "";
  try {
    let modules = [];
    if (course.value.modules_text) {
      modules = course.value.modules_text.split("\n").filter(Boolean).map((line) => {
        const parts = line.split("|").map((s) => s.trim());
        const title = parts[0] || "Module";
        const lessonsStr = parts[1] || "";
        const lessons = lessonsStr.split(";").map((t) => t.trim()).filter(Boolean).map((t) => ({ title: t }));
        return { title, lessons };
      });
    }
    await api.post("/training-courses", {
      title: course.value.title,
      description: course.value.description || course.value.title,
      category: course.value.category,
      format: course.value.format,
      level: course.value.level,
      duration_minutes: course.value.duration_minutes || null,
      video_url: course.value.video_url || null,
      language: course.value.language || "sw",
      is_free: !!course.value.is_free,
      price: course.value.is_free ? 0 : (course.value.price || 0),
      is_published: !!course.value.is_published,
      modules,
    });
    course.value.title = "";
    course.value.description = "";
    course.value.modules_text = "";
    await load();
  } catch (e) {
    const d = e.response?.data;
    courseError.value = d?.message || (d?.errors ? JSON.stringify(d.errors) : "Failed to save course");
  }
}

onMounted(load);
</script>
