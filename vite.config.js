import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

const isWatch = process.argv.includes('--watch');

export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
  ],
  resolve: {
    extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
  },
  build: {
    outDir: 'dist',
    manifest: true,
    emptyOutDir: !isWatch,
    watch: isWatch ? { exclude: ['dist/**'] } : null,
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
