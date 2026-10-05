<template>

<div class="bg-white rounded-xl shadow p-6">

<h2 class="text-2xl font-bold mb-4">

Location

</h2>

<LMap
    style="height:450px"
    :zoom="13"
    :center="[latitude,longitude]"
>

<LTileLayer
url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
/>

<LMarker
:lat-lng="[latitude,longitude]"
/>

</LMap>

</div>

</template>

<script setup>

import {

LMap,

LTileLayer,

LMarker

}

from "@vue-leaflet/vue-leaflet";

defineProps({

latitude:Number,

longitude:Number

});

</script>