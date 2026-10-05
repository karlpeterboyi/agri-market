<template>

<div class="p-6">

<h1 class="text-3xl font-bold mb-6">

Withdrawal Approvals

</h1>

<table class="w-full bg-white shadow rounded">

<thead class="bg-gray-800 text-white">

<tr>

<th class="p-3">Farmer</th>

<th>Phone</th>

<th>Amount</th>

<th>Status</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<tr
v-for="withdrawal in withdrawals"
:key="withdrawal.id"
class="border-b">

<td class="p-3">

{{ withdrawal.user.name }}

</td>

<td>

{{ withdrawal.phone }}

</td>

<td>

TZS {{ withdrawal.amount }}

</td>

<td>

{{ withdrawal.status }}

</td>

<td>

<div
v-if="withdrawal.status==='pending'"
class="flex gap-2">

<button

@click="approve(withdrawal.id)"

class="bg-green-600 text-white px-3 py-1 rounded">

Approve

</button>

<button

@click="reject(withdrawal.id)"

class="bg-red-600 text-white px-3 py-1 rounded">

Reject

</button>

</div>

</td>

</tr>

</tbody>

</table>

</div>

</template>

<script setup>

import {ref,onMounted} from "vue";

import api from "../../services/api";

const withdrawals=ref([]);

async function loadWithdrawals(){

const res=await api.get("/admin/withdrawals");

withdrawals.value=res.data;

}

async function approve(id){

await api.post(`/admin/withdrawals/${id}/approve`);

loadWithdrawals();

}

async function reject(id){

await api.post(`/admin/withdrawals/${id}/reject`);

loadWithdrawals();

}

onMounted(loadWithdrawals);

</script>