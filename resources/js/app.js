/**
 * NAMA FILE    : app.js
 * LOKASI       : resources/js/app.js
 * FUNGSI       : Titik Masuk Utama Runtime JavaScript Front-End (Alpine.js Initialization)
 * DESKRIPSI    : Menginisialisasi framework Alpine.js yang bertindak sebagai mesin interaktivitas
 *                komponen antarmuka pengguna (UI) ringan pada sistem CAVA.
 * CARA KERJA   : 1. Mengimpor core framework Alpine.js dari dependensi node_modules.
 *                2. Menautkan objek Alpine ke properti global `window.Alpine` agar dapat
 *                   diakses oleh Alpine DevTools dan skrip kustom halaman jika diperlukan.
 *                3. Mengeksekusi `Alpine.start()` yang mengaktifkan MutationObserver pada DOM
 *                   browser untuk memindai dan menghidupkan direktif reaktif (x-data, x-show,
 *                   x-on, x-transition, x-model).
 */

// =========================================================================================
// 1. IMPOR LIBRARY ALPINE.JS
// =========================================================================================
// SYNTAX     : import Alpine from 'alpinejs';
// CARA KERJA : Mengimpor objek default Alpine dari paket npm 'alpinejs'.
import Alpine from 'alpinejs';

// =========================================================================================
// 2. EXPOSE KE GLOBAL WINDOW BROWSER
// =========================================================================================
// SYNTAX     : window.Alpine = Alpine;
// CARA KERJA : Menyimpan referensi Alpine ke objek global `window` di browser.
// TUJUAN     : Memungkinkan integrasi dengan ekstensi debugging browser (Alpine DevTools)
//              serta memungkinkan pemanggilan fungsi utilitas Alpine dari tag <script> Blade.
window.Alpine = Alpine;

// =========================================================================================
// 3. BOOTSTRAP & AKTIVASI ALPINE RUNTIME
// =========================================================================================
// SYNTAX     : Alpine.start();
// CARA KERJA : Memulai lifecycle Alpine:
//              - Memindai seluruh dokumen HTML dari tag <body>.
//              - Menginisialisasi state lokal komponen (seperti modal x-data="{ open: false }").
//              - Memasang event listener klik, submit form, dan transisi animasi mikro Bento.
Alpine.start();
