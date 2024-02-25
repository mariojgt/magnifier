import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import ReactivityTransform from '@vue-macros/reactivity-transform/vite'

export default defineConfig({
    plugins: [
        ReactivityTransform(),
        laravel([
            'resources/vendor/Magnifier/js/app.js',
            'resources/vendor/Magnifier/js/vue.js',
            'resources/vendor/Magnifier/sass/app.scss',
        ]),
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
                app: 'resources/vendor/Magnifier/js/app.js',
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
