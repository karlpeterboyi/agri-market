<template>

<div>

    <!-- Main Image -->

    <div
        class="relative rounded-xl overflow-hidden shadow-lg"
    >

        <img
            :src="currentImage"
            class="w-full h-[520px] object-cover cursor-pointer"
            @click="openLightbox=true"
        >

        <button
            v-if="images.length>1"
            @click="previous"
            class="absolute left-3 top-1/2 -translate-y-1/2 bg-white rounded-full w-10 h-10 shadow"
        >
            ❮
        </button>

        <button
            v-if="images.length>1"
            @click="next"
            class="absolute right-3 top-1/2 -translate-y-1/2 bg-white rounded-full w-10 h-10 shadow"
        >
            ❯
        </button>

    </div>

    <!-- Thumbnails -->

    <div
        class="grid grid-cols-4 gap-3 mt-4"
    >

        <img
            v-for="(image,index) in images"
            :key="index"
            :src="image"
            @click="select(index)"
            class="h-24 rounded-lg object-cover cursor-pointer border-2"
            :class="index===current?'border-green-600':'border-transparent'"
        >

    </div>

    <!-- Lightbox -->

    <div
        v-if="openLightbox"
        class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center"
    >

        <button
            class="absolute top-6 right-8 text-white text-4xl"
            @click="openLightbox=false"
        >
            ✕
        </button>

        <button
            @click="previous"
            class="absolute left-8 text-white text-5xl"
        >
            ❮
        </button>

        <img
            :src="currentImage"
            class="max-h-[90vh] max-w-[90vw] object-contain"
        >

        <button
            @click="next"
            class="absolute right-8 text-white text-5xl"
        >
            ❯
        </button>

    </div>

</div>

</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    images:{
        type:Array,
        default:()=>[]
    }
});

const current=ref(0);

const openLightbox=ref(false);

const currentImage=computed(()=>{
    return props.images[current.value];
});

function select(index){
    current.value=index;
}

function previous(){

    current.value--;

    if(current.value<0){

        current.value=props.images.length-1;

    }

}

function next(){

    current.value++;

    if(current.value>=props.images.length){

        current.value=0;

    }

}
</script>