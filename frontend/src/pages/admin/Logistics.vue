<template>
  <div class="space-y-6">

    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-gray-800">
        Logistics & Dispatch
      </h1>

      <p class="text-gray-500 mt-1">
        Assign transporters and vehicles to pending delivery requests.
      </p>
    </div>

    <!-- Loading -->
    <div
      v-if="loading"
      class="bg-white rounded-lg shadow p-6 text-gray-500"
    >
      Loading logistics requests...
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4"
    >
      {{ error }}
    </div>

    <template v-else>

      <!-- Pending Requests -->
      <section>
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-gray-800">
            Pending Transport Requests
          </h2>

          <span
            class="bg-yellow-100 text-yellow-800 text-sm font-semibold px-3 py-1 rounded-full"
          >
            {{ pendingRequests.length }} pending
          </span>
        </div>

        <div
          v-if="pendingRequests.length === 0"
          class="bg-white rounded-lg shadow p-8 text-center text-gray-500"
        >
          No pending transport requests.
        </div>

        <div v-else class="space-y-4">

          <div
            v-for="request in pendingRequests"
            :key="request.id"
            class="bg-white rounded-lg shadow border p-6"
          >

            <!-- Request information -->
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">

              <div class="flex-1">

                <div class="flex items-center gap-3 mb-4">
                  <h3 class="text-lg font-bold text-gray-800">
                    Order #{{ request.order_id }}
                  </h3>

                  <span
                    class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full"
                  >
                    {{ formatStatus(request.status) }}
                  </span>
                </div>

                <div class="grid md:grid-cols-2 gap-4">

                  <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">
                      Buyer
                    </p>

                    <p class="font-semibold text-gray-800 mt-1">
                      {{ request.buyer?.name || "N/A" }}
                    </p>
                  </div>

                  <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">
                      Commodity
                    </p>

                    <p class="font-semibold text-gray-800 mt-1">
                      {{ request.listing?.commodity?.name || "N/A" }}
                    </p>
                  </div>

                  <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">
                      Pickup
                    </p>

                    <p class="font-medium text-gray-700 mt-1">
                      {{ location(request.pickup_region, request.pickup_district) }}
                    </p>
                  </div>

                  <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">
                      Destination
                    </p>

                    <p class="font-medium text-gray-700 mt-1">
                      {{ location(request.delivery_region, request.delivery_district) }}
                    </p>
                  </div>

                  <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">
                      Quantity
                    </p>

                    <p class="font-semibold text-gray-800 mt-1">
                      {{ request.quantity ?? "N/A" }}
                    </p>
                  </div>

                </div>

              </div>

              <!-- Assignment -->
              <div class="lg:w-96 border-t lg:border-t-0 lg:border-l pt-5 lg:pt-0 lg:pl-5">

                <h4 class="font-semibold text-gray-800 mb-4">
                  Assign Transport
                </h4>

                <!-- Transporter -->
                <label class="block text-sm font-medium text-gray-600 mb-1">
                  Transporter
                </label>

                <select
                  v-model="selections[request.id].transporter_id"
                  @change="onTransporterChange(request.id)"
                  class="w-full border rounded-lg px-3 py-2 mb-4 focus:ring-2 focus:ring-red-500 focus:outline-none"
                >
                  <option value="">
                    Select transporter
                  </option>

                  <option
                    v-for="transporter in transporters"
                    :key="transporter.id"
                    :value="String(transporter.id)"
                  >
                    {{ transporter.company_name }}
                  </option>
                </select>

                <!-- Vehicle -->
                <label class="block text-sm font-medium text-gray-600 mb-1">
                  Vehicle
                </label>

                <select
                  v-model="selections[request.id].vehicle_id"
                  :disabled="!selections[request.id].transporter_id"
                  class="w-full border rounded-lg px-3 py-2 mb-4 disabled:bg-gray-100 disabled:text-gray-400 focus:ring-2 focus:ring-red-500 focus:outline-none"
                >
                  <option value="">
                    Select vehicle
                  </option>

                  <option
                    v-for="vehicle in availableVehicles(request.id)"
                    :key="vehicle.id"
                    :value="String(vehicle.id)"
                  >
                    {{ vehicle.registration_number }}
                    — {{ vehicle.vehicle_type }}
                  </option>
                </select>

                <!-- Assignment error -->
                <div
                  v-if="assignmentErrors[request.id]"
                  class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-3"
                >
                  {{ assignmentErrors[request.id] }}
                </div>

                <!-- Assign button -->
                <button
                  type="button"
                  @click="assignTransport(request)"
                  :disabled="assigningId === request.id"
                  class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold px-4 py-2.5 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{
                    assigningId === request.id
                      ? "Assigning..."
                      : "Assign Transport"
                  }}
                </button>

              </div>

            </div>

          </div>

        </div>
      </section>

      <!-- Assigned Requests -->
      <section>

        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-gray-800">
            Assigned Transport
          </h2>

          <span
            class="bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full"
          >
            {{ assignedRequests.length }} assigned
          </span>
        </div>

        <div
          v-if="assignedRequests.length === 0"
          class="bg-white rounded-lg shadow p-8 text-center text-gray-500"
        >
          No transport assignments yet.
        </div>

        <div v-else class="grid md:grid-cols-2 gap-4">

          <div
            v-for="request in assignedRequests"
            :key="request.id"
            class="bg-white rounded-lg shadow border p-5"
          >

            <div class="flex items-start justify-between gap-3 mb-4">

              <div>
                <p class="text-sm text-gray-500">
                  Order #{{ request.order_id }}
                </p>

                <h3 class="font-bold text-lg text-gray-800">
                  {{ request.buyer?.name || "N/A" }}
                </h3>
              </div>

              <span
                class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full"
              >
                {{ formatStatus(request.assignment?.status || request.status) }}
              </span>

            </div>

            <div class="space-y-3">

              <div>
                <p class="text-xs text-gray-400 uppercase">
                  Transporter
                </p>

                <p class="font-semibold text-gray-800">
                  {{ request.assignment?.transporter?.company_name || "N/A" }}
                </p>
              </div>

              <div>
                <p class="text-xs text-gray-400 uppercase">
                  Vehicle
                </p>

                <p class="font-semibold text-gray-800">
                  {{ request.assignment?.vehicle?.registration_number || "N/A" }}
                </p>
              </div>

              <div>
                <p class="text-xs text-gray-400 uppercase">
                  Route
                </p>

                <p class="text-gray-700">
                  {{ location(request.pickup_region, request.pickup_district) }}
                  →
                  {{ location(request.delivery_region, request.delivery_district) }}
                </p>
              </div>

            </div>

          </div>

        </div>

      </section>

    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../../services/api";

