import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            "@": "/resources/js",
        },
    },
    server: {
        host: "localhost",
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: "localhost",
            protocol: "ws",
        },
        proxy: {
            "/api": {
                target: "http://web:80",
                changeOrigin: true,
            },
        },
    },
    preview: {
        port: 5173,
        host: "0.0.0.0",
    },
});
