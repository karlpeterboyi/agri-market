<template>
<div class="bg-white rounded-xl shadow p-6">

    <div class="flex items-center gap-4">

        <div
            class="w-16 h-16 rounded-full bg-green-600 text-white flex items-center justify-center text-2xl font-bold"
        >
            {{ initials }}
        </div>

        <div>

            <h2 class="text-xl font-bold">
                {{ owner?.name }}
            </h2>

            <p class="text-gray-500">
                Machinery Provider
            </p>

        </div>

    </div>

    <div class="mt-6 space-y-2">

        <p>

            📧 {{ owner?.email }}

        </p>

        <p>

            📞 {{ owner?.phone || "Not Available" }}

        </p>

    </div>

    <div class="grid grid-cols-2 gap-3 mt-6">

        <a
            :href="`tel:${owner?.phone}`"
            class="text-center border rounded-lg py-2"
        >
            Call
        </a>

        <a
            :href="`https://wa.me/${owner?.phone}`"
            target="_blank"
            class="text-center bg-green-600 text-white rounded-lg py-2"
        >
            WhatsApp
        </a>

    </div>

</div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    owner: Object
});

const initials = computed(() => {

    if (!props.owner?.name) return "P";

    return props.owner.name
        .split(" ")
        .map(n => n[0])
        .join("")
        .substring(0,2)
        .toUpperCase();

});
</script>