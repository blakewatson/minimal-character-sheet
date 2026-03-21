import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
  ],
  build: {
    outDir: 'dist',
    manifest: true,
    emptyOutDir: true,
    rollupOptions: {
      input: {
        app: 'js/app.js',
        dashboard: 'js/dashboard.js',
        print: 'js/print.js',
        styles: 'css/app.css',
      },
    },
  },
  publicDir: 'public',
});
