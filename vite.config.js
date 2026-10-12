/**
 * NAMA FILE    : vite.config.js
 * LOKASI       : vite.config.js
 * FUNGSI       : Konfigurasi Build Tool, Asset Bundler & Hot Module Replacement (Vite)
 * DESKRIPSI    : Mengonfigurasi engine Vite sebagai bundler modern pengganti Laravel Mix (Webpack).
 *                Bertanggung jawab mengompilasi berkas CSS Tailwind dan berkas JavaScript Alpine
 *                serta menyediakannya saat local development (`npm run dev`) maupun production build (`npm run build`).
 * CARA KERJA   : 1. Memuat helper defineConfig untuk autocomplete dan type safety.
 *                2. Mendaftarkan plugin resmi laravel-vite-plugin.
 *                3. Memetakan entry point input asset (app.css & app.js).
 *                4. Mengaktifkan fitur auto-refresh browser saat file Blade/PHP/CSS dimodifikasi.
 */

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// =========================================================================================
// DEFINISI KONFIGURASI UTAMA VITE
// =========================================================================================
// SYNTAX     : export default defineConfig({ ... })
// CARA KERJA : Mengekspor objek konfigurasi Vite sebagai modul ES Module (ESM) standar.
// FITUR JS   : 'defineConfig' menyediakan IntelliSense dan validasi konfigurasi tanpa perlu JSDoc rumit.
export default defineConfig({
    plugins: [
        // =====================================================================================
        // PLUGIN LARAVEL VITE (INTEGRASI ASSET FRAMEWORK)
        // =====================================================================================
        // SYNTAX     : laravel({ input: [...], refresh: true })
        // CARA KERJA : Menghubungkan direktori Laravel dengan dev server Vite (port 5173).
        //              - input: Array jalur file utama yang menjadi titik masuk kompilasi asset:
        //                * 'resources/css/app.css' -> Diproses oleh PostCSS dan plugin Tailwind CSS.
        //                * 'resources/js/app.js'   -> Menginisialisasi runtime Alpine.js browser.
        //              - refresh: Boolean true mengaktifkan 'Full Page Reload' otomatis di browser
        //                setiap kali pengembang menyimpan perubahan pada file template Blade (resources/views/**).
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
