<template>
  <div class="bg-amber-50 border border-amber-200 rounded-lg p-5">
    <h2 class="text-xl font-bold mb-2">Machinery listings</h2>
    <p class="text-sm text-amber-900">
      Service providers cannot publish machinery. That marketplace is for
      <strong>farmers / machinery owners</strong> (and admins).
    </p>
    <p class="text-sm text-gray-600 mt-3">
      Use <router-link class="text-green-700 underline" to="/provider/services">My Services</router-link>
      for vet, ploughing-as-a-service, training, etc.
    </p>
    <router-link to="/machinery" class="inline-block mt-4 text-green-700 font-medium">Browse public machinery →</router-link>
  </div>
</template>
