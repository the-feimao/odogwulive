import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin-legacy.js',
                'resources/js/pwa-register.js',
                'resources/js/checkout.js',
                'resources/js/user-area.js',
                'resources/js/public-social.js',
                'resources/js/contact-map.js',
                'resources/js/scanner-pwa.js',
            ],
            refresh: true,
        }),
    ],
});
