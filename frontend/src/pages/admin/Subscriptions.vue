<template>
  <div class="p-4 max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-1">Subscriptions</h1>
    <p class="text-sm text-gray-500 mb-6">Manage plans and assign / update user subscriptions (farmers are exempt).</p>

    <div class="flex gap-2 mb-6 text-sm">
      <button type="button" class="px-3 py-1.5 rounded" :class="tab === 'users' ? 'bg-green-600 text-white' : 'bg-gray-100'" @click="tab = 'users'">User subscriptions</button>
      <button type="button" class="px-3 py-1.5 rounded" :class="tab === 'plans' ? 'bg-green-600 text-white' : 'bg-gray-100'" @click="tab = 'plans'">Plans</button>
    </div>

    <!-- USER SUBS -->
    <section v-if="tab === 'users'">
      <form class="bg-white rounded-lg shadow p-4 mb-6 grid sm:grid-cols-3 gap-3" @submit.prevent="assignSub">
        <h3 class="sm:col-span-3 font-semibold">Assign subscription</h3>
        <input v-model.number="assign.user_id" type="number" required placeholder="User ID *" class="border rounded px-3 py-2" />
        <select v-model="assign.status" class="border rounded px-3 py-2">
          <option value="active">active</option>
          <option value="pending">pending</option>
          <option value="cancelled">cancelled</option>
          <option value="expired">expired</option>
        </select>
        <input v-model.number="assign.months" type="number" min="1" placeholder="Months (default 1)" class="border rounded px-3 py-2" />
        <input v-model="assign.plan_code" placeholder="Plan code (optional)" class="border rounded px-3 py-2" />
        <input v-model="assign.notes" placeholder="Notes" class="border rounded px-3 py-2 sm:col-span-2" />
        <button type="submit" class="bg-green-600 text-white rounded px-4 py-2 font-semibold" :disabled="saving">
          {{ saving ? "Saving…" : "Assign" }}
        </button>
        <p v-if="msg" class="sm:col-span-3 text-sm" :class="msgErr ? 'text-red-600' : 'text-green-700'">{{ msg }}</p>
      </form>

      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-left">
            <tr>
              <th class="p-3">ID</th>
              <th class="p-3">User</th>
              <th class="p-3">Role</th>
              <th class="p-3">Status</th>
              <th class="p-3">Starts</th>
              <th class="p-3">Expires</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in subs" :key="s.id" class="border-t">
              <td class="p-3">{{ s.id }}</td>
              <td class="p-3">{{ s.user?.name || s.user_id }}<br><span class="text-xs text-gray-400">{{ s.user?.email }}</span></td>
              <td class="p-3">{{ s.user?.role }}</td>
              <td class="p-3">
                <select :value="s.status" class="border rounded text-xs px-1 py-1" @change="updateSub(s, { status: $event.target.value })">
                  <option value="active">active</option>
                  <option value="pending">pending</option>
                  <option value="cancelled">cancelled</option>
                  <option value="expired">expired</option>
                </select>
              </td>
              <td class="p-3">{{ (s.starts_at || "").toString().slice(0, 10) }}</td>
              <td class="p-3">
                <input
                  type="date"
                  class="border rounded text-xs px-1 py-1"
                  :value="(s.expires_at || '').toString().slice(0, 10)"
                  @change="updateSub(s, { expires_at: $event.target.value })"
                />
              </td>
              <td class="p-3">
                <button type="button" class="text-red-600 text-xs" @click="removeSub(s)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="!subs.length" class="p-4 text-gray-400 text-sm">No subscriptions yet.</p>
      </div>
    </section>

    <!-- PLANS -->
    <section v-else>
      <form class="bg-white rounded-lg shadow p-4 mb-6 grid sm:grid-cols-2 gap-3" @submit.prevent="createPlan">
        <h3 class="sm:col-span-2 font-semibold">Create plan</h3>
        <input v-model="planForm.name" required placeholder="Name *" class="border rounded px-3 py-2" />
        <input v-model="planForm.slug" placeholder="Slug (auto)" class="border rounded px-3 py-2" />
        <input v-model.number="planForm.price_tzs" type="number" placeholder="Price TZS" class="border rounded px-3 py-2" />
        <input v-model="planForm.description" placeholder="Description" class="border rounded px-3 py-2" />
        <label class="flex items-center gap-2 text-sm"><input v-model="planForm.active" type="checkbox" /> Active</label>
        <button type="submit" class="bg-green-600 text-white rounded px-4 py-2 font-semibold">Create plan</button>
      </form>

      <div class="grid sm:grid-cols-2 gap-4">
        <div v-for="p in plans" :key="p.id" class="bg-white rounded-lg shadow p-4">
          <div class="flex justify-between gap-2">
            <div>
              <p class="font-bold">{{ p.name }}</p>
              <p class="text-xs text-gray-400">{{ p.slug }} · {{ p.active ? "active" : "inactive" }}</p>
              <p class="text-sm text-gray-600 mt-1">{{ p.description }}</p>
            </div>
            <div class="flex flex-col gap-1">
              <button type="button" class="text-xs border rounded px-2 py-1" @click="togglePlan(p)">
                {{ p.active ? "Deactivate" : "Activate" }}
              </button>
              <button type="button" class="text-xs text-red-600" @click="deletePlan(p)">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const tab = ref("users");
const subs = ref([]);
const plans = ref([]);
const saving = ref(false);
const msg = ref("");
const msgErr = ref(false);
const assign = ref({ user_id: null, status: "active", months: 1, plan_code: "", notes: "" });
const planForm = ref({ name: "", slug: "", description: "", price_tzs: null, active: true });

async function loadSubs() {
  const { data } = await api.get("/admin/user-subscriptions");
  const page = data.data;
  subs.value = page?.data || page || [];
  if (!Array.isArray(subs.value)) subs.value = [];
}

async function loadPlans() {
  const { data } = await api.get("/admin/subscription-plans");
  plans.value = data.data || [];
}

async function assignSub() {
  saving.value = true;
  msg.value = "";
  try {
    await api.post("/admin/user-subscriptions", assign.value);
    msgErr.value = false;
    msg.value = "Assigned";
    await loadSubs();
  } catch (e) {
    msgErr.value = true;
    msg.value = e.response?.data?.message || "Failed";
  } finally {
    saving.value = false;
  }
}

async function updateSub(s, patch) {
  try {
    await api.put(`/admin/user-subscriptions/${s.id}`, patch);
    await loadSubs();
  } catch (e) {
    alert(e.response?.data?.message || "Update failed");
  }
}

async function removeSub(s) {
  if (!confirm("Delete subscription #" + s.id + "?")) return;
  await api.delete(`/admin/user-subscriptions/${s.id}`);
  await loadSubs();
}

async function createPlan() {
  try {
    await api.post("/admin/subscription-plans", planForm.value);
    planForm.value = { name: "", slug: "", description: "", price_tzs: null, active: true };
    await loadPlans();
  } catch (e) {
    alert(e.response?.data?.message || "Create failed");
  }
}

async function togglePlan(p) {
  await api.put(`/admin/subscription-plans/${p.id}`, { active: !p.active });
  await loadPlans();
}

async function deletePlan(p) {
  if (!confirm("Delete plan " + p.name + "?")) return;
  await api.delete(`/admin/subscription-plans/${p.id}`);
  await loadPlans();
}

onMounted(async () => {
  try {
    await Promise.all([loadSubs(), loadPlans()]);
  } catch (e) {
    msgErr.value = true;
    msg.value = e.response?.data?.message || "Load failed (admin only)";
  }
});
</script>
