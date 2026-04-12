import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vite';

const isWatch = process.argv.includes('--watch');

export default defineConfig(({ mode }) => {
  const isDev = mode === 'development';

  return {
    plugins: [
      vue({
        features: {
          prodDevtools: isDev,
        },
      }),
      tailwindcss(),
    ],
    resolve: {
      extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    },
    build: {
      outDir: 'dist',
      manifest: true,
      minify: !isDev,
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
  };
});
