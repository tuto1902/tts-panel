import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    optimizeDeps: {
        include: ['animate.css']
    },
    build: {
        rollupOptions: {
            external: ['/node_modules/animate.css/animate.css']
        }
    }
});
