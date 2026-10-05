import "./main.css";
import { createApp } from "vue";
import { createPinia } from "pinia";

import App from "./App.vue";
import router from "./router";
import "leaflet/dist/leaflet.css";
import { useAuthStore } from "./stores/auth";

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

const auth = useAuthStore(pinia);

async function bootstrap() {
  if (auth.token) {
    try {
      await auth.loadProfile();
    } catch (error) {
      console.error("Session restoration failed:", error);
      auth.logout();
    }
  }

  app.mount("#app");
}

bootstrap();
