import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import vue2 from '@vitejs/plugin-vue2';
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
        vue2({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        react(),
        run([
            {
                name: "typescript transform",
                run: ["php", "artisan", "typescript:transform"],
                pattern: ["app/**/*Data.php"],
            },
            {
                name: "sync types to astro",
                run: [
                    "cp",
                    "resources/js/types/generated.d.ts",
                    "../wahastro/src/types/laravel.d.ts",
                ],
                pattern: [
                    "app/**/*Data.php",
                    "resources/js/types/generated.d.ts",
                ],
                delay: 500,
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
