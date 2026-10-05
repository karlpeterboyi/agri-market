<template>

<div class="p-8">

    <h1 class="text-3xl font-bold mb-6">
        Payments
    </h1>

    <table class="w-full bg-white shadow rounded">

        <thead class="bg-green-700 text-white">

            <tr>

                <th class="p-3">Buyer</th>

                <th>Seller</th>

                <th>Amount</th>

                <th>Method</th>

                <th>Status</th>

                <th>Reference</th>

            </tr>

        </thead>

        <tbody>

            <tr
                v-for="payment in payments"
                :key="payment.id"
                class="border-b"
            >

                <td class="p-3">

                    {{ payment.order?.buyer?.name ?? "N/A" }}

                </td>

                <td>

                    {{ payment.order?.seller?.name ?? "N/A" }}

                </td>

                <td>

                    TZS {{ Number(payment.amount).toLocaleString() }}

                </td>

                <td>

                    {{ payment.method }}

                </td>

                <td>

                    {{ payment.status }}

                </td>

                <td>

                    {{ payment.transaction_ref }}

                </td>

            </tr>

        </tbody>

    </table>

</div>

</template>

<script setup>

import { ref,onMounted } from "vue";

import api from "../../services/api";

const payments = ref([]);

async function load(){

    const res = await api.get("/admin/payments");

    payments.value = res.data;

}

onMounted(load);

</script>