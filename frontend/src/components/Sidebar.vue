<template>
  <aside class="w-64 bg-white shadow min-h-screen">
    <div class="p-4">
      <!-- Buyer Sidebar -->
      <template v-if="auth.role === 'buyer'">
        <router-link class="block py-2" to="/buyer">
          Dashboard
        </router-link>
        <router-link class="block py-2" to="/orders">
          Orders
        </router-link>
        <router-link class="block py-2" to="/wallet">
          Wallet
        </router-link>
      </template>

      <!-- Farmer Sidebar (Updated) -->
      <template v-else-if="auth.role === 'farmer'">
        <router-link class="block py-2" to="/farmer">
          Dashboard
        </router-link>
        
        <router-link class="block py-2" to="/farmer/listings">
          My Listings
        </router-link>
        
        <router-link class="block py-2" to="/farmer/orders">
          Orders
        </router-link>
        
        <router-link class="block py-2" to="/farmer/logistics">
          Logistics
        </router-link>
        
        <router-link class="block py-2" to="/farmer/wallet">
          Wallet
        </router-link>
        
        <router-link class="block py-2" to="/farmer/withdrawals">
          Withdrawals
        </router-link>
        
        <router-link class="block py-2" to="/farmer/profile">
          Profile
        </router-link>
      </template>

      <!-- Admin Sidebar -->
      <template v-else-if="auth.role === 'admin'">
        <router-link class="block py-2" to="/admin">
          Dashboard
        </router-link>
        <router-link class="block py-2" to="/admin/users">
          Users
        </router-link>
        <router-link class="block py-2" to="/admin/withdrawals">
          Withdrawals
        </router-link>
        <RouterLink
  to="/admin/withdrawals"
  class="block px-4 py-2 hover:bg-gray-200"
>
  Manage Withdrawals
</RouterLink>
      </template>
    </div>
  </aside>
</template>

<script setup>
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
</script>