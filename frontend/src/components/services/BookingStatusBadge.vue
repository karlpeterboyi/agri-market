<template>

<span
class="px-3 py-1 rounded-full text-sm font-semibold"
:class="classes">

{{ status }}

</span>

</template>

<script setup>

import {computed} from "vue";

const props=defineProps({

status:String

});

const classes=computed(()=>{

switch(props.status){

case "pending":

return "bg-yellow-100 text-yellow-700";

case "accepted":

return "bg-green-100 text-green-700";

case "on_the_way":

return "bg-blue-100 text-blue-700";

case "in_progress":

return "bg-purple-100 text-purple-700";

case "completed":

return "bg-green-700 text-white";

case "cancelled":

return "bg-red-100 text-red-700";

default:

return "bg-gray-100";

}

});

</script>