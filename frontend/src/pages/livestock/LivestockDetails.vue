<template>
<div v-if="listing" class="max-w-7xl mx-auto p-6">

<div class="grid lg:grid-cols-3 gap-8">

<!-- LEFT -->
<div class="lg:col-span-2">

<img
:src="selectedImage"
class="w-full h-[500px] object-cover rounded-xl shadow"
/>

<div class="flex gap-3 mt-4 overflow-x-auto">

<img
v-for="img in listing.images"
:key="img.id"
:src="img.image"
@click="selectedImage=img.image"
class="w-24 h-24 object-cover rounded cursor-pointer border hover:border-green-600"
/>

</div>

<div class="mt-8">

<h1 class="text-4xl font-bold">

{{ listing.title }}

</h1>

<div class="text-3xl font-bold text-green-700 mt-3">

TZS {{ Number(listing.price).toLocaleString() }}

</div>

<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8">

<div class="bg-gray-50 p-4 rounded">

<b>Species</b>

<p>{{ listing.category.name }}</p>

</div>

<div class="bg-gray-50 p-4 rounded">

<b>Breed</b>

<p>{{ listing.breed.name }}</p>

</div>

<div class="bg-gray-50 p-4 rounded">

<b>Age</b>

<p>{{ listing.age }} Months</p>

</div>

<div class="bg-gray-50 p-4 rounded">

<b>Gender</b>

<p>{{ listing.gender }}</p>

</div>

<div class="bg-gray-50 p-4 rounded">

<b>Weight</b>

<p>{{ listing.weight }} Kg</p>

</div>

<div class="bg-gray-50 p-4 rounded">

<b>Location</b>

<p>{{ listing.region }}</p>

</div>

</div>

<div class="mt-10">

<h2 class="text-2xl font-bold mb-4">

Description

</h2>

<p class="leading-8">

{{ listing.description }}

</p>

</div>

</div>

</div>

<!-- RIGHT -->

<div>

<div class="bg-white rounded-xl shadow p-6 sticky top-5">

<h2 class="text-2xl font-bold mb-6">

Seller

</h2>

<div class="flex items-center gap-4">

<div class="w-16 h-16 rounded-full bg-green-600 text-white flex items-center justify-center text-2xl">

{{ listing.seller.name.charAt(0) }}

</div>

<div>

<div class="font-bold">

{{ listing.seller.name }}

</div>

<div class="text-gray-500">

{{ listing.region }}

</div>

</div>

</div>

<button
class="w-full mt-8 bg-green-700 text-white rounded-lg py-3">

📞 Call Seller

</button>

<button
class="w-full mt-4 bg-green-500 text-white rounded-lg py-3">

💬 WhatsApp

</button>

<button
class="w-full mt-4 bg-orange-500 text-white rounded-lg py-3"
@click="offerDialog=true">

Make Offer

</button>

<button
class="w-full mt-4 border rounded-lg py-3">

❤️ Save Listing

</button>

</div>

</div>

</div>

<!-- Related -->

<div class="mt-16">

<h2 class="text-3xl font-bold mb-6">

Related Livestock

</h2>

<div class="grid md:grid-cols-4 gap-6">

<div
v-for="item in related"
:key="item.id"
class="bg-white rounded shadow hover:shadow-lg">

<img
:src="item.images[0]?.image"
class="h-48 w-full object-cover"
/>

<div class="p-4">

<div class="font-bold">

{{ item.title }}

</div>

<div class="text-green-700 font-bold mt-2">

TZS {{ Number(item.price).toLocaleString() }}

</div>

</div>

</div>

</div>

</div>

</div>
</template>

<script setup>
import {ref,onMounted} from "vue";
import {useRoute} from "vue-router";
import api from "../../services/api";

const route=useRoute();

const listing=ref(null);

const related=ref([]);

const selectedImage=ref("");

const offerDialog=ref(false);

async function load(){

const res=await api.get("/livestock/"+route.params.id);

listing.value=res.data.listing;

related.value=res.data.related;

selectedImage.value=
listing.value.images.length
?listing.value.images[0].image
:"";

}

onMounted(load);
</script>