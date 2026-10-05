<template>

<div class="p-8">

<h1 class="text-3xl font-bold mb-6">

Incoming Jobs

</h1>

<div
v-for="job in jobs"
:key="job.id"
class="bg-white rounded shadow p-5 mb-4">

<h2>

{{ job.customer.name }}

</h2>

<p>

{{ job.quote.service.name }}

</p>

<p>

{{ job.status }}

</p>

<div class="space-x-2 mt-4">

<button
@click="update(job.id,'accept')"
class="bg-green-700 text-white px-4 py-2 rounded">

Accept

</button>

<button
@click="update(job.id,'reject')"
class="bg-red-700 text-white px-4 py-2 rounded">

Reject

</button>

</div>

</div>

</div>

</template>

<script setup>

import {ref,onMounted} from "vue";

import api from "../../services/api";

const jobs=ref([]);

async function load(){

const res=await api.get("/service-bookings/provider");

jobs.value=res.data.data;

}

async function update(id,action){

await api.put(`/service-bookings/${id}/${action}`);

load();

}

onMounted(load);

</script>