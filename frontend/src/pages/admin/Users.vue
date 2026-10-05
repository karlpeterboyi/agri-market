<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
      <h1 class="text-2xl sm:text-3xl font-bold">Users</h1>
      <button @click="openCreate" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">+ Add user</button>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
      <input v-model="search" @input="load" placeholder="Search name, email, phone…" class="border rounded px-3 py-2 text-sm flex-1 min-w-[180px]" />
      <select v-model="roleFilter" @change="load" class="border rounded px-3 py-2 text-sm">
        <option value="">All roles</option>
        <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
      </select>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm min-w-[700px]">
        <thead class="bg-green-700 text-white text-left">
          <tr>
            <th class="p-3">Name</th>
            <th class="p-3">Email</th>
            <th class="p-3">Phone</th>
            <th class="p-3">Role</th>
            <th class="p-3">Status</th>
            <th class="p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id" class="border-t">
            <td class="p-3 font-medium">{{ u.name }}</td>
            <td class="p-3">{{ u.email }}</td>
            <td class="p-3">{{ u.phone }}</td>
            <td class="p-3"><span class="text-xs bg-gray-100 px-2 py-0.5 rounded">{{ u.role }}</span></td>
            <td class="p-3">{{ u.status }}</td>
            <td class="p-3 space-x-2 whitespace-nowrap">
              <button @click="openEdit(u)" class="text-green-700 text-xs font-medium">Edit</button>
              <button @click="remove(u)" class="text-red-600 text-xs font-medium">Delete</button>
            </td>
          </tr>
          <tr v-if="!users.length"><td colspan="6" class="p-4 text-gray-400">No users found.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="modal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
      <form @submit.prevent="save" class="bg-white rounded-xl p-6 w-full max-w-md space-y-3 shadow-xl">
        <h3 class="font-bold text-lg">{{ editing ? 'Edit user' : 'New user' }}</h3>
        <input v-model="form.name" required placeholder="Name" class="w-full border rounded px-3 py-2" />
        <input v-model="form.email" type="email" required placeholder="Email" class="w-full border rounded px-3 py-2" />
        <input v-model="form.phone" required placeholder="Phone" class="w-full border rounded px-3 py-2" />
        <select v-model="form.role" required class="w-full border rounded px-3 py-2">
          <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
        </select>
        <select v-model="form.status" class="w-full border rounded px-3 py-2">
          <option value="active">active</option>
          <option value="inactive">inactive</option>
          <option value="suspended">suspended</option>
        </select>
        <input v-model="form.password" :required="!editing" type="password" :placeholder="editing ? 'New password (optional)' : 'Password'" class="w-full border rounded px-3 py-2" />
        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="modal=false" class="px-4 py-2 border rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded font-semibold">Save</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const roles = ["farmer", "buyer", "processor", "provider", "agrodealer", "transporter", "admin"];
const users = ref([]);
const search = ref("");
const roleFilter = ref("");
const modal = ref(false);
const editing = ref(null);
const error = ref("");
const form = ref({});

async function load() {
  const { data } = await api.get("/admin/users", {
    params: { search: search.value || undefined, role: roleFilter.value || undefined },
  });
  users.value = data.data || data || [];
}

function openCreate() {
  editing.value = null;
  form.value = { name: "", email: "", phone: "", role: "farmer", status: "active", password: "" };
  error.value = "";
  modal.value = true;
}

function openEdit(u) {
  editing.value = u;
  form.value = { name: u.name, email: u.email, phone: u.phone, role: u.role, status: u.status || "active", password: "" };
  error.value = "";
  modal.value = true;
}

async function save() {
  error.value = "";
  try {
    const payload = { ...form.value };
    if (editing.value && !payload.password) delete payload.password;
    if (editing.value) {
      await api.put(`/admin/users/${editing.value.id}`, payload);
    } else {
      await api.post("/admin/users", payload);
    }
    modal.value = false;
    await load();
  } catch (e) {
    error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(" ") || "Failed";
  }
}

async function remove(u) {
  if (!confirm(`Delete ${u.name}?`)) return;
  try {
    await api.delete(`/admin/users/${u.id}`);
    await load();
  } catch (e) {
    alert(e.response?.data?.message || "Delete failed");
  }
}

onMounted(load);
</script>
