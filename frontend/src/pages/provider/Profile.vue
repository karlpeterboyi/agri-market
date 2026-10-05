<template>
<div v-if="profile" class="bg-gray-100 min-h-screen">

    <!-- Cover -->

    <div class="relative">

        <img
            :src="profile.cover_photo || 'https://placehold.co/1600x450'"
            class="w-full h-80 object-cover"
        >

        <div class="absolute -bottom-16 left-10">

            <img
                :src="profile.logo || 'https://placehold.co/160'"
                class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover"
            >

        </div>

    </div>

    <div class="max-w-7xl mx-auto pt-24 px-6">

        <div class="flex justify-between items-start flex-wrap gap-6">

            <div>

                <div class="flex items-center gap-3">

                    <h1 class="text-4xl font-bold">

                        {{ profile.business_name }}

                    </h1>

                    <span
                        v-if="profile.verified"
                        class="bg-green-600 text-white px-3 py-1 rounded-full text-sm"
                    >
                        ✔ Verified
                    </span>

                </div>

                <p class="text-gray-500 mt-2">

                    {{ profile.about }}

                </p>

            </div>

            <router-link
                to="/provider/profile/edit"
                class="bg-green-700 text-white px-5 py-3 rounded-lg"
            >
                Edit Profile
            </router-link>

        </div>

        <!-- Stats -->

        <div class="grid md:grid-cols-4 gap-5 mt-10">

            <div class="bg-white rounded-lg shadow p-5">

                <div class="text-gray-500">

                    Rating

                </div>

                <div class="text-3xl font-bold text-yellow-500">

                    ⭐ {{ profile.rating }}

                </div>

            </div>

            <div class="bg-white rounded-lg shadow p-5">

                <div class="text-gray-500">

                    Reviews

                </div>

                <div class="text-3xl font-bold">

                    {{ profile.reviews }}

                </div>

            </div>

            <div class="bg-white rounded-lg shadow p-5">

                <div class="text-gray-500">

                    Completed Jobs

                </div>

                <div class="text-3xl font-bold">

                    {{ profile.completed_jobs }}

                </div>

            </div>

            <div class="bg-white rounded-lg shadow p-5">

                <div class="text-gray-500">

                    Experience

                </div>

                <div class="text-3xl font-bold">

                    {{ profile.years_experience }} yrs

                </div>

            </div>

        </div>

        <!-- Two Columns -->

        <div class="grid lg:grid-cols-2 gap-8 mt-10">

            <div class="bg-white rounded-xl shadow p-6">

                <h2 class="text-2xl font-bold mb-4">

                    Contact Information

                </h2>

                <div class="space-y-3">

                    <p><strong>Phone:</strong> {{ profile.phone }}</p>

                    <p><strong>WhatsApp:</strong> {{ profile.whatsapp }}</p>

                    <p><strong>Email:</strong> {{ profile.email }}</p>

                    <p><strong>Website:</strong> {{ profile.website }}</p>

                    <p><strong>TIN:</strong> {{ profile.tin }}</p>

                    <p><strong>VRN:</strong> {{ profile.vrn }}</p>

                </div>

            </div>

            <div class="bg-white rounded-xl shadow p-6">

                <h2 class="text-2xl font-bold mb-4">

                    Service Regions

                </h2>

                <div class="flex flex-wrap gap-3">

                    <span
                        v-for="region in profile.service_regions"
                        :key="region"
                        class="bg-green-100 text-green-700 px-3 py-2 rounded-full"
                    >
                        {{ region }}
                    </span>

                </div>

            </div>

        </div>

        <!-- Certifications -->

        <div class="bg-white rounded-xl shadow p-6 mt-8">

            <h2 class="text-2xl font-bold mb-4">

                Certifications

            </h2>

            <ul class="space-y-3">

                <li
                    v-for="item in profile.certifications"
                    :key="item"
                >

                    ✅ {{ item }}

                </li>

            </ul>

        </div>

        <!-- Business Hours -->

        <div class="bg-white rounded-xl shadow p-6 mt-8 mb-10">

            <h2 class="text-2xl font-bold mb-4">

                Business Hours

            </h2>

            <div
                v-for="(time,day) in profile.business_hours"
                :key="day"
                class="flex justify-between border-b py-2"
            >

                <span>{{ day }}</span>

                <span>{{ time }}</span>

            </div>

        </div>

    </div>

</div>
</template>

<script setup>

import {ref,onMounted} from "vue";
import api from "../../services/api";

const profile=ref(null);

onMounted(async()=>{

const res=await api.get("/provider/profile");

profile.value=res.data;

});

</script>