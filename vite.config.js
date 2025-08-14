import react from "@vitejs/plugin-react";
import vue2 from "@vitejs/plugin-vue2";
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
                "resources/api-docs/openapi.yaml",
            ],
            refresh: true,
            detectTls: true,
        }),
        vue2(),
        react(),
        run([
            {
                name: "typescript transform",
                run: ["php", "artisan", "typescript:transform"],
                pattern: ["app/**/*Data.php"],
            },
        ]),
    ],
});
