import react from "@vitejs/plugin-react";
import vue2 from "@vitejs/plugin-vue2";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

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
            detectTls: true,
        }),
        vue2(),
        react(),
    ],
});
