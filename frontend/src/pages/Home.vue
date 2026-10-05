<template>
  <div class="relative h-[100dvh] max-h-[100dvh] flex flex-col bg-gray-900 text-white overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 z-0">
      <img
        src="https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1920&q=80"
        alt="Agricultural Field"
        class="w-full h-full object-cover object-center opacity-40"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-900/70 to-black/50"></div>
    </div>

    <!-- Lang switcher -->
    <div class="absolute top-3 right-3 z-20">
      <select
        :value="locale"
        @change="setLocale($event.target.value)"
        class="bg-black/40 text-white text-xs sm:text-sm rounded px-2 py-1 border border-white/20 backdrop-blur"
      >
        <option v-for="l in available" :key="l.code" :value="l.code">{{ l.label }}</option>
      </select>
    </div>

    <!-- Center content — fills viewport, no page scroll -->
    <main class="relative z-10 flex-1 flex flex-col justify-center items-center px-5 py-3 sm:py-6 text-center min-h-0">
      <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/25 text-emerald-400 text-[11px] sm:text-xs font-medium mb-3 sm:mb-4 backdrop-blur-md">
        <span>🌱 {{ t('home.badge') }}</span>
      </div>

      <AppLogo
        :show-text="false"
        img-class="h-14 w-14 sm:h-16 sm:w-16 rounded-full shadow-lg mb-3 sm:mb-4"
        link-class="pointer-events-none"
      />

      <!-- Title on one visual line / balanced size -->
      <h1 class="text-[1.35rem] xs:text-[1.5rem] leading-snug sm:text-4xl md:text-5xl font-extrabold tracking-tight text-white mb-2 sm:mb-3 px-1 max-w-full">
        <span class="inline-block whitespace-nowrap">
          {{ t('home.title') }}
          <span class="bg-gradient-to-r from-emerald-400 to-green-500 bg-clip-text text-transparent">Agri-market</span>
        </span>
      </h1>

      <p class="text-sm sm:text-base text-gray-300 max-w-md mx-auto mb-4 sm:mb-6 font-light leading-snug px-2">
        {{ t('home.subtitle') }}
      </p>

      <div class="w-full max-w-sm flex flex-col gap-2.5 sm:gap-3">
        <router-link
          to="/marketplace"
          class="w-full px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold shadow-lg transition text-center text-sm sm:text-base"
        >
          {{ t('home.explore') }}
        </router-link>
        <router-link
          to="/login"
          class="w-full px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold border border-white/20 backdrop-blur transition text-center text-sm sm:text-base"
        >
          {{ t('home.login') }}
        </router-link>
        <router-link
          to="/register"
          class="w-full px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold border border-white/20 backdrop-blur transition text-center text-sm sm:text-base"
        >
          {{ t('home.register') }}
        </router-link>
      </div>
    </main>

    <!-- Bottom strip — pinned, compact, no scroll -->
    <footer class="relative z-10 shrink-0 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-2">
      <div class="max-w-lg mx-auto border-t border-white/10 pt-3">
        <div class="grid grid-cols-4 gap-1 sm:gap-3">
          <div v-for="(f, i) in features" :key="i" class="text-center min-w-0">
            <p class="text-base sm:text-xl leading-none mb-0.5">{{ f.icon }}</p>
            <p class="text-[10px] sm:text-xs text-gray-300 leading-tight truncate">{{ f.label }}</p>
          </div>
        </div>
        <div class="mt-2 flex flex-wrap justify-center gap-x-3 gap-y-0.5 text-[11px] sm:text-xs">
          <router-link to="/knowledge" class="text-emerald-400/90 hover:underline">{{ t('home.knowledge') }}</router-link>
          <router-link to="/services" class="text-emerald-400/90 hover:underline">{{ t('home.services') }}</router-link>
          <router-link to="/machinery" class="text-emerald-400/90 hover:underline">{{ t('home.machinery') }}</router-link>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import AppLogo from "../components/AppLogo.vue";
import { computed } from "vue";
import { useI18n } from "../i18n";

const { t, locale, setLocale, available } = useI18n();

const features = computed(() => [
  { icon: "💰", label: t("home.feature1") },
  { icon: "✅", label: t("home.feature2") },
  { icon: "🔒", label: t("home.feature3") },
  { icon: "🚜", label: t("home.feature4") },
]);
</script>
