import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import ReactivityTransform from '@vue-macros/reactivity-transform/vite';

export default defineConfig({
  plugins: [
    ReactivityTransform(),
    laravel({
      input: [
        'resources/vendor/Magnifier/js/vue.js',
        'resources/vendor/Magnifier/sass/app.scss',
      ],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
      reactivityTransform: true
    }),
  ],
  build: {
    outDir: 'public/vendor/Magnifier',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        vue: 'resources/vendor/Magnifier/js/vue.js',
        css: 'resources/vendor/Magnifier/sass/app.scss',
      },
    },
  },
  server: {
    host: '0.0.0.0',
    hmr: {
      host: 'localhost'
    }
  },
});
