import { ref, computed } from "vue";
import en from "./en";
import sw from "./sw";

const messages = { en, sw };
const locale = ref(localStorage.getItem("locale") || "en");

export function useI18n() {
  const t = (key) => {
    const parts = key.split(".");
    let node = messages[locale.value] || messages.en;
    for (const p of parts) {
      node = node?.[p];
      if (node === undefined) {
        // fallback to English
        let fb = messages.en;
        for (const q of parts) fb = fb?.[q];
        return fb !== undefined ? fb : key;
      }
    }
    return typeof node === "string" ? node : key;
  };

  const setLocale = (code) => {
    locale.value = code;
    localStorage.setItem("locale", code);
  };

  return {
    t,
    locale: computed(() => locale.value),
    setLocale,
    available: [
      { code: "en", label: "English" },
      { code: "sw", label: "Kiswahili" },
    ],
  };
}

export { locale };
