import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vuetify()
  ],
  build: {
    outDir: 'RefundsDashboard',
    sourcemap: true,
    rollupOptions: {
      output: {
        entryFileNames: "RefundsDashboard.js",
        assetFileNames: "RefundsDashboard.css",
        chunkFileNames: "RefundsDashboard.js",
        manualChunks: undefined
      }
    }
  }
})