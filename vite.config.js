import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    resolve: {
        alias: {
            'lodash.isequal': 'fast-deep-equal',
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Inertia entry (mantém Blade intacto)
                'resources/js/inertia.js',
            ],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
});
