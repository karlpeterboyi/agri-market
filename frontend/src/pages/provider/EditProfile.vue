<template>
<div class="max-w-5xl mx-auto p-8">

<h1 class="text-3xl font-bold mb-8">

Edit Provider Profile

</h1>

<form
v-if="form"
@submit.prevent="save"
class="bg-white rounded-xl shadow p-8 space-y-5"
>

<input
v-model="form.business_name"
placeholder="Business Name"
class="w-full border rounded p-3"
/>

<textarea
v-model="form.about"
placeholder="About your business"
class="w-full border rounded p-3"
/>

<input
v-model="form.phone"
placeholder="Phone"
class="w-full border rounded p-3"
/>

<input
v-model="form.whatsapp"
placeholder="WhatsApp"
class="w-full border rounded p-3"
/>

<input
v-model="form.email"
placeholder="Email"
class="w-full border rounded p-3"
/>

<input
v-model="form.website"
placeholder="Website"
class="w-full border rounded p-3"
/>

<input
v-model="form.years_experience"
type="number"
placeholder="Years of Experience"
class="w-full border rounded p-3"
/>

<button
class="bg-green-700 text-white px-6 py-3 rounded-lg"
>

Save Changes

</button>

</form>

</div>
</template>

<script setup>

import {reactive,onMounted} from "vue";
import api from "../../services/api";

const form=reactive({});

onMounted(async()=>{

Object.assign(
form,
(await api.get("/provider/profile")).data
);

});

async function save(){

await api.put("/provider/profile",form);

alert("Profile updated successfully.");

}

</script>