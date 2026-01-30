import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/app.jsx", // React app
                "resources/css/filament/clinika/theme.css", // Filament theme
            ],
            refresh: true,
        }),
        react(),
    ],

    build: {
        rollupOptions: {
            input: {
                app: "resources/js/app.jsx",
                filament: "resources/css/filament/clinika/theme.css",
            },
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.endsWith(".css")) {
                        return "css/filament/style.css";
                    }
                    return "assets/[name]-[hash][extname]";
                },
            },
        },
    },
});
