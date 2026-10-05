<template>
  <router-link
    :to="to"
    class="inline-flex items-center gap-2 shrink-0 no-underline"
    :class="linkClass"
  >
    <img
      :src="logoUrl"
      :alt="alt"
      class="object-contain"
      :class="imgClass"
      width="40"
      height="40"
    />
    <span v-if="showText" class="font-bold tracking-tight" :class="textClass">
      {{ text }}
    </span>
  </router-link>
</template>

<script setup>
import logoUrl from "../assets/logo.png";

defineProps({
  to: { type: String, default: "/" },
  alt: { type: String, default: "Agri-market" },
  text: { type: String, default: "Agri-market" },
  showText: { type: Boolean, default: true },
  /** Tailwind classes for the img */
  imgClass: { type: String, default: "h-9 w-9 sm:h-10 sm:w-10 rounded-full" },
  textClass: { type: String, default: "text-lg sm:text-xl text-inherit" },
  linkClass: { type: String, default: "" },
});
</script>
