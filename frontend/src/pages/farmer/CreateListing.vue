<template>
  <div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">
          Create Listing
        </h1>
        <p class="text-gray-500 mt-1">
          List your agricultural produce for buyers.
        </p>
      </div>

      <router-link
        to="/farmer/listings"
        class="text-green-600 hover:text-green-700 font-medium"
      >
        ← My Listings
      </router-link>
    </div>

    <div class="bg-white rounded-lg shadow border p-6">
      <div
        v-if="success"
        class="mb-5 p-4 rounded-lg bg-green-100 text-green-700"
      >
        {{ success }}
      </div>

      <div
        v-if="error"
        class="mb-5 p-4 rounded-lg bg-red-100 text-red-700 whitespace-pre-line"
      >
        {{ error }}
      </div>

      <form @submit.prevent="createListing" class="space-y-5">

        <!-- Commodity -->
        <div>
          <label class="block mb-1 font-medium text-gray-700">
            Commodity
          </label>

          <select
            v-model="form.commodity_id"
            required
            class="w-full border rounded-lg p-3 bg-white"
          >
            <option value="" disabled>
              Select commodity
            </option>

            <option
              v-for="commodity in commodities"
              :key="commodity.id"
              :value="commodity.id"
            >
              {{ commodity.name }}
            </option>
          </select>

          <p
            v-if="loadingCommodities"
            class="text-sm text-gray-500 mt-1"
          >
            Loading commodities...
          </p>
        </div>

        <!-- Quantity + Unit -->
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Quantity
            </label>

            <input
              v-model.number="form.quantity"
              type="number"
              min="0.01"
              step="0.01"
              required
              placeholder="500"
              class="w-full border rounded-lg p-3"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Unit
            </label>

            <select
              v-model="form.unit"
              required
              class="w-full border rounded-lg p-3 bg-white"
            >
              <option value="" disabled>Select unit</option>
              <option value="kg">Kilograms (kg)</option>
              <option value="ton">Tonnes</option>
              <option value="bag">Bags</option>
              <option value="crate">Crates</option>
              <option value="piece">Pieces</option>
              <option value="litre">Litres</option>
            </select>
          </div>
        </div>

        <!-- Grade + Price -->
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Grade
            </label>

            <input
              v-model="form.grade"
              type="text"
              placeholder="Grade A"
              class="w-full border rounded-lg p-3"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Price (TZS)
            </label>

            <input
              v-model.number="form.price"
              type="number"
              min="0"
              step="0.01"
              required
              placeholder="1500"
              class="w-full border rounded-lg p-3"
            />
          </div>
        </div>

        <!-- Location -->
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700">
              Region
            </label>

            <input
              v-model="form.region"
              type="text"
              required
              placeholder="Morogoro"
              class="w-full border rounded-lg p-3"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700">
              District
            </label>

            <input
              v-model="form.district"
              type="text"
              required
              placeholder="Kilosa"
              class="w-full border rounded-lg p-3"
            />
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block mb-1 font-medium text-gray-700">
            Description
          </label>

          <textarea
            v-model="form.description"
            rows="4"
            placeholder="Describe the produce, quality, availability, etc."
            class="w-full border rounded-lg p-3"
          ></textarea>
        </div>

        <!-- Images: featured + 2 more -->
        <div>
          <label class="block mb-2 font-medium text-gray-700">Product images</label>
          <p class="text-xs text-gray-500 mb-3">Upload up to 3 photos. First is the featured image.</p>
          <div class="grid sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs text-gray-500 mb-1">Featured image</label>
              <input type="file" accept="image/*" @change="onFile('featured_image', $event)" class="w-full text-sm" />
              <img v-if="previews.featured_image" :src="previews.featured_image" class="mt-2 h-24 w-full object-cover rounded border" />
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Image 2</label>
              <input type="file" accept="image/*" @change="onFile('image_2', $event)" class="w-full text-sm" />
              <img v-if="previews.image_2" :src="previews.image_2" class="mt-2 h-24 w-full object-cover rounded border" />
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Image 3</label>
              <input type="file" accept="image/*" @change="onFile('image_3', $event)" class="w-full text-sm" />
              <img v-if="previews.image_3" :src="previews.image_3" class="mt-2 h-24 w-full object-cover rounded border" />
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex gap-3 pt-2">
          <router-link
            to="/farmer/listings"
            class="flex-1 text-center border border-gray-300 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-50"
          >
            Cancel
          </router-link>

          <button
            type="submit"
            :disabled="submitting || loadingCommodities"
            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold disabled:opacity-50"
          >
            {{ submitting ? "Creating..." : "Create Listing" }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../../services/api";

const router = useRouter();

const commodities = ref([]);
const loadingCommodities = ref(true);
const submitting = ref(false);

const success = ref("");
const error = ref("");

const form = ref({
  commodity_id: "",
  quantity: null,
  unit: "",
  grade: "",
  price: null,
  region: "",
  district: "",
  description: ""
});
const files = ref({ featured_image: null, image_2: null, image_3: null });
const previews = ref({ featured_image: "", image_2: "", image_3: "" });

function onFile(key, e) {
  const f = e.target.files?.[0] || null;
  files.value[key] = f;
  if (previews.value[key]) URL.revokeObjectURL(previews.value[key]);
  previews.value[key] = f ? URL.createObjectURL(f) : "";
}


async function loadCommodities() {
  loadingCommodities.value = true;

  try {
    const response = await api.get("/commodities");
    commodities.value = response.data || [];
  } catch (err) {
    console.error("Failed to load commodities:", err);

    error.value =
      err.response?.data?.message ||
      "Failed to load commodities.";
  } finally {
    loadingCommodities.value = false;
  }
}

async function createListing() {
  submitting.value = true;
  success.value = "";
  error.value = "";

  try {
    const fd = new FormData();
    fd.append("commodity_id", form.value.commodity_id);
    fd.append("quantity", form.value.quantity);
    fd.append("unit", form.value.unit);
    if (form.value.grade) fd.append("grade", form.value.grade);
    fd.append("price", form.value.price);
    fd.append("region", form.value.region);
    fd.append("district", form.value.district);
    if (form.value.description) fd.append("description", form.value.description);
    for (const key of ["featured_image", "image_2", "image_3"]) {
      const f = files.value[key];
      if (f instanceof File && f.size > 0) {
        // Explicit filename helps some servers detect MIME correctly
        fd.append(key, f, f.name || `${key}.jpg`);
      }
    }
    const response = await api.post("/listings", fd, {
      timeout: 120000,
      headers: { Accept: "application/json" },
      // Do not set Content-Type — browser must set multipart boundary
      transformRequest: [
        (data, headers) => {
          if (data instanceof FormData && headers) {
            delete headers["Content-Type"];
            delete headers["content-type"];
          }
          return data;
        },
      ],
    });

    success.value = response.data?.message || "Listing created successfully.";
    if (response.data?.image_notes && Object.keys(response.data.image_notes).length) {
      success.value += " (some images skipped: " + JSON.stringify(response.data.image_notes) + ")";
    }
    console.log("Listing created:", response.data);
    setTimeout(() => {
      const role = JSON.parse(localStorage.getItem("user") || "{}")?.role;
      router.push(role === "processor" ? "/processor/listings" : "/farmer/listings");
    }, 800);

  } catch (err) {
    console.error("Failed to create listing:", err);

    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)
        .flat()
        .join("\n");
    } else {
      error.value =
        err.response?.data?.message ||
        "Failed to create listing. Please try again.";
    }
  } finally {
    submitting.value = false;
  }
}

onMounted(loadCommodities);
</script>
