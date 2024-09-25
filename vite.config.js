import react from "@vitejs/plugin-react";
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
        react(),
    ],
});
