import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/pages/welcome.css',
                'resources/css/pages/guest.css',
                'resources/css/print/seat-report.css',
                'resources/css/print/travel-ticket.css',
                'resources/css/print/parcel-ticket.css',
            ],
            refresh: true,
        }),
    ],
});