const requests = ref([]);
const transporters = ref([]);

const loading = ref(true);
const error = ref("");

const assigningId = ref(null);

const selections = ref({});
const assignmentErrors = ref({});

const pendingRequests = computed(() => {
  return requests.value.filter(
    request => request.status === "pending"
  );
});

const assignedRequests = computed(() => {
  return requests.value.filter(
    request =>
      request.assignment ||
      request.status === "assigned"
  );
});

function initializeSelection(request) {
  if (!selections.value[request.id]) {
    selections.value[request.id] = {
      transporter_id: "",
      vehicle_id: ""
    };
  }
}

function availableVehicles(requestId) {
  const transporterId =
    selections.value[requestId]?.transporter_id;

  if (!transporterId) {
    return [];
  }

  const transporter = transporters.value.find(
    item => String(item.id) === String(transporterId)
  );

  return transporter?.vehicles || [];
}

function onTransporterChange(requestId) {
  initializeSelection(
    requests.value.find(request => request.id === requestId)
  );

  selections.value[requestId].vehicle_id = "";

  assignmentErrors.value[requestId] = "";
}

function location(region, district) {
  return [region, district]
    .filter(Boolean)
    .join(", ") || "Not specified";
}

function formatStatus(status) {
  if (!status) {
    return "Unknown";
  }

  return status
    .replaceAll("_", " ")
    .replace(/\b\w/g, char => char.toUpperCase());
}

async function loadData() {
  loading.value = true;
  error.value = "";

  try {
    const [requestsResponse, transportersResponse] =
      await Promise.all([
        api.get("/admin/logistics-requests"),
        api.get("/admin/transporters")
      ]);

    requests.value = Array.isArray(requestsResponse.data)
      ? requestsResponse.data
      : [];

    transporters.value = Array.isArray(transportersResponse.data)
      ? transportersResponse.data
      : [];

    requests.value.forEach(initializeSelection);

  } catch (err) {
    console.error("Failed to load logistics data:", err);

    error.value =
      err.response?.data?.message ||
      "Failed to load logistics data.";
  } finally {
    loading.value = false;
  }
}

async function assignTransport(request) {
  initializeSelection(request);

  const selection = selections.value[request.id];

  assignmentErrors.value[request.id] = "";

  if (!selection.transporter_id) {
    assignmentErrors.value[request.id] =
      "Please select a transporter.";

    return;
  }

  if (!selection.vehicle_id) {
    assignmentErrors.value[request.id] =
      "Please select a vehicle.";

    return;
  }

  assigningId.value = request.id;

  try {
    await api.post("/dispatch/assign", {
      logistics_request_id: request.id,
      transporter_id: Number(selection.transporter_id),
      vehicle_id: Number(selection.vehicle_id)
    });

    await loadData();

  } catch (err) {
    console.error("Failed to assign transport:", err);

    assignmentErrors.value[request.id] =
      err.response?.data?.message ||
      "Failed to assign transport.";
  } finally {
    assigningId.value = null;
  }
}

onMounted(loadData);
</script>
