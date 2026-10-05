<template>

<div class="p-8">

<h1 class="text-3xl font-bold mb-6">

My Service Bookings

</h1>

<div
v-for="booking in bookings"
:key="booking.id"
class="bg-white rounded shadow p-5 mb-4">

<h2 class="font-bold">

{{ booking.quote.service.name }}

</h2>

<p>

Provider:

{{ booking.provider.name }}

</p>

<p>

Status:

{{ booking.status }}

</p>

<p>

Booking Ref:

{{ booking.booking_reference }}

</p>

<router-link
:to="'/service-bookings/'+booking.id"
class="text-green-700">

View Details

</router-link>

</div>

</div>

</template>

<script setup>

import {ref,onMounted} from "vue";

import api from "../../services/api";

const bookings=ref([]);

onMounted(async()=>{

const res=await api.get("/service-bookings");

bookings.value=res.data.data;

});

</script>