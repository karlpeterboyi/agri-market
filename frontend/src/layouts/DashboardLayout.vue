<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Top bar -->
    <header class="bg-green-700 text-white shadow sticky top-0 z-30">
      <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 p-3 sm:p-4">
        <div class="flex items-center gap-3">
          <button type="button" class="lg:hidden p-2 rounded hover:bg-green-600" @click="sidebarOpen = !sidebarOpen" aria-label="Menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <AppLogo text="Agri-market" text-class="text-lg sm:text-xl text-white" img-class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-white/10 p-0.5" />
        </div>
        <div class="flex items-center gap-2 sm:gap-4">
          <!-- Language switcher -->
          <select
            :value="locale"
            @change="onLocale($event.target.value)"
            class="bg-green-800 text-white text-xs sm:text-sm rounded px-2 py-1 border border-green-600"
          >
            <option v-for="l in available" :key="l.code" :value="l.code">{{ l.label }}</option>
          </select>
          <span class="hidden sm:inline text-sm opacity-90 truncate max-w-[120px]">{{ auth.user?.name }}</span>
          <button @click="logout" class="bg-red-600 hover:bg-red-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded text-xs sm:text-sm font-medium">
            {{ t('common.logout') }}
          </button>
        </div>
      </div>
    </header>

    <div class="flex relative">
      <!-- Mobile overlay -->
      <div v-if="sidebarOpen" class="fixed inset-0 bg-black/40 z-20 lg:hidden" @click="sidebarOpen = false" />

      <!-- Sidebar -->
      <aside
        class="fixed lg:sticky top-[52px] sm:top-16 left-0 z-20 w-64 bg-white shadow min-h-[calc(100vh-52px)] sm:min-h-[calc(100vh-64px)] transform transition-transform duration-200 lg:translate-x-0 overflow-y-auto"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      >
        <nav class="flex flex-col p-3 sm:p-4 gap-0.5 text-sm pb-20">
          <template v-if="auth.user?.role === 'farmer'">
            <p class="nav-section">Marketplace</p>
            <router-link class="nav-link" to="/farmer" @click="closeSidebar">{{ t('nav.dashboard') }}</router-link>
            <router-link class="nav-link" to="/farmer/machinery" @click="closeSidebar">My Machinery</router-link>
            <router-link class="nav-link" to="/farmer/listings" @click="closeSidebar">{{ t('nav.listings') }}</router-link>
            <router-link class="nav-link" to="/farmer/orders" @click="closeSidebar">{{ t('nav.orders') }}</router-link>
            <router-link class="nav-link" to="/farmer/wallet" @click="closeSidebar">{{ t('nav.wallet') }}</router-link>
            <router-link class="nav-link" to="/farmer/withdrawals" @click="closeSidebar">{{ t('nav.withdrawals') }}</router-link>

            <p class="nav-section">Farm ERP</p>
            <router-link class="nav-link" to="/farmer/farms" @click="closeSidebar">{{ t('nav.farms') }}</router-link>
            <router-link class="nav-link" to="/farmer/activities" @click="closeSidebar">{{ t('nav.activities') }}</router-link>
            <router-link class="nav-link" to="/farmer/inventory" @click="closeSidebar">{{ t('nav.inventory') }}</router-link>
            <router-link class="nav-link" to="/farmer/analytics" @click="closeSidebar">{{ t('nav.analytics') }}</router-link>

            <p class="nav-section">Finance</p>
            <router-link class="nav-link" to="/farmer/loans" @click="closeSidebar">{{ t('nav.loans') }}</router-link>
            <router-link class="nav-link" to="/farmer/loans/products" @click="closeSidebar">{{ t('nav.loanProducts') }}</router-link>

            <p class="nav-section">Knowledge</p>
            <router-link class="nav-link" to="/knowledge" @click="closeSidebar">{{ t('nav.knowledge') }}</router-link>
            <router-link class="nav-link" to="/knowledge/courses" @click="closeSidebar">{{ t('nav.courses') }}</router-link>
            <router-link class="nav-link" to="/farmer/ai-advisor" @click="closeSidebar">{{ t('nav.aiAdvisor') }}</router-link>

            <p class="nav-section">Government</p>
            <router-link class="nav-link" to="/farmer/announcements" @click="closeSidebar">{{ t('nav.announcements') }}</router-link>
            <router-link class="nav-link" to="/farmer/subsidies" @click="closeSidebar">{{ t('nav.subsidies') }}</router-link>

            <p class="nav-section">Account</p>
            <router-link class="nav-link" to="/farmer/profile" @click="closeSidebar">{{ t('nav.profile') }}</router-link>
          </template>

          <template v-else-if="auth.user?.role === 'buyer'">
            <router-link class="nav-link" to="/buyer" @click="closeSidebar">{{ t('nav.marketplace') }}</router-link>
            <router-link class="nav-link" to="/buyer/orders" @click="closeSidebar">{{ t('nav.orders') }}</router-link>
            <router-link class="nav-link" to="/finance/subscriptions" @click="closeSidebar">Subscription</router-link>
            <router-link class="nav-link" to="/knowledge" @click="closeSidebar">{{ t('nav.knowledge') }}</router-link>
            <router-link class="nav-link" to="/farmer/announcements" @click="closeSidebar">{{ t('nav.announcements') }}</router-link>
          </template>

          <template v-else-if="auth.user?.role === 'provider'">
            <router-link class="nav-link" to="/provider" @click="closeSidebar">{{ t('nav.dashboard') }}</router-link>
            <router-link class="nav-link" to="/provider/services" @click="closeSidebar">My Services</router-link>
            <router-link class="nav-link" to="/finance/subscriptions" @click="closeSidebar">Subscription</router-link>
            <router-link class="nav-link" to="/provider/profile" @click="closeSidebar">{{ t('nav.profile') }}</router-link>
            <router-link class="nav-link" to="/marketplace" @click="closeSidebar">{{ t('nav.marketplace') }}</router-link>
          </template>

          <template v-else-if="auth.user?.role === 'agrodealer'">
            <router-link class="nav-link" to="/agrodealer" @click="closeSidebar">{{ t('nav.dashboard') }}</router-link>
            <router-link class="nav-link" to="/agrodealer/inputs" @click="closeSidebar">My Inputs</router-link>
            <router-link class="nav-link" to="/finance/subscriptions" @click="closeSidebar">Subscription</router-link>
            <router-link class="nav-link" to="/marketplace" @click="closeSidebar">{{ t('nav.marketplace') }}</router-link>
          </template>

          <template v-else-if="auth.user?.role === 'processor'">
            <router-link class="nav-link" to="/processor" @click="closeSidebar">{{ t('nav.dashboard') }}</router-link>
            <router-link class="nav-link" to="/marketplace" @click="closeSidebar">Source produce</router-link>
            <router-link class="nav-link" to="/processor/orders" @click="closeSidebar">Purchase orders</router-link>
            <router-link class="nav-link" to="/processor/listings" @click="closeSidebar">Sell listings</router-link>
            <router-link class="nav-link" to="/processor/listings/create" @click="closeSidebar">+ List goods</router-link>
            <router-link class="nav-link" to="/processor/sales" @click="closeSidebar">Sales orders</router-link>
            <router-link class="nav-link" to="/processor/wallet" @click="closeSidebar">Wallet</router-link>
            <router-link class="nav-link" to="/finance/subscriptions" @click="closeSidebar">Subscription</router-link>
            <router-link class="nav-link" to="/knowledge" @click="closeSidebar">{{ t('nav.knowledge') }}</router-link>
          </template>

          <template v-else-if="auth.user?.role === 'transporter'">
            <router-link class="nav-link" to="/transporter" @click="closeSidebar">{{ t('nav.dashboard') }}</router-link>
            <router-link class="nav-link" to="/transporter/trucks" @click="closeSidebar">My trucks</router-link>
            <router-link class="nav-link" to="/finance/subscriptions" @click="closeSidebar">Subscription</router-link>
            <router-link class="nav-link" to="/marketplace" @click="closeSidebar">{{ t('nav.marketplace') }}</router-link>
          </template>

          <template v-else-if="auth.user?.role === 'financier' || auth.user?.role === 'financial_institution'">
            <router-link class="nav-link" to="/finance/institution" @click="closeSidebar">Institution portal</router-link>
            <router-link class="nav-link" to="/finance/subscriptions" @click="closeSidebar">Subscription</router-link>
          </template>
          <template v-else-if="auth.user?.role === 'educator'">
            <router-link class="nav-link" to="/knowledge/educator" @click="closeSidebar">Educator portal</router-link>
            <router-link class="nav-link" to="/finance/subscriptions" @click="closeSidebar">Subscription</router-link>
          </template>
          <template v-else-if="auth.user?.role === 'admin'">
            <router-link class="nav-link" to="/admin/subscriptions" @click="closeSidebar">Subscriptions</router-link>
            <router-link class="nav-link" to="/admin" @click="closeSidebar">{{ t('nav.dashboard') }}</router-link>
            <router-link class="nav-link" to="/admin/users" @click="closeSidebar">{{ t('nav.users') }}</router-link>
            <router-link class="nav-link" to="/admin/listings" @click="closeSidebar">{{ t('nav.listings') }}</router-link>
            <router-link class="nav-link" to="/admin/orders" @click="closeSidebar">{{ t('nav.orders') }}</router-link>
            <router-link class="nav-link" to="/admin/payments" @click="closeSidebar">{{ t('nav.payments') }}</router-link>
            <router-link class="nav-link" to="/admin/withdrawals" @click="closeSidebar">{{ t('nav.withdrawals') }}</router-link>
            <router-link class="nav-link" to="/admin/reports" @click="closeSidebar">{{ t('nav.reports') }}</router-link>
            <router-link class="nav-link" to="/admin/analytics" @click="closeSidebar">{{ t('nav.platformAnalytics') }}</router-link>
            <router-link class="nav-link" to="/admin/government-review" @click="closeSidebar">{{ t('nav.govReview') }}</router-link>
            <router-link class="nav-link" to="/knowledge" @click="closeSidebar">{{ t('nav.knowledge') }}</router-link>
            <router-link class="nav-link" to="/farmer/announcements" @click="closeSidebar">{{ t('nav.announcements') }}</router-link>
            <router-link class="nav-link" to="/farmer/subsidies" @click="closeSidebar">{{ t('nav.subsidies') }}</router-link>
          </template>

          <template v-else>
            <router-link class="nav-link" to="/" @click="closeSidebar">Home</router-link>
            <router-link class="nav-link" to="/marketplace" @click="closeSidebar">{{ t('nav.marketplace') }}</router-link>
            <router-link class="nav-link" to="/knowledge" @click="closeSidebar">{{ t('nav.knowledge') }}</router-link>
          </template>
        </nav>
      </aside>

      <main class="flex-1 p-3 sm:p-6 w-full max-w-6xl mx-auto min-w-0">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import AppLogo from "../components/AppLogo.vue";
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useI18n } from "../i18n";

const auth = useAuthStore();
const router = useRouter();
const { t, locale, setLocale, available } = useI18n();
const sidebarOpen = ref(false);

function closeSidebar() {
  sidebarOpen.value = false;
}

function onLocale(code) {
  setLocale(code);
}

function logout() {
  auth.logout();
  router.push("/login");
}
</script>

<style scoped>
.nav-section {
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #9ca3af;
  margin-top: 1rem;
  margin-bottom: 0.25rem;
  padding: 0 0.5rem;
}
.nav-link {
  display: block;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  color: #374151;
  text-decoration: none;
}
.nav-link:hover {
  background: #f0fdf4;
  color: #15803d;
}
.nav-link.router-link-active {
  background: #dcfce7;
  color: #166534;
  font-weight: 600;
}
</style>
