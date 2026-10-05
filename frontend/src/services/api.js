import axios from "axios";

/**
 * API base URL resolution:
 * 1) VITE_API_URL if set (production or explicit)
 * 2) In browser dev: same-origin "/api" (Vite proxies to Laravel)
 * 3) Fallback localhost for SSR/tools
 */
function resolveBaseURL() {
  const envUrl = import.meta.env.VITE_API_URL;
  if (envUrl && String(envUrl).trim() !== "") {
    return String(envUrl).replace(/\/$/, "");
  }
  if (typeof window !== "undefined") {
    // Same host as the page → works with Vite proxy
    return `${window.location.origin}/api`;
  }
  return "http://127.0.0.1:8000/api";
}

const api = axios.create({
  baseURL: resolveBaseURL(),
  timeout: 60000,
  headers: {
    Accept: "application/json",
  },
  // Preserve multipart boundary for FormData
  transformRequest: [
    (data, headers) => {
      if (typeof FormData !== "undefined" && data instanceof FormData) {
        if (headers) {
          delete headers["Content-Type"];
          delete headers["content-type"];
          if (headers.common) delete headers.common["Content-Type"];
        }
      } else if (data && typeof data === "object" && !(typeof FormData !== "undefined" && data instanceof FormData)) {
        // let axios JSON-stringify objects
      }
      return data;
    },
    ...(axios.defaults.transformRequest || []),
  ],
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  if (typeof FormData !== "undefined" && config.data instanceof FormData) {
    if (config.headers) {
      delete config.headers["Content-Type"];
      delete config.headers["content-type"];
    }
  } else if (config.data && typeof config.data === "object" && !(config.data instanceof FormData)) {
    config.headers = config.headers || {};
    if (!config.headers["Content-Type"] && !config.headers["content-type"]) {
      config.headers["Content-Type"] = "application/json";
    }
  }

  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
    }
    return Promise.reject(error);
  }
);

export function getApiBaseURL() {
  return api.defaults.baseURL;
}

export default api;
