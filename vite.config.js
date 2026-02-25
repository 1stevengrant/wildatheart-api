import { resolve } from "node:path";

import statamic from "@statamic/cms/vite-plugin";
import react from "@vitejs/plugin-react";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";
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
        statamic(),
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
        jsx: "automatic",
    },
    resolve: {
        alias: {
            "ziggy-js": resolve(__dirname, "vendor/tightenco/ziggy"),
        },
    },
});
