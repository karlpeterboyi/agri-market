<template>
<div class="bg-white rounded-xl shadow p-6">

<h2 class="text-2xl font-bold mb-5">
Book This Service
</h2>

<form @submit.prevent="submit">

<div class="space-y-4">

<div>

<label class="font-medium">
Booking Date
</label>

<input
type="date"
v-model="form.booking_date"
class="w-full border rounded p-2"
/>

</div>

<div>

<label>
Booking Time
</label>

<input
type="time"
v-model="form.booking_time"
class="w-full border rounded p-2"
/>

</div>

<div>

<label>
Quantity
</label>

<input
type="number"
v-model="form.quantity"
class="w-full border rounded p-2"
/>

</div>

<div>

<label>
Unit
</label>

<input
v-model="form.unit"
class="w-full border rounded p-2"
placeholder="Acres, Animals, Tons..."
/>

</div>

<div>

<label>
Region
</label>

<input
v-model="form.region"
class="w-full border rounded p-2"
/>

</div>

<div>

<label>
District
</label>

<input
v-model="form.district"
class="w-full border rounded p-2"
/>

</div>

<div>

<label>
Farm Location
</label>

<textarea
v-model="form.farm_location"
class="w-full border rounded p-2"
/>

</div>

<div>

<label>
Notes
</label>

<textarea
v-model="form.notes"
class="w-full border rounded p-2"
/>

</div>

<button
class="bg-green-700 text-white px-5 py-3 rounded w-full"
>

Book Service

</button>

</div>

</form>

</div>
</template>

<script setup>

import { reactive } from "vue";

import api from "../../services/api";

const props = defineProps({

quoteId:Number

});

const form=reactive({

service_quote_id:props.quoteId,

booking_date:"",

booking_time:"",

quantity:"",

unit:"",

region:"",

district:"",

farm_location:"",

notes:""

});

async function submit(){

await api.post("/service-bookings",form);

alert("Booking submitted successfully.");

}

</script>