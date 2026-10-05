<template>
<div class="p-8">

    <h1 class="text-3xl font-bold mb-6">
        Orders
    </h1>

    <table class="w-full bg-white shadow rounded">

        <thead class="bg-green-700 text-white">

            <tr>
                <th class="p-3">Buyer</th>
                <th>Seller</th>
                <th>Commodity</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Status</th>
            </tr>

        </thead>

        <tbody>

            <tr
                v-for="order in orders"
                :key="order.id"
                class="border-b"
            >

                <td class="p-3">
                    {{ order.buyer?.name ?? "N/A" }}
                </td>

                <td>
                    {{ order.seller?.name ?? "N/A" }}
                </td>

                <td>
                    {{ order.listing?.commodity?.name ?? "N/A" }}
                </td>

                <td>
                    {{ order.quantity }}
                </td>

                <td>
                    TZS {{ Number(order.total_amount).toLocaleString() }}
                </td>

                <td>
                    {{ order.status }}
                </td>

            </tr>

        </tbody>

    </table>

</div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../services/api";

const orders = ref([]);

async function load() {

    const res = await api.get("/admin/orders");

    orders.value = res.data;

}

onMounted(load);
</script>