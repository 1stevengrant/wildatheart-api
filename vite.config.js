import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { run } from "vite-plugin-run";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/local.tsx",
                "resources/css/app.css",
                "resources/js/cp.js",
                "resources/css/cp.css",
            ],
            refresh: true,
        }),
        vue(),
        react(),
        run([
            {
                name: "typescript transform",
                run: ["php", "artisan", "typescript:transform"],
                pattern: ["app/**/*Data.php"],
            },
        ]),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
});
