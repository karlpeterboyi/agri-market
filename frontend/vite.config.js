import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

const target = process.env.VITE_PROXY_TARGET || 'http://127.0.0.1:8000'

// Proxy /api and /storage → Laravel (required for listing photos on Vite dev server)
export default defineConfig({
  plugins: [vue()],
  server: {
    host: true,
    port: 5173,
    allowedHosts: true, // <--- Added to allow Cloudflare Tunnel connections
    proxy: {
      '/api': {
        target,
        changeOrigin: true,
        secure: false,
      },
      '/storage': {
        target,
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
